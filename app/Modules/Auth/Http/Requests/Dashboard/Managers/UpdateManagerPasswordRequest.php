<?php

namespace App\Modules\Auth\Http\Requests\Dashboard\Managers;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateManagerPasswordRequest extends FormRequest
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
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
