<?php

namespace App\Modules\Base\Support;

use HTMLPurifier;
use HTMLPurifier_Config;

/**
 * Server-side HTML allow-list sanitizer (HTMLPurifier) for rich-text content
 * that a client will render as HTML. Only a small, safe set of tags/attributes
 * survives; scripts, event-handler attributes, and dangerous URI schemes
 * (javascript:, data:, vbscript:) are stripped. Sanitize on write so every
 * reader — the dashboard editor and the public API — is safe by construction.
 */
final class HtmlSanitizer
{
    /** Tags/attributes permitted in rich-text content — mirrors the Quill toolbar. */
    private const ALLOWED_HTML = 'p,br,strong,b,em,i,u,h2,h3,ol,ul,li,a[href|title]';

    private ?HTMLPurifier $purifier = null;

    public function sanitize(string $html): string
    {
        return $this->purifier()->purify($html);
    }

    private function purifier(): HTMLPurifier
    {
        if ($this->purifier === null) {
            $config = HTMLPurifier_Config::createDefault();
            $config->set('HTML.Allowed', self::ALLOWED_HTML);
            $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true]);
            // No target=_blank is possible (target is not allowed), so no window.opener risk.
            $config->set('HTML.Nofollow', true);
            // Disable the on-disk definition cache: no filesystem dependency in any environment.
            $config->set('Cache.DefinitionImpl', null);

            $this->purifier = new HTMLPurifier($config);
        }

        return $this->purifier;
    }
}
