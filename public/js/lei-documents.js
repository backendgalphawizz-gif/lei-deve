(function () {
    const page = document.querySelector('.lei-doc-page');
    if (!page) return;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const toast = document.getElementById('leiDocToast');
    let previewScale = 1;

    function showToast(msg) {
        if (!toast) return;
        toast.textContent = msg;
        toast.hidden = false;
        setTimeout(() => { toast.hidden = true; }, 3200);
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
            const card = document.querySelector(`.lei-doc-stat-card[data-stat-key="${s.stat_key}"]`);
            const val = card?.querySelector('.lei-doc-stat-value');
            if (val) val.textContent = s.value;
        });
    }

    function updateTableRow(doc) {
        if (!doc) return;
        const row = document.querySelector(`.js-doc-row[data-doc-id="${doc.id}"]`);
        if (!row) return;
        row.querySelector('.lei-doc-code').textContent = doc.document_code;
        const sec = row.querySelector('.lei-doc-security');
        if (sec) {
            sec.className = `lei-doc-security lei-doc-security--${doc.security_tone}`;
            sec.innerHTML = doc.security_tone === 'clean'
                ? '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg> ' + doc.security_label
                : doc.security_label;
        }
        const st = row.querySelector('.lei-doc-status');
        if (st) {
            st.className = 'lei-doc-status lei-doc-status--' + doc.status_tone;
            st.textContent = doc.status;
        }
    }

    function bindSideEvents() {
        document.getElementById('leiDocBtnVerify')?.addEventListener('click', async () => {
            const reason = document.querySelector('#leiDocVerifyForm textarea')?.value?.trim();
            if (!page.dataset.verifyUrl) return;
            try {
                const data = await postJson(page.dataset.verifyUrl, { reason });
                document.getElementById('leiDocSideWrap').innerHTML = data.html;
                if (data.viewer) document.getElementById('leiDocViewerWrap').innerHTML = data.viewer;
                updateTableRow(data.doc);
                updateStats(data.stats);
                bindSideEvents();
                bindViewerTools();
                showToast(data.message);
            } catch (err) {
                showToast(err.message);
            }
        });

        document.getElementById('leiDocBtnReject')?.addEventListener('click', async () => {
            const reason = document.querySelector('#leiDocVerifyForm textarea')?.value?.trim();
            if (!reason) {
                showToast('Please enter a rejection reason.');
                return;
            }
            if (!page.dataset.rejectUrl) return;
            try {
                const data = await postJson(page.dataset.rejectUrl, { reason });
                document.getElementById('leiDocSideWrap').innerHTML = data.html;
                if (data.viewer) document.getElementById('leiDocViewerWrap').innerHTML = data.viewer;
                updateTableRow(data.doc);
                updateStats(data.stats);
                bindSideEvents();
                bindViewerTools();
                showToast(data.message);
            } catch (err) {
                showToast(err.message);
            }
        });
    }

    function bindViewerTools() {
        const img = document.getElementById('leiDocPreviewImg');
        document.getElementById('leiDocZoomIn')?.addEventListener('click', () => {
            if (!img) return;
            previewScale = Math.min(2, previewScale + 0.15);
            img.style.transform = `scale(${previewScale})`;
        });
        document.getElementById('leiDocZoomOut')?.addEventListener('click', () => {
            if (!img) return;
            previewScale = Math.max(0.5, previewScale - 0.15);
            img.style.transform = `scale(${previewScale})`;
        });
    }

    async function loadDocument(id) {
        const url = page.dataset.docUrl.replace('__ID__', id);
        const res = await fetch(url, { headers: { Accept: 'application/json' } });
        const data = await res.json();
        if (!res.ok || !data.ok) throw new Error(data.message || 'Failed to load document');
        document.getElementById('leiDocSideWrap').innerHTML = data.html;
        document.getElementById('leiDocViewerWrap').innerHTML = data.viewer;
        if (data.urls) {
            page.dataset.verifyUrl = data.urls.verify;
            page.dataset.rejectUrl = data.urls.reject;
        }
        previewScale = 1;
        updateTableRow(data.doc);
        updateStats(data.stats);
        document.querySelectorAll('.js-doc-row').forEach((r) => {
            r.classList.toggle('lei-doc-row--active', r.dataset.docId === String(id));
        });
        bindSideEvents();
        bindViewerTools();
        history.replaceState(null, '', updateUrlParam('doc', id));
    }

    function updateUrlParam(key, val) {
        const u = new URL(window.location.href);
        u.searchParams.set(key, val);
        return u.toString();
    }

    document.querySelectorAll('.js-doc-row').forEach((row) => {
        row.addEventListener('click', async (e) => {
            if (e.metaKey || e.ctrlKey) return;
            e.preventDefault();
            try {
                await loadDocument(row.dataset.docId);
            } catch (err) {
                showToast(err.message);
            }
        });
    });

    document.getElementById('leiDocTypeFilter')?.addEventListener('change', () => {
        document.getElementById('leiDocFilterForm')?.submit();
    });

    document.getElementById('leiDocConfigure')?.addEventListener('click', () => {
        showToast('Configure Rules — saved to environment config.');
    });

    bindSideEvents();
    bindViewerTools();
})();
