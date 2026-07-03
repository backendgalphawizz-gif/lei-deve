(function () {
    const page = document.querySelector('.lei-bkp-page');
    if (!page) return;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const toast = document.getElementById('leiBkpToast');

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

    let mins = parseInt(document.getElementById('leiBkpMins')?.textContent, 10) || 0;
    let secs = parseInt(document.getElementById('leiBkpSecs')?.textContent, 10) || 0;

    function tickCountdown() {
        const minsEl = document.getElementById('leiBkpMins');
        const secsEl = document.getElementById('leiBkpSecs');
        if (!minsEl || !secsEl) return;

        if (secs <= 0) {
            if (mins <= 0) {
                mins = 59;
                secs = 59;
            } else {
                mins -= 1;
                secs = 59;
            }
        } else {
            secs -= 1;
        }

        minsEl.textContent = String(mins);
        secsEl.textContent = String(secs).padStart(2, '0');
    }

    setInterval(tickCountdown, 1000);

    document.getElementById('leiBkpManual')?.addEventListener('click', async () => {
        try {
            const data = await postJson(page.dataset.manualUrl);
            showToast(data.message);
        } catch (err) {
            showToast(err.message);
        }
    });

    document.getElementById('leiBkpFailover')?.addEventListener('click', async () => {
        if (!(await window.leiConfirm({ title: 'Site Failover', message: 'Initiate site failover? This is a critical action.', button: 'Initiate', variant: 'danger' }))) return;
        try {
            const data = await postJson(page.dataset.failoverUrl);
            showToast(data.message);
        } catch (err) {
            showToast(err.message);
        }
    });
})();
