<?php

namespace App\Modules\Structure\Support;

/**
 * Builds the validation rule set for a section from its registry definition, so
 * the same declarative definition that drives the editor and serialization also
 * drives validation.
 */
final class SectionRules
{
    /**
     * @return array<string, mixed>
     */
    public static function for(string $key): array
    {
        $section = SectionRegistry::find($key);
        $rules = [];

        foreach ($section['shared'] ?? [] as $field => $definition) {
            $rules = self::mergeFieldRule($rules, "all.{$field}", $definition);
        }

        foreach ($section['repeatables'] ?? [] as $name => $definition) {
            if ($definition['shared'] ?? false) {
                $rules += self::repeatableRules('all', $name, $definition);
            }
        }

        foreach (SectionRegistry::locales() as $locale) {
            foreach ($section['fields'] ?? [] as $field => $definition) {
                $rules = self::mergeFieldRule($rules, "{$locale}.{$field}", $definition);
            }

            foreach ($section['groups'] ?? [] as $group => $groupDefinition) {
                foreach ($groupDefinition['fields'] ?? [] as $field => $definition) {
                    $rules = self::mergeFieldRule($rules, "{$locale}.{$group}.{$field}", $definition);
                }
            }

            foreach ($section['repeatables'] ?? [] as $name => $definition) {
                if (! ($definition['shared'] ?? false)) {
                    $rules += self::repeatableRules($locale, $name, $definition);
                }
            }
        }

        if (self::hasImageField($section)) {
            $rules['file.*'] = ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'];
        }

        return $rules;
    }

    /**
     * @param  array<string, mixed>  $rules
     * @param  array<string, mixed>  $definition
     * @return array<string, mixed>
     */
    private static function mergeFieldRule(array $rules, string $path, array $definition): array
    {
        if (($definition['type'] ?? 'text') === 'image') {
            return $rules; // uploads validated by the `file.*` rule
        }

        $rules[$path] = $definition['rules'] ?? ['nullable', 'string'];

        return $rules;
    }

    /**
     * @param  array<string, mixed>  $definition
     * @return array<string, mixed>
     */
    private static function repeatableRules(string $prefix, string $name, array $definition): array
    {
        $max = $definition['max'] ?? SectionRegistry::MAX_ITEMS;
        $rules = ["{$prefix}.{$name}" => ['sometimes', 'array', "max:{$max}"]];
        $rules = self::itemMetaRules($rules, "{$prefix}.{$name}", $definition['item'] ?? []);

        foreach ($definition['fields'] ?? [] as $field => $fieldDefinition) {
            if (($fieldDefinition['type'] ?? 'text') === 'image') {
                continue;
            }
            $rules["{$prefix}.{$name}.*.{$field}"] = $fieldDefinition['rules'] ?? ['nullable', 'string'];
        }

        return $rules;
    }

    /**
     * @param  array<string, mixed>  $rules
     * @param  list<string>  $meta
     * @return array<string, mixed>
     */
    private static function itemMetaRules(array $rules, string $path, array $meta): array
    {
        $metaRules = [
            'key' => ['required', 'string', 'max:80', 'distinct'],
            'sort_order' => ['required', 'integer', 'min:1', 'distinct'],
            'visible' => ['required', 'boolean'],
            'rating' => ['required', 'integer', 'between:1,5'],
        ];

        foreach ($meta as $name) {
            if (isset($metaRules[$name])) {
                $rules["{$path}.*.{$name}"] = $metaRules[$name];
            }
        }

        return $rules;
    }

    /**
     * @param  array<string, mixed>  $section
     */
    private static function hasImageField(array $section): bool
    {
        foreach ([...array_values($section['fields'] ?? []), ...array_values($section['shared'] ?? [])] as $definition) {
            if (($definition['type'] ?? null) === 'image') {
                return true;
            }
        }

        foreach ($section['groups'] ?? [] as $group) {
            foreach ($group['fields'] ?? [] as $definition) {
                if (($definition['type'] ?? null) === 'image') {
                    return true;
                }
            }
        }

        return false;
    }
}
