(function () {
    'use strict';

    function slugify(text) {
        return String(text || '')
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');
    }

    var titleEl = document.getElementById('sp_title');
    var slugEl = document.getElementById('sp_slug');

    if (titleEl && slugEl) {
        var slugTouched = slugEl.value.length > 0;

        slugEl.addEventListener('input', function () {
            slugTouched = slugEl.value.length > 0;
        });

        titleEl.addEventListener('input', function () {
            if (!slugTouched) {
                slugEl.value = slugify(titleEl.value);
            }
        });
    }

    document.querySelectorAll('.lei-sp-delete-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            var msg = form.getAttribute('data-confirm') || 'Delete this page?';
            if (!window.confirm(msg)) {
                e.preventDefault();
            }
        });
    });
})();
