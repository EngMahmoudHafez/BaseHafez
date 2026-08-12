<?php

namespace App\Modules\Auth\Http\Requests\Api\V1\Auth;

use App\Support\PersonalName;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SignUpRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $firstName = $this->string('first_name')->trim()->value();
        $lastName = $this->string('last_name')->trim()->value();
        $legacyWhatsapp = $this->string('whatsapp')->trim()->value();
        $email = $this->string('email')->trim()->lower()->value();

        $merged = [];

        if (! $this->filled('name') && $firstName !== '') {
            $merged['name'] = PersonalName::combine($firstName, $lastName);
        }

        if (! $this->filled('phone') && $legacyWhatsapp !== '') {
            $merged['phone'] = $legacyWhatsapp;
        }

        if ($email !== '') {
            $merged['email'] = $email;
        }

        if ($merged !== []) {
            $this->merge($merged);
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
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required_without:first_name', 'nullable', 'string', 'max:255'],
            'first_name' => ['required_without:name', 'nullable', 'string', 'max:120'],
            'last_name' => ['required_with:first_name', 'nullable', 'string', 'max:120'],
            'country_id' => ['required_without:country_code', 'integer', 'exists:countries,id'],
            'country_code' => ['required_without:country_id', 'string', 'regex:/^\+[1-9]\d{0,3}$/', 'exists:countries,dial_code'],
            'phone' => ['required', 'string', 'max:30'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'string', 'email', 'max:180'],
            'terms_accepted' => ['required', 'accepted'],
            'guest_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'guest_attempt_id' => ['nullable', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required_without' => __('validation.required', ['attribute' => __('validation.attributes.name')]),
            'last_name.required_with' => __('validation.required', ['attribute' => __('validation.attributes.name')]),
            'country_id.required_without' => __('validation.required', ['attribute' => __('validation.attributes.country')]),
            'country_code.required_without' => __('validation.required', ['attribute' => __('validation.attributes.country_code')]),
            'country_code.regex' => __('messages.Country code format is invalid'),
            'phone.required' => __('validation.required', ['attribute' => __('validation.attributes.phone')]),
            'terms_accepted.required' => __('validation.required', ['attribute' => app()->getLocale() === 'ar' ? 'الشروط والأحكام' : 'terms and conditions']),
            'terms_accepted.accepted' => __('validation.accepted', ['attribute' => app()->getLocale() === 'ar' ? 'الشروط والأحكام' : 'terms and conditions']),
        ];
    }
}
