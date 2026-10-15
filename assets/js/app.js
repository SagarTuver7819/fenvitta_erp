(function () {
    'use strict';

    const fmt = (n, d = 2) => Number(n || 0).toLocaleString('en-IN', { minimumFractionDigits: d, maximumFractionDigits: d });
    const num = (v) => parseFloat(String(v || '').replace(/,/g, '')) || 0;
    window.fvFmt = fmt;
    window.fvNum = num;

    /* ================= Page loader ================= */
    const pageLoader = document.getElementById('pageLoader');
    const hideLoader = () => pageLoader && pageLoader.classList.add('hide');
    if (document.readyState === 'complete') hideLoader(); else window.addEventListener('load', hideLoader);
    setTimeout(hideLoader, 2500);

    const splash = document.getElementById('welcomeSplash');
    if (splash) setTimeout(() => splash.classList.add('hide'), 1900);

    // Top progress bar when moving to another page
    const topLoader = document.getElementById('topLoader');
    const startTopLoader = () => topLoader && topLoader.classList.add('run');
    document.addEventListener('click', (e) => {
        const a = e.target.closest('a[href]');
        if (!a || e.defaultPrevented || e.ctrlKey || e.metaKey || e.shiftKey) return;
        const href = a.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript') || a.target === '_blank' || a.hasAttribute('download') || a.dataset.bsToggle) return;
        startTopLoader();
    });
    window.addEventListener('pageshow', () => topLoader && topLoader.classList.remove('run'));

    /* ================= Toast ================= */
    const toastIcons = { success: 'bi-check-circle-fill', danger: 'bi-x-circle-fill', warning: 'bi-exclamation-triangle-fill', info: 'bi-info-circle-fill' };
    const toastTitles = { success: 'Success', danger: 'Error', warning: 'Warning', info: 'Info' };
    window.fvToast = function (type, msg, ms = 4500) {
        const box = document.getElementById('toastBox');
        if (!box) return;
        type = toastIcons[type] ? type : 'info';
        const t = document.createElement('div');
        t.className = 'fv-toast ' + type;
        t.innerHTML = '<i class="bi ' + toastIcons[type] + ' ti"></i><div class="tb"><b>' + toastTitles[type] + '</b><div></div></div>'
            + '<button type="button" class="tc"><i class="bi bi-x"></i></button><span class="tp" style="animation-duration:' + ms + 'ms"></span>';
        t.querySelector('.tb div').textContent = msg;
        const close = () => { t.classList.add('out'); setTimeout(() => t.remove(), 350); };
        t.querySelector('.tc').onclick = close;
        box.appendChild(t);
        setTimeout(close, ms);
    };
    (window.FV_FLASH || []).forEach((f, i) => setTimeout(() => fvToast(f.type, f.msg), 300 + i * 200));

    /* ================= Confirm dialog (replaces browser confirm) ================= */
    const confirmModalEl = document.getElementById('confirmModal');
    let confirmAction = null;
    const askConfirm = (text, action) => {
        if (!confirmModalEl || !window.bootstrap) { if (confirm(text)) action(); return; }
        document.getElementById('confirmText').textContent = text;
        confirmAction = action;
        bootstrap.Modal.getOrCreateInstance(confirmModalEl).show();
    };
    if (confirmModalEl) {
        document.getElementById('confirmYes').addEventListener('click', () => {
            bootstrap.Modal.getInstance(confirmModalEl).hide();
            if (confirmAction) confirmAction();
        });
    }
    document.addEventListener('click', (e) => {
        const a = e.target.closest('a[data-confirm]');
        if (!a) return;
        e.preventDefault();
        askConfirm(a.dataset.confirm, () => { startTopLoader(); location.href = a.href; });
    });

    /* ================= Form submit: confirm + button loading ================= */
    document.addEventListener('submit', (e) => {
        const f = e.target;
        if (f.dataset.confirm && !f.dataset.confirmed) {
            e.preventDefault();
            askConfirm(f.dataset.confirm, () => { f.dataset.confirmed = '1'; f.requestSubmit ? f.requestSubmit() : f.submit(); });
            return;
        }
        if (e.defaultPrevented || f.method.toLowerCase() === 'get' && !f.querySelector('button[type=submit],button:not([type])')) return;
        const btn = e.submitter || f.querySelector('button:not([type=button])');
        if (btn && !btn.classList.contains('no-loading')) {
            // keep the clicked button name/value (e.g. "save_print") because disabled buttons are not submitted
            if (btn.name) {
                const h = document.createElement('input');
                h.type = 'hidden'; h.name = btn.name; h.value = btn.value;
                f.appendChild(h);
            }
            btn.dataset.html = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> ' + (f.method.toLowerCase() === 'get' ? 'Loading' : 'Saving') + '...';
            setTimeout(() => { btn.disabled = true; }, 0);
        }
        startTopLoader();
    });
    window.addEventListener('pageshow', () => {
        document.querySelectorAll('button[data-html]').forEach((b) => { b.innerHTML = b.dataset.html; b.disabled = false; });
    });

    /* ================= Sidebar toggle ================= */
    const html = document.documentElement;
    const isMobile = () => window.innerWidth < 992;
    let tips = [];
    const setTooltips = () => {
        tips.forEach((t) => t.dispose());
        tips = [];
        if (!window.bootstrap || isMobile() || !html.classList.contains('sb-mini')) return;
        document.querySelectorAll('.side-nav .nav-link').forEach((a) => {
            tips.push(new bootstrap.Tooltip(a, { title: a.dataset.title, placement: 'right', trigger: 'hover', customClass: 'sb-tip' }));
        });
    };
    document.getElementById('sbToggle')?.addEventListener('click', () => {
        if (isMobile()) {
            document.body.classList.toggle('sb-open');
        } else {
            html.classList.toggle('sb-mini');
            localStorage.setItem('fv-sb-mini', html.classList.contains('sb-mini') ? '1' : '0');
            setTooltips();
            setTimeout(() => window.dispatchEvent(new Event('resize')), 320); // let charts resize
        }
    });
    document.getElementById('sbBackdrop')?.addEventListener('click', () => document.body.classList.remove('sb-open'));
    window.addEventListener('resize', () => { if (!isMobile()) document.body.classList.remove('sb-open'); });
    setTooltips();
    document.querySelector('.side-nav .nav-link.active')?.scrollIntoView({ block: 'center' });

    /* ================= Sidebar menu search ================= */
    const menuSearch = document.getElementById('menuSearch');
    if (menuSearch) {
        const links = document.querySelectorAll('.side-nav .nav-link');
        const sections = document.querySelectorAll('.side-nav .nav-section');
        const filterMenu = () => {
            const q = menuSearch.value.trim().toLowerCase();
            let found = 0;
            links.forEach((a) => {
                const text = (a.dataset.title + ' ' + a.dataset.section).toLowerCase();
                const show = q === '' || text.includes(q);
                a.classList.toggle('d-none', !show);
                if (show) found++;
            });
            // Hide section title when none of its links are visible
            sections.forEach((s) => {
                const visible = [...links].some((a) => a.dataset.section === s.dataset.section && !a.classList.contains('d-none'));
                s.classList.toggle('d-none', !visible);
            });
            document.getElementById('menuEmpty').classList.toggle('show', found === 0);
            menuSearch.parentElement.classList.toggle('has-text', q !== '');
        };
        menuSearch.addEventListener('input', filterMenu);
        menuSearch.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') { menuSearch.value = ''; filterMenu(); }
            if (e.key === 'Enter') {
                const first = [...links].find((a) => !a.classList.contains('d-none'));
                if (first) { startTopLoader(); location.href = first.href; }
            }
        });
        document.getElementById('menuSearchClear').addEventListener('click', () => { menuSearch.value = ''; filterMenu(); menuSearch.focus(); });
        // Mini sidebar: clicking the search icon opens full sidebar and focuses search
        menuSearch.parentElement.addEventListener('click', () => {
            if (!isMobile() && html.classList.contains('sb-mini')) {
                html.classList.remove('sb-mini');
                localStorage.setItem('fv-sb-mini', '0');
                setTooltips();
                setTimeout(() => menuSearch.focus(), 300);
            }
        });
        // Shortcut: press "/" anywhere to search the menu
        document.addEventListener('keydown', (e) => {
            if (e.key === '/' && !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) {
                e.preventDefault();
                if (isMobile()) document.body.classList.add('sb-open');
                menuSearch.focus();
            }
        });
    }

    /* ================= Live clock in top bar ================= */
    const clock = document.getElementById('liveClock');
    if (clock) {
        const dayEl = document.getElementById('liveDay');
        const tick = () => {
            const now = new Date();
            clock.textContent = now.toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true }).toUpperCase();
            dayEl.textContent = now.toLocaleDateString('en-IN', { weekday: 'long' });
        };
        tick();
        setInterval(tick, 1000);
    }

    /* ================= Count-up numbers ================= */
    document.querySelectorAll('[data-count]').forEach((el) => {
        const target = num(el.dataset.count);
        const dec = parseInt(el.dataset.dec || '0', 10);
        const prefix = el.dataset.prefix || '';
        const finalText = el.textContent.trim();
        const dur = 1300;
        const t0 = performance.now() + (splash ? 1700 : 200);
        el.textContent = prefix + fmt(0, dec);
        const step = (now) => {
            const p = Math.min(Math.max((now - t0) / dur, 0), 1);
            const eased = 1 - Math.pow(1 - p, 3);
            el.textContent = prefix + fmt(target * eased, dec);
            if (p < 1) requestAnimationFrame(step);
            else el.textContent = finalText;
        };
        requestAnimationFrame(step);
    });

    // Animate progress bars from 0
    document.querySelectorAll('.progress-bar[data-width]').forEach((b) => {
        setTimeout(() => { b.style.width = b.dataset.width + '%'; }, splash ? 1800 : 300);
    });

    /* ================= Table search ================= */
    document.querySelectorAll('[data-table-search]').forEach((inp) => {
        const table = document.querySelector(inp.dataset.tableSearch);
        if (!table) return;
        inp.addEventListener('input', () => {
            const q = inp.value.toLowerCase();
            table.querySelectorAll('tbody tr').forEach((tr) => {
                tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    });

    /* ================= Export table to CSV (opens in Excel) ================= */
    document.querySelectorAll('[data-export]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const table = document.querySelector(btn.dataset.export);
            if (!table) return;
            const lines = [];
            table.querySelectorAll('tr').forEach((tr) => {
                if (tr.style.display === 'none' || tr.classList.contains('no-export')) return;
                const cells = [...tr.querySelectorAll('th,td')].filter((c) => !c.classList.contains('no-export'));
                lines.push(cells.map((c) => '"' + c.innerText.replace(/\s+/g, ' ').trim().replace(/"/g, '""') + '"').join(','));
            });
            const blob = new Blob(['\ufeff' + lines.join('\r\n')], { type: 'text/csv;charset=utf-8' });
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = (btn.dataset.filename || 'export') + '.csv';
            a.click();
            fvToast('success', 'Excel file downloaded: ' + a.download, 2500);
        });
    });

    /* ================= Line-item forms (purchase / sales) ================= */
    document.querySelectorAll('form[data-line-items]').forEach((form) => {
        const tbody = form.querySelector('.item-table tbody');
        const tpl = form.querySelector('template.row-tpl');

        const recalc = () => {
            let sub = 0, gst = 0;
            tbody.querySelectorAll('tr').forEach((tr, i) => {
                const q = num(tr.querySelector('.f-qty')?.value);
                const r = num(tr.querySelector('.f-rate')?.value);
                const g = num(tr.querySelector('.f-gst')?.value);
                const amt = q * r, ga = amt * g / 100;
                sub += amt; gst += ga;
                const sn = tr.querySelector('.f-sn'); if (sn) sn.textContent = i + 1;
                const a = tr.querySelector('.f-amount'); if (a) a.textContent = fmt(amt);
                const t = tr.querySelector('.f-total'); if (t) t.textContent = fmt(amt + ga);
            });
            const freight = num(form.querySelector('[name=freight]')?.value);
            const other = num(form.querySelector('[name=other_charges]')?.value);
            const set = (sel, v) => { const el = form.querySelector(sel); if (el) el.textContent = fmt(v); };
            set('.t-sub', sub); set('.t-gst', gst); set('.t-total', sub + gst + freight + other);
        };

        const bindRow = (tr) => {
            const sel = tr.querySelector('.f-item');
            if (sel) sel.addEventListener('change', () => {
                const o = sel.selectedOptions[0];
                if (!o) return;
                const u = tr.querySelector('.f-unit'); if (u) u.value = o.dataset.unit || '';
                const g = tr.querySelector('.f-gst'); if (g && o.dataset.gst !== undefined) g.value = o.dataset.gst;
                const r = tr.querySelector('.f-rate'); if (r && !num(r.value) && num(o.dataset.rate)) r.value = o.dataset.rate;
                const gr = tr.querySelector('.f-grade'); if (gr && !gr.value) gr.value = o.dataset.grade || '';
                recalc();
            });
            tr.querySelector('.f-remove')?.addEventListener('click', () => {
                if (tbody.querySelectorAll('tr').length > 1) {
                    tr.classList.add('row-out');
                    setTimeout(() => { tr.remove(); recalc(); }, 250);
                } else {
                    fvToast('warning', 'At least one row is required.', 2500);
                }
            });
        };

        form.querySelector('.add-row')?.addEventListener('click', () => {
            const tr = tpl.content.firstElementChild.cloneNode(true);
            tr.classList.add('row-in');
            tbody.appendChild(tr);
            bindRow(tr);
            recalc();
        });
        tbody.querySelectorAll('tr').forEach(bindRow);
        form.addEventListener('input', recalc);
        recalc();
    });

    /* ================= Party select -> payment terms / location ================= */
    document.querySelectorAll('select[data-party-terms]').forEach((sel) => {
        sel.addEventListener('change', () => {
            const o = sel.selectedOptions[0];
            const t = document.querySelector(sel.dataset.partyTerms);
            if (t && o && o.dataset.terms !== undefined) t.value = o.dataset.terms;
            const loc = document.querySelector(sel.dataset.partyCity || '#__none');
            if (loc && o && !loc.value) loc.value = o.dataset.city || '';
        });
    });

    /* ================= Item select -> stock hint ================= */
    document.querySelectorAll('select[data-stock-hint]').forEach((sel) => {
        const hint = document.querySelector(sel.dataset.stockHint);
        const upd = () => {
            const o = sel.selectedOptions[0];
            if (hint) hint.textContent = o && o.dataset.stock !== undefined ? 'Available: ' + o.dataset.stock + ' ' + (o.dataset.unit || '') : '';
            const rate = document.querySelector(sel.dataset.rateTarget || '#__none');
            if (rate && o && o.dataset.rate !== undefined) rate.value = o.dataset.rate;
        };
        sel.addEventListener('change', upd);
        upd();
    });
})();
