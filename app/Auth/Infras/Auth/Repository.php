<?php

namespace App\Auth\Infras\Auth;

use App\Auth\Domains\Auth\Repository as AuthRepository;
use App\Models\User;
use Illuminate\Validation\UnauthorizedException;

class Repository implements AuthRepository
{
    public function createToken(string $key): \Laravel\Sanctum\NewAccessToken
    {
        /** @var User */
        $user = auth()->user();
        throw_if(is_null($user), new UnauthorizedException());

        return $user->createToken($key);
    }
}
