<?php

namespace App\Modules\Auth\Http\Requests\Api\V1\UserProfile;

use App\Modules\Auth\Models\User;
use App\Modules\Auth\Rules\PhoneMatchesCountryCode;
use App\Support\PersonalName;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('email')) {
            $this->merge(['email' => $this->string('email')->trim()->lower()->value()]);
        }

        if ($this->exists('first_name') || $this->exists('last_name')) {
            [$storedFirstName, $storedLastName] = PersonalName::split($this->user()?->name);

            $firstName = $this->exists('first_name')
                ? trim((string) $this->input('first_name', ''))
                : $storedFirstName;
            $lastName = $this->exists('last_name')
                ? trim((string) $this->input('last_name', ''))
                : $storedLastName;
            $name = PersonalName::combine($firstName, $lastName);

            if ($name !== null) {
                $this->merge(['name' => $name]);
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'first_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'last_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'country_code' => ['required_with:phone', 'string', 'regex:/^\+[1-9]\d{0,3}$/'],
            'phone' => [
                'sometimes',
                'string',
                'regex:/^[0-9]{6,14}$/',
                new PhoneMatchesCountryCode($this->input('country_code')),
                function ($attribute, $value, $fail) use ($userId) {
                    if (! $this->has('country_code')) {
                        return;
                    }
                    $e164 = (string) $this->input('country_code') . ltrim($value, '0');
                    $exists = User::query()
                        ->where('phone', $e164)
                        ->where('id', '!=', $userId)
                        ->exists();
                    if ($exists) {
                        $fail(__('messages.Phone already exists'));
                    }
                },
            ],
            'email' => ['sometimes', 'nullable', 'email', 'max:180', 'unique:users,email,' . $this->user()->id],
            'profile_image' => ['sometimes', 'nullable', 'image', 'mimes:jpeg,jpg,png,gif', 'max:2048'],
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => __('validation.required', ['attribute' => __('auth.name')]),
            'phone.regex' => __('messages.Phone format is invalid'),
            'country_code.required_with' => __('validation.required_with', ['attribute' => __('validation.attributes.country_code'), 'values' => __('validation.attributes.phone')]),
            'country_code.regex' => __('messages.Country code format is invalid'),
            'phone.unique' => __('validation.unique', ['attribute' => __('auth.phone')]),
            'email.email' => __('validation.email', ['attribute' => __('auth.email')]),
            'email.unique' => __('validation.unique', ['attribute' => __('auth.email')]),
        ];
    }
}
