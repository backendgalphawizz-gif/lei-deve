(function () {
    'use strict';

    function syncColorPicker(picker) {
        var wrap = picker.closest('.lei-bs-color');
        if (!wrap) return;
        var text = wrap.querySelector('.lei-bs-color-text');
        if (text) text.value = picker.value;
        var key = picker.getAttribute('data-bs-color');
        if (key === 'sidebar') {
            var side = document.getElementById('leiBsPreviewSidebar');
            if (side) side.style.backgroundColor = picker.value;
        }
    }

    document.querySelectorAll('input[type="color"][data-bs-color]').forEach(function (picker) {
        syncColorPicker(picker);
        picker.addEventListener('input', function () {
            syncColorPicker(picker);
        });
    });

    document.querySelectorAll('[data-bs-preview]').forEach(function (el) {
        el.addEventListener('input', function () {
            var key = el.getAttribute('data-bs-preview');
            if (key === 'company') {
                var t = document.getElementById('leiBsPreviewCompany');
                if (t) t.textContent = el.value;
            }
            if (key === 'tagline') {
                var tg = document.getElementById('leiBsPreviewTagline');
                if (tg) tg.textContent = el.value;
            }
            if (key === 'portal') {
                var p = document.getElementById('leiBsPreviewPortal');
                if (p) p.textContent = el.value;
            }
            if (key === 'breadcrumb') {
                var bc = document.getElementById('leiBsPreviewBreadcrumb');
                if (bc) bc.textContent = el.value || 'Registry';
            }
            if (key === 'search') {
                var sr = document.getElementById('leiBsPreviewSearch');
                if (sr) sr.setAttribute('placeholder', el.value || 'Global Search...');
            }
            if (key === 'prefix') {
                var pr = document.getElementById('leiBsPreviewPrefix');
                if (pr) pr.textContent = (el.value || 'Welcome,') + ' ';
            }
            if (key === 'subtitle') {
                var st = document.getElementById('leiBsPreviewSubtitle');
                if (st) st.textContent = el.value || st.getAttribute('data-fallback') || '';
            }
        });
    });

    var subtitleEl = document.querySelector('[data-bs-preview="subtitle"]');
    if (subtitleEl) {
        var subPreview = document.getElementById('leiBsPreviewSubtitle');
        if (subPreview && !subtitleEl.value) {
            subPreview.setAttribute('data-fallback', subPreview.textContent);
        }
    }

    document.querySelectorAll('[data-bs-preview="shownotif"]').forEach(function (cb) {
        cb.addEventListener('change', function () {
            var bell = document.getElementById('leiBsPreviewBell');
            if (bell) bell.hidden = !cb.checked;
        });
    });

    document.querySelectorAll('input[type="color"][data-bs-color="primary"]').forEach(function (picker) {
        picker.addEventListener('input', function () {
            var welcome = document.querySelector('.lei-bs-preview-welcome span');
            if (welcome) welcome.style.color = picker.value;
        });
    });

    document.querySelectorAll('[data-bs-file-preview]').forEach(function (input) {
        input.addEventListener('change', function () {
            var id = input.getAttribute('data-bs-file-preview');
            var box = document.getElementById(id);
            if (!box || !input.files || !input.files[0]) return;
            var reader = new FileReader();
            reader.onload = function (e) {
                box.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
                if (id === 'leiBsLogoPreview') {
                    var logo = document.getElementById('leiBsPreviewLogo');
                    if (logo) logo.src = e.target.result;
                }
                if (id === 'leiBsSidebarPreview') {
                    var icon = document.getElementById('leiBsPreviewIcon');
                    if (icon) icon.src = e.target.result;
                }
            };
            reader.readAsDataURL(input.files[0]);
        });
    });
})();
