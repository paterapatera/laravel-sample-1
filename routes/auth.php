<?php

use App\Http\Controllers\Auth\Api;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Laravel\Fortify\Http\Controllers\ConfirmablePasswordController;
use Laravel\Fortify\Http\Controllers\ConfirmedPasswordStatusController;
use Laravel\Fortify\Http\Controllers\ConfirmedTwoFactorAuthenticationController;
use Laravel\Fortify\Http\Controllers\NewPasswordController;
use Laravel\Fortify\Http\Controllers\PasswordController;
use Laravel\Fortify\Http\Controllers\PasswordResetLinkController;
use Laravel\Fortify\Http\Controllers\ProfileInformationController;
use Laravel\Fortify\Http\Controllers\RecoveryCodeController;
use Laravel\Fortify\Http\Controllers\RegisteredUserController;
use Laravel\Fortify\Http\Controllers\TwoFactorAuthenticationController;
use Laravel\Fortify\Http\Controllers\TwoFactorQrCodeController;
use Laravel\Fortify\Http\Controllers\TwoFactorSecretKeyController;

Route::group(['middleware' => ['api']], function () {
    $limiter = config('fortify.limiters.login');
    $twoFactorLimiter = config('fortify.limiters.two-factor');
    $verificationLimiter = config('fortify.limiters.verification', '6,1');

    Route::post('/login', Api\Login\Controller::class)->middleware(array_filter([
        'guest:sanctum',
        $limiter ? 'throttle:' . $limiter : null,
    ]));

    Route::post('/logout', Api\Logout\Controller::class)->middleware(['auth:sanctum']);

    // Password Reset...
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
        ->middleware(['guest:sanctum'])
        ->name('password.email');

    Route::post('/reset-password', [NewPasswordController::class, 'store'])
        ->middleware(['guest:sanctum'])
        ->name('password.update');

    // Registration...
    Route::post('/register', [RegisteredUserController::class, 'store'])
        ->middleware(['guest:sanctum']);

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
    Route::post('/two-factor-challenge', Api\TwoFactorChallenge\Controller::class)
        ->middleware(array_filter([
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
