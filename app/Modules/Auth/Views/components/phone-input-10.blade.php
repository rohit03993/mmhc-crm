{{-- 10-digit Indian mobile (+91 prefix shown) --}}
@props([
    'name' => 'phone',
    'id' => null,
    'value' => '',
    'required' => true,
    'class' => 'form-control',
    'errorKey' => null,
])
@php
    $inputId = $id ?? $name;
    $digits = preg_replace('/\D/', '', (string) old($name, $value));
    if (strlen($digits) > 10) {
        $digits = substr($digits, -10);
    }
    $errKey = $errorKey ?? $name;
@endphp
<div class="input-group mmhc-phone-input-10">
    <span class="input-group-text">+91</span>
    <input type="tel"
           name="{{ $name }}"
           id="{{ $inputId }}"
           value="{{ $digits }}"
           class="{{ $class }} @error($errKey) is-invalid @enderror"
           maxlength="10"
           pattern="[6-9][0-9]{9}"
           inputmode="numeric"
           placeholder="9876543210"
           autocomplete="tel"
           @if($required) required @endif
           data-mmhc-phone-10="1"
           oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)">
</div>
@error($errKey)
    <div class="invalid-feedback d-block">{{ $message }}</div>
@enderror
