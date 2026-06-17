(function () {
    const page = document.querySelector('.lei-md-page');
    if (!page) return;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const toast = document.getElementById('leiMdToast');

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
            body: JSON.stringify(body),
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(data.message || Object.values(data.errors || {})[0]?.[0] || 'Request failed');
        return data;
    }

    const modal = document.getElementById('leiMdCountryModal');
    document.getElementById('leiMdAddCountry')?.addEventListener('click', () => {
        if (modal) modal.hidden = false;
    });
    document.querySelectorAll('[data-close-md]').forEach((el) => {
        el.addEventListener('click', () => { if (modal) modal.hidden = true; });
    });

    document.getElementById('leiMdCountryForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        const body = {
            name: form.name.value,
            iso_alpha2: form.iso_alpha2.value.toUpperCase(),
            region: form.region.value,
            status: form.status.value,
            dialing_code: form.dialing_code.value,
        };
        try {
            const data = await postJson(page.dataset.countryUrl, body);
            showToast(data.message || 'Saved.');
            setTimeout(() => window.location.reload(), 800);
        } catch (err) {
            showToast(err.message);
        }
    });

    document.getElementById('leiMdUpdateMapping')?.addEventListener('click', async () => {
        const box = document.getElementById('leiMdValidation');
        if (!box) return;
        const body = {
            kyc_verification: box.querySelector('[name="kyc_verification"]')?.checked || false,
            tax_residency_proof: box.querySelector('[name="tax_residency_proof"]')?.checked || false,
            swift_bic_validation: box.querySelector('[name="swift_bic_validation"]')?.checked || false,
        };
        try {
            const data = await postJson(page.dataset.validationUrl, body);
            showToast(data.message);
        } catch (err) {
            showToast(err.message);
        }
    });

    document.getElementById('leiMdSaveDropdown')?.addEventListener('click', async () => {
        try {
            const data = await postJson(page.dataset.dropdownUrl, {
                display_format: document.getElementById('leiMdDisplayFormat')?.value,
                sort_order: document.getElementById('leiMdSortOrder')?.value,
                allow_custom_entries: document.getElementById('leiMdAllowCustom')?.checked || false,
            });
            showToast(data.message);
        } catch (err) {
            showToast(err.message);
        }
    });
})();
