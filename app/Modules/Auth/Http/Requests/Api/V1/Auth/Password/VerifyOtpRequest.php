<?php

namespace App\Modules\Auth\Http\Requests\Api\V1\Auth\Password;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email:rfc', 'max:180'],
            'otp_token' => ['required', 'string'],
            'otp' => ['required', 'string', 'min:4', 'max:6'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => __('validation.required', ['attribute' => __('validation.attributes.email')]),
            'email.email' => __('validation.email', ['attribute' => __('validation.attributes.email')]),
            'otp_token.required' => __('messages.OTP token is required'),
            'otp.required' => __('messages.OTP code is required'),
            'otp.min' => __('messages.OTP code must be at least 4 digits'),
            'otp.max' => __('messages.OTP code must be at most 6 digits'),
        ];
    }
}
