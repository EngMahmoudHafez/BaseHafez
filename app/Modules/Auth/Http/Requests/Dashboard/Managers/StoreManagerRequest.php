<?php

namespace App\Modules\Auth\Http\Requests\Dashboard\Managers;

use App\Modules\Auth\Enums\UserStatus;
use App\Modules\Auth\Rules\AssignableRole;
use App\Modules\Auth\Rules\Phone;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreManagerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('manager')->check();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'role_id' => ['required', 'integer', 'exists:roles,id', new AssignableRole],
            'name' => ['required', 'string', 'max:180'],
            'email' => ['required', 'email', 'max:180', 'unique:managers,email'],
            'phone' => ['nullable', new Phone, 'max:30', 'unique:managers,phone'],
            'whatsapp' => ['nullable', new Phone, 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status' => ['required', Rule::enum(UserStatus::class)],
        ];
    }
}
