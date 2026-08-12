---
paths:
  - 'app/Modules/Structure/**'
---

# Structure

## Rich-text content is sanitized on write in publish()
Structure section content is stored as JSON and served raw to a headless API + rehydrated into a Quill editor via innerHTML, so it must be sanitized. StructureRepository::publish() runs SectionContentSanitizer over every `textarea` (rich-text) field (allow-list via App\Modules\Base\Support\HtmlSanitizer = ezyang/htmlpurifier: p,br,strong,b,em,i,u,h2,h3,ol,ul,li,a[href|title]; schemes http/https/mailto). Plain text/url fields are deliberately NOT sanitized (avoid mangling). ANY new write path to structures.content MUST go through publish() or re-apply the sanitizer, or it reintroduces stored XSS. API JSON additionally hex-escapes < > & ' via SetJsonEncodingOptions. Tests: tests/Feature/Structure/SectionContentSanitizationTest.php.
