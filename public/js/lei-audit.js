(function () {
    const page = document.querySelector('.lei-audit-page');
    if (!page) return;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const toast = document.getElementById('leiAuditToast');
    const modal = document.getElementById('leiAuditModal');
    const modalBody = document.getElementById('leiAuditModalBody');
    const filterUrl = page.dataset.filterUrl;
    const exportBase = page.dataset.exportUrl;
    const detailTpl = page.dataset.detailUrl;
    const syncUrl = page.dataset.syncUrl;

    function showToast(msg) {
        if (!toast) return;
        toast.textContent = msg;
        toast.hidden = false;
        setTimeout(() => { toast.hidden = true; }, 3200);
    }

    function applyFilters() {
        const severity = document.getElementById('leiAuditSeverity')?.value || 'all';
        const category = document.getElementById('leiAuditCategory')?.value || 'all';
        const url = new URL(filterUrl, window.location.origin);
        url.searchParams.set('severity', severity);
        url.searchParams.set('category', category);
        window.location.href = url.toString();
    }

    function updateExportLink() {
        const link = document.getElementById('leiAuditExport');
        if (!link) return;
        const severity = document.getElementById('leiAuditSeverity')?.value || 'all';
        const category = document.getElementById('leiAuditCategory')?.value || 'all';
        const url = new URL(exportBase, window.location.origin);
        url.searchParams.set('severity', severity);
        url.searchParams.set('category', category);
        link.href = url.toString();
    }

    document.getElementById('leiAuditSeverity')?.addEventListener('change', () => {
        updateExportLink();
        applyFilters();
    });

    document.getElementById('leiAuditCategory')?.addEventListener('change', () => {
        updateExportLink();
        applyFilters();
    });

    document.getElementById('leiAuditDateBtn')?.addEventListener('click', () => {
        showToast('Date range picker: using seeded range for demo.');
    });

    async function openDetail(entryId) {
        const url = detailTpl.replace('__ID__', entryId);
        const res = await fetch(url, { headers: { Accept: 'application/json' } });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) {
            showToast(data.message || 'Could not load entry.');
            return;
        }
        const e = data.entry;
        modalBody.textContent = [
            `Timestamp: ${e.logged_at}`,
            `Category: ${e.category}`,
            `Actor: ${e.actor}`,
            `Action: ${e.action}`,
            `Status: ${e.status}`,
            '',
            'Changes:',
            e.changes,
        ].join('\n');
        modal.hidden = false;
    }

    document.querySelectorAll('[data-audit-view]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const id = btn.closest('.lei-audit-row')?.dataset.entryId;
            if (id) openDetail(id);
        });
    });

    document.querySelectorAll('[data-audit-info]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const id = btn.closest('.lei-audit-row')?.dataset.entryId;
            if (id) openDetail(id);
        });
    });

    document.querySelectorAll('[data-audit-menu]').forEach((btn) => {
        btn.addEventListener('click', () => {
            showToast('Row actions: Export row, Flag review, Copy hash (demo).');
        });
    });

    document.querySelectorAll('[data-close-audit-modal]').forEach((el) => {
        el.addEventListener('click', () => { modal.hidden = true; });
    });

    document.getElementById('leiAuditTelemetry')?.addEventListener('click', async () => {
        if (!syncUrl) return;
        try {
            const res = await fetch(syncUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: '{}',
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Sync failed');
            const el = document.getElementById('leiAuditSyncMs');
            if (el && data.sync_ms) el.textContent = data.sync_ms;
            showToast(data.message || 'Sync complete.');
        } catch (err) {
            showToast(err.message);
        }
    });

    updateExportLink();
})();
