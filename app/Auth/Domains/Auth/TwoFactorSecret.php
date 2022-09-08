<?php

namespace App\Auth\Domains\Auth;

class TwoFactorSecret
{
    public function __construct(private string $value)
    {
    }

    public function value(): string
    {
        return $this->value;
    }

    public function decrypt(): string
    {
        return decrypt($this->value);
    }
}
