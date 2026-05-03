@php
    $assignment = $assignment ?? null;
    $raw = old('checklist_items_raw');
    if ($raw === null && $assignment && ! empty($assignment->checklist_items)) {
        $lines = [];
        foreach ($assignment->normalizedChecklistItems() as $row) {
            $p = (float) ($row['points'] ?? 1);
            $label = $row['label'] ?? '';
            if (abs($p - 1.0) < 0.001) {
                $lines[] = $label;
            } else {
                $lines[] = $label.' | '.$p;
            }
        }
        $raw = implode("\n", $lines);
    }
    $raw = $raw ?? '';
@endphp
<div class="col-12">
    <label for="checklist_items_raw" class="form-label">Checklist criteria <span class="text-muted fw-normal">(for checklist &amp; mixed types)</span></label>
    <textarea class="form-control font-monospace small @error('checklist_items_raw') is-invalid @enderror" id="checklist_items_raw" name="checklist_items_raw" rows="6" placeholder="One line per item. Optional points: Hand hygiene | 2">{{ $raw }}</textarea>
    @error('checklist_items_raw')<div class="invalid-feedback">{{ $message }}</div>@enderror
    <p class="form-text small mb-0">Students see these as tick boxes. Points default to 1 per line; use <code>Label | 2</code> for a different weight.</p>
</div>
