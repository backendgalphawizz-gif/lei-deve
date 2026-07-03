(function () {
    const searchInput = document.querySelector('[data-lei-user-search]');
    const filterForm = document.querySelector('[data-lei-users-filter-form]');
    const advancedToggle = document.querySelector('[data-lei-advanced-filters-toggle]');
    const advancedPanel = document.querySelector('[data-lei-advanced-filters]');

    if (advancedToggle && advancedPanel) {
        advancedToggle.addEventListener('click', function () {
            const open = advancedPanel.hidden;
            advancedPanel.hidden = !open;
            this.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    }

    if (searchInput && filterForm) {
        let timer;
        searchInput.addEventListener('input', function () {
            clearTimeout(timer);
            timer = setTimeout(() => filterForm.requestSubmit(), 400);
        });
    }

    document.querySelectorAll('.lei-role-card').forEach(function (card) {
        card.addEventListener('click', function () {
            document.querySelectorAll('.lei-role-card').forEach(function (c) {
                c.classList.remove('selected');
            });
            this.classList.add('selected');
            const radio = this.querySelector('input[type="radio"]');
            if (radio) {
                radio.checked = true;
                if (typeof loadRolePermissions === 'function') {
                    loadRolePermissions(radio.value);
                }
            }
        });
    });

    const createForm = document.getElementById('createUserForm');
    if (createForm) {
        const statusEl = createForm.querySelector('[data-credential-status]');
        const fields = ['name', 'email', 'organization_id'];

        function updateCredentialStatus() {
            if (!statusEl) return;
            const ready = fields.every(function (id) {
                const el = createForm.querySelector('#' + id);
                return el && String(el.value).trim() !== '';
            });
            statusEl.classList.toggle('ready', ready);
            statusEl.querySelector('.lei-status-text').textContent = ready
                ? 'Ready to create user'
                : 'Awaiting Mandatory Credentials';
        }

        fields.forEach(function (id) {
            const el = createForm.querySelector('#' + id);
            if (el) {
                el.addEventListener('input', updateCredentialStatus);
                el.addEventListener('change', updateCredentialStatus);
            }
        });
        updateCredentialStatus();
    }

    document.querySelectorAll('.lei-users-row[data-href]').forEach(function (row) {
        row.addEventListener('click', function (e) {
            if (e.target.closest('a, button, form, .lei-icon-actions')) return;
            window.location.href = row.dataset.href;
        });
    });
})();
