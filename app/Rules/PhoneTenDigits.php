<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PhoneTenDigits implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (! preg_match('/^\d{10}$/', (string) $value)) {
            $fail('The :attribute must be a valid 10-digit mobile number.');
        }
    }
}
