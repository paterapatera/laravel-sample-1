<?php

namespace App\Http\Controllers\Auth\Api\Login;

use App\Http\Controllers\ApiController;
use App\Http\Responses\Api;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Controller extends ApiController
{
    public function __construct(private \Laravel\Fortify\LoginRateLimiter  $limiter)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        return (new \Illuminate\Routing\Pipeline(app()))->send($request)->through(array_filter([
            \Laravel\Fortify\Actions\EnsureLoginIsNotThrottled::class,
            RedirectIfTwoFactorAuthenticatable::class,
            \Laravel\Fortify\Actions\AttemptToAuthenticate::class,
            function ($request, $next) use ($limiter) {
                $this->limiter->clear($request);
                return $next($request);
            },
        ]))->then(function ($request) {
            return Api::ok([
                'token' => auth()->user()->createToken('sample')->plainTextToken,
                'two_factor' => false,
            ]);
        });
    }
}
