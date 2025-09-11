<?php
namespace Core\Services;

class FormatterRegistry
{
    protected array $formatters = [];

    public function __construct()
    {
        // Register default formatters
        $this->formatters = [
            'htmlspecialchars' => fn($value) => htmlspecialchars($value ?? ''),
            'truncate30' => fn($value) => mb_strimwidth($value ?? '', 0, 30, '...'),
            'badge_status' => function ($value) {
                $class = ($value === 'Published') ? 'success' : 'warning';
                return '<span class="badge bg-' . $class . '">' . htmlspecialchars($value) . '</span>';
            },
            'date_short' => fn($value) => date('Y-m-d', strtotime($value)),
            // Add more as needed...
        ];
    }

    public function get(string $key): ?callable
    {
        return $this->formatters[$key] ?? null;
    }

    public function register(string $key, callable $formatter): void
    {
        $this->formatters[$key] = $formatter;
    }
}

<?php
// In dependencies.php
'Core\List\FormatterRegistry' => \DI\autowire(\Core\List\FormatterRegistry::class),


<?php
// In your PostsFieldRegistry or column config
return [
    'title' => [
        'label' => 'posts.title',
        'formatter' => 'truncate30', // Reference by key
        // ...
    ],
    'status' => [
        'label' => 'posts.status',
        'formatter' => 'badge_status',
        // ...
    ],
];


<?php
// In your ListBuilder or renderer
$formatterRegistry = $container->get(\Core\List\FormatterRegistry::class);

foreach ($columns as $col => $def) {
    $value = $row[$col];
    $formatter = $def['formatter'] ?? null;

    if ($formatter && is_string($formatter)) {
        $callable = $formatterRegistry->get($formatter);
        $displayValue = $callable ? $callable($value, $row) : $value;
    } elseif (is_callable($formatter)) {
        $displayValue = $formatter($value, $row);
    } else {
        $displayValue = $value;
    }
    // Output $displayValue in the cell
}