<?php

namespace App\Http\Controllers\Auth\Api\TwoFactorChallenge;

use App\Auth\Applications\TwoFactorChallenge\Output;

class ResponseMapper
{
    static public function toResponse(Output $output): array
    {
        return [
            'token' => $output->token,
        ];
    }
}
