(function () {
    const page = document.querySelector('.lei-sla-page');
    if (!page) return;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const toast = document.getElementById('leiSlaToast');

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

    document.getElementById('leiSlaUpdateTriggers')?.addEventListener('click', async () => {
        try {
            const data = await postJson(page.dataset.triggersUrl, {
                cpu_threshold: 85,
                ram_threshold: 90,
                disk_threshold: 95,
            });
            showToast(data.message);
        } catch (err) {
            showToast(err.message);
        }
    });

    document.getElementById('leiSlaManualTrigger')?.addEventListener('click', async () => {
        try {
            const data = await postJson(page.dataset.backupUrl);
            const last = document.getElementById('leiSlaBackupLast');
            const next = document.getElementById('leiSlaBackupNext');
            if (last) last.textContent = 'Just now';
            if (next) next.textContent = 'In 04:00:00';
            showToast(data.message);
        } catch (err) {
            showToast(err.message);
        }
    });

    document.getElementById('leiSlaClearInfo')?.addEventListener('click', async () => {
        try {
            const data = await postJson(page.dataset.clearInfoUrl);
            document.querySelectorAll('.lei-sla-pill--info').forEach((pill) => {
                pill.closest('.lei-sla-table-row')?.remove();
            });
            showToast(data.message);
        } catch (err) {
            showToast(err.message);
        }
    });

    page.querySelectorAll('.lei-sla-action-btn').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const row = btn.closest('.lei-sla-table-row');
            const id = row?.dataset.incidentId;
            if (!id) return;
            const url = page.dataset.incidentUrl.replace('__ID__', id);
            try {
                const data = await postJson(url, { action: btn.dataset.action });
                if (btn.dataset.action === 'dismiss' || btn.dataset.action === 'acknowledge') {
                    row.remove();
                }
                showToast(data.message);
            } catch (err) {
                showToast(err.message);
            }
        });
    });
})();
