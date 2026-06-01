/**
 * Copy referral link + optional WhatsApp share (staff rewards/referrals pages).
 */
(function () {
    function getInput(id) {
        const el = document.getElementById(id);
        if (!el || !el.value) {
            return null;
        }
        return el.value.trim();
    }

    function flashButton(btn, successHtml, revertMs) {
        if (!btn) {
            return;
        }
        const original = btn.innerHTML;
        const hadSuccess = btn.classList.contains('btn-success');
        btn.innerHTML = successHtml;
        btn.classList.add('btn-success');
        if (!hadSuccess && btn.classList.contains('btn-primary')) {
            btn.classList.remove('btn-primary');
        }
        setTimeout(function () {
            btn.innerHTML = original;
            if (!hadSuccess && btn.dataset.mmhcBtnClass) {
                btn.className = btn.dataset.mmhcBtnClass;
            }
        }, revertMs || 2000);
    }

    window.mmhcCopyReferralLink = function (inputId, btn) {
        const url = getInput(inputId);
        if (!url) {
            alert('No link to copy.');
            return;
        }
        const done = function () {
            flashButton(btn, '<i class="fas fa-check me-1"></i>Copied!');
        };
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(done).catch(function () {
                fallbackCopy(inputId, done);
            });
        } else {
            fallbackCopy(inputId, done);
        }
    };

    function fallbackCopy(inputId, onSuccess) {
        const input = document.getElementById(inputId);
        if (!input) {
            return;
        }
        input.select();
        input.setSelectionRange(0, 99999);
        try {
            document.execCommand('copy');
            onSuccess();
        } catch (e) {
            alert('Could not copy. Please select the link and copy manually.');
        }
    }

    window.mmhcShareReferralWhatsApp = function (inputId, messagePrefix) {
        const url = getInput(inputId);
        if (!url) {
            alert('No link to share.');
            return;
        }
        const text = (messagePrefix || 'Join MMHC using my link: ') + url;
        const waUrl = 'https://wa.me/?text=' + encodeURIComponent(text);
        window.open(waUrl, '_blank', 'noopener,noreferrer');
    };

    document.addEventListener('click', function (e) {
        const copyBtn = e.target.closest('[data-mmhc-copy]');
        if (copyBtn) {
            e.preventDefault();
            mmhcCopyReferralLink(copyBtn.getAttribute('data-mmhc-copy'), copyBtn);
            return;
        }
        const waBtn = e.target.closest('[data-mmhc-whatsapp]');
        if (waBtn) {
            e.preventDefault();
            mmhcShareReferralWhatsApp(
                waBtn.getAttribute('data-mmhc-whatsapp'),
                waBtn.getAttribute('data-mmhc-whatsapp-text') || undefined
            );
        }
    });
})();
