<?php

namespace App\Modules\Auth\Http\Requests\Dashboard\Managers;

use Illuminate\Foundation\Http\FormRequest;

class UpdateManagerPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('manager')->check();
    }

    public function rules(): array
    {
        return [
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
