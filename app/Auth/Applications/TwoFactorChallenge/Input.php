<?php

namespace App\Auth\Applications\TwoFactorChallenge;

use App\Auth\Domains\Auth\Email;
use App\Auth\Domains\Auth\PlainPassword;
use App\Auth\Domains\Auth\TwoFactorCode;
use App\Auth\Domains\Auth\TwoFactorRecoveryCode;

class Input
{
    public function __construct(
        public Email $email,
        public PlainPassword $password,
        public ?TwoFactorCode $code,
        public ?TwoFactorRecoveryCode $recoveryCode
    ) {
    }
}
