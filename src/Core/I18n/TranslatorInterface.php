<?php

declare(strict_types=1);

namespace Core\I18n;

interface TranslatorInterface
{
    /**
     * Get a translation with optional placeholder replacements.
     *
     * @param string $key Translation key (e.g., 'form.hints.minlength')
     * @param string $name Context name (e.g., 'testy_list')
     * @param array<string, mixed> $replacements Placeholder values (e.g., ['min' => 5])
     * @return string
     */
    public function get(string $key, string $name, array $replacements = []): string;
}
