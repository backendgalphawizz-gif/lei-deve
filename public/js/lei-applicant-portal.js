/* Applicant portal — mobile nav + file upload */
(function () {
    'use strict';

    var body = document.body;
    var menuBtn = document.getElementById('leiPortalMenuBtn');
    var backdrop = document.getElementById('leiPortalSidebarBackdrop');

    function setNavOpen(open) {
        body.classList.toggle('lei-portal-nav-open', open);
        if (menuBtn) {
            menuBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
        }
        if (backdrop) {
            backdrop.hidden = !open;
            backdrop.setAttribute('aria-hidden', open ? 'false' : 'true');
        }
    }

    if (menuBtn) {
        menuBtn.addEventListener('click', function () {
            setNavOpen(!body.classList.contains('lei-portal-nav-open'));
        });
    }

    if (backdrop) {
        backdrop.addEventListener('click', function () {
            setNavOpen(false);
        });
    }

    document.querySelectorAll('.lei-applicant-portal .lei-sidebar-nav a').forEach(function (link) {
        link.addEventListener('click', function () {
            if (window.matchMedia('(max-width: 768px)').matches) {
                setNavOpen(false);
            }
        });
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            setNavOpen(false);
        }
    });

    document.querySelectorAll('.lei-applicant-portal .lei-portal-upload').forEach(function (zone) {
        var input = zone.querySelector('input[type="file"]');
        if (!input) return;

        zone.addEventListener('click', function (e) {
            if (e.target === input) return;
            input.click();
        });

        input.addEventListener('change', function () {
            if (!input.files || !input.files.length) return;
            var textEl = zone.querySelector('.lei-portal-upload-text span') || zone.querySelector('div');
            if (textEl) {
                textEl.textContent = input.files[0].name;
            }
            refreshUploadSidebar();
        });
    });

    document.querySelectorAll('.lei-applicant-portal .lei-portal-doc-upload-btn').forEach(function (btn) {
        var input = btn.querySelector('input[type="file"]');
        if (!input) return;

        btn.addEventListener('click', function (e) {
            if (e.target === input) return;
            input.click();
        });

        input.addEventListener('change', function () {
            if (!input.files || !input.files.length) return;
            var filename = document.querySelector('[data-proof-filename]');
            if (filename) {
                filename.textContent = 'Selected: ' + input.files[0].name;
                filename.hidden = false;
            }
            refreshUploadSidebar();
        });
    });

    function formatFileSize(bytes) {
        if (bytes >= 1048576) {
            return (bytes / 1048576).toFixed(1) + ' MB';
        }

        return (bytes / 1024).toFixed(1) + ' KB';
    }

    function escapeHtml(text) {
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function refreshUploadSidebar() {
        var sidebar = document.getElementById('leiDocUploadSidebar');
        if (!sidebar) return;

        var total = parseInt(sidebar.getAttribute('data-total'), 10) || 3;
        var serverFiles = [];
        try {
            serverFiles = JSON.parse(sidebar.getAttribute('data-server-files') || '[]');
        } catch (e) {
            serverFiles = [];
        }

        var serverByKey = {};
        serverFiles.forEach(function (file) {
            serverByKey[file.key] = file;
        });

        var items = [];
        document.querySelectorAll('.lei-applicant-portal input[type="file"][data-doc-key]').forEach(function (input) {
            var key = input.getAttribute('data-doc-key');
            if (input.files && input.files.length) {
                items.push({
                    key: key,
                    name: input.files[0].name,
                    size: formatFileSize(input.files[0].size),
                });
            } else if (serverByKey[key]) {
                items.push(serverByKey[key]);
            }
        });

        var listEl = sidebar.querySelector('[data-upload-list]');
        var emptyEl = sidebar.querySelector('[data-upload-empty]');
        var countEl = sidebar.querySelector('[data-upload-count]');
        var barEl = sidebar.querySelector('[data-upload-bar]');
        var barFill = sidebar.querySelector('[data-upload-bar-fill]');
        var count = items.length;
        var percent = total > 0 ? Math.round((count / total) * 100) : 0;

        if (listEl) {
            listEl.innerHTML = items.map(function (file) {
                var name = file.name.length > 28 ? file.name.slice(0, 25) + '…' : file.name;
                return '<li data-doc-key="' + escapeHtml(file.key) + '">' +
                    '<div class="lei-portal-upload-sidebar-icon"><i class="fa-regular fa-file-pdf"></i></div>' +
                    '<div><strong>' + escapeHtml(name) + '</strong><span>' + escapeHtml(file.size) + '</span></div>' +
                    '</li>';
            }).join('');
            listEl.hidden = count === 0;
        }

        if (emptyEl) {
            emptyEl.hidden = count > 0;
        }

        if (countEl) {
            countEl.textContent = count + '/' + total;
        }

        if (barFill) {
            barFill.style.width = percent + '%';
        }

        if (barEl) {
            barEl.setAttribute('aria-valuenow', String(count));
            barEl.setAttribute('aria-valuemax', String(total));
        }
    }

    refreshUploadSidebar();
})();
