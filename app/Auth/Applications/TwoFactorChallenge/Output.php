<?php

namespace App\Auth\Applications\TwoFactorChallenge;

class Output
{
    public function __construct(public string $token)
    {
    }
}
