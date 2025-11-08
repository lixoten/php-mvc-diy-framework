<?php

declare(strict_types=1);

use App\Helpers\DebugRt;

/**
 * @var array $data
 */
DebugRt::j('1', '', '111');
?>

<h1><?= $title ?></h1>

<!-- account/store/index.php -->
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= htmlspecialchars($store->getName()) ?> Dashboard</h1>
        <div>
            <a href="/<?= htmlspecialchars($store->getSlug()) ?>" class="btn btn-outline-primary" target="_blank">
                <i class="fas fa-external-link-alt me-2"></i> View Store
            </a>
            <a href="/account/store/edit" class="btn btn-secondary ms-2">
                <i class="fas fa-cog me-2"></i> Settings
            </a>
        </div>
    </div>

    <!-- Statistics cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <h2 class="display-4 text-primary"><?= $productCount ?></h2>
                    <p class="card-text">Products</p>
                    <a href="/account/store/products" class="btn btn-sm btn-outline-primary">Manage Products</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <h2 class="display-4 text-success"><?= $orderCount ?></h2>
                    <p class="card-text">Orders</p>
                    <a href="/account/store/orders" class="btn btn-sm btn-outline-success">View Orders</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <h2 class="display-4 text-info">0</h2>
                    <p class="card-text">Reviews</p>
                    <a href="/account/store/reviews" class="btn btn-sm btn-outline-info">View Reviews</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <h2 class="display-4 text-warning">0</h2>
                    <p class="card-text">Messages</p>
                    <a href="/account/store/messages" class="btn btn-sm btn-outline-warning">View Messages</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick access actions -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="/account/store/products/create" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i> Add Product
                        </a>
                        <a href="/account/store/edit" class="btn btn-outline-secondary">
                            <i class="fas fa-edit me-2"></i> Edit Store
                        </a>
                        <a href="/account/store/orders" class="btn btn-outline-success">
                            <i class="fas fa-shopping-cart me-2"></i> Process Orders
                        </a>
                        <a href="/account/store/promotion" class="btn btn-outline-warning">
                            <i class="fas fa-bullhorn me-2"></i> Create Promotion
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent orders -->
    <div class="row">
        <div class="col-md-7">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Orders</h5>
                    <a href="/account/store/orders" class="btn btn-sm btn-link">View All</a>
                </div>
                <div class="card-body p-0">
                    <?php if (count($recentOrders) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Date</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentOrders as $order): ?>
                                        <tr>
                                            <td><?= $order->getOrderNumber() ?></td>
                                            <td><?= $order->getCreatedAt()->format('M d, Y') ?></td>
                                            <td><?= htmlspecialchars($order->getCustomerName()) ?></td>
                                            <td>$<?= number_format($order->getTotal(), 2) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $order->getStatus() === 'completed' ? 'success' : 'warning' ?>">
                                                    <?= ucfirst($order->getStatus()) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="p-4 text-center">
                            <p class="text-muted mb-0">No orders yet. Your orders will appear here once customers start purchasing.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Store health -->
        <div class="col-md-5">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Store Health</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Store Status</strong>
                                <div class="text-muted">Your store is live and visible to customers</div>
                            </div>
                            <span class="badge bg-success rounded-pill">Active</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Product Catalog</strong>
                                <div class="text-muted">Add more products to attract customers</div>
                            </div>
                            <span class="badge bg-<?= $productCount > 0 ? 'success' : 'warning' ?> rounded-pill">
                                <?= $productCount > 0 ? 'Good' : 'Needs Attention' ?>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Store Description</strong>
                                <div class="text-muted">Tell customers about your business</div>
                            </div>
                            <span class="badge bg-<?= strlen($store->getDescription()) > 50 ? 'success' : 'warning' ?> rounded-pill">
                                <?= strlen($store->getDescription()) > 50 ? 'Good' : 'Needs Attention' ?>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Store Images</strong>
                                <div class="text-muted">Logo and banner make your store look professional</div>
                            </div>
                            <span class="badge bg-<?= ($store->getLogo() && $store->getBanner()) ? 'success' : 'warning' ?> rounded-pill">
                                <?= ($store->getLogo() && $store->getBanner()) ? 'Good' : 'Needs Attention' ?>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
