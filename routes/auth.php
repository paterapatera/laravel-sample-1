<?php

use App\Http\Controllers\Auth\Api;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Laravel\Fortify\Http\Controllers\ConfirmablePasswordController;
use Laravel\Fortify\Http\Controllers\ConfirmedPasswordStatusController;
use Laravel\Fortify\Http\Controllers\ConfirmedTwoFactorAuthenticationController;
use Laravel\Fortify\Http\Controllers\EmailVerificationNotificationController;
use Laravel\Fortify\Http\Controllers\EmailVerificationPromptController;
use Laravel\Fortify\Http\Controllers\NewPasswordController;
use Laravel\Fortify\Http\Controllers\PasswordController;
use Laravel\Fortify\Http\Controllers\PasswordResetLinkController;
use Laravel\Fortify\Http\Controllers\ProfileInformationController;
use Laravel\Fortify\Http\Controllers\RecoveryCodeController;
use Laravel\Fortify\Http\Controllers\RegisteredUserController;
use Laravel\Fortify\Http\Controllers\TwoFactorAuthenticatedSessionController;
use Laravel\Fortify\Http\Controllers\TwoFactorAuthenticationController;
use Laravel\Fortify\Http\Controllers\TwoFactorQrCodeController;
use Laravel\Fortify\Http\Controllers\TwoFactorSecretKeyController;
use Laravel\Fortify\Http\Controllers\VerifyEmailController;

class RedirectIfTwoFactorAuthenticatable extends \Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable
{
    protected function twoFactorChallengeResponse($request, $user)
    {
        \Laravel\Fortify\Events\TwoFactorAuthenticationChallenged::dispatch($user);

        return $request->wantsJson()
            ? response()->json(['two_factor' => true])
            : redirect()->route('two-factor.login');
    }
}

