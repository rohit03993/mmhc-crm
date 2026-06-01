/**
 * MMHC patient booking UI helpers (Package B)
 */
(function () {
    'use strict';

    var filterToggle = document.getElementById('mmhcFilterToggle');
    var filterPanel = document.getElementById('mmhcFilterPanel');
    if (filterToggle && filterPanel) {
        filterToggle.addEventListener('click', function () {
            var open = filterPanel.classList.toggle('is-open');
            filterToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            filterToggle.innerHTML = open
                ? '<i class="fas fa-times"></i> Hide filters'
                : '<i class="fas fa-sliders-h"></i> Filters';
        });
        if (window.matchMedia('(min-width: 768px)').matches) {
            filterPanel.classList.add('is-open');
        }
    }

    document.querySelectorAll('.app-filter-select').forEach(function (el) {
        el.addEventListener('change', function () {
            var form = document.getElementById('searchFilterForm');
            if (form && window.matchMedia('(max-width: 767px)').matches) {
                form.requestSubmit();
            }
        });
    });
})();
