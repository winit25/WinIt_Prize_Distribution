<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidAmount implements Rule
{
    private $minAmount;
    private $maxAmount;

    public function __construct($minAmount = 100, $maxAmount = 100000)
    {
        $this->minAmount = $minAmount;
        $this->maxAmount = $maxAmount;
    }

    public function passes($attribute, $value)
    {
        $amount = (float) $value;
        return $amount >= $this->minAmount && $amount <= $this->maxAmount;
    }

    public function message()
    {
        return "The :attribute must be between ₦{$this->minAmount} and ₦{$this->maxAmount}.";
    }
}
