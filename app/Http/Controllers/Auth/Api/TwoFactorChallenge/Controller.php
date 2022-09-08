<?php

namespace App\Http\Controllers\Auth\Api\TwoFactorChallenge;

use App\Auth\Applications\TwoFactorChallenge\Service;
use App\Http\Controllers\ApiController;
use App\Http\Responses\Api;
use Illuminate\Http\JsonResponse;

class Controller extends ApiController
{
    public function __construct(
        private Service $service,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        return Api::ok(ResponseMapper::toResponse($this->service->run($request->toInput())));
    }
}
