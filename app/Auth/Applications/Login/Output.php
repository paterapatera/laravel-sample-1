<?php

namespace App\Auth\Applications\Login;

class Output
{
    public $twoFactor = false;
    public function __construct(public string $token)
    {
    }
}
