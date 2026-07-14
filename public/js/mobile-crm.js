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
        if (main.querySelector('.mobile-app-container, .app-mobile-header, .app-header-mobile, .community-page, .admin-mobile-shell--layout')) {
            document.body.classList.add('mmhc-mobile-app-shell');
        }
    }

    /** Toast stack — UI-only replacement for short alert() messages on phone */
    function initToastStack() {
        if (document.getElementById('mmhcToastStack')) {
            return;
        }
        var stack = document.createElement('div');
        stack.id = 'mmhcToastStack';
        stack.className = 'mmhc-toast-stack';
        stack.setAttribute('aria-live', 'polite');
        stack.setAttribute('aria-atomic', 'true');
        document.body.appendChild(stack);

        window.mmhcToast = function (message, type) {
            if (!message) {
                return;
            }
            var toast = document.createElement('div');
            toast.className = 'mmhc-toast mmhc-toast--' + (type || 'info');
            toast.setAttribute('role', 'status');
            toast.textContent = String(message);
            stack.appendChild(toast);
            requestAnimationFrame(function () {
                toast.classList.add('is-visible');
            });
            window.setTimeout(function () {
                toast.classList.remove('is-visible');
                window.setTimeout(function () {
                    toast.remove();
                }, 220);
            }, 3200);
        };

        if (mq.matches && !window.__mmhcAlertPatched) {
            window.__mmhcAlertPatched = true;
            var nativeAlert = window.alert;
            window.alert = function (msg) {
                if (typeof msg === 'string' && msg.length > 0 && msg.length <= 240 && !msg.includes('\n')) {
                    window.mmhcToast(msg, 'info');
                    return;
                }
                nativeAlert(msg);
            };
        }
    }

    function hidePageSkeleton() {
        document.querySelectorAll('[data-mmhc-skeleton]').forEach(function (el) {
            el.classList.add('is-hidden');
            el.setAttribute('aria-hidden', 'true');
        });
    }

    /** Admin GET filter forms → bottom sheet on phone (fields unchanged) */
    function enhanceAdminFilterSheets() {
        if (!mq.matches) {
            return;
        }
        var root = document.querySelector('.main-content') || document.body;
        root.querySelectorAll('form[method="get"]').forEach(function (form) {
            if (form.getAttribute('data-mmhc-filter-sheet') === '1') {
                return;
            }
            if (form.id === 'searchFilterForm' || form.closest('.navbar, .app-bottom-nav, .mmhc-action-notices')) {
                return;
            }
            var fields = form.querySelectorAll('input:not([type="hidden"]):not([type="submit"]):not([type="button"]), select, textarea');
            if (fields.length < 2 || fields.length > 10) {
                return;
            }
            if (!form.querySelector('button[type="submit"], input[type="submit"]')) {
                return;
            }
            form.setAttribute('data-mmhc-filter-sheet', '1');
            form.classList.add('mmhc-admin-filter-panel');

            var toggle = document.createElement('button');
            toggle.type = 'button';
            toggle.className = 'mmhc-admin-filter-toggle d-md-none';
            toggle.setAttribute('aria-expanded', 'false');
            toggle.innerHTML = '<i class="fas fa-sliders-h" aria-hidden="true"></i> Filters';

            var backdrop = document.createElement('div');
            backdrop.className = 'mmhc-filter-sheet-backdrop';
            backdrop.setAttribute('aria-hidden', 'true');

            function setOpen(open) {
                form.classList.toggle('is-open', open);
                backdrop.classList.toggle('is-open', open);
                toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                toggle.innerHTML = open
                    ? '<i class="fas fa-times" aria-hidden="true"></i> Close'
                    : '<i class="fas fa-sliders-h" aria-hidden="true"></i> Filters';
                document.body.classList.toggle('mmhc-filter-sheet-open', open);
            }

            toggle.addEventListener('click', function () {
                setOpen(!form.classList.contains('is-open'));
            });
            backdrop.addEventListener('click', function () {
                setOpen(false);
            });

            form.parentNode.insertBefore(toggle, form);
            document.body.appendChild(backdrop);
        });
    }

    /** Legacy Tailwind admin CMS — inject compact mobile header */
    function enhanceAdminStandaloneChrome() {
        if (!mq.matches || !document.body.classList.contains('mmhc-admin-standalone')) {
            return;
        }
        if (document.querySelector('.mmhc-admin-mobile-header')) {
            return;
        }
        var desktopHeader = document.querySelector('header');
        var titleEl = desktopHeader ? desktopHeader.querySelector('h1') : null;
        var title = titleEl ? (titleEl.textContent || '').trim() : 'Admin';
        var backHref = desktopHeader ? desktopHeader.querySelector('a[href*="dashboard"]') : null;
        var backUrl = (window.mmhcAdminDashboardUrl || '/admin/dashboard');
        if (backHref) {
            backUrl = backHref.getAttribute('href') || backUrl;
        }

        var bar = document.createElement('header');
        bar.className = 'mmhc-admin-mobile-header';
        bar.setAttribute('role', 'banner');
        bar.innerHTML =
            '<div class="mmhc-admin-mobile-header__bar">' +
            '<a href="' + backUrl + '" class="mmhc-admin-mobile-header__back" aria-label="Back">' +
            '<i class="fas fa-arrow-left" aria-hidden="true"></i></a>' +
            '<div class="mmhc-admin-mobile-header__titles">' +
            '<h1 class="mmhc-admin-mobile-header__title"></h1>' +
            '<p class="mmhc-admin-mobile-header__subtitle mb-0">MMHC Admin</p></div></div>';
        bar.querySelector('.mmhc-admin-mobile-header__title').textContent = title;

        var mount = document.querySelector('.min-h-screen') || document.body;
        mount.insertBefore(bar, mount.firstChild);
        if (desktopHeader) {
            desktopHeader.classList.add('mmhc-admin-standalone-desktop-header');
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
        document.querySelectorAll('.staff-earnings-fold').forEach(function (fold) {
            fold.open = false;
        });
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

    /** Jobs tab scrolls to assignments; open past jobs on desktop */
    function enhanceStaffJobsTab() {
        document.querySelectorAll('[data-mmhc-staff-jobs-tab]').forEach(function (link) {
            link.addEventListener('click', function () {
                setTimeout(function () {
                    var el = document.getElementById('today-jobs') || document.getElementById('assignments');
                    if (el) {
                        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }, 50);
            });
        });
        if (!mq.matches) {
            document.querySelectorAll('.staff-past-jobs').forEach(function (el) {
                el.open = true;
            });
        }
    }

    function initMobileLayout() {
        applyMobileClass();
        initToastStack();
        markMobileAppShell();
        wrapTables();
        enhanceMobileTables();
        enhanceStickyFormActions();
        enhanceStaffEarningsCards();
        enhanceStaffJobsTab();
        enhanceAdminFilterSheets();
        enhanceAdminStandaloneChrome();
        bindActionNoticesToggle();
        hidePageSkeleton();
    }

    initMobileLayout();

    window.addEventListener('load', hidePageSkeleton);

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
