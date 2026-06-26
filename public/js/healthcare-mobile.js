/**
 * Healthcare mobile — table cards + touch polish
 */
(function () {
    var mq = window.matchMedia('(max-width: 767.98px)');

    function isHealthcareMobile() {
        return mq.matches && document.documentElement.classList.contains('mmhc-healthcare-mobile');
    }

    function enhanceTables() {
        if (!isHealthcareMobile()) {
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

    function init() {
        enhanceTables();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    mq.addEventListener('change', init);
})();
