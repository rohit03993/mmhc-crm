/**
 * Pull-to-refresh for mobile app dashboards (academics + healthcare).
 * Attach data-mmhc-ptr to a scrollable container or let it bind to [data-mmhc-ptr].
 */
(function () {
    var mq = window.matchMedia('(max-width: 767.98px)');
    var THRESHOLD = 72;

    function createIndicator() {
        var el = document.createElement('div');
        el.className = 'mmhc-ptr-indicator';
        el.setAttribute('aria-hidden', 'true');
        el.innerHTML = '<span class="mmhc-ptr-indicator__icon"><i class="fas fa-sync-alt"></i></span><span class="mmhc-ptr-indicator__text">Pull to refresh</span>';
        document.body.appendChild(el);
        return el;
    }

    function bindPullRefresh(target) {
        if (!target || target.getAttribute('data-mmhc-ptr-bound') === '1') {
            return;
        }
        target.setAttribute('data-mmhc-ptr-bound', '1');

        var indicator = createIndicator();
        var startY = 0;
        var pulling = false;
        var refreshing = false;

        function scrollTop() {
            return window.scrollY || document.documentElement.scrollTop || 0;
        }

        function setState(distance, state) {
            indicator.classList.remove('is-pulling', 'is-ready', 'is-refreshing');
            if (state) {
                indicator.classList.add(state);
            }
            var clamped = Math.min(distance, THRESHOLD + 20);
            indicator.style.transform = 'translateY(' + (clamped - 56) + 'px)';
        }

        target.addEventListener('touchstart', function (e) {
            if (refreshing || scrollTop() > 4) {
                return;
            }
            startY = e.touches[0].clientY;
            pulling = true;
        }, { passive: true });

        target.addEventListener('touchmove', function (e) {
            if (!pulling || refreshing) {
                return;
            }
            if (scrollTop() > 4) {
                pulling = false;
                setState(0, null);
                return;
            }
            var distance = e.touches[0].clientY - startY;
            if (distance <= 0) {
                setState(0, null);
                return;
            }
            setState(distance, distance >= THRESHOLD ? 'is-ready' : 'is-pulling');
        }, { passive: true });

        function endPull() {
            if (!pulling || refreshing) {
                return;
            }
            pulling = false;
            var ready = indicator.classList.contains('is-ready');
            if (!ready) {
                setState(0, null);
                return;
            }
            refreshing = true;
            setState(THRESHOLD, 'is-refreshing');
            indicator.querySelector('.mmhc-ptr-indicator__text').textContent = 'Refreshing…';
            window.location.reload();
        }

        target.addEventListener('touchend', endPull, { passive: true });
        target.addEventListener('touchcancel', endPull, { passive: true });
    }

    function init() {
        if (!mq.matches) {
            return;
        }
        document.querySelectorAll('[data-mmhc-ptr]').forEach(bindPullRefresh);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    mq.addEventListener('change', init);
})();
