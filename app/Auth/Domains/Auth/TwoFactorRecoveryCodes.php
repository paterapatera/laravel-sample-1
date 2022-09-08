<?php

namespace App\Auth\Domains\Auth;

use Illuminate\Support\Collection;

class TwoFactorRecoveryCodes
{
    public function __construct(private string $value)
    {
    }

    public function value(): string
    {
        return $this->value;
    }

    public function decrypt(): Collection
    {
        $decCodes = json_decode(decrypt($this->value), true);
        return collect($decCodes)
            ->map(fn ($code) => new TwoFactorRecoveryCode($code));
    }

    public function contains(TwoFactorRecoveryCode $code): bool
    {
        return $this->decrypt()->contains(fn ($c) => $c->value() === $code->value());
    }

    public function replaceRecoveryCode(TwoFactorRecoveryCode $code, TwoFactorRecoveryCode $newCode): TwoFactorRecoveryCodes
    {
        return new TwoFactorRecoveryCodes(encrypt(str_replace(
            $code->value(),
            $newCode->value(),
            decrypt($this->value)
        )));
    }
}
