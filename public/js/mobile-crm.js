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
            if (table.closest('.table-responsive') || table.closest('.mmhc-table-scroll')) {
                return;
            }
            var wrap = document.createElement('div');
            wrap.className = 'table-responsive mmhc-table-scroll';
            table.parentNode.insertBefore(wrap, table);
            wrap.appendChild(table);
        });
    }

    applyMobileClass();
    wrapTables();

    if (typeof mq.addEventListener === 'function') {
        mq.addEventListener('change', function () {
            applyMobileClass();
        });
    } else if (typeof mq.addListener === 'function') {
        mq.addListener(applyMobileClass);
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
