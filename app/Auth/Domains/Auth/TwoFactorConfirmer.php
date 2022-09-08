<?php

namespace App\Auth\Domains\Auth;

use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Throwable;

class TwoFactorConfirmer
{
    static public function isValidRecovery(
        ?TwoFactorRecoveryCode $twoFactorRecoveryCode,
        TwoFactorRecoveryCodes $twoFactorRecoveryCodes
    ): bool {
        return $twoFactorRecoveryCode && $twoFactorRecoveryCodes->contains($twoFactorRecoveryCode);
    }

    static public function isValid(
        ?TwoFactorCode $twoFactorCode,
        TwoFactorSecret $twoFactorSecret,
        TwoFactorAuthenticationProvider $tfaProvider
    ): bool {
        return $twoFactorCode && $tfaProvider->verify(
            secret: $twoFactorSecret->decrypt(),
            code: $twoFactorCode->value()
        );
    }
}
