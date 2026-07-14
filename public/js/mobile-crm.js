/**
 * MMHC CRM — mobile layout helpers (all browsers; native app included).
 * UI-only: card tables, sticky form bars, compact notices. No API/route changes.
 */
(function () {
    var mq = window.matchMedia('(max-width: 767.98px)');

    function applyMobileClass() {
        if (mq.matches) {
            document.documentElement.classList.add('mmhc-mobile');
        } else {
            document.documentElement.classList.remove('mmhc-mobile');
        }
    }

    function wrapTables() {
        if (!mq.matches) {
            return;
        }
        var root = document.querySelector('.main-content') || document.querySelector('body.mmhc-admin-standalone main');
        if (!root) {
            return;
        }
        root.querySelectorAll('table').forEach(function (table) {
            if (table.closest('.table-responsive') || table.closest('.mmhc-table-scroll') || table.closest('.um-table-wrap') || table.classList.contains('mmhc-no-mobile-cards')) {
                return;
            }
            var wrap = document.createElement('div');
            wrap.className = 'table-responsive mmhc-table-scroll';
            table.parentNode.insertBefore(wrap, table);
            wrap.appendChild(table);
        });
    }

    /** Convert list tables to labeled card rows on mobile. */
    function enhanceMobileTables() {
        if (!mq.matches) {
            return;
        }
        var root = document.querySelector('.main-content') || document.querySelector('body.mmhc-admin-standalone main');
        if (!root) {
            return;
        }
        root.querySelectorAll('table').forEach(function (table) {
            if (table.classList.contains('mmhc-table-cards') || table.classList.contains('mmhc-no-mobile-cards')) {
                return;
            }
            if (table.closest('.mmhc-no-mobile-cards')) {
                return;
            }
            // Skip layout / matrix tables with few header cells but many columns (calendars etc.)
            var thead = table.querySelector('thead');
            if (!thead) {
                return;
            }
            var headers = [];
            thead.querySelectorAll('th').forEach(function (th) {
                headers.push((th.textContent || '').trim());
            });
            if (headers.length < 2 || headers.length > 12) {
                return;
            }
            var rows = table.querySelectorAll('tbody tr');
            if (rows.length === 0) {
                return;
            }
            rows.forEach(function (row) {
                row.querySelectorAll('td').forEach(function (td, index) {
                    if (!td.hasAttribute('data-label') && headers[index]) {
                        td.setAttribute('data-label', headers[index]);
                    }
                });
            });
            table.classList.add('mmhc-table-cards');
            if (table.closest('.card')) {
                table.closest('.card').classList.add('mmhc-has-table-cards');
            }
        });
    }

    function markMobileAppShell() {
        document.body.classList.remove('mmhc-mobile-app-shell');
        if (!mq.matches) {
            return;
        }
        var main = document.querySelector('.main-content');
        if (!main) {
            return;
        }
        if (main.querySelector('.mobile-app-container, .app-mobile-header, .app-header-mobile, .community-page')) {
            document.body.classList.add('mmhc-mobile-app-shell');
        }
    }

    /** Sticky primary actions on long forms — same buttons, better reachability. */
    function enhanceStickyFormActions() {
        if (!mq.matches) {
            return;
        }
        document.querySelectorAll('.main-content form, .mobile-app-container form, body.mmhc-admin-standalone form').forEach(function (form) {
            if (form.getAttribute('data-mmhc-sticky') === '1') {
                return;
            }
            if (form.closest('.app-alert, .mmhc-action-notices, .navbar, .staff-location-panel, .app-bottom-nav')) {
                return;
            }
            // Skip tiny filter / search forms
            var fields = form.querySelectorAll('input:not([type="hidden"]):not([type="submit"]):not([type="button"]), select, textarea');
            if (fields.length < 3) {
                return;
            }
            var submit = form.querySelector('button[type="submit"], input[type="submit"]');
            if (!submit) {
                return;
            }
            var bar = submit.closest('.app-form-actions, .form-actions, .mmhc-sticky-actions, .d-grid');
            if (!bar) {
                // Prefer a trailing button group in the form
                var parent = submit.parentElement;
                if (parent && (parent.classList.contains('d-flex') || parent.classList.contains('btn-toolbar') || parent.tagName === 'DIV')) {
                    var siblingButtons = parent.querySelectorAll('button, a.btn, input[type="submit"]');
                    if (siblingButtons.length >= 1 && siblingButtons.length <= 4) {
                        bar = parent;
                    }
                }
            }
            if (!bar || bar.classList.contains('mmhc-sticky-actions')) {
                if (bar) form.setAttribute('data-mmhc-sticky', '1');
                return;
            }
            // Don't sticky if bar is a filter toolbar at top of form
            var formHeight = form.offsetHeight || 0;
            if (formHeight > 0 && bar.offsetTop < formHeight * 0.35 && fields.length < 6) {
                return;
            }
            bar.classList.add('mmhc-sticky-actions');
            form.setAttribute('data-mmhc-sticky', '1');
        });
    }

    function bindActionNoticesToggle() {
        document.querySelectorAll('[data-mmhc-notices-toggle]').forEach(function (btn) {
            if (btn.getAttribute('data-mmhc-bound') === '1') {
                return;
            }
            btn.setAttribute('data-mmhc-bound', '1');
            btn.addEventListener('click', function () {
                var stack = btn.closest('[data-mmhc-action-notices]');
                if (!stack) return;
                var open = stack.classList.toggle('is-open');
                btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            });
        });
    }

    /** Collapse verbose earnings breakdown on staff mobile — same data, less scroll */
    function enhanceStaffEarningsCards() {
        if (!mq.matches || !document.body.classList.contains('mmhc-healthcare-role-staff')) {
            return;
        }
        document.querySelectorAll('.earnings-source-card').forEach(function (card) {
            if (card.querySelector('.earnings-source-toggle')) {
                return;
            }
            var details = card.querySelector('.earnings-source-details');
            if (!details) {
                return;
            }
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'earnings-source-toggle';
            btn.textContent = 'Show breakdown';
            btn.addEventListener('click', function () {
                var expanded = card.classList.toggle('is-expanded');
                btn.textContent = expanded ? 'Hide breakdown' : 'Show breakdown';
            });
            details.parentNode.insertBefore(btn, details);
        });
    }

    function initMobileLayout() {
        applyMobileClass();
        markMobileAppShell();
        wrapTables();
        enhanceMobileTables();
        enhanceStickyFormActions();
        enhanceStaffEarningsCards();
        bindActionNoticesToggle();
    }

    initMobileLayout();

    if (typeof mq.addEventListener === 'function') {
        mq.addEventListener('change', initMobileLayout);
    } else if (typeof mq.addListener === 'function') {
        mq.addListener(initMobileLayout);
    }

    document.addEventListener('shown.bs.offcanvas', function (e) {
        if (e.target && e.target.id === 'mmhcAppSidebar') {
            document.body.classList.add('mmhc-sidebar-open');
        }
    });
    document.addEventListener('hidden.bs.offcanvas', function (e) {
        if (e.target && e.target.id === 'mmhcAppSidebar') {
            document.body.classList.remove('mmhc-sidebar-open');
        }
    });
})();
