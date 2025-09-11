<?php
// filepath: d:\xampp\htdocs\my_projects\mvclixo\src\App\Features\Stores\Profile\Views\index.php
declare(strict_types=1);
/**
 * @var array $data
 * @var string $title
 */

// Store info - would come from controller
$store = $data['store'] ?? null;
$storeName = $store ? $store->getName() : 'Your Store';
$storeDescription = $store ? $store->getDescription() : 'Your store description goes here';
$storeSlug = $store ? $store->getSlug() : 'your-store';
$storeStatus = $store ? $store->getStatus() : 'active';
$storeLogo = BASE_URL . '/assets/img/default-store-logo.png';
$storeCreatedAt = 'N/A';

// Configuration sections
$configSections = $data['configSections'] ?? [
    [
        'title' => 'Basic Information',
        'description' => 'Update your store name, description, and contact details',
        'icon' => 'fa-store',
        'url' => BASE_URL . '/account/stores/profile/edit',
        'badge' => null
    ],
    [
        'title' => 'Branding',
        'description' => 'Customize your store logo, colors, and appearance',
        'icon' => 'fa-paint-brush',
        'url' => BASE_URL . '/account/stores/profile/branding',
        'badge' => null
    ],
    [
        'title' => 'Payment Methods',
        'description' => 'Configure how your store accepts payments',
        'icon' => 'fa-credit-card',
        'url' => BASE_URL . '/account/stores/profile/payments',
        'badge' => null
    ],
    [
        'title' => 'Shipping Options',
        'description' => 'Setup shipping zones, rates, and delivery methods',
        'icon' => 'fa-truck',
        'url' => BASE_URL . '/account/stores/profile/shipping',
        'badge' => null
    ],
    [
        'title' => 'Store Policies',
        'description' => 'Manage refund, privacy, and terms of service policies',
        'icon' => 'fa-file-contract',
        'url' => BASE_URL . '/account/stores/profile/policies',
        'badge' => null
    ],
    [
        'title' => 'Team Management',
        'description' => 'Invite and manage staff accounts for your store',
        'icon' => 'fa-users',
        'url' => BASE_URL . '/account/stores/profile/team',
        'badge' => $data['teamMemberCount'] ?? 0
    ],
    [
        'title' => 'Social Media',
        'description' => 'Connect your store to social media platforms',
        'icon' => 'fa-share-alt',
        'url' => BASE_URL . '/account/stores/profile/social',
        'badge' => null
    ],
    [
        'title' => 'Notifications',
        'description' => 'Configure email and alert preferences',
        'icon' => 'fa-bell',
        'url' => BASE_URL . '/account/stores/profile/notifications',
        'badge' => null
    ],
];
?>

<div class="profile-header">
    <h1>Store Profile</h1>
    <div class="actions">
        <a href="/<?= htmlspecialchars($storeSlug) ?>" class="btn btn-outline-primary me-2" target="_blank">
            <i class="fas fa-external-link-alt"></i> View Store
        </a>
        <a href="<?= BASE_URL ?>/account/stores/profile/edit" class="btn btn-primary">
            <i class="fas fa-edit"></i> Edit Profile
        </a>
    </div>
</div>

<!-- Store Summary -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2 text-center">
                        <img src="<?= htmlspecialchars($storeLogo) ?>" alt="<?= htmlspecialchars($storeName) ?> Logo" class="store-logo img-fluid mb-3">
                    </div>
                    <div class="col-md-10">
                        <h2 class="store-name"><?= htmlspecialchars($storeName) ?></h2>
                        <p class="store-description text-muted mb-3"><?= htmlspecialchars($storeDescription) ?></p>

                        <div class="store-details">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="detail-item">
                                        <span class="detail-label">Store URL</span>
                                        <span class="detail-value">
                                            <a href="/<?= htmlspecialchars($storeSlug) ?>" target="_blank">
                                                <?= $_SERVER['HTTP_HOST'] ?>/<?= htmlspecialchars($storeSlug) ?>
                                            </a>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="detail-item">
                                        <span class="detail-label">Status</span>
                                        <span class="detail-value">
                                            <span class="badge bg-<?= $storeStatus === 'active' ? 'success' : 'warning' ?>">
                                                <?= ucfirst($storeStatus) ?>
                                            </span>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="detail-item">
                                        <span class="detail-label">Created</span>
                                        <span class="detail-value"><?= $storeCreatedAt ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Configuration Sections -->
