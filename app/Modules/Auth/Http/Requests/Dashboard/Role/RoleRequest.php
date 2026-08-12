<?php

namespace App\Modules\Auth\Http\Requests\Dashboard\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('manager')->check();
    }

    public function rules(): array
    {
        return [
            'display_name_ar' => ['required', 'string', 'max:100'],
            'display_name_en' => [
                'required',
                'string',
                'max:100',
                Rule::unique('roles', 'display_name_en')->ignore($this->route('id')),
            ],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['required', Rule::exists('permissions', 'id')],
        ];
    }
}
