<?php

namespace App\Rules;

use App\Modules\Auth\Services\UserService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Indian mobile: exactly 10 digits, first digit 6–9 (accepts +91 / 91 prefix in input).
 */
class IndianMobileTenDigits implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $ten = app(UserService::class)->parseTenDigitIndianMobile(is_scalar($value) ? (string) $value : null);
        if ($ten === null) {
            $fail('Enter a valid 10-digit Indian mobile number (starting with 6, 7, 8, or 9).');
        }
    }
}
