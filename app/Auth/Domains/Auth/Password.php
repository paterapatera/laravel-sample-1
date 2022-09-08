<?php

namespace App\Auth\Domains\Auth;

use Illuminate\Support\Facades\Hash;

class Password
{
    public function __construct(private string $value)
    {
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equalHash(PlainPassword $plainPass): bool
    {
        return Hash::check($plainPass->value(), $this->value);
    }
}
