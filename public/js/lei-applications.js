(function () {
    const page = document.querySelector('.lei-app-page');
    if (!page) return;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const showUrlTpl = page.dataset.showUrl || '';
    const actionUrlTpl = page.dataset.actionUrl || '';
    const detailPanel = document.getElementById('leiAppDetailPanel');
    const filterForm = document.getElementById('leiAppFilterForm');
    const filterSelected = document.getElementById('filterSelected');
    const toast = document.getElementById('leiAppToast');

    function showUrl(id) {
        if (showUrlTpl) {
            return showUrlTpl.replace('__ID__', String(id));
        }
        return window.location.pathname.replace(/\/$/, '') + '/' + id;
    }

    function actionUrl(id) {
        if (actionUrlTpl) {
            return actionUrlTpl.replace('__ID__', String(id));
        }
        return window.location.pathname.replace(/\/$/, '') + '/' + id + '/action';
    }

    function showToast(message, type) {
        if (!toast) return;
        toast.textContent = message;
        toast.className = 'lei-app-toast lei-app-toast--' + (type || 'success');
        toast.hidden = false;
        setTimeout(function () {
            toast.hidden = true;
        }, 3200);
    }

    async function loadDetail(appId, appCode) {
        if (!detailPanel || !appId) return;
        detailPanel.innerHTML = '<div class="lei-app-detail-loading">Loading...</div>';

        try {
            const res = await fetch(showUrl(appId), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!res.ok) throw new Error('Failed to load');
            const data = await res.json();
            detailPanel.innerHTML = data.html || '';
            bindDetailActions(appId);
            if (filterSelected && appCode) {
                filterSelected.value = appCode;
            }
        } catch (e) {
            detailPanel.innerHTML = '<div class="lei-app-detail-empty">Could not load details. Please refresh the page and try again.</div>';
            if (typeof console !== 'undefined') {
                console.error('Application detail load failed:', e);
            }
        }
    }

    function setActiveRow(appId) {
        document.querySelectorAll('.lei-app-row').forEach(function (row) {
            row.classList.toggle('active', parseInt(row.dataset.appId, 10) === appId);
        });
    }

    function updateRowFromApp(app) {
        const row = document.querySelector('.lei-app-row[data-app-id="' + app.id + '"]');
        if (!row || !app) return;

        const pill = row.querySelector('[data-status-pill]');
        const label = row.querySelector('[data-status-label]');
        if (pill && label) {
            pill.className = 'lei-app-status lei-app-status--' + app.status_tone;
            label.textContent = app.status_label;
        }
        const teamCell = row.querySelector('.lei-app-td--team');
        if (teamCell) teamCell.textContent = app.assigned_team || '';
    }

    function updateMetrics(stats) {
        if (!stats || !Array.isArray(stats)) return;
        stats.forEach(function (stat) {
            const el = document.querySelector('[data-stat-key="' + stat.key + '"]');
            if (el) {
                el.textContent = Number(stat.value).toLocaleString();
            }
        });
    }

    async function performAction(appId, action, team, leiNumber) {
        const body = new FormData();
        body.append('action', action);
        if (team) body.append('team', team);
        if (leiNumber) body.append('lei_number', leiNumber);
        body.append('_token', csrf);

        const res = await fetch(actionUrl(appId), {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: body,
        });

        const data = await res.json();
        if (!res.ok || !data.success) {
            throw new Error(data.message || 'Action failed');
        }
        return data;
    }

    function bindDetailActions(appId) {
        const panel = document.getElementById('leiAppDetailPanel');
        if (!panel) return;

        panel.querySelectorAll('[data-action]').forEach(function (btn) {
            btn.addEventListener('click', async function () {
                if (btn.disabled) return;
                const action = btn.dataset.action;
                let team = null;

                if (action === 'reassign') {
                    team = window.prompt('Assign to team:', 'Tier 1 Review');
                    if (!team) return;
                }

                if (action === 'reject' && !window.confirm('Reject this application?')) return;
                if (action === 'approve' && !window.confirm('Grant final approval?')) return;

                const leiInput = panel.querySelector('#lei_number_input');
                const leiNumber = leiInput ? leiInput.value.trim() : null;

                btn.disabled = true;
                try {
                    const data = await performAction(appId, action, team, leiNumber);
                    detailPanel.innerHTML = data.html;
                    bindDetailActions(appId);
                    updateRowFromApp(data.application);
                    updateMetrics(data.stats);
                    showToast(data.message, 'success');
                } catch (e) {
                    showToast(e.message || 'Something went wrong', 'error');
                    btn.disabled = false;
                }
            });
        });
    }

    document.querySelectorAll('.lei-app-row[data-app-id]').forEach(function (row) {
        row.addEventListener('click', function (e) {
            if (e.target.closest('[data-stop-prop]')) return;
            const id = parseInt(row.dataset.appId, 10);
            const code = row.dataset.appCode;
            setActiveRow(id);
            loadDetail(id, code);
        });

        row.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                row.click();
            }
        });
    });

    const initialDetail = document.querySelector('.lei-app-detail-inner[data-app-id]');
    if (initialDetail) {
        bindDetailActions(parseInt(initialDetail.dataset.appId, 10));
    }

    if (filterForm) {
        const priorityButtons = filterForm.querySelectorAll('.lei-app-priority-btn');
        const hiddenPriority = filterForm.querySelector('input[name="priority"]');

        priorityButtons.forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const value = btn.dataset.priority ?? '';
                if (hiddenPriority) hiddenPriority.value = value;
                priorityButtons.forEach(function (b) {
                    b.classList.remove('active', 'high');
                });
                btn.classList.add('active');
                if (value === 'high') btn.classList.add('high');
                filterForm.requestSubmit();
            });
        });

        filterForm.querySelectorAll('[data-auto-filter]').forEach(function (el) {
            el.addEventListener('change', function () {
                filterForm.requestSubmit();
            });
        });
    }

    const globalSearch = document.querySelector('[data-lei-global-search]');
    if (globalSearch) {
        const searchBase = globalSearch.dataset.searchUrl;
        let searchTimer;
        globalSearch.addEventListener('keydown', function (e) {
            if (e.key !== 'Enter' || !searchBase) return;
            e.preventDefault();
            const q = globalSearch.value.trim();
            const url = new URL(searchBase, window.location.origin);
            if (q) url.searchParams.set('q', q);
            window.location.href = url.toString();
        });
        globalSearch.addEventListener('input', function () {
            if (!searchBase || !filterForm) return;
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () {
                let qInput = filterForm.querySelector('input[name="q"]');
                if (!qInput) {
                    qInput = document.createElement('input');
                    qInput.type = 'hidden';
                    qInput.name = 'q';
                    filterForm.appendChild(qInput);
                }
                qInput.value = globalSearch.value.trim();
                filterForm.requestSubmit();
            }, 500);
        });
    }

    const selectAll = document.getElementById('leiSelectAll');
    if (selectAll) {
        selectAll.addEventListener('change', function () {
            document.querySelectorAll('.lei-app-row-check').forEach(function (cb) {
                cb.checked = selectAll.checked;
            });
        });
    }
})();
