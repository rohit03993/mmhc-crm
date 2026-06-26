/**
 * Academics exam — mobile step navigation (one question per screen)
 */
(function () {
    var root = document.getElementById('acadExamMobile');
    if (!root || !window.matchMedia('(max-width: 767.98px)').matches) {
        return;
    }

    var steps = Array.from(root.querySelectorAll('.acad-exam-step'));
    if (steps.length === 0) {
        return;
    }

    var current = 0;
    var total = steps.length;
    var progressEl = document.getElementById('acadExamProgress');
    var progressBar = document.getElementById('acadExamProgressBar');
    var btnPrev = document.getElementById('acadExamPrev');
    var btnNext = document.getElementById('acadExamNext');
    var btnSubmit = document.getElementById('acadExamSubmit');

    function stepAnswered(step) {
        var isMulti = step.getAttribute('data-multi') === '1';
        var required = step.getAttribute('data-required') === '1';
        var inputs = step.querySelectorAll('input[type="radio"], input[type="checkbox"]');
        if (isMulti) {
            return Array.from(inputs).some(function (i) { return i.checked; });
        }
        if (!required) {
            return true;
        }
        return Array.from(inputs).some(function (i) { return i.checked; });
    }

    function updateUi() {
        steps.forEach(function (step, idx) {
            step.classList.toggle('is-active', idx === current);
        });
        if (progressEl) {
            progressEl.textContent = 'Question ' + (current + 1) + ' of ' + total;
        }
        if (progressBar) {
            progressBar.style.width = Math.round(((current + 1) / total) * 100) + '%';
        }
        if (btnPrev) {
            btnPrev.disabled = current === 0;
        }
        var isLast = current === total - 1;
        if (btnNext) {
            btnNext.classList.toggle('d-none', isLast);
        }
        if (btnSubmit) {
            btnSubmit.classList.toggle('d-none', !isLast);
        }
    }

    function goNext() {
        var step = steps[current];
        if (!stepAnswered(step)) {
            step.classList.add('is-shake');
            setTimeout(function () { step.classList.remove('is-shake'); }, 400);
            return;
        }
        if (current < total - 1) {
            current++;
            updateUi();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }

    function goPrev() {
        if (current > 0) {
            current--;
            updateUi();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }

    if (btnNext) {
        btnNext.addEventListener('click', goNext);
    }
    if (btnPrev) {
        btnPrev.addEventListener('click', goPrev);
    }

    steps.forEach(function (step) {
        step.querySelectorAll('.acad-exam-option input').forEach(function (input) {
            input.addEventListener('change', function () {
                if (input.type === 'radio' && current < total - 1) {
                    setTimeout(goNext, 280);
                }
            });
        });
    });

    updateUi();
})();
