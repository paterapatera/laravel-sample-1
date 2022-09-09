<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Auth\Domains;
use App\Auth\Infras;
use App\Http\Controllers\Auth\Api\Register\Response;
use App\Mail\TMail;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Mail;
use Laravel\Fortify\Contracts\RegisterResponse;

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
        $this->app->singleton(Domains\Auth\Repository::class, Infras\Auth\Repository::class);
        $this->app->singleton(RegisterResponse::class, Response::class);
    }
}
