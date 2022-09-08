<?php

namespace App\Http\Controllers\Auth\Api\TwoFactorChallenge;

use App\Auth\Applications\TwoFactorChallenge\Input;
use App\Auth\Domains\Auth\Email;
use App\Auth\Domains\Auth\PlainPassword;
use App\Auth\Domains\Auth\TwoFactorCode;
use App\Auth\Domains\Auth\TwoFactorRecoveryCode;
use Illuminate\Foundation\Http\FormRequest;

class Request extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => 'string',
            'password' => 'string',
            'code' => 'nullable|string',
            'recovery_code' => 'nullable|string',
        ];
    }

    public function toInput(): Input
    {
        return new Input(
            new Email($this->email),
            new PlainPassword($this->password),
            valmap($this->code, fn ($v) => new TwoFactorCode($v)),
            valmap($this->recovery_code, fn ($v) => new TwoFactorRecoveryCode($v)),
        );
    }
}
