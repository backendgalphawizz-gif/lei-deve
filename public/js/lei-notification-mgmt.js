(function () {
    const page = document.querySelector('.lei-nm-page');
    if (!page) return;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const toast = document.getElementById('leiNmToast');

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

    function updateStats(stats) {
        if (!stats) return;
        stats.forEach((s) => {
            const el = document.querySelector(`.lei-nm-stat-card[data-stat-key="${s.stat_key}"] strong`);
            if (el) el.textContent = s.value;
        });
    }

    document.querySelectorAll('.lei-nm-channel-btn').forEach((btn) => {
        btn.addEventListener('click', async () => {
            try {
                const data = await postJson(page.dataset.channelUrl, { channel: btn.dataset.channel });
                if (data.redirect) window.location.href = data.redirect;
            } catch (err) {
                showToast(err.message);
            }
        });
    });

    document.getElementById('leiNmSystemBroadcast')?.addEventListener('click', () => {
        document.getElementById('leiNmBroadcastCard')?.scrollIntoView({ behavior: 'smooth' });
    });

    document.getElementById('leiNmSaveDraft')?.addEventListener('click', async () => {
        const form = document.getElementById('leiNmBroadcastForm');
        const fd = new FormData(form);
        try {
            const data = await postJson(page.dataset.draftUrl, Object.fromEntries(fd));
            showToast(data.message);
        } catch (err) {
            showToast(err.message);
        }
    });

    document.getElementById('leiNmExecuteBroadcast')?.addEventListener('click', async () => {
        const form = document.getElementById('leiNmBroadcastForm');
        const fd = new FormData(form);
        const body = Object.fromEntries(fd);
        if (!body.broadcast_message?.trim()) {
            showToast('Please enter message content.');
            return;
        }
        try {
            const data = await postJson(page.dataset.broadcastUrl, body);
            updateStats(data.stats);
            showToast(data.message);
        } catch (err) {
            showToast(err.message);
        }
    });

    document.getElementById('leiNmOtpForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(e.target);
        try {
            const data = await postJson(page.dataset.otpUrl, Object.fromEntries(fd));
            showToast(data.message);
        } catch (err) {
            showToast(err.message);
        }
    });

    document.querySelectorAll('.js-nm-trigger').forEach((input) => {
        input.addEventListener('change', async () => {
            const url = page.dataset.triggerUrl.replace('__ID__', input.dataset.id);
            try {
                const data = await postJson(url, {});
                updateStats(data.stats);
            } catch (err) {
                input.checked = !input.checked;
                showToast(err.message);
            }
        });
    });

    document.querySelectorAll('.js-otp-minus, .js-otp-plus').forEach((btn) => {
        btn.addEventListener('click', () => {
            const field = btn.dataset.field;
            const input = document.querySelector(`[name="${field}"]`);
            if (!input) return;
            const min = parseInt(input.min, 10) || 1;
            const max = parseInt(input.max, 10) || 99;
            let v = parseInt(input.value, 10) || min;
            v = btn.classList.contains('js-otp-plus') ? Math.min(max, v + 1) : Math.max(min, v - 1);
            input.value = v;
        });
    });

    document.querySelectorAll('.lei-nm-ph-chip').forEach((chip) => {
        chip.addEventListener('click', () => {
            const text = chip.dataset.copy || chip.textContent;
            navigator.clipboard?.writeText(text).then(() => showToast('Copied ' + text));
        });
    });

    document.getElementById('leiNmCreateTemplate')?.addEventListener('click', async () => {
        const name = prompt('Template name:');
        if (!name) return;
        const channel = document.querySelector('.lei-nm-channel-btn--active')?.dataset.channel || 'email';
        try {
            const data = await postJson(page.dataset.templateUrl, {
                name,
                subtitle: '',
                category: 'General',
                channel,
            });
            showToast(data.message);
            if (data.redirect) window.location.href = data.redirect;
        } catch (err) {
            showToast(err.message);
        }
    });
})();
