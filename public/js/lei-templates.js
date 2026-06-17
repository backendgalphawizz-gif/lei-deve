(function () {
    const page = document.querySelector('.lei-tpl-page');
    if (!page) return;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const toast = document.getElementById('leiTplToast');
    const initial = {
        name: document.getElementById('leiTplName')?.value || '',
        module: document.getElementById('leiTplModule')?.value || '',
        sla: document.getElementById('leiTplSla')?.value || '',
    };

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
        if (!res.ok) throw new Error(data.message || 'Request failed');
        return data;
    }

    document.getElementById('leiTplCancel')?.addEventListener('click', () => {
        const name = document.getElementById('leiTplName');
        const module = document.getElementById('leiTplModule');
        const sla = document.getElementById('leiTplSla');
        if (name) name.value = initial.name;
        if (module) module.value = initial.module;
        if (sla) sla.value = initial.sla;
        showToast('Changes discarded.');
    });

    document.getElementById('leiTplSave')?.addEventListener('click', async () => {
        try {
            const data = await postJson(page.dataset.saveUrl, {
                name: document.getElementById('leiTplName')?.value,
                module: document.getElementById('leiTplModule')?.value,
                sla_hours: parseInt(document.getElementById('leiTplSla')?.value, 10) || 48,
            });
            initial.name = document.getElementById('leiTplName')?.value;
            initial.module = document.getElementById('leiTplModule')?.value;
            initial.sla = document.getElementById('leiTplSla')?.value;
            if (data.synced_at) {
                const sync = document.getElementById('leiTplSyncText');
                if (sync) sync.textContent = `Last synchronized with Registry Service at ${data.synced_at} UTC`;
            }
            showToast(data.message || 'Saved.');
        } catch (err) {
            showToast(err.message);
        }
    });

    const modal = document.getElementById('leiTplStateModal');
    const canvas = document.getElementById('leiTplFlowCanvas');

    document.getElementById('leiTplAddState')?.addEventListener('click', () => {
        if (modal) modal.hidden = false;
    });

    document.querySelectorAll('.lei-tpl-node--placeholder').forEach((el) => {
        el.addEventListener('click', () => { if (modal) modal.hidden = false; });
    });

    document.querySelectorAll('[data-close-tpl]').forEach((el) => {
        el.addEventListener('click', () => { if (modal) modal.hidden = true; });
    });

    function accentClass(accent) {
        return `lei-tpl-node--${accent}`;
    }

    const gripSvg = '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><circle cx="9" cy="6" r="1.5"/><circle cx="15" cy="6" r="1.5"/><circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/><circle cx="9" cy="18" r="1.5"/><circle cx="15" cy="18" r="1.5"/></svg>';

    function insertStateNode(state) {
        if (!canvas) return;
        const placeholder = canvas.querySelector('.lei-tpl-node--placeholder');

        const vline = document.createElement('div');
        vline.className = 'lei-tpl-flow-vline';
        vline.setAttribute('aria-hidden', 'true');

        const node = document.createElement('div');
        node.className = `lei-tpl-node ${accentClass(state.accent)}`;
        node.dataset.stateId = state.id;
        node.innerHTML = `
            <span class="lei-tpl-node-accent lei-tpl-node-accent--${state.accent}" aria-hidden="true"></span>
            <div class="lei-tpl-node-body">
                ${state.rule_label ? `<span class="lei-tpl-node-label">${state.rule_label}</span>` : ''}
                <strong>${state.title}</strong>
                ${state.description ? `<p>${state.description}</p>` : ''}
            </div>
            <button type="button" class="lei-tpl-node-grip" aria-label="Reorder state">${gripSvg}</button>
        `;

        if (placeholder) {
            canvas.insertBefore(vline, placeholder);
            canvas.insertBefore(node, placeholder);
        } else {
            if (canvas.children.length > 0) {
                canvas.appendChild(vline);
            }
            canvas.appendChild(node);
        }

        const total = document.getElementById('leiTplTotalNodes');
        if (total) {
            const count = canvas.querySelectorAll('.lei-tpl-node:not(.lei-tpl-node--placeholder)').length;
            total.textContent = `${count} ${count === 1 ? 'State' : 'States'}`;
            total.classList.add('lei-tpl-summary-tag');
        }
    }

    document.getElementById('leiTplStateForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        try {
            const data = await postJson(page.dataset.stateUrl, {
                title: form.title.value,
                description: form.description.value,
                rule_label: form.rule_label.value,
                accent: form.accent.value,
            });
            insertStateNode(data.state);
            if (modal) modal.hidden = true;
            form.reset();
            form.rule_label.value = 'TRANSITION RULE: MANUAL';
            form.accent.value = 'approval';
            const sync = document.getElementById('leiTplSyncText');
            if (sync) {
                const now = new Date();
                const h = String(now.getHours()).padStart(2, '0');
                const m = String(now.getMinutes()).padStart(2, '0');
                sync.textContent = `Last synchronized with Registry Service at ${h}:${m} UTC`;
            }
            showToast(data.message || 'State added.');
        } catch (err) {
            showToast(err.message);
        }
    });
})();
