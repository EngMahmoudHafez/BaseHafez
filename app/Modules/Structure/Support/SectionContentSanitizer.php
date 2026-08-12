<?php

namespace App\Modules\Structure\Support;

use App\Modules\Base\Support\HtmlSanitizer;

/**
 * Sanitizes only the rich-text (`textarea`) leaves of a section's built content
 * against an HTML allow-list, leaving plain text / url / number fields untouched
 * (so a legitimate "A < B" title is not mangled). Driven by the section registry
 * so a newly declared textarea field is covered automatically.
 */
final class SectionContentSanitizer
{
    public function __construct(private readonly HtmlSanitizer $html) {}

    /**
     * @param  array<string, mixed>  $content  per-locale content buckets
     * @return array<string, mixed>
     */
    public function sanitize(string $key, array $content): array
    {
        if (! SectionRegistry::has($key)) {
            return $content;
        }

        $paths = $this->richTextPaths($key);

        if ($paths === []) {
            return $content;
        }

        foreach (SectionRegistry::locales() as $locale) {
            if (! is_array($content[$locale] ?? null)) {
                continue;
            }

            foreach ($paths as $path) {
                $content[$locale] = $this->sanitizePath($content[$locale], $path);
            }
        }

        return $content;
    }

    /**
     * Every rich-text field path within a locale bucket. Shared fields and shared
     * repeatables are merged into each locale, so they are covered here too.
     *
     * @return list<string>
     */
    private function richTextPaths(string $key): array
    {
        $section = SectionRegistry::find($key);
        $paths = [];

        foreach ($section['fields'] ?? [] as $field => $definition) {
            if (self::isRichText($definition)) {
                $paths[] = $field;
            }
        }

        foreach ($section['groups'] ?? [] as $group => $groupDefinition) {
            foreach ($groupDefinition['fields'] ?? [] as $field => $definition) {
                if (self::isRichText($definition)) {
                    $paths[] = "{$group}.{$field}";
                }
            }
        }

        foreach ($section['shared'] ?? [] as $field => $definition) {
            if (self::isRichText($definition)) {
                $paths[] = $field;
            }
        }

        foreach ($section['repeatables'] ?? [] as $name => $definition) {
            foreach ($definition['fields'] ?? [] as $field => $fieldDefinition) {
                if (self::isRichText($fieldDefinition)) {
                    $paths[] = "{$name}.*.{$field}";
                }
            }
        }

        return $paths;
    }

    /**
     * @param  array<string, mixed>  $bucket
     * @return array<string, mixed>
     */
    private function sanitizePath(array $bucket, string $path): array
    {
        if (str_contains($path, '.*.')) {
            [$prefix, $suffix] = explode('.*.', $path, 2);
            $items = data_get($bucket, $prefix);

            if (is_array($items)) {
                foreach (array_keys($items) as $index) {
                    $value = data_get($bucket, "{$prefix}.{$index}.{$suffix}");

                    if (is_string($value)) {
                        data_set($bucket, "{$prefix}.{$index}.{$suffix}", $this->html->sanitize($value));
                    }
                }
            }

            return $bucket;
        }

        $value = data_get($bucket, $path);

        if (is_string($value)) {
            data_set($bucket, $path, $this->html->sanitize($value));
        }

        return $bucket;
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    private static function isRichText(array $definition): bool
    {
        return ($definition['type'] ?? null) === 'textarea';
    }
}
