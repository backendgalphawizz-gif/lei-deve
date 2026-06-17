(function () {
    const page = document.querySelector('.lei-notif-page');
    if (!page) return;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const toast = document.getElementById('leiNotifToast');

    function showToast(msg) {
        if (!toast) return;
        toast.textContent = msg;
        toast.hidden = false;
        setTimeout(() => { toast.hidden = true; }, 3200);
    }

    document.getElementById('leiNotifBrowse')?.addEventListener('click', () => {
        document.getElementById('leiNotifFile')?.click();
    });

    document.getElementById('leiNotifFile')?.addEventListener('change', (e) => {
        const label = document.getElementById('leiNotifFileLabel');
        const file = e.target.files?.[0];
        if (label) label.value = file ? file.name : '';
    });

    document.getElementById('leiNotifSendForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        const fd = new FormData(form);
        try {
            const res = await fetch(page.dataset.storeUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
                body: fd,
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(data.message || 'Failed to send');
            showToast(data.message);
            if (data.redirect) window.location.href = data.redirect;
        } catch (err) {
            showToast(err.message);
        }
    });

    document.querySelectorAll('.js-notif-edit').forEach((btn) => {
        btn.addEventListener('click', () => {
            const row = btn.closest('.lei-notif-row');
            document.getElementById('leiNotifEditId').value = btn.dataset.id;
            document.getElementById('leiNotifEditTitle').value = row?.dataset.title || '';
            document.getElementById('leiNotifEditDesc').value = row?.dataset.description || '';
            document.getElementById('leiNotifEditModal').hidden = false;
        });
    });

    document.querySelectorAll('.js-modal-close').forEach((b) => {
        b.addEventListener('click', () => {
            document.getElementById('leiNotifEditModal').hidden = true;
        });
    });

    document.getElementById('leiNotifEditForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('leiNotifEditId').value;
        const url = page.dataset.updateUrl.replace('__ID__', id);
        const fd = new FormData(e.target);
        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
                body: fd,
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(data.message || 'Update failed');
            showToast(data.message);
            if (data.redirect) window.location.href = data.redirect;
        } catch (err) {
            showToast(err.message);
        }
    });

    document.querySelectorAll('.js-notif-delete').forEach((btn) => {
        btn.addEventListener('click', async () => {
            if (!confirm('Delete this notification?')) return;
            const url = page.dataset.deleteUrl.replace('__ID__', btn.dataset.id);
            try {
                const res = await fetch(url, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok) throw new Error(data.message || 'Delete failed');
                btn.closest('.lei-notif-row')?.remove();
                const badge = document.querySelector('.lei-notif-count-badge');
                if (badge) badge.textContent = document.querySelectorAll('.lei-notif-row:not(.lei-notif-row--head):not(.lei-notif-row--empty)').length;
                showToast(data.message);
            } catch (err) {
                showToast(err.message);
            }
        });
    });
})();
