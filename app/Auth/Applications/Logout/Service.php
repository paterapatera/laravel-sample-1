<?php

namespace App\Auth\Applications\Logout;

use App\Auth\Domains\Auth\Repository;
use Illuminate\Contracts\Auth\StatefulGuard;

class Service
{
    public function __construct(
        private Repository $authRepository,
        private StatefulGuard $guard
    ) {
    }

    public function run(): void
    {
        $this->authRepository->deleteAllTokens();
        $this->guard->logout();
    }
}
