<?php

namespace App\Modules\Auth\Services;

/**
 * Normalize Indian and common phone inputs to E.164 (e.g. +919876543210).
 */
class PhoneNormalizer
{
    public function toE164(string $raw): ?string
    {
        $trimmed = trim($raw);
        if ($trimmed === '') {
            return null;
        }
        if (str_starts_with($trimmed, '+')) {
            $digits = preg_replace('/\D+/', '', substr($trimmed, 1));
            if ($digits === '' || strlen($digits) < 10) {
                return null;
            }

            return '+'.$digits;
        }

        $digits = preg_replace('/\D+/', '', $trimmed);
        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            return '+'.$digits;
        }
        if (strlen($digits) === 10 && preg_match('/^[6-9]/', $digits)) {
            return '+91'.$digits;
        }
        if (strlen($digits) > 10) {
            $last = substr($digits, -10);
            if (strlen($last) === 10 && preg_match('/^[6-9]/', $last)) {
                return '+91'.$last;
            }
        }

        return null;
    }
}
