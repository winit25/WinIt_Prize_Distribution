<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class NigerianPhoneNumber implements Rule
{
    public function passes($attribute, $value)
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $value);
        
        // Check if it's a valid Nigerian phone number
        // Nigerian numbers start with 0 and are 11 digits, or start with 234 and are 13 digits
        return preg_match('/^(0[789][01]\d{8}|234[789][01]\d{8})$/', $phone);
    }

    public function message()
    {
        return 'The :attribute must be a valid Nigerian phone number (e.g., 08012345678).';
    }
}
