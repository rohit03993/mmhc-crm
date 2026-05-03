@php
    use App\Modules\Academics\Models\Topic;
    use App\Modules\Academics\Support\AcademicsTaxonomy;
    $topic = $topic ?? new Topic;
    $tm = AcademicsTaxonomy::teachingMethods();
    $oldKeys = old('teaching_method_keys', $topic->teaching_method_keys ?? []);
    if (! is_array($oldKeys)) {
        $oldKeys = [];
    }
@endphp
<div class="col-12">
    <label class="form-label">Teaching formats <span class="text-muted fw-normal">(optional, multi-select)</span></label>
    <p class="small text-muted mb-2">Tag this topic with methods from your curriculum plan (Phase A taxonomy).</p>
    <div class="row g-2 border rounded p-3 bg-light bg-opacity-50" style="max-height: 14rem; overflow-y: auto;">
        @foreach($tm as $key => $label)
            <div class="col-12 col-md-6">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="teaching_method_keys[]" id="tm_{{ $key }}" value="{{ $key }}" @checked(in_array($key, $oldKeys, true))>
                    <label class="form-check-label small" for="tm_{{ $key }}">{{ $label }}</label>
                </div>
            </div>
        @endforeach
    </div>
    @error('teaching_method_keys')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    @error('teaching_method_keys.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
</div>
