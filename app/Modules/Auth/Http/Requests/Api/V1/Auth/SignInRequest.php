<?php

namespace App\Modules\Auth\Http\Requests\Api\V1\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SignInRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $legacyWhatsapp = $this->string('whatsapp')->trim()->value();
        $legacyUsername = $this->string('username')->trim()->value();

        if (! $this->filled('phone')) {
            if ($legacyWhatsapp !== '') {
                $this->merge(['phone' => $legacyWhatsapp]);
            } elseif ($legacyUsername !== '') {
                $this->merge(['phone' => $legacyUsername]);
            }
        }
    }

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
            'country_id' => ['required_without:country_code', 'integer', 'exists:countries,id'],
            'country_code' => ['required_without:country_id', 'string', 'regex:/^\+[1-9]\d{0,3}$/', 'exists:countries,dial_code'],
            'phone' => ['required', 'string', 'max:30'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'username' => ['nullable', 'string', 'max:30'],
            'guest_attempt_id' => ['nullable', 'integer'],
            'guest_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'phone.required' => __('validation.required', ['attribute' => __('validation.attributes.phone')]),
        ];
    }
}
