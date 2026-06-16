<script>
(function () {
    var RESEND_SECONDS = 60;

    function initResendCooldown() {
        var form = document.getElementById('resendLoginOtpForm');
        if (!form) return;

        var btn = form.querySelector('.wa-otp-resend-btn');
        var timerEl = document.getElementById('waOtpResendTimer');
        if (!btn) return;

        var key = 'mmhc_wa_otp_resend_' + (form.querySelector('input[name="phone"]')?.value || 'x');
        var until = parseInt(sessionStorage.getItem(key) || '0', 10);
        var now = Date.now();

        if (until > now) {
            startCountdown(btn, timerEl, until, key);
            return;
        }

        btn.disabled = false;
        if (timerEl) timerEl.textContent = '';

        form.addEventListener('submit', function () {
            sessionStorage.setItem(key, String(Date.now() + RESEND_SECONDS * 1000));
            btn.disabled = true;
            if (timerEl) timerEl.textContent = 'Sending…';
        });
    }

    function startCountdown(btn, timerEl, until, key) {
        btn.disabled = true;
        function tick() {
            var left = Math.ceil((until - Date.now()) / 1000);
            if (left <= 0) {
                sessionStorage.removeItem(key);
                btn.disabled = false;
                if (timerEl) timerEl.textContent = '';
                return;
            }
            if (timerEl) {
                timerEl.textContent = 'Resend available in ' + left + 's';
            }
            setTimeout(tick, 1000);
        }
        tick();
    }

    function initOtpInput() {
        var otp = document.getElementById('otp');
        if (!otp) return;
        otp.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '').slice(0, 6);
        });
    }

    function initPhoneInput() {
        var phone = document.getElementById('login_phone');
        if (!phone) return;
        phone.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '').slice(0, 10);
        });
    }

    function initSubmitLoading() {
        ['phoneOtpForm', 'verifyOtpForm', 'resendLoginOtpForm'].forEach(function (id) {
            var form = document.getElementById(id);
            if (!form) return;
            form.addEventListener('submit', function () {
                var btn = form.querySelector('button[type="submit"]');
                if (!btn || btn.disabled) return;
                btn.dataset.originalHtml = btn.innerHTML;
                btn.disabled = true;
                if (id === 'verifyOtpForm') {
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Verifying…';
                } else if (id === 'resendLoginOtpForm') {
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Sending…';
                } else {
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending OTP…';
                }
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initResendCooldown();
        initOtpInput();
        initPhoneInput();
        initSubmitLoading();
    });
})();
</script>
