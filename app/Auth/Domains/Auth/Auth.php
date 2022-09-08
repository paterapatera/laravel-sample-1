<?php

namespace App\Auth\Domains\Auth;

use App\Models\User;

class Auth
{
    public ?TwoFactorRecoveryCodes $twoFactorRecoveryCodes;
    public ?TwoFactorSecret $twoFactorSecret;

    public function __construct(
        public Email $email,
        public Password $password,
        public User $user,
    ) {
    }

    public function replaceRecoveryCode(TwoFactorRecoveryCode $code, TwoFactorRecoveryCode $newCode): self
    {
        if (!is_null($this->twoFactorRecoveryCodes)) {
            $this->twoFactorRecoveryCodes = $this->twoFactorRecoveryCodes->replaceRecoveryCode($code, $newCode);
        }

        return $this;
    }
}
