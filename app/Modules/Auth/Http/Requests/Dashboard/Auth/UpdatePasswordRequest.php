<?php

namespace App\Modules\Auth\Http\Requests\Dashboard\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdatePasswordRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'current_password:manager'],
            'new_password' => ['required', Password::min(8), 'confirmed', 'different:current_password'],
        ];
    }

    public function attributes(): array
    {
        return [
            'current_password' => __('auth.current_password') ?: 'كلمة المرور الحالية',
            'new_password' => __('auth.new_password') ?: 'كلمة المرور الجديدة',
            'new_password_confirmation' => __('auth.password_confirmation') ?: 'تأكيد كلمة المرور الجديدة',
        ];
    }
}
