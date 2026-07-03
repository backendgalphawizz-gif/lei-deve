(function () {
    'use strict';

    var modal = document.getElementById('leiConfirmModal');
    if (!modal) return;

    var titleEl = document.getElementById('leiConfirmTitle');
    var messageEl = document.getElementById('leiConfirmMessage');
    var iconEl = document.getElementById('leiConfirmIcon');
    var confirmBtn = document.getElementById('leiConfirmSubmit');
    var pendingForm = null;
    var pendingResolve = null;
    var lastFocus = null;

    function iconClass(variant, custom) {
        if (custom) return custom;
        if (variant === 'primary') return 'fa-file-signature';
        if (variant === 'warning') return 'fa-lock';
        return 'fa-triangle-exclamation';
    }

    function showModal(config) {
        lastFocus = document.activeElement;

        var variant = config.variant || 'danger';
        titleEl.textContent = config.title || 'Confirm Action';
        messageEl.textContent = config.message || 'Are you sure you want to continue?';
        confirmBtn.textContent = config.button || 'Confirm';

        confirmBtn.className = 'lei-confirm-modal__btn lei-confirm-modal__btn--confirm lei-confirm-modal__btn--' + variant;
        iconEl.className = 'lei-confirm-modal__icon lei-confirm-modal__icon--' + variant;
        iconEl.innerHTML = '<i class="fa-solid ' + iconClass(variant, config.icon) + '" aria-hidden="true"></i>';

        modal.hidden = false;
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('lei-modal-open');
        confirmBtn.focus();
    }

    function finishModal(confirmed) {
        modal.hidden = true;
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('lei-modal-open');

        if (pendingForm && confirmed) {
            var form = pendingForm;
            pendingForm = null;
            form.dataset.leiConfirmed = '1';
            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
            } else {
                form.submit();
            }
        } else {
            pendingForm = null;
        }

        if (pendingResolve) {
            var resolve = pendingResolve;
            pendingResolve = null;
            resolve(!!confirmed);
        }

        if (lastFocus && typeof lastFocus.focus === 'function') {
            lastFocus.focus();
        }
    }

    function openFormModal(form) {
        pendingForm = form;
        pendingResolve = null;
        showModal({
            title: form.getAttribute('data-confirm-title'),
            message: form.getAttribute('data-confirm'),
            button: form.getAttribute('data-confirm-button'),
            variant: form.getAttribute('data-confirm-variant'),
            icon: form.getAttribute('data-confirm-icon'),
        });
    }

    window.leiConfirm = function (options) {
        options = options || {};
        return new Promise(function (resolve) {
            pendingResolve = resolve;
            pendingForm = null;
            showModal({
                title: options.title,
                message: options.message,
                button: options.button,
                variant: options.variant,
                icon: options.icon,
            });
        });
    };

    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form || !form.getAttribute || !form.getAttribute('data-confirm')) return;
        if (form.dataset.leiConfirmed === '1') {
            delete form.dataset.leiConfirmed;
            return;
        }
        e.preventDefault();
        e.stopPropagation();
        openFormModal(form);
    }, true);

    confirmBtn.addEventListener('click', function () {
        finishModal(true);
    });

    modal.querySelectorAll('[data-lei-confirm-cancel]').forEach(function (el) {
        el.addEventListener('click', function () {
            finishModal(false);
        });
    });

    document.addEventListener('keydown', function (e) {
        if (modal.hidden) return;
        if (e.key === 'Escape') {
            e.preventDefault();
            finishModal(false);
        }
    });
})();
