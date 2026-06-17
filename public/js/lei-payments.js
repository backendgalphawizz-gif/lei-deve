(function () {
    const page = document.querySelector('.lei-pay-page');
    if (!page) return;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const toast = document.getElementById('leiPayToast');

    function showToast(msg) {
        if (!toast) return;
        toast.textContent = msg;
        toast.hidden = false;
        setTimeout(() => { toast.hidden = true; }, 3200);
    }

    document.querySelectorAll('[data-auto-filter]').forEach((el) => {
        el.addEventListener('change', () => {
            el.closest('form')?.submit();
        });
    });

    document.querySelectorAll('[data-prevent]').forEach((el) => {
        el.addEventListener('click', (e) => {
            e.preventDefault();
            const label = el.getAttribute('title') || el.textContent?.trim() || 'Action';
            showToast(label + ' — available in production registry build.');
        });
    });

    document.querySelectorAll('.lei-pay-tab').forEach((tab) => {
        tab.addEventListener('click', () => {
            const key = tab.dataset.tab;
            document.querySelectorAll('.lei-pay-tab').forEach((t) => t.classList.toggle('active', t === tab));
            document.querySelectorAll('.lei-pay-tab-panel').forEach((p) => {
                const on = p.dataset.panel === key;
                p.classList.toggle('active', on);
                p.hidden = !on;
            });
        });
    });

    page.querySelectorAll('[data-refund-action]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const card = btn.closest('[data-refund-id]');
            if (!card || btn.disabled) return;

            const id = card.dataset.refundId;
            const action = btn.dataset.refundAction;
            const url = (page.dataset.refundUrl || '').replace('__ID__', id);

            btn.disabled = true;
            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify({ action }),
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Request failed');

                card.remove();
                showToast(data.message || 'Refund updated.');
                updateStats(data.stats);
                updateRefundMetric();
            } catch (err) {
                showToast(err.message || 'Could not update refund.');
                btn.disabled = false;
            }
        });
    });

    function updateRefundMetric() {
        const left = document.querySelectorAll('#leiPayRefundList [data-refund-id]').length;
        const card = document.querySelector('[data-stat-key="refunds"]');
        if (card) {
            const val = card.querySelector('[data-stat-value]');
            if (val) val.textContent = left + ' Requests';
        }
    }

    function updateStats(stats) {
        if (!stats) return;
        stats.forEach((s) => {
            const card = document.querySelector(`[data-stat-key="${s.key}"]`);
            if (!card) return;
            const val = card.querySelector('[data-stat-value]');
            const sub = card.querySelector('[data-stat-sub]');
            if (val) val.textContent = s.value;
            if (sub && s.subtitle) sub.textContent = s.subtitle;
        });
    }

    const reconcileBtn = document.getElementById('leiPayReconcileBtn');
    if (reconcileBtn) {
        reconcileBtn.addEventListener('click', async () => {
            reconcileBtn.disabled = true;
            try {
                const res = await fetch(page.dataset.reconcileUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Failed');
                showToast(data.message || 'Reconciliation complete.');
                setTimeout(() => window.location.reload(), 1200);
            } catch (err) {
                showToast(err.message || 'Reconcile failed.');
                reconcileBtn.disabled = false;
            }
        });
    }

    const refreshEl = document.getElementById('leiPayRefreshSec');
    let sec = 8;
    if (refreshEl) {
        setInterval(() => {
            sec -= 1;
            if (sec <= 0) {
                sec = 8;
                document.querySelectorAll('[data-latency]').forEach((el) => {
                    const base = parseInt(el.textContent, 10) || 140;
                    const jitter = Math.floor(Math.random() * 40) - 20;
                    el.textContent = Math.max(80, base + jitter) + 'ms';
                });
            }
            refreshEl.textContent = String(sec);
        }, 1000);
    }
})();
