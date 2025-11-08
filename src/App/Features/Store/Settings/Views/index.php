<?php
// filepath: d:\xampp\htdocs\my_projects\mvclixo\src\App\Features\Store\Settings\Views\index.php
declare(strict_types=1);
/**
 * @var array $data
 * @var string $title
 */

// Store info - would come from controller
$store = $data['store'] ?? null;
$storeName = $store ? $store->getName() : 'Your Store';

// Configuration completion status (would come from controller)
$configStatus = $data['configStatus'] ?? [
    'payments' => [
        'complete' => false,
        'count' => 0,
        'total' => 3
    ],
    'shipping' => [
        'complete' => false,
        'count' => 1,
        'total' => 2
    ],
    'tax' => [
        'complete' => true,
        'count' => 1,
        'total' => 1
    ],
    'notifications' => [
        'complete' => false,
        'count' => 2,
        'total' => 4
    ]
];

// Settings categories
$settingsCategories = [
    [
        'id' => 'payments',
        'title' => 'Payment Methods',
        'description' => 'Configure how your store accepts payments',
        'icon' => 'fa-credit-card',
        'url' => BASE_URL . '/store/settings/payments',
        'status' => $configStatus['payments']
    ],
    [
        'id' => 'shipping',
        'title' => 'Shipping Options',
        'description' => 'Set up shipping zones, rates, and delivery methods',
        'icon' => 'fa-truck',
        'url' => BASE_URL . '/store/settings/shipping',
        'status' => $configStatus['shipping']
    ],
    [
        'id' => 'tax',
        'title' => 'Tax Settings',
        'description' => 'Configure tax rates and tax collection rules',
        'icon' => 'fa-file-invoice-dollar',
        'url' => BASE_URL . '/store/settings/tax',
        'status' => $configStatus['tax']
    ],
    [
        'id' => 'notifications',
        'title' => 'Notifications',
        'description' => 'Set up email templates and notification preferences',
        'icon' => 'fa-bell',
        'url' => BASE_URL . '/store/settings/notifications',
        'status' => $configStatus['notifications']
    ]
];
?>

<div class="settings-header">
    <h1>Store Settings</h1>
    <p class="text-muted">Configure how your store operates</p>
</div>

<!-- Settings Complete Status -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="status-chart me-4">
                        <?php
                        // Calculate overall completion percentage
                        $totalItems = 0;
                        $completedItems = 0;
                        foreach ($configStatus as $status) {
                            $totalItems += $status['total'];
                            $completedItems += $status['count'];
                        }
                        $completionPercentage = $totalItems > 0 ? round(($completedItems / $totalItems) * 100) : 0;
                        ?>
                        <div class="progress-circle" style="--percentage: <?= $completionPercentage ?>;">
                            <div class="progress-circle-inner">
                                <span class="progress-percentage"><?= $completionPercentage ?>%</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h3 class="mb-1">Settings Configuration</h3>
                        <p class="mb-0 text-muted">You've completed <?= $completedItems ?> of <?= $totalItems ?> configuration items</p>
                        <?php if ($completionPercentage < 100): ?>
                            <div class="mt-2">
                                <span class="badge bg-warning text-dark">Action Needed</span>
                                <span class="ms-2">Complete your store settings to ensure smooth operation</span>
                            </div>
                        <?php else: ?>
                            <div class="mt-2">
                                <span class="badge bg-success">Complete</span>
                                <span class="ms-2">All configuration items completed</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Settings Categories -->
