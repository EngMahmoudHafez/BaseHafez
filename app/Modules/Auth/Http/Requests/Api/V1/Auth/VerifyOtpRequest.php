<?php

namespace App\Modules\Auth\Http\Requests\Api\V1\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $identifier = $this->string('identifier')->trim()->value();
        $legacyWhatsapp = $this->string('whatsapp')->trim()->value();
        $legacyUsername = $this->string('username')->trim()->value();

        if (! $this->filled('phone')) {
            if ($identifier !== '') {
                $this->merge(['phone' => $identifier]);
            } elseif ($legacyWhatsapp !== '') {
                $this->merge(['phone' => $legacyWhatsapp]);
            } elseif ($legacyUsername !== '') {
                $this->merge(['phone' => $legacyUsername]);
            }
        }
    }

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
            'identifier' => ['nullable', 'string', 'max:30'],
            'phone' => ['nullable', 'string', 'max:30'],
            'username' => ['nullable', 'string', 'max:30'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'otp_token' => 'required|string',
            'code' => 'required|string|min:4|max:6',
        ];
    }

    public function messages(): array
    {
        return [
            'otp_token.required' => __('messages.OTP token is required'),
            'code.required' => __('messages.OTP code is required'),
            'code.min' => __('messages.OTP code must be at least 4 digits'),
            'code.max' => __('messages.OTP code must be at most 6 digits'),
        ];
    }
}
