(function () {
    const page = document.querySelector('.lei-env-page');
    if (!page) return;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const toast = document.getElementById('leiEnvToast');

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

    document.querySelectorAll('[data-prevent]').forEach((el) => {
        el.addEventListener('click', (e) => e.preventDefault());
    });

    document.getElementById('leiTriggerDeploy')?.addEventListener('click', async () => {
        try {
            const data = await postJson(page.dataset.deployUrl);
            showToast(data.message);
            const fill = document.getElementById('leiProgressFill');
            const pct = document.getElementById('leiProgressPct');
            if (fill && pct) {
                const v = Math.min(100, parseInt(pct.textContent, 10) + 12);
                fill.style.width = v + '%';
                pct.textContent = v + '%';
            }
        } catch (err) {
            showToast(err.message);
        }
    });

    document.querySelectorAll('[data-cmd]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const action = btn.dataset.cmd;
            if (action === 'lockout' && !(await window.leiConfirm({ title: 'Emergency Lockout', message: 'Activate emergency lockout?', button: 'Activate', variant: 'warning' }))) return;
            if (action === 'rollback' && !(await window.leiConfirm({ title: 'Manual Rollback', message: 'Schedule manual rollback?', button: 'Schedule', variant: 'warning' }))) return;
            try {
                const data = await postJson(page.dataset.commandUrl, { action });
                showToast(data.message);
            } catch (err) {
                showToast(err.message);
            }
        });
    });

    document.querySelectorAll('[data-release-action]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const card = btn.closest('[data-release-id]');
            if (!card) return;
            const id = card.dataset.releaseId;
            const url = (page.dataset.releaseUrl || '').replace('__ID__', id);
            try {
                const data = await postJson(url, { action: btn.dataset.releaseAction });
                showToast(data.message);
                if (data.removed) card.remove();
            } catch (err) {
                showToast(err.message);
            }
        });
    });
})();
