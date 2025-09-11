<?php

declare(strict_types=1);

namespace Core\I18n;

class LabelProvider
{
    protected array $labels;

    public function __construct(array $labels)
    {
        $this->labels = $labels;
    }

    /**
     * Get a label by key, e.g. 'posts.title'
     */
    public function get(string $key): string
    {
        $parts = explode('.', $key);
        $value = $this->labels;
        foreach ($parts as $part) {
            if (isset($value[$part])) {
                $value = $value[$part];
            } else {
                // // Fallback to common labels
                // // $common = include __DIR__ . '/common.php';
                // $common = include dirname(__DIR__, 3) . '/lang/en/common.php';
                // return $common[$part] ?? $key;
                // Fallback to common labels if available
                if (isset($this->labels['common'][$part])) {
                    return $this->labels['common'][$part];
                }
                return $key;
            }
        }
        return is_string($value) ? $value : $key;
    }
}
