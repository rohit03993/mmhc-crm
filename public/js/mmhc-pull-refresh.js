/**
 * Pull-to-refresh for mobile app dashboards (academics + healthcare).
 * One shared indicator; fully hidden until the user pulls.
 */
(function () {
    var mq = window.matchMedia('(max-width: 767.98px)');
    var THRESHOLD = 72;
    var indicator = null;

    function ensureIndicator() {
        if (indicator && document.body.contains(indicator)) {
            return indicator;
        }
        // Remove any orphaned indicators from earlier buggy loads
        document.querySelectorAll('.mmhc-ptr-indicator').forEach(function (el) {
            el.remove();
        });
        var el = document.createElement('div');
        el.className = 'mmhc-ptr-indicator';
        el.setAttribute('aria-hidden', 'true');
        el.innerHTML =
            '<span class="mmhc-ptr-indicator__icon"><i class="fas fa-sync-alt" aria-hidden="true"></i></span>' +
            '<span class="mmhc-ptr-indicator__text">Pull to refresh</span>';
        document.body.appendChild(el);
        indicator = el;
        hideIndicator();
        return indicator;
    }

    function hideIndicator() {
        if (!indicator) return;
        indicator.classList.remove('is-pulling', 'is-ready', 'is-refreshing', 'is-visible');
        indicator.style.transform = '';
        indicator.setAttribute('aria-hidden', 'true');
        var text = indicator.querySelector('.mmhc-ptr-indicator__text');
        if (text) text.textContent = 'Pull to refresh';
    }

    function setState(distance, state) {
        ensureIndicator();
        indicator.classList.remove('is-pulling', 'is-ready', 'is-refreshing', 'is-visible');
        if (!state || distance <= 0) {
            hideIndicator();
            return;
        }
        indicator.classList.add('is-visible', state);
        indicator.setAttribute('aria-hidden', 'false');
        var clamped = Math.min(distance, THRESHOLD + 24);
        // Keep horizontal centering: CSS uses left 50%; offset vertically from hide position
        var offset = Math.min(clamped, 72);
        indicator.style.transform = 'translate(-50%, ' + (offset - 8) + 'px)';
    }

    function bindPullRefresh(target) {
        if (!target || target.getAttribute('data-mmhc-ptr-bound') === '1') {
            return;
        }
        target.setAttribute('data-mmhc-ptr-bound', '1');
        ensureIndicator();

        var startY = 0;
        var pulling = false;
        var refreshing = false;

        function scrollTop() {
            return window.scrollY || document.documentElement.scrollTop || 0;
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
                hideIndicator();
                return;
            }
            var distance = e.touches[0].clientY - startY;
            if (distance <= 8) {
                hideIndicator();
                return;
            }
            setState(distance, distance >= THRESHOLD ? 'is-ready' : 'is-pulling');
        }, { passive: true });

        function endPull() {
            if (!pulling || refreshing) {
                pulling = false;
                return;
            }
            pulling = false;
            var ready = indicator && indicator.classList.contains('is-ready');
            if (!ready) {
                hideIndicator();
                return;
            }
            refreshing = true;
            setState(THRESHOLD, 'is-refreshing');
            var text = indicator.querySelector('.mmhc-ptr-indicator__text');
            if (text) text.textContent = 'Refreshing…';
            window.setTimeout(function () {
                window.location.reload();
            }, 180);
        }

        target.addEventListener('touchend', endPull, { passive: true });
        target.addEventListener('touchcancel', function () {
            pulling = false;
            if (!refreshing) hideIndicator();
        }, { passive: true });
    }

    function init() {
        if (!mq.matches) {
            document.querySelectorAll('.mmhc-ptr-indicator').forEach(function (el) {
                el.remove();
            });
            indicator = null;
            return;
        }
        document.querySelectorAll('[data-mmhc-ptr]').forEach(bindPullRefresh);
        ensureIndicator();
        hideIndicator();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    mq.addEventListener('change', init);
})();