Route::group(['middleware' => ['api']], function () {
    $limiter = config('fortify.limiters.login');
    $twoFactorLimiter = config('fortify.limiters.two-factor');
    $verificationLimiter = config('fortify.limiters.verification', '6,1');

    Route::post('/login', Api\Login\Controller::class)->middleware(array_filter([
        'guest:sanctum',
        $limiter ? 'throttle:' . $limiter : null,
    ]));

    Route::post('/logout', function (Request $request, \Illuminate\Contracts\Auth\StatefulGuard $guard, \Laravel\Fortify\LoginRateLimiter  $limiter) {
        return (new \Illuminate\Routing\Pipeline(app()))->send($request)->through(array_filter([
            function ($request, $next) use ($guard, $limiter) {
                auth()->user()?->tokens()->delete();
                $guard->logout();
                return $next($request);
            },
        ]))->then(function ($request) {
            return response()->json('', 204);
        });
    })->middleware(['auth:sanctum'])->name('logout');

    // Password Reset...
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
        ->middleware(['guest:' . config('fortify.guard')])
        ->name('password.email');

    Route::post('/reset-password', [NewPasswordController::class, 'store'])
        ->middleware(['guest:' . config('fortify.guard')])
        ->name('password.update');

    // Registration...
    Route::post('/register', [RegisteredUserController::class, 'store'])
        ->middleware(['guest:' . config('fortify.guard')]);

    // Email Verification...
    // Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
    //     ->middleware(['auth:sanctum', 'signed', 'throttle:' . $verificationLimiter])
    //     ->name('verification.verify');

    // Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
    //     ->middleware(['auth:sanctum', 'throttle:' . $verificationLimiter])
    //     ->name('verification.send');

    // Profile Information...
    Route::put('/user/profile-information', [ProfileInformationController::class, 'update'])
        ->middleware(['auth:sanctum'])
        ->name('user-profile-information.update');

    // Passwords...
    Route::put('/user/password', [PasswordController::class, 'update'])
        ->middleware(['auth:sanctum'])
        ->name('user-password.update');

    // Password Confirmation...
    Route::get('/user/confirmed-password-status', [ConfirmedPasswordStatusController::class, 'show'])
        ->middleware(['auth:sanctum'])
        ->name('password.confirmation');

    Route::post('/user/confirm-password', [ConfirmablePasswordController::class, 'store'])
        ->middleware(['auth:sanctum'])
        ->name('password.confirm');

    // Two Factor Authentication...
    Route::post('/two-factor-challenge', function (Request $request, \Illuminate\Contracts\Auth\StatefulGuard $guard, \Laravel\Fortify\LoginRateLimiter  $limiter) {

        $throwFailedAuthenticationException = function ($request) use ($limiter) {
            $limiter->increment($request);

            throw \Illuminate\Validation\ValidationException::withMessages([
                \Laravel\Fortify\Fortify::username() => [trans('auth.failed')],
            ]);
        };
        $fireFailedEvent = function ($request, $user = null) {
            event(new \Illuminate\Auth\Events\Failed(config('fortify.guard'), $user, [
                \Laravel\Fortify\Fortify::username() => $request->{\Laravel\Fortify\Fortify::username()},
                'password' => $request->password,
            ]));
        };
        $validateCredentials = function (Request $request) use ($guard, $fireFailedEvent, $throwFailedAuthenticationException) {
            $model = $guard->getProvider()->getModel();

            return tap($model::where(\Laravel\Fortify\Fortify::username(), $request->{\Laravel\Fortify\Fortify::username()})->first(), function ($user) use ($guard, $throwFailedAuthenticationException, $fireFailedEvent, $request) {
                if (!$user || !$guard->getProvider()->validateCredentials($user, ['password' => $request->password])) {
                    $fireFailedEvent($request, $user);

                    $throwFailedAuthenticationException($request);
                }
            });
        };

        $validRecoveryCode = function () use ($request, $validateCredentials) {
            if (!$request->recovery_code) {
                return;
            }

            return collect($validateCredentials($request)->recoveryCodes())->first(function ($code) use ($request) {
                return hash_equals($request->recovery_code, $code) ? $code : null;
            });
        };

        $hasValidCode = function () use ($request, $validateCredentials) {
            return $request->code && app(\Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider::class)->verify(
                decrypt($validateCredentials($request)->two_factor_secret),
                $request->code
            );
        };

        $request->validate([
            'code' => 'nullable|string',
            'recovery_code' => 'nullable|string',
        ]);

        $user = $validateCredentials($request);

        if ($code = $validRecoveryCode()) {
            $user->replaceRecoveryCode($code);

            event(new \Laravel\Fortify\Events\RecoveryCodeReplaced($user, $code));
        } elseif (!$hasValidCode()) {
            return app(\Laravel\Fortify\Contracts\FailedTwoFactorLoginResponse::class)->toResponse($request);
        }

        $guard->login($user);

        return response()->json(['token' => $user->createToken('sample')->plainTextToken,]);
    })->middleware(array_filter([
        'guest:sanctum',
        $twoFactorLimiter ? 'throttle:' . $twoFactorLimiter : null,
    ]));

    $twoFactorMiddleware = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword')
        ? ['auth:sanctum', 'password.confirm']
        : ['auth:sanctum'];

    Route::post('/user/two-factor-authentication', [TwoFactorAuthenticationController::class, 'store'])
        ->middleware($twoFactorMiddleware)
        ->name('two-factor.enable');

    Route::post('/user/confirmed-two-factor-authentication', [ConfirmedTwoFactorAuthenticationController::class, 'store'])
        ->middleware($twoFactorMiddleware)
        ->name('two-factor.confirm');

    Route::delete('/user/two-factor-authentication', [TwoFactorAuthenticationController::class, 'destroy'])
        ->middleware($twoFactorMiddleware)
        ->name('two-factor.disable');

    Route::get('/user/two-factor-qr-code', [TwoFactorQrCodeController::class, 'show'])
        ->middleware($twoFactorMiddleware)
        ->name('two-factor.qr-code');

    Route::get('/user/two-factor-secret-key', [TwoFactorSecretKeyController::class, 'show'])
        ->middleware($twoFactorMiddleware)
        ->name('two-factor.secret-key');

    Route::get('/user/two-factor-recovery-codes', [RecoveryCodeController::class, 'index'])
        ->middleware($twoFactorMiddleware)
        ->name('two-factor.recovery-codes');

    Route::post('/user/two-factor-recovery-codes', [RecoveryCodeController::class, 'store'])
        ->middleware($twoFactorMiddleware);
});
