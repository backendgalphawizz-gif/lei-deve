(function () {
    const page = document.querySelector('.lei-sec-page');
    if (!page) return;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const toast = document.getElementById('leiSecToast');
    const modal = document.getElementById('leiSecIpModal');

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

    function applyDashboard(dashboard) {
        if (!dashboard) return;

        dashboard.stats?.forEach((stat) => {
            const card = document.querySelector(`[data-stat-key="${stat.stat_key}"]`);
            if (!card) return;
            const valueEl = card.querySelector('.lei-sec-stat-value');
            const badgeEl = card.querySelector('.lei-sec-stat-badge');
            if (valueEl) valueEl.textContent = stat.value;
            if (badgeEl) {
                badgeEl.textContent = stat.badge_text || '';
                badgeEl.className = `lei-sec-stat-badge lei-sec-stat-badge--${stat.badge_tone || 'muted'}`;
                badgeEl.hidden = !stat.badge_text;
            } else if (stat.badge_text) {
                const span = document.createElement('span');
                span.className = `lei-sec-stat-badge lei-sec-stat-badge--${stat.badge_tone || 'muted'}`;
                span.textContent = stat.badge_text;
                card.appendChild(span);
            }
        });

        const crit = document.getElementById('leiSecCriticalCount');
        const warn = document.getElementById('leiSecWarningCount');
        if (crit) crit.textContent = `Critical (${dashboard.critical_count})`;
        if (warn) warn.textContent = `Warning (${dashboard.warning_count})`;

        const overlay = document.getElementById('leiSecOverlayStatus');
        if (overlay && dashboard.overlay_status) {
            overlay.textContent = `Live Sync: ${dashboard.overlay_status}`;
        }

        const synced = document.getElementById('leiSecLastSynced');
        if (synced && dashboard.last_synced) {
            synced.textContent = `Last sync: ${dashboard.last_synced}`;
        }

        dashboard.summaries?.forEach((s) => {
            const card = document.querySelector(`[data-summary-title="${s.title}"]`);
            if (!card) return;
            const primary = card.querySelector('.lei-sec-summary-primary');
            const secondary = card.querySelector('.lei-sec-summary-secondary');
            if (primary) primary.textContent = s.line_primary;
            if (secondary) secondary.textContent = s.line_secondary;
        });
    }

    function bindDeleteIp(btn) {
        btn.addEventListener('click', async () => {
            const row = btn.closest('.lei-sec-ip-row');
            const id = row?.dataset.ruleId;
            if (!id || !(await window.leiConfirm({ title: 'Remove IP Range', message: 'Remove this IP range?', button: 'Remove', variant: 'danger' }))) return;
            const url = page.dataset.ipDeleteUrl.replace('__ID__', id);
            try {
                const res = await fetch(url, {
                    method: 'DELETE',
                    headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok) throw new Error(data.message || 'Delete failed');
                row.remove();
                applyDashboard(data.dashboard);
                showToast(data.message);
            } catch (err) {
                showToast(err.message);
            }
        });
    }

    function appendIpRow(rule) {
        const body = document.getElementById('leiSecIpBody');
        if (!body) return;
        const row = document.createElement('div');
        row.className = 'lei-sec-ip-row';
        row.dataset.ruleId = rule.id;
        row.innerHTML = `
            <span><span class="lei-sec-ip-pill lei-sec-ip-pill--${rule.status_tone}">${rule.status}</span></span>
            <span class="lei-sec-mono">${rule.ip_range}</span>
            <span>${rule.location}</span>
            <span>${rule.context}</span>
            <span>
                <button type="button" class="lei-sec-trash" data-delete-ip aria-label="Delete">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                </button>
            </span>`;
        body.appendChild(row);
        bindDeleteIp(row.querySelector('[data-delete-ip]'));
    }

    function bindIncidentAction(btn) {
        btn.addEventListener('click', async () => {
            const row = btn.closest('.lei-sec-inc-row');
            const id = row?.dataset.incidentId;
            const action = btn.dataset.action;
            if (!id || !action) return;

            const url = page.dataset.incidentUrl.replace('__ID__', id);
            try {
                const data = await postJson(url, { action });
                if (data.removed) {
                    row.remove();
                } else if (data.incident) {
                    const statusText = row.querySelector('.lei-sec-inc-status-text');
                    const statusDot = row.querySelector('.lei-sec-dot');
                    if (statusText) statusText.textContent = data.incident.current_status;
                    if (statusDot) {
                        statusDot.className = `lei-sec-dot lei-sec-dot--${data.incident.status_tone}`;
                    }
                    btn.textContent = data.incident.action_label;
                    btn.className = `lei-sec-inc-btn lei-sec-inc-btn--${data.incident.action_style}`;
                    if (data.incident.action_key) btn.dataset.action = data.incident.action_key;
                }
                applyDashboard(data.dashboard);
                showToast(data.message);
            } catch (err) {
                showToast(err.message);
            }
        });
    }

    page.querySelectorAll('[data-delete-ip]').forEach(bindDeleteIp);
    page.querySelectorAll('[data-incident-action]').forEach(bindIncidentAction);

    document.getElementById('leiSecSync')?.addEventListener('click', async () => {
        try {
            const data = await postJson(page.dataset.syncUrl);
            applyDashboard(data.dashboard);
            showToast(data.message);
        } catch (err) {
            showToast(err.message);
        }
    });

    document.getElementById('leiSecUpdatePolicy')?.addEventListener('click', async () => {
        try {
            const data = await postJson(page.dataset.policyUrl, {
                mfa_enabled: document.getElementById('leiSecMfa')?.checked ?? true,
                session_timeout: document.getElementById('leiSecSession')?.value,
                max_login_attempts: document.getElementById('leiSecMaxAttempts')?.value,
                mfa_adoption: document.getElementById('leiSecMfaAdoption')?.value,
                failed_login_count: parseInt(document.getElementById('leiSecFailedLogins')?.value, 10) || 0,
            });
            applyDashboard(data.dashboard);
            showToast(data.message);
        } catch (err) {
            showToast(err.message);
        }
    });

    document.getElementById('leiSecClearInfo')?.addEventListener('click', async () => {
        try {
            const data = await postJson(page.dataset.clearInfoUrl);
            document.querySelectorAll('.lei-sec-inc-row[data-severity="info"]').forEach((r) => r.remove());
            applyDashboard(data.dashboard);
            showToast(data.message);
        } catch (err) {
            showToast(err.message);
        }
    });

    document.getElementById('leiSecApplyFilter')?.addEventListener('click', () => {
        const severity = document.getElementById('leiSecSeverityFilter')?.value || 'all';
        const url = new URL(page.dataset.filterUrl, window.location.origin);
        url.searchParams.set('severity', severity);
        window.location.href = url.toString();
    });

    document.getElementById('leiSecNewRange')?.addEventListener('click', () => {
        if (modal) modal.hidden = false;
    });

    modal?.querySelectorAll('[data-close-modal]').forEach((el) => {
        el.addEventListener('click', () => { modal.hidden = true; });
    });

    document.getElementById('leiSecIpForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        const payload = Object.fromEntries(new FormData(form).entries());
        try {
            const data = await postJson(page.dataset.ipStoreUrl, payload);
            appendIpRow(data.rule);
            applyDashboard(data.dashboard);
            form.reset();
            modal.hidden = true;
            showToast(data.message);
        } catch (err) {
            showToast(err.message);
        }
    });

})();
