<?php

namespace App\Auth\Applications\TwoFactorChallenge;

use App\Auth\Domains\Auth\Auth;
use App\Auth\Domains\Auth\TwoFactorRecoveryCode;
use App\Auth\Domains\Auth\Repository;
use App\Auth\Domains\Auth\TwoFactorConfirmer;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Laravel\Fortify\RecoveryCode as FortifyRecoveryCode;

class Service
{
    public function __construct(
        private Repository $authRepository,
        private StatefulGuard $guard,
        private TwoFactorAuthenticationProvider $tfaProvider,
    ) {
    }

    public function run(Input $input): Output
    {
        $auth = $this->getAuthOrFail($input);

        $this->confirmTwoFactor($input, $auth);

        $this->replaceIfValidRecovery($input, $auth);

        $this->guard->login($auth->user);

        $key = 'sample';
        return new Output(
            token: $this->authRepository->createToken($key)->plainTextToken
        );
    }

    private function getAuthOrFail(Input $input): Auth
    {
        $exception = ValidationException::withMessages(['email' => [trans('auth.failed')]]);
        try {
            $auth = $this->authRepository->findByEmail($input->email);
        } catch (ModelNotFoundException $e) {
            throw $exception;
        }

        throw_if(!$auth->password->equalHash($input->password), $exception);

        return $auth;
    }

    private function confirmTwoFactor(Input $input, Auth $auth): void
    {
        $isValidRecovery = TwoFactorConfirmer::isValidRecovery($input->recoveryCode, $auth->twoFactorRecoveryCodes);
        $isValidCode = fn () => TwoFactorConfirmer::isValid($input->code, $auth->twoFactorSecret, $this->tfaProvider);

        if (!$isValidRecovery && !$isValidCode()) {
            [$key, $message] = $input->recoveryCode
                ? ['recovery_code', __('The provided two factor recovery code was invalid.')]
                : ['code', __('The provided two factor authentication code was invalid.')];

            throw ValidationException::withMessages([$key => [$message]]);
        }
    }

    private function replaceIfValidRecovery(Input $input, Auth $auth): void
    {
        $isValidRecovery = TwoFactorConfirmer::isValidRecovery($input->recoveryCode, $auth->twoFactorRecoveryCodes);
        if ($isValidRecovery) {
            $newCode = new TwoFactorRecoveryCode(FortifyRecoveryCode::generate());
            $auth->replaceRecoveryCode($input->recoveryCode, $newCode);
        }
    }
}
