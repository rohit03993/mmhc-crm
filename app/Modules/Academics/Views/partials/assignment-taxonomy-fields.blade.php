@php
    use App\Modules\Academics\Models\Assignment;
    use App\Modules\Academics\Support\AcademicsTaxonomy;
    $assignment = $assignment ?? new Assignment;
    $atypes = AcademicsTaxonomy::assignmentTypes();
    $asm = AcademicsTaxonomy::assessmentTypes();
    $oldAsm = old('assessment_type_keys', $assignment->assessment_type_keys ?? []);
    if (! is_array($oldAsm)) {
        $oldAsm = [];
    }
    $typeVal = old('assignment_type', $assignment->assignment_type ?? 'file_upload');
@endphp
<div class="col-12">
    <label for="assignment_type" class="form-label">Assignment type <span class="text-danger">*</span></label>
    <select name="assignment_type" id="assignment_type" class="form-select @error('assignment_type') is-invalid @enderror" required>
        @foreach($atypes as $key => $label)
            <option value="{{ $key }}" @selected($typeVal === $key)>{{ $label }}</option>
        @endforeach
    </select>
    @error('assignment_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
    <p class="form-text small mb-0">Drives student UI (e.g. file required vs optional) and reporting filters.</p>
</div>
<div class="col-12">
    <label class="form-label">Assessment / evaluation tags <span class="text-muted fw-normal">(optional)</span></label>
    <div class="row g-2 border rounded p-3 bg-light bg-opacity-50" style="max-height: 12rem; overflow-y: auto;">
        @foreach($asm as $key => $label)
            <div class="col-12 col-md-6">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="assessment_type_keys[]" id="asm_{{ $key }}" value="{{ $key }}" @checked(in_array($key, $oldAsm, true))>
                    <label class="form-check-label small" for="asm_{{ $key }}">{{ $label }}</label>
                </div>
            </div>
        @endforeach
    </div>
    @error('assessment_type_keys.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
</div>
<div class="col-12">
    <span class="form-label d-block">Evaluation focus</span>
    <div class="d-flex flex-wrap gap-3">
        <div class="form-check">
            <input type="hidden" name="is_formative" value="0">
            <input type="checkbox" name="is_formative" value="1" class="form-check-input" id="is_formative" @checked(old('is_formative', $assignment->is_formative ?? true))>
            <label class="form-check-label small" for="is_formative">Formative</label>
        </div>
        <div class="form-check">
            <input type="hidden" name="is_summative" value="0">
            <input type="checkbox" name="is_summative" value="1" class="form-check-input" id="is_summative" @checked(old('is_summative', $assignment->is_summative ?? false))>
            <label class="form-check-label small" for="is_summative">Summative</label>
        </div>
        <div class="form-check">
            <input type="hidden" name="eval_includes_mcq" value="0">
            <input type="checkbox" name="eval_includes_mcq" value="1" class="form-check-input" id="eval_includes_mcq" @checked(old('eval_includes_mcq', $assignment->eval_includes_mcq ?? false))>
            <label class="form-check-label small" for="eval_includes_mcq">Includes MCQ</label>
        </div>
        <div class="form-check">
            <input type="hidden" name="eval_includes_practical" value="0">
            <input type="checkbox" name="eval_includes_practical" value="1" class="form-check-input" id="eval_includes_practical" @checked(old('eval_includes_practical', $assignment->eval_includes_practical ?? false))>
            <label class="form-check-label small" for="eval_includes_practical">Practical</label>
        </div>
        <div class="form-check">
            <input type="hidden" name="eval_includes_viva" value="0">
            <input type="checkbox" name="eval_includes_viva" value="1" class="form-check-input" id="eval_includes_viva" @checked(old('eval_includes_viva', $assignment->eval_includes_viva ?? false))>
            <label class="form-check-label small" for="eval_includes_viva">Viva</label>
        </div>
        <div class="form-check">
            <input type="hidden" name="eval_includes_checklist" value="0">
            <input type="checkbox" name="eval_includes_checklist" value="1" class="form-check-input" id="eval_includes_checklist" @checked(old('eval_includes_checklist', $assignment->eval_includes_checklist ?? false))>
            <label class="form-check-label small" for="eval_includes_checklist">Checklist</label>
        </div>
    </div>
</div>
