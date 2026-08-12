<?php

namespace App\Modules\Auth\Http\Requests\Dashboard\User;

use App\Modules\Auth\Enums\UserStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => $this->normalizedPhone('phone'),
            'whatsapp' => $this->normalizedPhone('whatsapp'),
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $userId = $this->route('id');

        return [
            'country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'name' => ['required', 'string', 'max:180'],
            'phone' => ['nullable', 'regex:/^\+[1-9]\d{7,15}$/', Rule::unique('users', 'phone')->ignore($userId)],
            'whatsapp' => ['required', 'regex:/^\+[1-9]\d{7,15}$/', Rule::unique('users', 'whatsapp')->ignore($userId)],
            'email' => ['nullable', 'email', 'max:180', Rule::unique('users', 'email')->ignore($userId)],
            'status' => ['required', Rule::enum(UserStatus::class)],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ];
    }

    private function normalizedPhone(string $field): ?string
    {
        $phone = $this->input($field);

        return is_string($phone) && trim($phone) !== ''
            ? preg_replace('/\s+/', '', trim($phone))
            : null;
    }
}