<div class="row settings-categories">
    <?php foreach ($settingsCategories as $category): ?>
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <div class="d-flex align-items-center">
                        <div class="category-icon me-3">
                            <i class="fas <?= $category['icon'] ?>"></i>
                        </div>
                        <h3 class="category-title mb-0"><?= htmlspecialchars($category['title']) ?></h3>
                    </div>
                    <div class="completion-badge">
                        <?php if ($category['status']['complete']): ?>
                            <span class="badge bg-success">Complete</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark">
                                <?= $category['status']['count'] ?>/<?= $category['status']['total'] ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <p class="category-description mb-4"><?= htmlspecialchars($category['description']) ?></p>

                <div class="progress mb-3" style="height: 8px;">
                    <?php $percent = $category['status']['total'] > 0 ?
                        ($category['status']['count'] / $category['status']['total']) * 100 : 0; ?>
                    <div class="progress-bar bg-<?= $category['status']['complete'] ? 'success' : 'warning' ?>"
                        role="progressbar" style="width: <?= $percent ?>%;"
                        aria-valuenow="<?= $percent ?>" aria-valuemin="0" aria-valuemax="100">
                    </div>
                </div>

                <div class="text-end">
                    <a href="<?= $category['url'] ?>" class="btn btn-primary">
                        <?= $category['status']['complete'] ? 'Manage' : 'Configure' ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Advanced Settings -->
<div class="row mt-3 mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">Advanced Settings</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="advanced-setting-item">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-database me-2 text-muted"></i>
                                <h5 class="mb-0">Data Export</h5>
                            </div>
                            <p class="text-muted">Export all your store data as CSV or JSON</p>
                            <a href="<?= BASE_URL ?>/account/store/settings/export" class="btn btn-sm btn-outline-secondary">Export Data</a>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="advanced-setting-item">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-plug me-2 text-muted"></i>
                                <h5 class="mb-0">Integrations</h5>
                            </div>
                            <p class="text-muted">Connect with third-party services and tools</p>
                            <a href="<?= BASE_URL ?>/account/store/settings/integrations" class="btn btn-sm btn-outline-secondary">Manage Integrations</a>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="advanced-setting-item">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-shield-alt me-2 text-muted"></i>
                                <h5 class="mb-0">Security</h5>
                            </div>
                            <p class="text-muted">Configure store security settings</p>
                            <a href="<?= BASE_URL ?>/account/store/settings/security" class="btn btn-sm btn-outline-secondary">Security Settings</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.settings-header {
    margin-bottom: 1.5rem;
}

.category-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background-color: rgba(13, 110, 253, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    color: #0d6efd;
}

.category-title {
    font-size: 1.25rem;
    font-weight: 500;
}

.category-description {
    color: #6c757d;
}

.settings-categories .card {
    transition: transform 0.2s, box-shadow 0.2s;
    border-left: 4px solid transparent;
}

.settings-categories .card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 15px rgba(0,0,0,0.1);
}

/* Color-code cards by completion status */
.settings-categories .card:nth-child(1) {
    border-left-color: <?= $configStatus['payments']['complete'] ? '#198754' : '#ffc107' ?>;
}

.settings-categories .card:nth-child(2) {
    border-left-color: <?= $configStatus['shipping']['complete'] ? '#198754' : '#ffc107' ?>;
}

.settings-categories .card:nth-child(3) {
    border-left-color: <?= $configStatus['tax']['complete'] ? '#198754' : '#ffc107' ?>;
}

.settings-categories .card:nth-child(4) {
    border-left-color: <?= $configStatus['notifications']['complete'] ? '#198754' : '#ffc107' ?>;
}

.advanced-setting-item {
    padding: 1rem;
    height: 100%;
    border-radius: 0.5rem;
    background-color: #f8f9fa;
    transition: background-color 0.2s;
}

.advanced-setting-item:hover {
    background-color: #e9ecef;
}

.status-chart {
    position: relative;
    width: 80px;
    height: 80px;
}

.progress-circle {
    --size: 80px;
    --border: 8px;
    width: var(--size);
    height: var(--size);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: conic-gradient(
        #198754 calc(var(--percentage) * 1%),
        #e9ecef calc(var(--percentage) * 1%)
    );
    position: relative;
}

.progress-circle-inner {
    width: calc(var(--size) - 2 * var(--border));
    height: calc(var(--size) - 2 * var(--border));
    background: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.progress-percentage {
    font-size: 1.25rem;
    font-weight: 600;
    color: #198754;
}
</style>