(function () {
    'use strict';

    var searchInput = document.getElementById('leiGlobalSearchInput');
    var searchDropdown = document.getElementById('leiSearchDropdown');
    var searchWrap = document.getElementById('leiSearchWrap');
    var searchTimer;

    function showSearchDropdown() {
        if (!searchDropdown) return;
        searchDropdown.classList.add('is-open');
        searchDropdown.removeAttribute('hidden');
    }

    function hideSearchDropdown() {
        if (!searchDropdown) return;
        searchDropdown.classList.remove('is-open');
        searchDropdown.setAttribute('hidden', 'hidden');
        searchDropdown.innerHTML = '';
    }

    function renderSearchItems(items, moreUrl) {
        if (!searchDropdown) return;
        if (!items.length) {
            searchDropdown.innerHTML = '<div class="lei-search-dd-empty">No quick results</div>';
            showSearchDropdown();
            bindSearchDropdownClicks();
            return;
        }
        var html = items.map(function (item) {
            return '<a href="' + item.url + '" class="lei-search-dd-item">' +
                '<span class="lei-search-dd-type">' + (item.type || '').toUpperCase() + '</span>' +
                '<div><strong>' + escapeHtml(item.title) + '</strong><span>' + escapeHtml(item.meta || '') + '</span></div>' +
                '</a>';
        }).join('');
        if (moreUrl) {
            html += '<a href="' + moreUrl + '" class="lei-search-dd-more">View all results</a>';
        }
        searchDropdown.innerHTML = html;
        showSearchDropdown();
        bindSearchDropdownClicks();
    }

    function bindSearchDropdownClicks() {
        if (!searchDropdown) return;
        searchDropdown.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                hideSearchDropdown();
            });
        });
    }

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function fetchSuggestions(q) {
        var suggestUrl = searchInput && searchInput.dataset.suggestUrl;
        if (!suggestUrl || q.length < 2) {
            hideSearchDropdown();
            return;
        }
        fetch(suggestUrl + '?q=' + encodeURIComponent(q), {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.ok) renderSearchItems(data.items || [], data.more_url);
            })
            .catch(function () { hideSearchDropdown(); });
    }

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimer);
            var q = searchInput.value.trim();
            if (q.length < 2) {
                hideSearchDropdown();
                return;
            }
            searchTimer = setTimeout(function () { fetchSuggestions(q); }, 280);
        });

        searchInput.addEventListener('focus', function () {
            var q = searchInput.value.trim();
            if (q.length >= 2) fetchSuggestions(q);
        });

        searchInput.addEventListener('blur', function () {
            setTimeout(function () {
                if (!searchDropdown || !searchDropdown.classList.contains('is-open')) return;
                if (searchDropdown.matches(':hover')) return;
                hideSearchDropdown();
            }, 150);
        });

        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                hideSearchDropdown();
                searchInput.blur();
            }
        });
    }

    document.addEventListener('mousedown', function (e) {
        if (!searchWrap || searchWrap.contains(e.target)) return;
        hideSearchDropdown();
    });

    /* —— Profile dropdown —— */
    var profileTrigger = document.getElementById('leiProfileTrigger');
    var profileDropdown = document.getElementById('leiProfileDropdown');
    var profileMenu = document.getElementById('leiProfileMenu');

    function closeProfileMenu() {
        if (!profileDropdown || !profileTrigger) return;
        profileDropdown.classList.remove('is-open');
        profileDropdown.setAttribute('hidden', 'hidden');
        profileTrigger.setAttribute('aria-expanded', 'false');
    }

    function openProfileMenu() {
        if (!profileDropdown || !profileTrigger) return;
        hideSearchDropdown();
        profileDropdown.classList.add('is-open');
        profileDropdown.removeAttribute('hidden');
        profileTrigger.setAttribute('aria-expanded', 'true');
    }

    function toggleProfileMenu() {
        if (!profileDropdown) return;
        if (profileDropdown.classList.contains('is-open')) closeProfileMenu();
        else openProfileMenu();
    }

    if (profileTrigger && profileDropdown) {
        profileTrigger.addEventListener('click', function (e) {
            e.stopPropagation();
            toggleProfileMenu();
        });
    }

    document.addEventListener('click', function (e) {
        if (!profileMenu || profileMenu.contains(e.target)) return;
        closeProfileMenu();
    });

    if (profileDropdown) {
        profileDropdown.querySelectorAll('a.lei-profile-dropdown-item').forEach(function (link) {
            link.addEventListener('click', function () {
                closeProfileMenu();
            });
        });

        profileDropdown.querySelectorAll('.lei-profile-logout-form').forEach(function (form) {
            form.addEventListener('submit', function () {
                closeProfileMenu();
            });
        });
    }
})();
