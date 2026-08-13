<?php

namespace App\Modules\Auth\Http\Requests\Api\V1\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ResendOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'otp_token' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'otp_token.required' => __('messages.OTP token is required'),
        ];
    }
}
