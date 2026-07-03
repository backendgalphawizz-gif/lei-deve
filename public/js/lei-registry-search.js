(function () {
    document.querySelectorAll('.lei-reg-search-form').forEach(function (form) {
        var suggestUrl = form.dataset.suggestUrl;
        var input = form.querySelector('.lei-reg-search-input');
        var dropdown = form.querySelector('.lei-reg-suggest');
        var clearBtn = form.querySelector('.lei-reg-search-clear');
        var timer = null;
        var activeIndex = -1;

        if (!input || !dropdown || !suggestUrl) return;

        function selectedType() {
            var checked = form.querySelector('input[name="type"]:checked');
            return checked ? checked.value : 'all';
        }

        function hideSuggestions() {
            dropdown.hidden = true;
            dropdown.innerHTML = '';
            input.setAttribute('aria-expanded', 'false');
            activeIndex = -1;
        }

        function showSuggestions() {
            dropdown.hidden = false;
            input.setAttribute('aria-expanded', 'true');
        }

        function toggleClear() {
            if (!clearBtn) return;
            clearBtn.hidden = input.value.trim() === '';
        }

        function renderItems(items, moreUrl) {
            var registerUrl = form.dataset.registerUrl || '/pricing';

            if (!items.length) {
                dropdown.innerHTML =
                    '<div class="lei-reg-suggest-empty">No matching records in the registry</div>' +
                    '<a href="' + escapeHtml(registerUrl) + '" class="lei-reg-suggest-register">Register for LEI</a>';
                showSuggestions();
                return;
            }

            var html = items.map(function (item, i) {
                var reg = item.registration_number
                    ? '<span class="lei-reg-suggest-reg">' + escapeHtml(item.registration_number) + '</span>'
                    : '';
                var statusTone = item.status_tone || 'active';
                var statusLabel = item.status_label || 'ISSUED';
                var statusBadge = '<span class="lei-reg-suggest-status lei-reg-suggest-status--' + escapeHtml(statusTone) + '">' +
                    escapeHtml(statusLabel) + '</span>';
                var source = item.source || 'local';
                var sourceLabel = item.source_label || (source === 'gleif' ? 'GLEIF' : 'Our Registry');
                var sourceBadge = '<span class="lei-reg-suggest-source lei-reg-suggest-source--' + escapeHtml(source) + '">' +
                    escapeHtml(sourceLabel) + '</span>';
                var renewBtn = item.can_renew && item.renew_url
                    ? '<a href="' + escapeHtml(item.renew_url) + '" class="lei-reg-suggest-renew">Renew</a>'
                    : '';

                return '<div class="lei-reg-suggest-row">' +
                    '<a href="' + escapeHtml(item.url) + '" class="lei-reg-suggest-item" role="option" data-index="' + i + '">' +
                        '<span class="lei-reg-suggest-name">' + escapeHtml(item.entity_name) + '</span>' +
                        '<span class="lei-reg-suggest-meta">' +
                            '<span class="lei-reg-suggest-lei">' + escapeHtml(item.lei_number) + '</span>' +
                            reg +
                            statusBadge +
                            sourceBadge +
                            '<span class="lei-reg-suggest-country">' + escapeHtml(item.country) + '</span>' +
                        '</span>' +
                    '</a>' +
                    renewBtn +
                '</div>';
            }).join('');

            if (moreUrl) {
                html += '<a href="' + escapeHtml(moreUrl) + '" class="lei-reg-suggest-more">View all results</a>';
            }

            dropdown.innerHTML = html;
            showSuggestions();
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        function fetchSuggestions() {
            var q = input.value.trim();
            toggleClear();

            if (q.length < 2) {
                hideSuggestions();
                return;
            }

            var url = suggestUrl + '?q=' + encodeURIComponent(q) + '&type=' + encodeURIComponent(selectedType());

            fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    renderItems(data.items || [], data.more_url || null);
                })
                .catch(function () {
                    hideSuggestions();
                });
        }

        function scheduleFetch() {
            clearTimeout(timer);
            timer = setTimeout(fetchSuggestions, 280);
        }

        input.addEventListener('input', scheduleFetch);

        input.addEventListener('focus', function () {
            if (input.value.trim().length >= 2) {
                fetchSuggestions();
            }
        });

        input.addEventListener('keydown', function (e) {
            var items = dropdown.querySelectorAll('.lei-reg-suggest-item');
            if (!items.length || dropdown.hidden) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeIndex = Math.min(activeIndex + 1, items.length - 1);
                highlightItem(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeIndex = Math.max(activeIndex - 1, 0);
                highlightItem(items);
            } else if (e.key === 'Enter' && activeIndex >= 0) {
                e.preventDefault();
                items[activeIndex].click();
            } else if (e.key === 'Escape') {
                hideSuggestions();
            }
        });

        function highlightItem(items) {
            items.forEach(function (el, i) {
                el.classList.toggle('is-active', i === activeIndex);
            });
            if (activeIndex >= 0 && items[activeIndex]) {
                items[activeIndex].scrollIntoView({ block: 'nearest' });
            }
        }

        form.querySelectorAll('.lei-reg-search-type input').forEach(function (radio) {
            radio.addEventListener('change', function () {
                form.querySelectorAll('.lei-reg-search-type').forEach(function (label) {
                    label.classList.toggle('active', label.querySelector('input') === radio);
                });
                if (input.value.trim().length >= 2) {
                    scheduleFetch();
                }
            });
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                input.value = '';
                toggleClear();
                hideSuggestions();
                input.focus();
            });
        }

        document.addEventListener('click', function (e) {
            if (!form.contains(e.target)) {
                hideSuggestions();
            }
        });

        toggleClear();
    });
})();
