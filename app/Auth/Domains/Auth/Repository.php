<?php

namespace App\Auth\Domains\Auth;

interface Repository
{
    public function createToken(string $key): \Laravel\Sanctum\NewAccessToken;
    public function deleteAllTokens(): void;
    public function findByEmail(Email $email): Auth;
}
