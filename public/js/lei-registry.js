(function () {
    const page = document.querySelector('.lei-reg-page');
    if (!page) return;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const toast = document.getElementById('leiRegToast');

    const initial = captureState();

    function showToast(msg) {
        if (!toast) return;
        toast.textContent = msg;
        toast.hidden = false;
        setTimeout(() => { toast.hidden = true; }, 3200);
    }

    function captureState() {
        return JSON.stringify(collectPayload());
    }

    function collectPayload() {
        const formats = [];
        document.querySelectorAll('.lei-reg-format-pill.active').forEach((btn) => {
            formats.push(btn.dataset.format);
        });

        return {
            document_name: document.getElementById('leiRegDocName')?.value || '',
            primary_category: document.getElementById('leiRegPrimary')?.value,
            sub_category: document.getElementById('leiRegSub')?.value,
            mandatory_flag: document.getElementById('leiRegMandatory')?.checked || false,
            ocr_verification: document.getElementById('leiRegOcr')?.checked || false,
            file_formats: formats,
            max_file_size_mb: parseInt(document.getElementById('leiRegMaxSize')?.value, 10) || 25,
            versioning_mode: document.querySelector('input[name="versioning"]:checked')?.value || 'audit_trail',
            approval_flow: document.getElementById('leiRegApproval')?.value,
            security_tier: document.querySelector('.lei-reg-tier.active')?.dataset.tier || 'standard',
        };
    }

    async function postJson(url, body) {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify(body),
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(data.message || 'Request failed');
        return data;
    }

    function updateSlider() {
        const input = document.getElementById('leiRegMaxSize');
        const fill = document.getElementById('leiRegSliderFill');
        const badge = document.getElementById('leiRegSizeBadge');
        if (!input || !fill || !badge) return;
        const pct = ((input.value - input.min) / (input.max - input.min)) * 100;
        fill.style.width = pct + '%';
        badge.textContent = input.value + ' MB';
    }

    updateSlider();
    document.getElementById('leiRegMaxSize')?.addEventListener('input', updateSlider);

    document.getElementById('leiRegFormats')?.addEventListener('click', (e) => {
        const pill = e.target.closest('.lei-reg-format-pill');
        if (!pill) return;
        pill.classList.toggle('active');
        const check = pill.querySelector('svg');
        if (pill.classList.contains('active')) {
            if (!check) {
                pill.insertAdjacentHTML('afterbegin', '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>');
            }
        } else if (check) {
            check.remove();
        }
        const active = document.querySelectorAll('.lei-reg-format-pill.active');
        if (active.length === 0) {
            pill.classList.add('active');
            pill.insertAdjacentHTML('afterbegin', '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>');
        }
    });

    document.getElementById('leiRegTiers')?.addEventListener('click', (e) => {
        const tier = e.target.closest('.lei-reg-tier');
        if (!tier) return;
        document.querySelectorAll('.lei-reg-tier').forEach((t) => t.classList.remove('active'));
        tier.classList.add('active');
    });

    document.getElementById('leiRegDiscard')?.addEventListener('click', () => {
        window.location.reload();
    });

    document.getElementById('leiRegPublish')?.addEventListener('click', async () => {
        try {
            const data = await postJson(page.dataset.publishUrl, collectPayload());
            showToast(data.message || 'Published.');
            if (data.modified_at) {
                const el = document.getElementById('leiRegModified');
                if (el) {
                    el.innerHTML = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> Last modified at ${data.modified_at}`;
                }
            }
        } catch (err) {
            showToast(err.message);
        }
    });

    document.getElementById('leiRegSandbox')?.addEventListener('click', async () => {
        try {
            const data = await postJson(page.dataset.sandboxUrl, {});
            showToast(data.message);
        } catch (err) {
            showToast(err.message);
        }
    });

    window.addEventListener('beforeunload', (e) => {
        if (captureState() !== initial) {
            e.preventDefault();
        }
    });
})();
