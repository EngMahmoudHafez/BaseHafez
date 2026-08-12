<?php

namespace App\Modules\Auth\Http\Requests\Api\V1\UserProfile;

use Illuminate\Foundation\Http\FormRequest;

class UploadProfileImageRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->hasFile('profile_image') && $this->hasFile('image')) {
            $this->files->set('profile_image', $this->file('image'));
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
     */
    public function rules(): array
    {
        return [
            'profile_image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'profile_image.required' => __('validation.required', ['attribute' => __('auth.image')]),
            'profile_image.image' => __('validation.image', ['attribute' => __('auth.image')]),
            'profile_image.mimes' => __('validation.mimes', ['attribute' => __('auth.image')]),
            'profile_image.max' => __('validation.max.file', ['attribute' => __('auth.image'), 'max' => 2048]),
        ];
    }
}
