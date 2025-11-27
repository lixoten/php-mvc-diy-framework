<?php

declare(strict_types=1);

namespace Core\I18n;

interface TranslatorInterface
{
    /**
     * Get a translation with optional placeholder replacements.
     *
     * @param string $key Translation key (e.g., 'form.hints.minlength')
     * @param array<string, mixed> $replacements Placeholder values (e.g., ['min' => 5])
     * @param string $pageName Context pageName (e.g., 'testy_list')
     * @return string
     */
    public function get(string $key, array $replacements = [], string $pageName = null): string;
}
