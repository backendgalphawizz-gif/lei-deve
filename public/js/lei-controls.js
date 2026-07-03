(function () {
    const page = document.querySelector('.lei-ctrl-page');
    if (!page) return;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const toast = document.getElementById('leiCtrlToast');
    let modalVarId = null;

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
        el.addEventListener('click', (e) => {
            e.preventDefault();
            showToast('Feature available in production registry build.');
        });
    });

    const modal = document.getElementById('leiVarModal');
    const modalInput = document.getElementById('leiModalVarValue');
    const modalName = document.getElementById('leiModalVarName');

    document.querySelectorAll('[data-modify-var]').forEach((btn) => {
        btn.addEventListener('click', () => {
            modalVarId = btn.dataset.id;
            modalName.textContent = btn.dataset.name;
            modalInput.value = btn.dataset.value;
            modal.hidden = false;
        });
    });

    document.querySelectorAll('[data-close-modal]').forEach((el) => {
        el.addEventListener('click', () => { modal.hidden = true; });
    });

    document.getElementById('leiModalSave')?.addEventListener('click', async () => {
        if (!modalVarId) return;
        const url = (page.dataset.variableUrl || '').replace('__ID__', modalVarId);
        try {
            const data = await postJson(url, { value_display: modalInput.value });
            const row = document.querySelector(`[data-var-id="${modalVarId}"]`);
            const cell = row?.querySelector('[data-value-cell]');
            if (cell && data.variable) cell.textContent = data.variable.value_display;
            const last = document.getElementById('leiLastChange');
            if (last) last.textContent = 'Last manual change: ' + (data.last_manual_change || 'just now');
            modal.hidden = true;
            showToast(data.message || 'Saved.');
        } catch (err) {
            showToast(err.message);
        }
    });

    document.getElementById('leiUpdatePolicies')?.addEventListener('click', async () => {
        const policies = [];
        document.querySelectorAll('[data-policy-key]').forEach((row) => {
            policies.push({
                key: row.dataset.policyKey,
                enabled: row.querySelector('[data-policy-toggle]')?.checked || false,
            });
        });
        try {
            const data = await postJson(page.dataset.policiesUrl, { policies });
            showToast(data.message || 'Policies updated.');
        } catch (err) {
            showToast(err.message);
        }
    });

    const maintainToggle = document.getElementById('leiMaintenanceToggle');
    const maintainLabel = document.getElementById('leiMaintainLabel');
    maintainToggle?.addEventListener('change', async () => {
        try {
            const data = await postJson(page.dataset.maintenanceUrl, { enabled: maintainToggle.checked });
            if (maintainLabel) maintainLabel.textContent = data.enabled ? 'ON' : 'OFF';
            showToast(data.message);
        } catch (err) {
            maintainToggle.checked = !maintainToggle.checked;
            showToast(err.message);
        }
    });

    document.getElementById('leiOverrideArm')?.addEventListener('change', async (e) => {
        try {
            await postJson(page.dataset.overrideArmUrl, { armed: e.target.checked });
        } catch (err) {
            e.target.checked = !e.target.checked;
            showToast(err.message);
        }
    });

    document.getElementById('leiExecuteOverride')?.addEventListener('click', async () => {
        if (!(await window.leiConfirm({ title: 'System Override', message: 'Execute full system override? This action is logged and irreversible.', button: 'Execute', variant: 'danger' }))) return;
        try {
            const data = await postJson(page.dataset.overrideExecUrl);
            document.getElementById('leiOverrideArm').checked = false;
            showToast(data.message);
        } catch (err) {
            showToast(err.message);
        }
    });

    document.getElementById('leiRevokeSessions')?.addEventListener('click', async () => {
        if (!(await window.leiConfirm({ title: 'Revoke Sessions', message: 'Revoke all global admin sessions?', button: 'Revoke', variant: 'warning' }))) return;
        try {
            const data = await postJson(page.dataset.revokeUrl);
            showToast(data.message);
        } catch (err) {
            showToast(err.message);
        }
    });

    document.getElementById('leiForceMfa')?.addEventListener('click', async () => {
        try {
            const data = await postJson(page.dataset.mfaUrl);
            showToast(data.message);
        } catch (err) {
            showToast(err.message);
        }
    });

    document.getElementById('leiStartExport')?.addEventListener('click', async () => {
        try {
            const data = await postJson(page.dataset.exportUrl);
            showToast(data.message);
        } catch (err) {
            showToast(err.message);
        }
    });

    document.getElementById('leiInstantScrub')?.addEventListener('click', async () => {
        if (!(await window.leiConfirm({ title: 'Instant Scrub', message: 'Instant scrub is destructive. Continue?', button: 'Continue', variant: 'danger' }))) return;
        try {
            const data = await postJson(page.dataset.scrubUrl, { confirmed: true });
            showToast(data.message);
        } catch (err) {
            showToast(err.message);
        }
    });
})();
