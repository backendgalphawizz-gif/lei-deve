(function () {
    const page = document.querySelector('.lei-support-page');
    if (!page) return;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const toast = document.getElementById('leiSupportToast');
    const detailWrap = document.getElementById('leiSupportDetailWrap');
    const adminInitials = page.dataset.adminInitials || 'AD';

    function showToast(msg) {
        if (!toast) return;
        toast.textContent = msg;
        toast.hidden = false;
        setTimeout(() => { toast.hidden = true; }, 3200);
    }

    function setPageUrls(urls) {
        if (!urls) return;
        if (urls.message) page.dataset.messageUrl = urls.message;
        if (urls.note) page.dataset.noteUrl = urls.note;
        if (urls.action) page.dataset.actionUrl = urls.action;
    }

    async function postJson(url, body) {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify(body || {}),
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(data.message || Object.values(data.errors || {})[0]?.[0] || 'Request failed');
        return data;
    }

    function updateStats(stats) {
        if (!stats) return;
        stats.forEach((s) => {
            const card = document.querySelector(`.lei-support-stat-card[data-stat-key="${s.stat_key}"]`);
            if (!card) return;
            const val = card.querySelector('.lei-support-stat-value');
            const badge = card.querySelector('.lei-support-stat-badge');
            if (val) val.textContent = s.value;
            if (badge) {
                badge.textContent = s.badge_text || '';
                badge.className = 'lei-support-stat-badge lei-support-stat-badge--' + (s.badge_tone || 'muted');
                badge.hidden = !s.badge_text;
            }
        });
    }

    function updateCategories(categories) {
        const grid = document.getElementById('leiSupportCatGrid');
        if (!grid || !categories) return;
        grid.innerHTML = categories.map((c) => `
            <div class="lei-support-cat-item" data-category-id="${c.id}">
                <button type="button" class="lei-support-cat-gear js-cat-edit" data-id="${c.id}" data-name="${escapeHtml(c.name)}" aria-label="Edit">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/></svg>
                </button>
                <strong>${escapeHtml(c.name)}</strong>
                <span class="js-cat-count">${escapeHtml(c.ticket_count_label)}</span>
            </div>`).join('');
        bindCatEdit();
        syncCategorySelect(categories);
    }

    function syncCategorySelect(categories) {
        document.querySelectorAll('#leiSupportTicketForm select[name="category"]').forEach((sel) => {
            const cur = sel.value;
            sel.innerHTML = categories.map((c) =>
                `<option value="${escapeHtml(c.name)}"${c.name === cur ? ' selected' : ''}>${escapeHtml(c.name)}</option>`
            ).join('');
        });
        const filterCat = document.querySelector('#leiSupportFilterPanel select[name="category"]');
        if (filterCat) {
            const cur = filterCat.value;
            const opts = '<option value="all">All</option>' + categories.map((c) =>
                `<option value="${escapeHtml(c.name)}"${c.name === cur ? ' selected' : ''}>${escapeHtml(c.name)}</option>`
            ).join('');
            filterCat.innerHTML = opts;
        }
    }

    function escapeHtml(str) {
        const d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

    function updateTableRow(row) {
        if (!row) return;
        const el = document.querySelector(`.js-support-row[data-ticket-id="${row.id}"]`);
        if (!el) return;
        el.querySelector('.lei-support-ticket-id').textContent = row.ticket_code;
        const entity = el.querySelector('.lei-support-entity');
        if (entity) {
            entity.innerHTML = `<strong>${escapeHtml(row.user_entity)}</strong>`
                + (row.contact_email ? `<small>${escapeHtml(row.contact_email)}</small>` : '');
        }
        el.querySelector('.lei-support-cat-pill').textContent = row.category;
        const pri = el.querySelector('.lei-support-priority');
        pri.innerHTML = `<i class="lei-support-dot lei-support-dot--${row.priority_tone}"></i>${escapeHtml(row.priority)}`;
        const st = el.querySelector('.lei-support-status');
        st.className = 'lei-support-status lei-support-status--' + row.status_tone;
        st.textContent = row.status;
    }

    function bindDetailEvents() {
        const compose = document.getElementById('leiSupportCompose');
        const noteForm = document.getElementById('leiSupportNoteForm');
        if (compose) {
            compose.removeEventListener('submit', onMessageSubmit);
            compose.addEventListener('submit', onMessageSubmit);
        }
        const noteTa = noteForm?.querySelector('textarea');
        if (noteTa) {
            noteTa.removeEventListener('blur', onNoteBlur);
            noteTa.addEventListener('blur', onNoteBlur);
        }
        detailWrap?.querySelectorAll('.lei-support-action-btn').forEach((btn) => {
            btn.replaceWith(btn.cloneNode(true));
        });
        detailWrap?.querySelectorAll('.lei-support-action-btn').forEach((btn) => {
            btn.addEventListener('click', onActionClick);
        });
    }

    async function onMessageSubmit(e) {
        e.preventDefault();
        const input = e.target.querySelector('input[name="body"]');
        const body = input?.value?.trim();
        if (!body || !page.dataset.messageUrl) return;
        try {
            const data = await postJson(page.dataset.messageUrl, { body });
            const thread = document.getElementById('leiSupportThread');
            const empty = thread?.querySelector('.lei-support-thread-empty');
            if (empty) empty.remove();
            if (thread) {
                const wrap = document.createElement('div');
                wrap.className = 'lei-support-msg lei-support-msg--out';
                const m = data.msg || {};
                const meta = (m.time_label || 'Just now')
                    + (m.name ? ` · ${m.name}${m.role ? ` [${m.role}]` : ''}` : '');
                wrap.innerHTML = `<div class="lei-support-avatar lei-support-avatar--support">${m.initials || adminInitials}</div>
                    <div class="lei-support-bubble-wrap"><div class="lei-support-bubble"></div>
                    <span class="lei-support-time">${meta}</span></div>`;
                wrap.querySelector('.lei-support-bubble').textContent = m.body || body;
                thread.appendChild(wrap);
                thread.scrollTop = thread.scrollHeight;
            }
            input.value = '';
            updateTableRow(data.row);
            updateStats(data.stats);
            showToast(data.message);
        } catch (err) {
            showToast(err.message);
        }
    }

    async function onNoteBlur(e) {
        const ta = e.target;
        const body = ta?.value?.trim();
        if (!body || !page.dataset.noteUrl) return;
        try {
            const data = await postJson(page.dataset.noteUrl, { body });
            const log = document.getElementById('leiSupportNotesLog');
            const n = data.note || {};
            const author = n.author_name || 'Admin';
            if (log) {
                const entry = document.createElement('div');
                entry.className = 'lei-support-note-entry';
                entry.innerHTML = `<div class="lei-support-avatar lei-support-avatar--admin">${n.initials || adminInitials}</div>
                    <div class="lei-support-note-text"><p><strong>${escapeHtml(author)} added:</strong> <em></em></p>
                    <span class="lei-support-time">${n.time_label || 'Just now'}</span></div>`;
                entry.querySelector('em').textContent = n.body || body;
                log.appendChild(entry);
            }
            ta.value = '';
            showToast(data.message);
        } catch (err) {
            showToast(err.message);
        }
    }

    async function onActionClick() {
        const btn = this;
        const action = btn.dataset.action;
        if (!action || !page.dataset.actionUrl || btn.disabled) return;
        try {
            const data = await postJson(page.dataset.actionUrl, { action });
            if (data.html && detailWrap) {
                detailWrap.innerHTML = data.html;
                bindDetailEvents();
            }
            setPageUrls(data.urls);
            updateTableRow(data.row);
            updateStats(data.stats);
            updateCategories(data.categories);
            document.querySelectorAll('.js-support-row').forEach((r) => {
                r.classList.toggle('lei-support-row--active', r.dataset.ticketId === String(data.row?.id));
            });
            showToast(data.message);
        } catch (err) {
            showToast(err.message);
        }
    }

    async function loadTicket(id) {
        const url = page.dataset.ticketUrl.replace('__ID__', id);
        const res = await fetch(url, { headers: { Accept: 'application/json' } });
        const data = await res.json();
        if (!res.ok || !data.ok) throw new Error(data.message || 'Failed to load ticket');
        if (detailWrap) {
            detailWrap.innerHTML = data.html;
            bindDetailEvents();
        }
        setPageUrls(data.urls);
        updateTableRow(data.row);
        updateStats(data.stats);
        updateCategories(data.categories);
        document.querySelectorAll('.js-support-row').forEach((r) => {
            r.classList.toggle('lei-support-row--active', r.dataset.ticketId === String(id));
        });
        const filterTicket = document.querySelector('#leiSupportFilterPanel input[name="ticket"]');
        if (filterTicket) filterTicket.value = id;
        history.replaceState(null, '', updateUrlParam('ticket', id));
    }

    function updateUrlParam(key, val) {
        const u = new URL(window.location.href);
        u.searchParams.set(key, val);
        return u.toString();
    }

    document.querySelectorAll('.js-support-row').forEach((row) => {
        row.addEventListener('click', async (e) => {
            if (e.metaKey || e.ctrlKey) return;
            e.preventDefault();
            try {
                await loadTicket(row.dataset.ticketId);
            } catch (err) {
                showToast(err.message);
            }
        });
    });

    document.getElementById('leiSupportFilterBtn')?.addEventListener('click', () => {
        const panel = document.getElementById('leiSupportFilterPanel');
        const btn = document.getElementById('leiSupportFilterBtn');
        if (!panel) return;
        const open = panel.hidden;
        panel.hidden = !open;
        if (btn) btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });

    function openModal(id) {
        const el = document.getElementById(id);
        if (el) el.hidden = false;
    }

    function closeModals() {
        document.querySelectorAll('.lei-support-modal-overlay').forEach((m) => { m.hidden = true; });
    }

    document.querySelectorAll('.js-modal-close').forEach((b) => {
        b.addEventListener('click', closeModals);
    });

    document.querySelectorAll('.lei-support-modal-overlay').forEach((overlay) => {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) closeModals();
        });
    });

    document.getElementById('leiSupportNewTicket')?.addEventListener('click', () => openModal('leiSupportModalTicket'));

    document.getElementById('leiSupportNewCategory')?.addEventListener('click', () => {
        const form = document.getElementById('leiSupportCategoryForm');
        document.getElementById('leiSupportCatModalTitle').textContent = 'New Category';
        form.category_id.value = '';
        form.name.value = '';
        openModal('leiSupportModalCategory');
    });

    function bindCatEdit() {
        document.querySelectorAll('.js-cat-edit').forEach((btn) => {
            btn.addEventListener('click', () => {
                const form = document.getElementById('leiSupportCategoryForm');
                document.getElementById('leiSupportCatModalTitle').textContent = 'Edit Category';
                form.category_id.value = btn.dataset.id;
                form.name.value = btn.dataset.name;
                openModal('leiSupportModalCategory');
            });
        });
    }
    bindCatEdit();

    document.getElementById('leiSupportTicketForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(e.target);
        try {
            const data = await postJson(page.dataset.storeTicketUrl, Object.fromEntries(fd));
            closeModals();
            showToast(data.message);
            updateStats(data.stats);
            if (data.redirect) window.location.href = data.redirect;
        } catch (err) {
            showToast(err.message);
        }
    });

    document.getElementById('leiSupportCategoryForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(e.target);
        const id = fd.get('category_id');
        const url = id
            ? page.dataset.updateCategoryUrl.replace('__ID__', id)
            : page.dataset.storeCategoryUrl;
        try {
            const data = await postJson(url, { name: fd.get('name') });
            closeModals();
            updateCategories(data.categories);
            showToast(data.message);
        } catch (err) {
            showToast(err.message);
        }
    });

    bindDetailEvents();

    const hasFilters = new URLSearchParams(window.location.search);
    if (hasFilters.has('status') || hasFilters.has('priority') || hasFilters.has('category') || hasFilters.has('q')) {
        const panel = document.getElementById('leiSupportFilterPanel');
        const btn = document.getElementById('leiSupportFilterBtn');
        if (panel) panel.hidden = false;
        if (btn) btn.setAttribute('aria-expanded', 'true');
    }
})();
