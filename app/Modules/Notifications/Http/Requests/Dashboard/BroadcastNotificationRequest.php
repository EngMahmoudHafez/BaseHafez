<?php

namespace App\Modules\Notifications\Http\Requests\Dashboard;

use App\Modules\Notifications\Models\Notification;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BroadcastNotificationRequest extends FormRequest
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
            'target_type' => ['required', Rule::in(['all', 'users'])],
            'user_ids' => ['nullable', 'required_if:target_type,users', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'distinct', 'exists:users,id'],
            'title_ar' => ['required', 'string', 'max:255'],
            'title_en' => ['nullable', 'string', 'max:255'],
            'body_ar' => ['required', 'string', 'max:5000'],
            'body_en' => ['nullable', 'string', 'max:5000'],
            'type' => ['nullable', Rule::in(array_keys(Notification::getTypes()))],
            'data' => ['nullable', 'array'],
            'action_url' => ['nullable', 'url', 'max:500'],
            'icon' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function attributes(): array
    {
        return [
            'target_type' => __('notifications.fields.target_type'),
            'user_ids' => __('notifications.fields.users'),
            'title_ar' => __('notifications.fields.title_ar'),
            'title_en' => __('notifications.fields.title_en'),
            'body_ar' => __('notifications.fields.body_ar'),
            'body_en' => __('notifications.fields.body_en'),
            'type' => __('notifications.fields.type'),
            'action_url' => __('notifications.fields.action_url'),
        ];
    }
}
