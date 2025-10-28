<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class MeterNumber implements Rule
{
    public function passes($attribute, $value)
    {
        // Remove all non-numeric characters
        $meter = preg_replace('/[^0-9]/', '', $value);
        
        // Nigerian meter numbers are typically 11 digits
        return preg_match('/^\d{11}$/', $meter);
    }

    public function message()
    {
        return 'The :attribute must be a valid 11-digit meter number.';
    }
}
