<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidDiscoCode implements Rule
{
    private $validDiscos = [
        'AEDC', 'ABUJA', 'EKEDC', 'EKO', 'IKEDC', 'IKEJA', 
        'IBEDC', 'IBADAN', 'EEDC', 'ENUGU', 'PHED', 'PH',
        'JEDC', 'JOS', 'KAEDCO', 'KADUNA', 'KEDCO', 'KANO',
        'BEDC', 'BH'
    ];

    public function passes($attribute, $value)
    {
        return in_array(strtoupper($value), $this->validDiscos);
    }

    public function message()
    {
        return 'The :attribute must be a valid DISCO code (AEDC, EKO, IKEJA, IBADAN, ENUGU, PH, JOS, KADUNA, KANO, BH).';
    }
}
