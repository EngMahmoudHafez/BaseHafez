<?php

namespace App\Modules\Auth\Http\Requests\Dashboard\Managers;

use App\Modules\Auth\Enums\UserStatus;
use App\Modules\Auth\Models\Manager;
use App\Modules\Auth\Rules\AssignableRole;
use App\Modules\Auth\Rules\Phone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateManagerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('manager')->check();
    }

    public function rules(): array
    {
        $manager = $this->route('manager');
        $managerId = $manager instanceof Manager ? $manager->getKey() : $manager;

        return [
            'country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'role_id' => ['required', 'integer', 'exists:roles,id', new AssignableRole],
            'name' => ['required', 'string', 'max:180'],
            'email' => ['required', 'email', 'max:180', Rule::unique('managers', 'email')->ignore($managerId)],
            'phone' => ['nullable', new Phone, 'max:30', Rule::unique('managers', 'phone')->ignore($managerId)],
            'whatsapp' => ['nullable', new Phone, 'max:30'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status' => ['required', Rule::enum(UserStatus::class)],
        ];
    }
}
