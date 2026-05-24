/**
 * MMHC CRM — mobile layout helpers (all browsers; native app included).
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
        var root = document.querySelector('.main-content');
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
            var thead = table.querySelector('thead');
            if (!thead) {
                return;
            }
            var headers = [];
            thead.querySelectorAll('th').forEach(function (th) {
                headers.push((th.textContent || '').trim());
            });
            if (headers.length === 0) {
                return;
            }
            table.querySelectorAll('tbody tr').forEach(function (row) {
                row.querySelectorAll('td').forEach(function (td, index) {
                    if (!td.hasAttribute('data-label') && headers[index]) {
                        td.setAttribute('data-label', headers[index]);
                    }
                });
            });
            table.classList.add('mmhc-table-cards');
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

    function initMobileLayout() {
        applyMobileClass();
        markMobileAppShell();
        wrapTables();
        enhanceMobileTables();
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
