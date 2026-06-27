(function () {
    'use strict';

    var TYPE_META = {
        success: { title: 'Success', icon: 'fa-circle-check' },
        error: { title: 'Error', icon: 'fa-circle-xmark' },
        info: { title: 'Notice', icon: 'fa-circle-info' },
    };

    var overlay = null;
    var dialog = null;
    var titleEl = null;
    var messageEl = null;
    var iconEl = null;
    var closeBtn = null;
    var queue = [];
    var showing = false;

    function ensureElements() {
        if (overlay) {
            return;
        }

        overlay = document.getElementById('leiPubNotify');
        if (!overlay) {
            return;
        }

        dialog = overlay.querySelector('.lei-pub-notify-dialog');
        titleEl = overlay.querySelector('.lei-pub-notify-title');
        messageEl = overlay.querySelector('.lei-pub-notify-message');
        iconEl = overlay.querySelector('.lei-pub-notify-icon i');
        closeBtn = overlay.querySelector('.lei-pub-notify-close');

        overlay.querySelector('.lei-pub-notify-backdrop')?.addEventListener('click', hide);
        closeBtn?.addEventListener('click', hide);

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !overlay.hidden) {
                hide();
            }
        });
    }

    function hide() {
        if (!overlay) {
            return;
        }

        overlay.hidden = true;
        overlay.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('lei-pub-notify-open');
        showing = false;

        if (queue.length) {
            showNext();
        }
    }

    function render(item) {
        var meta = TYPE_META[item.type] || TYPE_META.info;

        overlay.className = 'lei-pub-notify lei-pub-notify--' + item.type;
        titleEl.textContent = item.title || meta.title;
        messageEl.textContent = item.message || '';
        iconEl.className = 'fa-solid ' + meta.icon;
        overlay.hidden = false;
        overlay.setAttribute('aria-hidden', 'false');
        document.body.classList.add('lei-pub-notify-open');
        showing = true;
        closeBtn.focus();
    }

    function showNext() {
        if (showing || !queue.length) {
            return;
        }

        ensureElements();
        if (!overlay) {
            return;
        }

        render(queue.shift());
    }

    function show(type, message, title) {
        if (!message) {
            return;
        }

        queue.push({
            type: type || 'info',
            message: String(message),
            title: title || null,
        });

        showNext();
    }

    function initFromDom() {
        var dataEl = document.getElementById('lei-pub-flash-data');
        if (!dataEl) {
            return;
        }

        try {
            var items = JSON.parse(dataEl.textContent || '[]');
            if (Array.isArray(items)) {
                items.forEach(function (item) {
                    if (item && item.message) {
                        show(item.type, item.message, item.title);
                    }
                });
            }
        } catch (e) {
            // ignore invalid flash payload
        }
    }

    window.LeiPubNotify = {
        show: show,
        hide: hide,
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initFromDom);
    } else {
        initFromDom();
    }
})();
