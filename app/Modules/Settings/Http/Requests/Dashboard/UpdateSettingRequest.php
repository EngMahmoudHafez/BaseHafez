<?php

namespace App\Modules\Settings\Http\Requests\Dashboard;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Updates the editable fields of a setting. The immutable `key` is intentionally
 * absent from the rules so it can never be changed through the dashboard.
 * Authorization is enforced by the `permission:settings-update` route middleware.
 */
class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'value' => ['nullable', 'string', 'max:65535'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'value' => __('settings::settings.fields.value'),
            'notes' => __('settings::settings.fields.notes'),
        ];
    }
}
