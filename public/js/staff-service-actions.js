/**
 * Staff start / complete service actions (UI wiring only).
 * OTP rules stay on the server — this handles fetch, loading, and skip-OTP UI.
 */
(function (window) {
    'use strict';

    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.content : '';
    }

    function notify(message, type) {
        if (typeof window.mmhcToast === 'function') {
            window.mmhcToast(message, type || 'info');
            return;
        }
        window.alert(message);
    }

    function parseJsonResponse(response) {
        return response.text().then(function (text) {
            var data = {};
            if (text) {
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    data = {
                        success: false,
                        message: response.ok
                            ? 'Unexpected server response.'
                            : 'Request failed (' + response.status + '). Please try again.'
                    };
                }
            }
            if (!response.ok && !data.message) {
                data.success = false;
                data.message = 'Request failed (' + response.status + '). Please try again.';
            }
            data.__httpOk = response.ok;
            return data;
        });
    }

    function postJson(url, body) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(body || {})
        }).then(parseJsonResponse);
    }

    function startService(serviceId, triggerEl) {
        if (!serviceId) {
            return;
        }
        if (!window.confirm('Are you sure you want to start this service?')) {
            return;
        }

        var btn = triggerEl || null;
        var originalHtml = btn ? btn.innerHTML : '';
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Starting...';
        }

        postJson('/staff/service/' + serviceId + '/start', {})
            .then(function (data) {
                if (data.success) {
                    if (btn) {
                        btn.innerHTML = '<i class="fas fa-check me-2"></i>Started!';
                    }
                    notify(data.message || 'Service started successfully!', 'info');
                    window.setTimeout(function () {
                        window.location.reload();
                    }, 600);
                    return;
                }
                notify(data.message || 'Failed to start service', 'info');
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
            })
            .catch(function () {
                notify('Failed to start service. Please retry. If the issue persists, contact support.', 'info');
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
            });
    }

    var completionState = {
        serviceId: null,
        skipPatientOtp: false,
        modal: null
    };

    function getModalEl() {
        return document.getElementById('completionOtpModal');
    }

    function setSkipMode(enabled, message) {
        completionState.skipPatientOtp = !!enabled;
        var otpWrap = document.getElementById('completionOtpFieldWrap');
        var sendBtn = document.getElementById('sendCompletionOtpBtn');
        var hint = document.getElementById('completionOtpHint');
        var verifyBtn = document.getElementById('verifyCompletionOtpBtn');
        var otpInput = document.getElementById('completionOtpInput');

        if (otpWrap) {
            otpWrap.classList.toggle('d-none', !!enabled);
        }
        if (sendBtn) {
            sendBtn.classList.toggle('d-none', !!enabled);
        }
        if (otpInput) {
            otpInput.value = '';
            otpInput.required = !enabled;
        }
        if (hint) {
            hint.textContent = message || (enabled
                ? 'Patient mobile matches your verified account — no separate OTP needed.'
                : 'OTP expires in 5 minutes.');
        }
        if (verifyBtn) {
            verifyBtn.innerHTML = enabled
                ? '<i class="fas fa-check me-1"></i>Complete service'
                : 'Verify & Complete';
        }
    }

    function openCompletionOtpModal(serviceId) {
        completionState.serviceId = serviceId;
        completionState.skipPatientOtp = false;

        var modalEl = getModalEl();
        if (!modalEl) {
            // Dashboard: complete only from details page
            window.location.href = '/staff/service/' + serviceId;
            return;
        }

        if (!completionState.modal && typeof bootstrap !== 'undefined') {
            completionState.modal = new bootstrap.Modal(modalEl);
        }

        setSkipMode(false, 'OTP expires in 5 minutes. Tap Send OTP first.');
        if (completionState.modal) {
            completionState.modal.show();
        }
    }

    function sendCompletionOtp() {
        if (!completionState.serviceId) {
            return;
        }
        var btn = document.getElementById('sendCompletionOtpBtn');
        var old = btn ? btn.innerHTML : '';
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Sending...';
        }

        postJson('/staff/service/' + completionState.serviceId + '/completion-otp', {})
            .then(function (data) {
                if (!data.success) {
                    notify(data.message || 'Failed to send OTP', 'info');
                    return;
                }
                if (data.skip_patient_otp) {
                    setSkipMode(true, data.message || 'No separate patient OTP needed.');
                    notify(data.message || 'You can complete without a patient OTP.', 'info');
                    return;
                }
                setSkipMode(false, 'OTP sent to ' + (data.sent_to || 'patient') + '. Ask them for the 6-digit code.');
                notify(data.message || 'OTP sent to patient.', 'info');
            })
            .catch(function () {
                notify('Failed to send OTP. Please try again.', 'info');
            })
            .finally(function () {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = old;
                }
            });
    }

    function verifyAndCompleteService() {
        if (!completionState.serviceId) {
            return;
        }

        var otpInput = document.getElementById('completionOtpInput');
        var otp = otpInput ? String(otpInput.value || '').trim() : '';

        if (!completionState.skipPatientOtp && !/^\d{6}$/.test(otp)) {
            notify('Please enter the valid 6-digit patient OTP.', 'info');
            return;
        }

        var btn = document.getElementById('verifyCompletionOtpBtn');
        var old = btn ? btn.innerHTML : '';
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Completing...';
        }

        var payload = completionState.skipPatientOtp ? {} : { otp_code: otp };

        postJson('/staff/service/' + completionState.serviceId + '/complete', payload)
            .then(function (data) {
                if (!data.success) {
                    notify(data.message || 'Could not complete service.', 'info');
                    return;
                }
                notify(data.message || 'Service completed successfully!', 'info');
                if (completionState.modal) {
                    completionState.modal.hide();
                }
                window.setTimeout(function () {
                    window.location.reload();
                }, 400);
            })
            .catch(function () {
                notify('Failed to complete service. Please try again.', 'info');
            })
            .finally(function () {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = old || 'Verify & Complete';
                }
            });
    }

    window.mmhcStaffServiceActions = {
        startService: startService,
        openCompletionOtpModal: openCompletionOtpModal,
        sendCompletionOtp: sendCompletionOtp,
        verifyAndCompleteService: verifyAndCompleteService
    };

    // Legacy global names used by existing Blade onclick handlers
    window.startService = function (serviceId) {
        var trigger = (typeof event !== 'undefined' && event && event.target)
            ? event.target.closest('button, .btn, .btn-action')
            : null;
        startService(serviceId, trigger);
    };
    window.openCompletionOtpModal = openCompletionOtpModal;
    window.sendCompletionOtp = sendCompletionOtp;
    window.verifyAndCompleteService = verifyAndCompleteService;
    window.completeService = openCompletionOtpModal;
})(window);
