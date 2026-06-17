(function () {
    const page = document.querySelector('.lei-rep-page');
    if (!page) return;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const toast = document.getElementById('leiRepToast');

    const CHART_W = 600;
    const CHART_H = 200;
    const PAD_X = 48;
    const PAD_Y = 28;
    const BASE_Y = CHART_H - PAD_Y;
    const PLOT_H = CHART_H - PAD_Y * 2;

    function showToast(msg) {
        if (!toast) return;
        toast.textContent = msg;
        toast.hidden = false;
        setTimeout(() => { toast.hidden = true; }, 3200);
    }

    async function postJson(url, body) {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify(body || {}),
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(data.message || 'Request failed');
        return data;
    }

    function toY(v) {
        return BASE_Y - ((v / 100) * PLOT_H);
    }

    function buildStepPaths(points) {
        const stepX = (CHART_W - PAD_X * 2) / Math.max(1, points.length - 1);
        let current = '';
        let prev = '';
        let area = '';
        const dots = [];

        points.forEach((p, i) => {
            const x = Math.round(PAD_X + i * stepX);
            const yc = Math.round(toY(p.current_value));
            const yp = Math.round(toY(p.previous_value));
            dots.push({ x, y: yc });
            if (i === 0) {
                current += `M${x},${yc} `;
                prev += `M${x},${yp} `;
                area += `M${x},${yc} `;
            } else {
                current += `H${x} V${yc} `;
                prev += `H${x} V${yp} `;
                area += `H${x} V${yc} `;
            }
        });

        if (points.length) {
            const lastX = Math.round(PAD_X + (points.length - 1) * stepX);
            area += `L${lastX},${BASE_Y} L${PAD_X},${BASE_Y} Z`;
        }

        return {
            current: current.trim(),
            prev: prev.trim(),
            area: area.trim(),
            dots,
        };
    }

    function updateChart(points) {
        if (!points?.length) return;
        const paths = buildStepPaths(points);
        const areaEl = document.getElementById('leiRepChartArea');
        const curEl = document.getElementById('leiRepChartCurrent');
        const prevEl = document.getElementById('leiRepChartPrev');
        if (areaEl) areaEl.setAttribute('d', paths.area);
        if (curEl) curEl.setAttribute('d', paths.current);
        if (prevEl) prevEl.setAttribute('d', paths.prev);

        const svg = document.getElementById('leiRepChartSvg');
        if (svg) {
            svg.querySelectorAll('.lei-rep-chart-dot').forEach((el) => el.remove());
            paths.dots.forEach((d) => {
                const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
                circle.setAttribute('class', 'lei-rep-chart-dot');
                circle.setAttribute('cx', d.x);
                circle.setAttribute('cy', d.y);
                circle.setAttribute('r', '5');
                svg.appendChild(circle);
            });
        }
    }

    function updateSla(d) {
        if (!d) return;
        const pct = document.getElementById('leiRepSlaPercent');
        const ring = document.getElementById('leiRepDonutRing');
        if (pct && d.sla_percent != null) pct.textContent = `${d.sla_percent}%`;
        if (ring && d.sla_percent != null) {
            ring.setAttribute('stroke-dashoffset', String(289 * (1 - d.sla_percent / 100)));
        }
        const crit = document.getElementById('leiRepCritical');
        const warn = document.getElementById('leiRepWarnings');
        const res = document.getElementById('leiRepResolution');
        if (crit) crit.textContent = String(d.critical_incidents ?? 0);
        if (warn) warn.textContent = String(d.warning_alerts ?? 0);
        if (res && d.resolution_time) res.textContent = d.resolution_time;
    }

    function bindRowActions(row) {
        row.querySelector('[data-download]')?.addEventListener('click', () => {
            window.location.href = page.dataset.downloadUrl.replace('__ID__', row.dataset.reportId);
        });
        row.querySelector('[data-delete]')?.addEventListener('click', async () => {
            const id = row.dataset.reportId;
            try {
                const res = await fetch(page.dataset.deleteUrl.replace('__ID__', id), {
                    method: 'DELETE',
                    headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Delete failed');
                row.remove();
                showToast(data.message);
            } catch (e) {
                showToast(e.message);
            }
        });
        row.querySelector('[data-regenerate]')?.addEventListener('click', async () => {
            const id = row.dataset.reportId;
            try {
                const data = await postJson(page.dataset.regenerateUrl.replace('__ID__', id), {});
                showToast(data.message);
                window.location.reload();
            } catch (e) {
                showToast(e.message);
            }
        });
    }

    document.querySelectorAll('.lei-rep-row[data-report-id]').forEach(bindRowActions);

    document.querySelectorAll('.lei-rep-tab').forEach((tab) => {
        tab.addEventListener('click', () => {
            const url = new URL(page.dataset.filterUrl, window.location.origin);
            url.searchParams.set('tab', tab.dataset.tab);
            window.location.href = url.toString();
        });
    });

    document.getElementById('leiRepRefresh')?.addEventListener('click', async () => {
        try {
            const data = await postJson(page.dataset.refreshUrl, {});
            showToast(data.message);
            data.dashboard?.stats?.forEach((s) => {
                const card = document.querySelector(`[data-stat-key="${s.stat_key}"]`);
                if (!card) return;
                const val = card.querySelector('.lei-rep-stat-value');
                const trend = card.querySelector('.lei-rep-trend');
                if (val) val.textContent = s.value;
                if (trend && s.trend_text) {
                    trend.textContent = s.trend_text;
                    trend.className = `lei-rep-trend lei-rep-trend--${s.trend_tone || 'muted'}`;
                }
            });
            if (data.dashboard?.chart) updateChart(data.dashboard.chart);
            updateSla(data.dashboard);
        } catch (e) {
            showToast(e.message);
        }
    });

    document.getElementById('leiRepPeriodBtn')?.addEventListener('click', async () => {
        const periods = ['Last 7 Days', 'Last 30 Days', 'Last 90 Days'];
        const label = document.getElementById('leiRepPeriodLabel');
        const current = label?.textContent || 'Last 30 Days';
        const next = periods[(periods.indexOf(current) + 1) % periods.length];
        try {
            const data = await postJson(page.dataset.periodUrl, { period: next });
            if (label) label.textContent = data.period || next;
            showToast(data.message);
        } catch (e) {
            showToast(e.message);
        }
    });

    document.getElementById('leiRepScheduled')?.addEventListener('change', async (e) => {
        try {
            const data = await postJson(page.dataset.scheduledUrl, {});
            showToast(data.message);
        } catch (err) {
            e.target.checked = !e.target.checked;
            showToast(err.message);
        }
    });

    document.getElementById('leiRepGenerate')?.addEventListener('click', async () => {
        try {
            const data = await postJson(page.dataset.generateUrl, {
                date_range: document.getElementById('leiRepDateRange')?.value,
                category: document.getElementById('leiRepCategory')?.value,
                entity: document.getElementById('leiRepEntity')?.value,
            });
            showToast(data.message);
            if (data.report) window.location.reload();
        } catch (e) {
            showToast(e.message);
        }
    });

    document.getElementById('leiRepViewAll')?.addEventListener('click', () => {
        showToast('Full report history — demo mode.');
    });

    document.querySelectorAll('.lei-rep-export-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            showToast(`Downloading ${btn.dataset.export || 'export'}...`);
        });
    });
})();
