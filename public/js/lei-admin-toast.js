(function () {
    'use strict';

    var container = null;
    var icons = {
        success: 'fa-circle-check',
        error: 'fa-circle-xmark',
        info: 'fa-circle-info',
        warning: 'fa-triangle-exclamation',
    };

    function ensureContainer() {
        if (container) return container;
        container = document.getElementById('leiToastStack');
        if (!container) {
            container = document.createElement('div');
            container.id = 'leiToastStack';
            container.className = 'lei-toast-stack';
            container.setAttribute('aria-live', 'polite');
            container.setAttribute('aria-atomic', 'true');
            document.body.appendChild(container);
        }
        return container;
    }

    function removeToast(el) {
        if (!el || !el.parentNode) return;
        el.classList.remove('is-visible');
        setTimeout(function () {
            el.remove();
        }, 220);
    }

    window.leiToast = function (message, type, options) {
        message = String(message || '').trim();
        if (!message) return;

        type = type || 'success';
        options = options || {};
        var duration = typeof options.duration === 'number' ? options.duration : 4200;

        var stack = ensureContainer();
        var toast = document.createElement('div');
        toast.className = 'lei-toast lei-toast--' + type;
        toast.setAttribute('role', 'alert');
        toast.innerHTML =
            '<span class="lei-toast__icon" aria-hidden="true"><i class="fa-solid ' + (icons[type] || icons.info) + '"></i></span>' +
            '<div class="lei-toast__body"><strong class="lei-toast__title">' + escapeHtml(options.title || titleFor(type)) + '</strong>' +
            '<p class="lei-toast__message">' + escapeHtml(message) + '</p></div>' +
            '<button type="button" class="lei-toast__close" aria-label="Dismiss"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>';

        stack.appendChild(toast);

        toast.querySelector('.lei-toast__close').addEventListener('click', function () {
            removeToast(toast);
        });

        requestAnimationFrame(function () {
            toast.classList.add('is-visible');
        });

        var timer = setTimeout(function () {
            removeToast(toast);
        }, duration);

        toast.addEventListener('mouseenter', function () {
            clearTimeout(timer);
        });

        toast.addEventListener('mouseleave', function () {
            timer = setTimeout(function () {
                removeToast(toast);
            }, 1800);
        });
    };

    function titleFor(type) {
        return {
            success: 'Success',
            error: 'Error',
            info: 'Notice',
            warning: 'Warning',
        }[type] || 'Notice';
    }

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function flushFlashMessages() {
        var payload = window.__leiFlashMessages;
        if (!payload || typeof payload !== 'object') return;

        var order = ['success', 'info', 'warning', 'error'];
        var delay = 0;

        order.forEach(function (type) {
            if (!payload[type]) return;
            setTimeout(function () {
                window.leiToast(payload[type], type);
            }, delay);
            delay += 180;
        });

        delete window.__leiFlashMessages;
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', flushFlashMessages);
    } else {
        flushFlashMessages();
    }
})();
