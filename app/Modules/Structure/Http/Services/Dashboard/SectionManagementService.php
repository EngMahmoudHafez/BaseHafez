<?php

namespace App\Modules\Structure\Http\Services\Dashboard;

use App\Modules\Base\Concerns\FileTrait;
use App\Modules\Structure\Http\Requests\Dashboard\SectionRequest;
use App\Modules\Structure\Repositories\StructureRepositoryInterface;
use App\Modules\Structure\Support\SectionRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

/**
 * The single dashboard workflow behind every content section editor: it renders
 * the generic editor for a section and persists it. Every section is a page.
 */
class SectionManagementService
{
    use FileTrait;

    public function __construct(
        private readonly StructureRepositoryInterface $structures,
    ) {}

    public function index(string $key): View
    {
        $stored = $this->structures->structure($key)?->content ?? [];
        $content = ['all' => $this->sharedContent($key, $stored)];

        foreach (SectionRegistry::locales() as $locale) {
            $content[$locale] = array_merge_recursive_distinct(
                SectionRegistry::pageLocalizedDefaults($key),
                is_array($stored[$locale] ?? null) ? $stored[$locale] : [],
            );
        }

        return $this->view($key, [
            'content' => $content,
            'images' => $this->imageValues($key, $stored),
        ]);
    }

    public function store(SectionRequest $request): RedirectResponse
    {
        $key = $request->sectionKey();
        $newFiles = [];
        $replacedFiles = [];

        try {
            $content = $this->buildPageContent($key, $request, $newFiles, $replacedFiles);
            $this->structures->publish($key, $content);
        } catch (Throwable $exception) {
            $this->safeDeleteFiles($newFiles, 'Structure upload rollback', ['section' => $key]);

            throw $exception;
        }

        $this->safeDeleteFiles($replacedFiles, 'Structure file replacement', ['section' => $key]);

        return redirect()
            ->route("dashboard.structures.{$key}.index")
            ->with('success', __('messages.updated_successfully'));
    }

    /**
     * @param  list<string>  $newFiles
     * @param  list<string>  $replacedFiles
     * @return array<string, mixed>
     */
    private function buildPageContent(string $key, SectionRequest $request, array &$newFiles, array &$replacedFiles): array
    {
        $data = $request->validated();
        $data['all'] ??= [];

        // Images are locale-independent: resolve each once and merge into `all`,
        // which is copied into every locale below.
        foreach ($this->imageFields($key) as $field) {
            $url = $this->resolveImageUrl($key, $field, $request, $newFiles, $replacedFiles);
            if ($url !== null) {
                data_set($data['all'], $field['path'], $url);
            }
        }

        $shared = is_array($data['all']) ? $data['all'] : [];

        $content = [];
        foreach (SectionRegistry::locales() as $locale) {
            $localized = is_array($data[$locale] ?? null) ? $data[$locale] : [];
            $content[$locale] = array_merge_recursive_distinct($localized, $shared);
        }

        return $content;
    }

    /**
     * @param  array{path: string, file_id: int|string}  $field
     * @param  list<string>  $newFiles
     * @param  list<string>  $replacedFiles
     */
    private function resolveImageUrl(string $key, array $field, SectionRequest $request, array &$newFiles, array &$replacedFiles): ?string
    {
        $existing = $request->input("old.{$field['file_id']}");
        $existing = is_string($existing) && $existing !== '' ? $existing : null;

        $upload = $request->file("file.{$field['file_id']}");
        if ($upload === null || ! $upload->isValid()) {
            return $existing;
        }

        $path = $this->image($upload, "content/{$key}");
        $newFiles[] = $path;

        $oldPath = $this->publicStoragePath($key, $existing);
        if ($oldPath !== null) {
            $replacedFiles[] = $oldPath;
        }

        return url(Storage::url($path));
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    private function view(string $key, array $extra): View
    {
        return view('structure::dashboard.section', [
            'sectionKey' => $key,
            'section' => SectionRegistry::find($key),
            ...$extra,
        ]);
    }

    /**
     * Shared (`all`) values for the editor, read back from either locale because
     * they are stored identically in both.
     *
     * @param  array<string, mixed>  $stored
     * @return array<string, mixed>
     */
    private function sharedContent(string $key, array $stored): array
    {
        $section = SectionRegistry::find($key);
        $shared = [];

        foreach (array_keys($section['shared'] ?? []) as $field) {
            $shared[$field] = data_get($stored, "ar.{$field}", data_get($stored, "en.{$field}"));
        }

        foreach ($section['repeatables'] ?? [] as $name => $definition) {
            if ($definition['shared'] ?? false) {
                $shared[$name] = data_get($stored, "ar.{$name}", data_get($stored, "en.{$name}", []));
            }
        }

        return $shared;
    }

    /**
     * Flatten every declared image field of a section to a descriptor with its
     * content path (relative to a locale bucket) and file input id.
     *
     * @return list<array{path: string, file_id: int|string}>
     */
    private function imageFields(string $key): array
    {
        $section = SectionRegistry::find($key);
        $fields = [];

        foreach ($section['fields'] ?? [] as $name => $definition) {
            if (($definition['type'] ?? null) === 'image') {
                $fields[] = ['path' => $name, 'file_id' => $definition['file_id'] ?? $name];
            }
        }

        foreach ($section['groups'] ?? [] as $group => $groupDefinition) {
            foreach ($groupDefinition['fields'] ?? [] as $name => $definition) {
                if (($definition['type'] ?? null) === 'image') {
                    $fields[] = ['path' => "{$group}.{$name}", 'file_id' => $definition['file_id'] ?? "{$group}_{$name}"];
                }
            }
        }

        foreach ($section['shared'] ?? [] as $name => $definition) {
            if (($definition['type'] ?? null) === 'image') {
                $fields[] = ['path' => $name, 'file_id' => $definition['file_id'] ?? $name];
            }
        }

        return $fields;
    }

    /**
     * Current stored URL for each declared image field, keyed by file input id,
     * so the editor can preview it and round-trip it through a hidden input.
     *
     * @param  array<string, mixed>  $stored
     * @return array<int|string, string|null>
     */
    private function imageValues(string $key, array $stored): array
    {
        $values = [];
        foreach ($this->imageFields($key) as $field) {
            $values[$field['file_id']] = data_get($stored, "ar.{$field['path']}", data_get($stored, "en.{$field['path']}"));
        }

        return $values;
    }

    private function publicStoragePath(string $key, ?string $url): ?string
    {
        if (! is_string($url) || $url === '') {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH) ?: $url;
        $storageMarker = '/storage/';
        $position = strpos($path, $storageMarker);

        $relativePath = $position === false
            ? ltrim($path, '/')
            : substr($path, $position + strlen($storageMarker));
        $expectedDirectory = "content/{$key}/";

        return str_starts_with($relativePath, $expectedDirectory) ? $relativePath : null;
    }
}
