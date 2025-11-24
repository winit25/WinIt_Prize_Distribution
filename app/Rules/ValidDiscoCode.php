<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidDiscoCode implements Rule
{
    private $validDiscos = [
        // Primary codes (required)
        'ABUJA', 'EKO', 'IKEJA', 'IBADAN', 'ENUGU', 'PH', 'JOS', 'KADUNA', 'KANO', 'BH',
        // Legacy aliases (for backward compatibility)
        'AEDC', 'EKEDC', 'IKEDC', 'IBEDC', 'EEDC', 'PHED', 'JEDC', 'KAEDCO', 'KEDCO', 'BEDC'
    ];

    public function passes($attribute, $value)
    {
        return in_array(strtoupper($value), $this->validDiscos);
    }

    public function message()
    {
        return 'The :attribute must be a valid DISCO code (ABUJA, EKO, IKEJA, IBADAN, ENUGU, PH, JOS, KADUNA, KANO, BH).';
    }
}
