<?php

declare(strict_types=1);

namespace Core\I18n;

class TranslationLoaderService
{
    public function __construct(
        private string $languageDir,
        private string $currentLocale = 'en'
    ) {}

    /**
     * Load all translation files for the current locale.
     *
     * @return array<string, array<string, mixed>>
     */
    public function loadTranslations(): array
    {
        $translations = [];
        $langPath = rtrim($this->languageDir, '/') . '/' . $this->currentLocale . '/';

        if (!is_dir($langPath)) {
            throw new \RuntimeException("Translation directory not found: {$langPath}");
        }

        foreach (glob($langPath . '*_lang.php') as $file) {
            $namespace = basename($file, '_lang.php');
            $translations[$namespace] = require $file;
        }

        return $translations;
    }
}
