<?php

namespace App\Modules\Structure\Http\Requests\Dashboard;

use App\Modules\Structure\Support\SectionRules;
use Illuminate\Foundation\Http\FormRequest;

/**
 * One Form Request for every content section. It resolves the section from the
 * route (bound as the `section` default) and builds its rules from the registry.
 * Authorization is enforced by the `permission:structures-update` route middleware.
 */
class SectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return SectionRules::for($this->sectionKey());
    }

    public function sectionKey(): string
    {
        return (string) $this->route('section');
    }
}
