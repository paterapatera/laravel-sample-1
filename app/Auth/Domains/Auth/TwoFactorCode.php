<?php

namespace App\Auth\Domains\Auth;

class TwoFactorCode
{
    public function __construct(private string $value)
    {
    }

    public function value(): string
    {
        return $this->value;
    }
}
