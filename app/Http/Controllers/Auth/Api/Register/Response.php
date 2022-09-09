<?php

namespace App\Http\Controllers\Auth\Api\Register;

use Illuminate\Http\JsonResponse;
use Illuminate\Validation\UnauthorizedException;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Laravel\Fortify\Fortify;

class Response implements RegisterResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        /** @var User */
        $user = auth()->user();
        throw_if(is_null($user), new UnauthorizedException());

        $key = 'sample';
        return $request->wantsJson()
            ? new JsonResponse([
                'token' => $user->createToken($key)->plainTextToken,
            ], 201)
            : redirect()->intended(Fortify::redirects('register'));
    }
}
