@extends('auth::layout')

@section('title', 'Sign up for Academics - MMHC CRM')

@section('head')
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <style>
        /* Match healthcare register-tabbed: purple shell + white card (scoped — no global body) */
        .auth-reg-academics-shell {
            --ac-reg-primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --ac-reg-shadow-sm: 0 2px 10px rgba(0, 0, 0, 0.08);
            --ac-reg-shadow-md: 0 4px 20px rgba(0, 0, 0, 0.12);
            --ac-reg-shadow-lg: 0 10px 40px rgba(0, 0, 0, 0.15);
            background: var(--ac-reg-primary-gradient);
            min-height: 100dvh;
            padding: 1rem 0;
        }
        .auth-reg-academics-shell .ac-reg-card {
            border: none;
            border-radius: 24px;
            box-shadow: var(--ac-reg-shadow-lg);
            overflow: hidden;
            background: #fff;
        }
        .auth-reg-academics-shell .ac-reg-card .card-body {
            padding: 1.5rem;
        }
        @media (min-width: 768px) {
            .auth-reg-academics-shell .ac-reg-card .card-body {
                padding: 2.5rem;
            }
        }
        .auth-reg-academics-shell .ac-reg-portal-here {
            background: linear-gradient(165deg, rgba(102, 126, 234, 0.14) 0%, rgba(118, 75, 162, 0.08) 100%);
            border-color: rgba(102, 126, 234, 0.38) !important;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.65);
        }
        .auth-reg-academics-shell .ac-reg-portal-health {
            border-color: rgba(102, 126, 234, 0.45) !important;
            background: linear-gradient(165deg, #ffffff 0%, rgba(247, 250, 252, 0.98) 100%);
            color: inherit;
            transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
        }
        .auth-reg-academics-shell .ac-reg-portal-health:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.2);
            border-color: rgba(102, 126, 234, 0.85) !important;
        }
        .auth-reg-academics-shell .ac-reg-portal-health:focus-visible {
            outline: 3px solid rgba(102, 126, 234, 0.45);
            outline-offset: 2px;
        }
        .auth-reg-academics-shell .ac-reg-details {
            border: 1px solid rgba(102, 126, 234, 0.22);
            border-radius: 12px;
            background: rgba(102, 126, 234, 0.05);
            padding: 0.4rem 0.75rem;
        }
        .auth-reg-academics-shell .ac-reg-details summary {
            cursor: pointer;
            list-style: none;
            font-size: 0.8rem;
            font-weight: 700;
            color: #5b21b6;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            user-select: none;
        }
        .auth-reg-academics-shell .ac-reg-details summary::-webkit-details-marker { display: none; }
        .auth-reg-academics-shell .ac-reg-details summary::after {
            content: ' \025BC';
            font-size: 0.55rem;
            opacity: 0.65;
        }
        .auth-reg-academics-shell .ac-reg-details[open] summary::after { content: ' \025B2'; }
        .auth-reg-academics-shell .ac-reg-tabs.nav-pills {
            background: #f8f9fa;
            padding: 0.5rem;
            border-radius: 16px;
            margin-bottom: 1rem;
            gap: 0.5rem;
        }
        .auth-reg-academics-shell .ac-reg-tabs .nav-link {
            border-radius: 12px;
            font-size: 0.95rem;
            font-weight: 600;
            padding: 0.85rem 1.25rem;
            border: 2px solid transparent;
            color: #6c757d;
            background: #fff;
            border-color: #e9ecef;
        }
        .auth-reg-academics-shell .ac-reg-tabs .nav-link.active {
            background: var(--ac-reg-primary-gradient);
            color: #fff;
            border-color: transparent;
            box-shadow: var(--ac-reg-shadow-md);
        }
        .auth-reg-academics-shell .ac-reg-tabs .nav-link:not(.active):hover {
            border-color: #667eea;
            color: #667eea;
        }
        .auth-reg-academics-shell .ac-reg-context {
            background: rgba(102, 126, 234, 0.06);
            border: 1px solid rgba(102, 126, 234, 0.22) !important;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            color: #4a5568;
            margin-bottom: 1rem;
        }
        .auth-reg-academics-shell .ac-reg-form-panel {
            background: #fff;
            border-radius: 20px;
            padding: 1.25rem 1.35rem;
            box-shadow: var(--ac-reg-shadow-md);
        }
        @media (min-width: 768px) {
            .auth-reg-academics-shell .ac-reg-form-panel {
                padding: 1.5rem;
            }
        }
        .auth-reg-academics-shell .ac-reg-form-panel .form-label {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 0.35rem;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .auth-reg-academics-shell .ac-reg-form-panel .input-group-text {
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            border: 2px solid #e2e8f0;
            border-right: none;
            color: #667eea;
            min-width: 46px;
            justify-content: center;
        }
        .auth-reg-academics-shell .ac-reg-form-panel .form-control,
        .auth-reg-academics-shell .ac-reg-form-panel .form-select {
            border: 2px solid #e2e8f0;
            border-radius: 0 10px 10px 0;
            padding: 0.65rem 0.875rem;
            font-size: 0.95rem;
        }
        .auth-reg-academics-shell .ac-reg-form-panel .input-group:not(.ac-reg-select-group) .form-control {
            border-radius: 0 10px 10px 0;
        }
        .auth-reg-academics-shell .ac-reg-form-panel .input-group .form-control:first-child,
        .auth-reg-academics-shell .ac-reg-form-panel .input-group .form-select {
            border-radius: 0 10px 10px 0;
        }
        .auth-reg-academics-shell .ac-reg-form-panel .ac-reg-select-group .form-select {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }
        .auth-reg-academics-shell .ac-reg-form-panel .input-group textarea.form-control {
            border-radius: 0 10px 10px 0;
            min-height: 2.75rem;
        }
        .auth-reg-academics-shell .ac-reg-form-panel .form-control:focus,
        .auth-reg-academics-shell .ac-reg-form-panel .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.12);
        }
        .auth-reg-academics-shell .ac-reg-batch {
            max-height: 10rem;
            overflow-y: auto;
            border-radius: 12px;
            border: 2px solid #e2e8f0;
            background: linear-gradient(180deg, #f8fafc 0%, #fff 100%);
            padding: 0.65rem 0.85rem;
        }
        .auth-reg-academics-shell .ac-reg-batch .form-check {
            margin-bottom: 0.35rem;
        }
        .auth-reg-academics-shell #academicsBatchInlineError {
            display: none;
            font-size: 0.85rem;
        }
        .auth-reg-academics-shell #academicsBatchInlineError.is-visible {
            display: block;
        }
    </style>
