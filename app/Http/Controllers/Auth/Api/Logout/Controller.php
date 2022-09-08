<?php

namespace App\Http\Controllers\Auth\Api\Logout;

use App\Auth\Applications\Logout\Service;
use App\Http\Controllers\ApiController;
use App\Http\Responses\Api;
use Illuminate\Http\JsonResponse;

class Controller extends ApiController
{
    public function __construct(
        private Service $service,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $this->service->run();
        return Api::noContent();
    }
}
