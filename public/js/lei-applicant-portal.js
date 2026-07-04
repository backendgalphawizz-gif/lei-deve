/* Applicant portal — mobile nav + file upload */
(function () {
    'use strict';

    /* —— Global form loading states —— */
    document.querySelectorAll('form').forEach(function (form) {
        form.addEventListener('submit', function () {
            form.querySelectorAll('[data-loading]').forEach(function (btn) {
                if (!btn.disabled) {
                    btn.disabled = true;
                    btn.dataset.original = btn.innerHTML;
                    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i> ' + btn.dataset.loading;
                }
            });
        });
    });

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

        /* Label elements already activate hidden file inputs — avoid opening the picker twice */
        if (zone.tagName !== 'LABEL') {
            zone.addEventListener('click', function (e) {
                if (e.target === input) return;
                input.click();
            });
        }

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

        if (btn.tagName !== 'LABEL') {
            btn.addEventListener('click', function (e) {
                if (e.target === input) return;
                input.click();
            });
        }

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

    /* —— Plan card selection —— */
    document.querySelectorAll('[data-plan-select-grid]').forEach(function (grid) {
        grid.querySelectorAll('[data-plan-card]:not(.blocked)').forEach(function (card) {
            function selectCard() {
                grid.querySelectorAll('[data-plan-card]').forEach(function (c) {
                    c.classList.remove('selected');
                    c.setAttribute('aria-pressed', 'false');
                });
                card.classList.add('selected');
                card.setAttribute('aria-pressed', 'true');
            }

            card.addEventListener('click', function (e) {
                if (e.target.closest('a, button, select, input, label')) return;
                selectCard();
            });

            card.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    selectCard();
                }
            });

            if (card.classList.contains('selected')) {
                card.setAttribute('aria-pressed', 'true');
            }
        });
    });

    /* —— Drag & drop on upload zones —— */
    document.querySelectorAll('.lei-drop-zone[data-drop]').forEach(function (zone) {
        var input = zone.querySelector('input[type="file"]');
        if (!input) return;

        ['dragenter', 'dragover'].forEach(function (evt) {
            zone.addEventListener(evt, function (e) {
                e.preventDefault();
                e.stopPropagation();
                zone.classList.add('lei-drop-zone--active');
            });
        });

        ['dragleave', 'drop'].forEach(function (evt) {
            zone.addEventListener(evt, function (e) {
                e.preventDefault();
                e.stopPropagation();
                zone.classList.remove('lei-drop-zone--active');
            });
        });

        zone.addEventListener('drop', function (e) {
            var dt = e.dataTransfer;
            if (!dt || !dt.files || !dt.files.length) return;
            var file = dt.files[0];
            /* Validate size (10 MB) */
            if (file.size > 10 * 1024 * 1024) {
                alert('File is too large. Maximum allowed size is 10 MB.');
                return;
            }
            /* Inject file into input via DataTransfer */
            var newDt = new DataTransfer();
            newDt.items.add(file);
            input.files = newDt.files;
            /* Update label text */
            var textEl = zone.querySelector('.lei-portal-upload-text span') || zone.querySelector('div');
            if (textEl) {
                textEl.textContent = file.name;
            }
            /* Show filename for proof-of-authority */
            var proofFilename = document.querySelector('[data-proof-filename]');
            if (proofFilename && input.name === 'proof_of_authority') {
                proofFilename.textContent = 'Selected: ' + file.name;
                proofFilename.hidden = false;
            }
            refreshUploadSidebar();
        });
    });

    /* —— Session timeout warning (25-minute idle) —— */
    (function () {
        var TIMEOUT_MS = 25 * 60 * 1000;
        var WARNING_MS = 23 * 60 * 1000;
        var warnTimer, expireTimer;
        var banner = document.getElementById('lei-session-warn');
        var countdownEl = banner ? banner.querySelector('[data-countdown]') : null;
        var countdownInterval;

        function resetTimers() {
            clearTimeout(warnTimer);
            clearTimeout(expireTimer);
            clearInterval(countdownInterval);
            if (banner) banner.hidden = true;

            warnTimer = setTimeout(function () {
                if (!banner) return;
                banner.hidden = false;
                var remaining = Math.round((TIMEOUT_MS - WARNING_MS) / 1000);
                if (countdownEl) countdownEl.textContent = remaining + 's';
                countdownInterval = setInterval(function () {
                    remaining -= 1;
                    if (countdownEl) countdownEl.textContent = remaining + 's';
                    if (remaining <= 0) clearInterval(countdownInterval);
                }, 1000);
            }, WARNING_MS);

            expireTimer = setTimeout(function () {
                window.location.href = '/session-expired';
            }, TIMEOUT_MS);
        }

        if (banner) {
            ['click', 'keydown', 'mousemove', 'touchstart'].forEach(function (evt) {
                document.addEventListener(evt, resetTimers, { passive: true });
            });
            banner.querySelector('[data-extend]')?.addEventListener('click', function () {
                banner.hidden = true;
                resetTimers();
            });
            resetTimers();
        }
    })();
})();