@endsection

@section('content')
<div class="auth-reg-academics-shell">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-10">
                <div class="card shadow-lg border-0 ac-reg-card">
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <div class="d-inline-block rounded-3 px-3 py-2 mb-2" style="background: rgba(102, 126, 234, 0.08);">
                                <img src="{{ $siteLogoUrl ?? asset('images/med-logo.png') }}" alt="{{ $siteCompanyName ?? 'MeD Miracle Health Care' }}" class="brand-logo brand-logo--auth" style="max-height: 50px; display: block;">
                            </div>
                            <h2 class="mt-2 mb-1" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; font-weight: 700; font-size: 1.5rem;">Sign up for your institute (Academics)</h2>
                            <p class="text-muted mb-3" style="font-size: 0.9rem;">Mobile number required — no email needed. Sign in later with SMS OTP on the Phone tab.</p>

                            <div class="register-portal-switch row g-2 g-md-3 mb-0 justify-content-center">
                                <div class="col-12 col-md-5 col-lg-5">
                                    <div class="h-100 rounded-3 px-3 py-3 border text-center text-md-start ac-reg-portal-here">
                                        <span class="badge rounded-pill mb-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">You are here</span>
                                        <div class="fw-bold text-dark mb-1"><i class="fas fa-graduation-cap me-1 text-primary"></i>Academics registration</div>
                                        <p class="small text-muted mb-0">Use <strong>Student</strong> or <strong>Faculty</strong> below, then your institute and batch(es).</p>
                                    </div>
                                </div>
                                <div class="col-12 col-md-5 col-lg-5">
                                    <a href="{{ route('auth.register') }}" class="ac-reg-portal-health d-flex flex-column h-100 rounded-3 px-3 py-3 border border-2 text-center text-md-start text-decoration-none">
                                        <span class="badge bg-light text-dark border mb-2 align-self-center align-self-md-start rounded-pill"><i class="fas fa-heart-pulse me-1 text-danger"></i>Healthcare</span>
                                        <div class="fw-bold mb-1" style="color: #553c9a;">Sign up for medical team <i class="fas fa-arrow-right ms-1 small opacity-75"></i></div>
                                        <p class="small text-muted mb-0 flex-grow-1">Patient, nurse, or caregiver — home care &amp; community registration.</p>
                                    </a>
                                </div>
                            </div>
                        </div>

                        @if(session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0 small">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <details class="ac-reg-details mb-3">
                            <summary>About this registration</summary>
                            <ul class="text-muted small ps-3 mt-2 mb-0">
                                <li><strong>Students:</strong> batches, subjects, assignments, quizzes, reports.</li>
                                <li><strong>Faculty:</strong> teaching, coursework, student progress for your batch(es).</li>
                                <li>Choose an <strong>existing</strong> institute and at least one <strong>batch</strong> your college set up on MMHC.</li>
                            </ul>
                        </details>

                        <form method="POST" action="{{ route('auth.register.post') }}" id="academicsRegisterForm">
                            @csrf
                            <input type="hidden" name="registration_portal" value="academics">
                            <input type="hidden" name="role" id="academic_role_input" value="{{ old('role', 'student') }}">

                            <ul class="nav nav-pills nav-fill ac-reg-tabs mb-3" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button type="button" class="nav-link {{ old('role', 'student') === 'student' ? 'active' : '' }}" id="tab-student" data-academic-role="student" data-bs-toggle="tab" data-bs-target="#pane-student-intro" role="tab" aria-controls="pane-student-intro" aria-selected="{{ old('role', 'student') === 'student' ? 'true' : 'false' }}">
                                        <i class="fas fa-user-graduate me-2 d-none d-sm-inline"></i>Student
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button type="button" class="nav-link {{ old('role') === 'faculty' ? 'active' : '' }}" id="tab-faculty" data-academic-role="faculty" data-bs-toggle="tab" data-bs-target="#pane-faculty-intro" role="tab" aria-controls="pane-faculty-intro" aria-selected="{{ old('role') === 'faculty' ? 'true' : 'false' }}">
                                        <i class="fas fa-chalkboard-teacher me-2 d-none d-sm-inline"></i>Faculty
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content visually-hidden" style="height:0;overflow:hidden;margin:0;padding:0;border:0;" aria-hidden="true">
                                <div class="tab-pane fade {{ old('role', 'student') === 'student' ? 'show active' : '' }}" id="pane-student-intro" role="tabpanel"></div>
                                <div class="tab-pane fade {{ old('role') === 'faculty' ? 'show active' : '' }}" id="pane-faculty-intro" role="tabpanel"></div>
                            </div>

                            <div id="academicsRoleHintWrap" class="ac-reg-context mb-3" role="status">
                                <span id="hintStudent" class="{{ old('role') === 'faculty' ? 'd-none' : '' }}"><strong>Student:</strong> pick your institute and batch(es), then sign in on the main page to open the Academics dashboard.</span>
                                <span id="hintFaculty" class="{{ old('role') === 'faculty' ? '' : 'd-none' }}"><strong>Faculty:</strong> add your qualification; your college may verify it. Choose institute and batch(es) you teach on MMHC.</span>
                            </div>

                            <div class="ac-reg-form-panel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="ac_reg_name">Full name <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                                <input type="text" id="ac_reg_name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required maxlength="255" autocomplete="name">
                                            </div>
                                            @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
<div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="ac_reg_phone">Phone <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text">+91</span>
                                                <input type="tel" id="ac_reg_phone" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" required pattern="[0-9]{10}" maxlength="10" inputmode="numeric" placeholder="9876543210" autocomplete="tel-national">
                                            </div>
                                            <div class="form-text small">10-digit Indian mobile — SMS sign-in after registration</div>
                                            @error('phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="ac_reg_pin">Pincode <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-map-pin"></i></span>
                                                <input type="text" id="ac_reg_pin" name="pincode" class="form-control @error('pincode') is-invalid @enderror" value="{{ old('pincode') }}" required pattern="[1-9][0-9]{5}" maxlength="6" inputmode="numeric" placeholder="6-digit pincode">
                                            </div>
                                            @error('pincode')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="institution_select">College / institute <span class="text-danger">*</span></label>
                                            <div class="input-group ac-reg-select-group">
                                                <span class="input-group-text"><i class="fas fa-university"></i></span>
                                                <select name="academic_institution_id" id="institution_select" class="form-select @error('academic_institution_id') is-invalid @enderror" required>
                                                    <option value="">— Select your institute —</option>
                                                    @foreach($institutions as $inst)
                                                        <option value="{{ $inst->id }}" {{ (string) old('academic_institution_id') === (string) $inst->id ? 'selected' : '' }}>
                                                            {{ $inst->name }} @if($inst->code) ({{ $inst->code }}) @endif
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @error('academic_institution_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                            <div class="form-text small">Only institutes onboarded on MMHC appear here.</div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label">Batch(es) <span class="text-danger">*</span></label>
                                            <div id="batch_checkboxes" class="ac-reg-batch">
                                                @forelse($batches as $batch)
                                                    <div class="form-check batch-row" data-institution-id="{{ $batch->institution_id }}" style="display: none;">
                                                        <input class="form-check-input" type="checkbox" name="academic_batch_ids[]" value="{{ $batch->id }}" id="batch_{{ $batch->id }}"
                                                            {{ is_array(old('academic_batch_ids')) && in_array((string) $batch->id, array_map('strval', old('academic_batch_ids', [])), true) ? 'checked' : '' }}>
                                                        <label class="form-check-label small" for="batch_{{ $batch->id }}">
                                                            {{ $batch->name }}
                                                            <span class="text-muted">({{ $batch->academic_year ?? '—' }})</span>
                                                        </label>
                                                    </div>
                                                @empty
                                                    <p class="text-muted small mb-0">No batches yet—ask your college admin.</p>
                                                @endforelse
                                            </div>
                                            @error('academic_batch_ids')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                            <div class="form-text small">Select one or more batches for your programme.</div>
                                            <div id="academicsBatchInlineError" class="alert alert-warning py-2 px-2 mt-2 mb-0" role="alert"></div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 faculty-only" style="display: {{ old('role') === 'faculty' ? 'block' : 'none' }};">
                                        <div class="mb-3">
                                            <label class="form-label" for="ac_reg_qual">Qualification <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-certificate"></i></span>
                                                <input type="text" id="ac_reg_qual" name="qualification" class="form-control @error('qualification') is-invalid @enderror" value="{{ old('qualification') }}" placeholder="e.g. M.Sc Nursing, Ph.D">
                                            </div>
                                            @error('qualification')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6 student-only" style="display: {{ old('role', 'student') === 'student' ? 'block' : 'none' }};">
                                        <div class="mb-3">
                                            <label class="form-label" for="ac_reg_dob">Date of birth <span class="text-muted fw-normal text-lowercase" style="letter-spacing:0;">(optional)</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                                <input type="date" id="ac_reg_dob" name="date_of_birth" class="form-control @error('date_of_birth') is-invalid @enderror" value="{{ old('date_of_birth') }}">
                                            </div>
                                            @error('date_of_birth')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label class="form-label" for="ac_reg_addr">Address <span class="text-muted fw-normal text-lowercase" style="letter-spacing:0;">(optional)</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text align-items-start pt-3"><i class="fas fa-map-marker-alt"></i></span>
                                                <textarea id="ac_reg_addr" name="address" class="form-control @error('address') is-invalid @enderror" rows="2" maxlength="500" placeholder="Street, area, city">{{ old('address') }}</textarea>
                                            </div>
                                            @error('address')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="ac_reg_pw">Password <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                                <input type="password" id="ac_reg_pw" name="password" class="form-control @error('password') is-invalid @enderror" required minlength="6" autocomplete="new-password">
                                            </div>
                                            @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="ac_reg_pw2">Confirm password <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                                <input type="password" id="ac_reg_pw2" name="password_confirmation" class="form-control" required minlength="6" autocomplete="new-password">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-grid mt-2">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-user-plus me-2"></i>Create academic account
                                    </button>
                                </div>
                            </div>
                        </form>

                        <div class="text-center mt-4">
                            <p class="text-muted mb-0">
                                Already have an account?
                                <a href="{{ route('auth.login') }}" class="text-primary text-decoration-none fw-semibold">Sign in here</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    var inst = document.getElementById('institution_select');
    var rows = document.querySelectorAll('.batch-row');
    var roleInput = document.getElementById('academic_role_input');
    var facultyOnly = document.querySelector('.faculty-only');
    var studentOnly = document.querySelector('.student-only');
    var tabStudent = document.getElementById('tab-student');
    var tabFaculty = document.getElementById('tab-faculty');
    var hintStudent = document.getElementById('hintStudent');
    var hintFaculty = document.getElementById('hintFaculty');
    var batchErr = document.getElementById('academicsBatchInlineError');

    function filterBatches() {
        var id = inst && inst.value ? inst.value : '';
        rows.forEach(function(row) {
            var ok = row.getAttribute('data-institution-id') === id;
            row.style.display = ok ? 'block' : 'none';
            if (!ok) {
                var cb = row.querySelector('input[type="checkbox"]');
                if (cb) cb.checked = false;
            }
        });
        if (batchErr) {
            batchErr.classList.remove('is-visible');
            batchErr.textContent = '';
        }
    }

    function setRole(r) {
        roleInput.value = r;
        if (facultyOnly) facultyOnly.style.display = r === 'faculty' ? 'block' : 'none';
        if (studentOnly) studentOnly.style.display = r === 'student' ? 'block' : 'none';
        var q = document.querySelector('input[name="qualification"]');
        if (q && r === 'student') q.required = false;
        if (q && r === 'faculty') q.required = true;
        if (hintStudent && hintFaculty) {
            if (r === 'faculty') {
                hintStudent.classList.add('d-none');
                hintFaculty.classList.remove('d-none');
            } else {
                hintFaculty.classList.add('d-none');
                hintStudent.classList.remove('d-none');
            }
        }
    }

    if (inst) {
        inst.addEventListener('change', filterBatches);
        filterBatches();
    }

    if (tabStudent) tabStudent.addEventListener('shown.bs.tab', function() { setRole('student'); });
    if (tabFaculty) tabFaculty.addEventListener('shown.bs.tab', function() { setRole('faculty'); });

    document.querySelectorAll('[data-academic-role]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            setRole(this.getAttribute('data-academic-role'));
        });
    });

    setRole(roleInput.value || 'student');

    document.getElementById('academicsRegisterForm').addEventListener('submit', function(e) {
        var any = document.querySelectorAll('input[name="academic_batch_ids[]"]:checked').length > 0;
        if (!any) {
            e.preventDefault();
            if (batchErr) {
                batchErr.textContent = 'Please select at least one batch for your institute.';
                batchErr.classList.add('is-visible');
            }
            var batchBox = document.getElementById('batch_checkboxes');
            if (batchBox) batchBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    });
})();
</script>
@endsection
