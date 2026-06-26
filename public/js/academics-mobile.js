/**
 * Academics mobile shell — table cards, FAB hints, page transitions
 */
(function () {
    var mq = window.matchMedia('(max-width: 767.98px)');

    function isAcademicsMobile() {
        return mq.matches && document.documentElement.classList.contains('mmhc-academics-mobile');
    }

    function enhanceAcademicsTables() {
        if (!isAcademicsMobile()) {
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

    function wrapAcademicsContent() {
        if (!isAcademicsMobile()) {
            return;
        }
        var main = document.querySelector('.main-content');
        if (!main || main.querySelector('.acad-page-wrap')) {
            return;
        }
        var header = main.querySelector('.acad-mobile-header');
        var dashHeader = main.querySelector('.acad-dash-header');
        var startNode = header || dashHeader;
        if (!startNode) {
            return;
        }
        var wrap = document.createElement('div');
        wrap.className = 'acad-page-wrap';
        var node = startNode.nextElementSibling;
        if (!node) {
            return;
        }
        startNode.parentNode.insertBefore(wrap, node);
        while (node) {
            var next = node.nextElementSibling;
            if (node.classList && node.classList.contains('app-bottom-nav')) {
                break;
            }
            wrap.appendChild(node);
            node = next;
        }
    }

    function addCreateFab() {
        if (!isAcademicsMobile()) {
            return;
        }
        if (document.querySelector('.acad-fab')) {
            return;
        }
        var toolbar = document.querySelector('.academics-page-toolbar') ||
            document.querySelector('.d-flex.flex-wrap.align-items-center.justify-content-between.gap-2.mb-3');
        if (!toolbar) {
            return;
        }
        var link = toolbar.querySelector('a.btn-primary[href*="create"]');
        if (!link) {
            toolbar.querySelectorAll('a.btn-primary').forEach(function (a) {
                if (!link && a.querySelector('.fa-plus')) {
                    link = a;
                }
            });
        }
        if (!link) {
            link = toolbar.querySelector('a.btn-primary');
        }
        if (!link || !link.href) {
            return;
        }
        var fab = document.createElement('a');
        fab.href = link.href;
        fab.className = 'acad-fab d-md-none';
        fab.setAttribute('aria-label', (link.textContent || 'Create').trim());
        fab.innerHTML = '<i class="fas fa-plus" aria-hidden="true"></i>';
        document.body.appendChild(fab);
    }

    function init() {
        enhanceAcademicsTables();
        wrapAcademicsContent();
        addCreateFab();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    mq.addEventListener('change', init);
})();
