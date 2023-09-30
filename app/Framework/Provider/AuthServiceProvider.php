<?php

namespace App\Framework\Provider;

// use Illuminate\Support\Facades\Gate;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Mail;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        ResetPassword::toMailUsing(function ($notifiable, $token) {
            return (new MailMessage())
                ->greeting(trans('Reset Password Notification'))
                ->subject(trans('Reset Password Notification'))
                ->line(trans('You are receiving this email because we received a password reset request for your account.'))
                ->line('CODE : ' . $token)
                ->line(trans('This password reset link will expire in :count minutes.', ['count' => config('auth.passwords.' . config('auth.defaults.passwords') . '.expire')]))
                ->line(trans('If you did not request a password reset, no further action is required.'));
        });
    }

    public function register()
    {
    }
}
