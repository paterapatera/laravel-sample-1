<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

use App\Auth\Domains\Auth\Auth;
use App\Auth\Domains\Auth\Email;
use App\Auth\Domains\Auth\Password;
use App\Auth\Domains\Auth\TwoFactorRecoveryCodes;
use App\Auth\Domains\Auth\TwoFactorSecret;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    static public function toDomain(User $user): Auth
    {
        $auth = new Auth(
            new Email($user->email),
            new Password($user->password),
            $user,
        );
        $auth->twoFactorRecoveryCodes = valmap(
            $user->two_factor_recovery_codes,
            fn ($v) => new TwoFactorRecoveryCodes($v)
        );
        $auth->twoFactorSecret = valmap(
            $user->two_factor_secret,
            fn ($v) => new TwoFactorSecret($v)
        );

        return $auth;
    }
}