<div class="row mb-4">
    <div class="col-12">
        <h2 class="section-title">Store Configuration</h2>
    </div>
</div>

<div class="row config-sections">
    <?php foreach ($configSections as $section): ?>
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="section-icon me-3">
                        <i class="fas <?= $section['icon'] ?>"></i>
                    </div>
                    <h3 class="section-name mb-0"><?= htmlspecialchars($section['title']) ?></h3>
                    <?php if (isset($section['badge']) && $section['badge']): ?>
                        <span class="badge bg-primary ms-2"><?= $section['badge'] ?></span>
                    <?php endif; ?>
                </div>
                <p class="section-description"><?= htmlspecialchars($section['description']) ?></p>
                <a href="<?= $section['url'] ?>" class="btn btn-outline-primary stretched-link">
                    Manage
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Store Health -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">Store Health</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h4 class="mb-3">Configuration Checklist</h4>
                        <ul class="checklist">
                            <li class="complete">
                                <i class="fas fa-check-circle text-success"></i>
                                <span>Basic store information completed</span>
                            </li>
                            <li class="complete">
                                <i class="fas fa-check-circle text-success"></i>
                                <span>Store logo uploaded</span>
                            </li>
                            <li class="incomplete">
                                <i class="fas fa-exclamation-circle text-warning"></i>
                                <span>Payment methods not configured</span>
                                <a href="<?= BASE_URL ?>/account/stores/profile/payments" class="ms-2">Configure</a>
                            </li>
                            <li class="incomplete">
                                <i class="fas fa-exclamation-circle text-warning"></i>
                                <span>Shipping options not set up</span>
                                <a href="<?= BASE_URL ?>/account/stores/profile/shipping" class="ms-2">Configure</a>
                            </li>
                            <li class="incomplete">
                                <i class="fas fa-exclamation-circle text-warning"></i>
                                <span>Store policies missing</span>
                                <a href="<?= BASE_URL ?>/account/stores/profile/policies" class="ms-2">Configure</a>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <div class="health-score">
                            <div class="score-circle">
                                <span class="score">60%</span>
                            </div>
                            <h4 class="mt-3">Store Health Score</h4>
                            <p class="text-muted">Complete the checklist items to improve your store's health score.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.profile-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.store-logo {
    max-width: 100%;
    height: auto;
    max-height: 120px;
    border-radius: 8px;
}

.store-name {
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.store-details {
    margin-top: 1rem;
}

.detail-item {
    margin-bottom: 0.5rem;
}

.detail-label {
    display: block;
    font-size: 0.875rem;
    color: #6c757d;
}

.detail-value {
    font-weight: 500;
}

.section-title {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #dee2e6;
}

.section-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background-color: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    color: #495057;
}

.section-name {
    font-size: 1.25rem;
    font-weight: 500;
}

.section-description {
    color: #6c757d;
    margin-bottom: 1.5rem;
    height: 48px;
    overflow: hidden;
}

.config-sections .card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.config-sections .card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 15px rgba(0,0,0,0.1);
}

.checklist {
    list-style: none;
    padding: 0;
}

.checklist li {
    padding: 0.75rem 0;
    border-bottom: 1px solid #f1f1f1;
    display: flex;
    align-items: center;
}

.checklist li:last-child {
    border-bottom: none;
}

.checklist li i {
    margin-right: 10px;
    font-size: 1.125rem;
}

.health-score {
    text-align: center;
    padding: 1rem;
}

.score-circle {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: conic-gradient(#28a745 60%, #e9ecef 0);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    position: relative;
}

.score-circle::before {
    content: '';
    position: absolute;
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: white;
}

.score {
    position: relative;
    z-index: 1;
    font-size: 1.75rem;
    font-weight: 700;
    color: #28a745;
}
</style>