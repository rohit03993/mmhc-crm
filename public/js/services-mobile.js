/**
 * MMHC patient booking UI helpers (Package B)
 */
(function () {
    'use strict';

    var mqMobile = window.matchMedia('(max-width: 767.98px)');

    var filterToggle = document.getElementById('mmhcFilterToggle');
    var filterPanel = document.getElementById('mmhcFilterPanel');
    var filterBackdrop = null;

    function ensureFilterBackdrop() {
        if (filterBackdrop || !filterPanel) {
            return filterBackdrop;
        }
        filterBackdrop = document.createElement('div');
        filterBackdrop.className = 'mmhc-filter-sheet-backdrop';
        filterBackdrop.setAttribute('aria-hidden', 'true');
        document.body.appendChild(filterBackdrop);
        filterBackdrop.addEventListener('click', function () {
            closeFilterSheet();
        });
        return filterBackdrop;
    }

    function setFilterOpen(open) {
        if (!filterToggle || !filterPanel) {
            return;
        }
        if (open) {
            filterPanel.classList.add('is-open');
            var backdrop = ensureFilterBackdrop();
            if (backdrop) {
                backdrop.classList.add('is-open');
                backdrop.setAttribute('aria-hidden', 'false');
            }
            document.body.classList.add('mmhc-filter-sheet-open');
        } else {
            filterPanel.classList.remove('is-open');
            if (filterBackdrop) {
                filterBackdrop.classList.remove('is-open');
                filterBackdrop.setAttribute('aria-hidden', 'true');
            }
            document.body.classList.remove('mmhc-filter-sheet-open');
        }
        filterToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        filterToggle.innerHTML = open
            ? '<i class="fas fa-times"></i> Close filters'
            : '<i class="fas fa-sliders-h"></i> Filters';
    }

    function openFilterSheet() {
        setFilterOpen(true);
    }

    function closeFilterSheet() {
        setFilterOpen(false);
    }

    if (filterToggle && filterPanel) {
        filterToggle.addEventListener('click', function () {
            var open = !filterPanel.classList.contains('is-open');
            if (open) {
                openFilterSheet();
            } else {
                closeFilterSheet();
            }
        });
        if (window.matchMedia('(min-width: 768px)').matches) {
            filterPanel.classList.add('is-open');
        }
    }

    document.querySelectorAll('.app-filter-select').forEach(function (el) {
        el.addEventListener('change', function () {
            var form = document.getElementById('searchFilterForm');
            if (form && mqMobile.matches) {
                form.requestSubmit();
            }
        });
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && filterPanel && filterPanel.classList.contains('is-open')) {
            closeFilterSheet();
        }
    });

    mqMobile.addEventListener('change', function () {
        if (!mqMobile.matches) {
            closeFilterSheet();
        }
    });
})();
