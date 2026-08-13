<?php

namespace App\Modules\Auth\Http\Requests\Dashboard\Settings;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InfoSettingsRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'email' => ['required', 'email', 'max:255', Rule::unique('managers', 'email')->ignore(auth('manager')->id())],
            'phone' => ['nullable', 'string', 'max:30', Rule::unique('managers', 'phone')->ignore(auth('manager')->id())],
        ];
    }
}
