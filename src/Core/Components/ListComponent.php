<?php
declare(strict_types=1);

namespace Core\Components;

/**
 * Component wrapper for lists.
 * Wraps list data and renders it as HTML.
 */
class ListComponent extends AbstractComponent
{
    /**
     * Constructor.
     *
     * @param array<array<string, mixed>> $listData The list data to render (array of rows).
     * @param array<string, mixed> $options Additional component options.
     */
    public function __construct(
        private readonly array $listData,
        private array $options = [],
    ) {
        parent::__construct(
            // Assuming ConfigService and FieldRegistryService are injected elsewhere or via DI
            // For now, we'll assume they are available; in practice, adjust based on DI setup
        );
    }

    /**
     * Renders the list component.
     *
     * @param array<string, mixed> $options Additional rendering options.
     * @return string The rendered HTML string.
     */
    public function render(array $options = []): string
    {
        // Merge component options with passed options
        $mergedOptions = array_merge($this->options, $options);

        // Load component options with fallbacks (e.g., for 'list')
        $componentOptions = $this->loadOptions('list', $mergedOptions['pageName'] ?? null, $mergedOptions['entityName'] ?? null);
        $mergedOptions = array_merge($componentOptions, $mergedOptions);

        // Basic HTML rendering for the list
        $html = '<ul class="list-component">';
        foreach ($this->listData as $item) {
            $html .= '<li>';
            if (is_array($item)) {
                $html .= implode(', ', array_map('htmlspecialchars', $item));
            } else {
                $html .= htmlspecialchars((string)$item);
            }
            $html .= '</li>';
        }
        $html .= '</ul>';

        return $html;
    }
}