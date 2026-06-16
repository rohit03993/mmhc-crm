/**
 * MMHC register — app view toggle + stepped forms + bottom dock.
 */
(function () {
    'use strict';

    var STORAGE_KEY = 'mmhc_register_view';
    var shell = document.getElementById('registerShell');
    if (!shell) return;

    var mqMobile = window.matchMedia('(max-width: 767.98px)');
    var toggleBtn = document.getElementById('registerViewToggle');
    var dockBack = document.getElementById('registerDockBack');
    var dockPrimary = document.getElementById('registerDockPrimary');
    var stepForms = shell.querySelectorAll('[data-form-steps]');

    var roleLabels = {
        patient: { next: 'Continue', submit: 'Register as Patient', btnClass: 'btn-primary' },
        nurse: { next: 'Continue', submit: 'Register as Nurse', btnClass: 'btn-info' },
        caregiver: { next: 'Continue', submit: 'Register as Caregiver', btnClass: 'btn-success' },
        student: { next: 'Continue', submit: 'Create academic account', btnClass: 'btn-primary' },
        faculty: { next: 'Continue', submit: 'Create academic account', btnClass: 'btn-primary' }
    };

    function readPreference() {
        try {
            return localStorage.getItem(STORAGE_KEY);
        } catch (e) {
            return null;
        }
    }

    function savePreference(mode) {
        try {
            localStorage.setItem(STORAGE_KEY, mode);
        } catch (e) { /* ignore */ }
    }

    function isMobileViewport() {
        return mqMobile.matches || document.body.classList.contains('capacitor-app');
    }

    function shouldUseApp() {
        if (isMobileViewport()) {
            return true;
        }
        var pref = readPreference();
        return pref === 'app';
    }

    function persistViewChoice(mode) {
        if (isMobileViewport()) {
            return;
        }
        savePreference(mode);
    }

    function setAppMode(enabled) {
        document.documentElement.classList.toggle('register-shell--app', enabled);
        document.querySelectorAll('.register-view-toggle, [data-register-view-toggle]').forEach(function (btn) {
            btn.setAttribute('aria-pressed', enabled ? 'true' : 'false');
            if (btn.id === 'registerViewToggle') {
                btn.innerHTML = enabled
                    ? '<i class="fas fa-desktop" aria-hidden="true"></i><span class="d-none d-sm-inline">Web</span>'
                    : '<i class="fas fa-mobile-screen-button" aria-hidden="true"></i><span class="d-none d-sm-inline">App</span>';
                btn.title = enabled ? 'Switch to web layout' : 'Switch to app layout';
            } else {
                btn.innerHTML = enabled
                    ? '<i class="fas fa-desktop" aria-hidden="true"></i> Web view'
                    : '<i class="fas fa-mobile-screen-button" aria-hidden="true"></i> App view';
            }
        });
        stepForms.forEach(resetSteps);
        syncDock();
    }

    function getActiveForm() {
        var pane = shell.querySelector('.tab-pane.active.show, .tab-pane.active');
        if (pane) {
            var f = pane.querySelector('form');
            if (f) return f;
        }
        return shell.querySelector('form[id$="Form"], form[id$="RegisterForm"]');
    }

    function getActiveRole() {
        var form = getActiveForm();
        if (!form) return 'patient';
        var roleInput = form.querySelector('input[name="role"]');
        return roleInput ? roleInput.value : 'patient';
    }

    function getStepContainer(form) {
        return form ? form.querySelector('[data-form-steps]') : null;
    }

    function getSteps(form) {
        var c = getStepContainer(form);
        if (!c) return [];
        return Array.prototype.slice.call(c.querySelectorAll('.register-step'));
    }

    function getCurrentStepIndex(form) {
        var steps = getSteps(form);
        for (var i = 0; i < steps.length; i++) {
            if (steps[i].classList.contains('is-active')) return i;
        }
        return 0;
    }

    function setStep(form, index) {
        var steps = getSteps(form);
        if (!steps.length) return;
        index = Math.max(0, Math.min(index, steps.length - 1));
        steps.forEach(function (s, i) {
            s.classList.toggle('is-active', i === index);
        });
        updateStepBar(form, index, steps.length);
        syncDock();
    }

    function resetSteps(container) {
        var form = container.closest('form') || getActiveForm();
        if (!form) return;
        setStep(form, 0);
    }

    function updateStepBar(form, index, total) {
        var c = getStepContainer(form);
        if (!c) return;
        var bar = c.querySelector('.register-form-steps__bar');
        if (!bar) return;
        var dots = bar.querySelectorAll('.register-form-steps__dot');
        dots.forEach(function (d, i) {
            d.classList.toggle('is-active', i === index);
        });
        var label = bar.querySelector('.register-form-steps__label');
        var labels = ['Your details', 'Location', 'Account'];
        if (label) label.textContent = labels[index] || ('Step ' + (index + 1));
    }

    function validateStepFields(stepEl) {
        if (!stepEl) return true;
        var fields = stepEl.querySelectorAll('input, select, textarea');
        for (var i = 0; i < fields.length; i++) {
            var f = fields[i];
            if (f.disabled || f.type === 'hidden') continue;
            if (!f.checkValidity()) {
                f.reportValidity();
                f.focus();
                return false;
            }
        }
        return true;
    }

    function syncDock() {
        if (!dockPrimary || !dockBack) return;
        var app = document.documentElement.classList.contains('register-shell--app');
        if (!app) return;

        var form = getActiveForm();
        if (!form) return;

        var role = getActiveRole();
        var meta = roleLabels[role] || roleLabels.patient;
        var steps = getSteps(form);
        var idx = getCurrentStepIndex(form);
        var last = steps.length - 1;

        dockBack.disabled = steps.length ? idx <= 0 : true;
        if (!steps.length) {
            dockPrimary.type = 'submit';
            dockPrimary.textContent = meta.submit;
            dockPrimary.className = 'register-app-dock__primary ' + meta.btnClass;
            dockPrimary.setAttribute('form', form.id);
            return;
        }

        dockPrimary.type = idx >= last ? 'submit' : 'button';
        dockPrimary.textContent = idx >= last ? meta.submit : meta.next;
        dockPrimary.className = 'register-app-dock__primary ' + meta.btnClass;
        dockPrimary.setAttribute('form', idx >= last ? form.id : '');
    }

    function onDockPrimaryClick(e) {
        if (!document.documentElement.classList.contains('register-shell--app')) return;
        var form = getActiveForm();
        if (!form) return;
        var steps = getSteps(form);
        if (!steps.length) return;

        var idx = getCurrentStepIndex(form);
        if (idx >= steps.length - 1) return;

        e.preventDefault();
        if (!validateStepFields(steps[idx])) return;
        setStep(form, idx + 1);
    }

    function onDockBackClick() {
        var form = getActiveForm();
        if (!form) return;
        var idx = getCurrentStepIndex(form);
        if (idx > 0) setStep(form, idx - 1);
    }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function () {
            if (isMobileViewport()) {
                return;
            }
            var next = !document.documentElement.classList.contains('register-shell--app');
            persistViewChoice(next ? 'app' : 'classic');
            setAppMode(next);
        });
    }

    document.querySelectorAll('[data-register-view-toggle]').forEach(function (btn) {
        if (btn === toggleBtn) return;
        btn.addEventListener('click', function () {
            if (isMobileViewport()) {
                return;
            }
            var next = !document.documentElement.classList.contains('register-shell--app');
            persistViewChoice(next ? 'app' : 'classic');
            setAppMode(next);
        });
    });

    if (dockPrimary) {
        dockPrimary.addEventListener('click', onDockPrimaryClick);
    }
    if (dockBack) {
        dockBack.addEventListener('click', onDockBackClick);
    }

    shell.querySelectorAll('[data-bs-toggle="pill"], [data-bs-toggle="tab"]').forEach(function (tab) {
        tab.addEventListener('shown.bs.tab', function () {
            var form = getActiveForm();
            if (form) setStep(form, 0);
            syncDock();
        });
    });

    document.querySelectorAll('[data-academic-role]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            setTimeout(syncDock, 50);
        });
    });

    mqMobile.addEventListener('change', function () {
        setAppMode(shouldUseApp());
    });

    setAppMode(shouldUseApp());
})();
