<?php

namespace App\Auth\Domains\Auth;

interface Repository
{
    public function createToken(string $key): \Laravel\Sanctum\NewAccessToken;
}
