<?php

declare(strict_types=1);

/**
 * @var array $data
 * @var string $title
 */

// Mock data for the example - in reality this would come from your controller
$stats = [
    'orders' => $data['stats']['orders'] ?? 12,
    'products' => $data['stats']['products'] ?? 45,
    'posts' => $data['stats']['posts'] ?? 8,
    'revenue' => $data['stats']['revenue'] ?? '$1,250.00'
];
$recentOrders = $data['recentOrders'] ?? [];
?>


<div class="dashboard-header">
    <h1><?= htmlspecialchars($title) ?></h1>

    <a href="/sluggggggggggggggg" class="btn btn-outline-primary" target="_blank">
        <i class="fas fa-external-link-alt"></i> View Store
    </a>
</div>

<!-- Quick Stats -->
<div class="row stats-cards mb-4">
    <div class="col-md-3 mb-3">
        <div class="card bg-primary text-white h-100">
            <div class="card-body d-flex align-items-center">
                <i class="fas fa-shopping-cart fa-2x me-3"></i>
                <div>
                    <h5 class="card-title mb-0"><?= $stats['orders'] ?></h5>
                    <div class="card-text">Orders</div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 text-end">
                <a href="<?= BASE_URL ?>/store/orders" class="text-white">View all <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card bg-success text-white h-100">
            <div class="card-body d-flex align-items-center">
                <i class="fas fa-box fa-2x me-3"></i>
                <div>
                    <h5 class="card-title mb-0"><?= $stats['products'] ?></h5>
                    <div class="card-text">Products</div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 text-end">
                <a href="<?= BASE_URL ?>/store/products" class="text-white">View all <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card bg-info text-white h-100">
            <div class="card-body d-flex align-items-center">
                <i class="fas fa-newspaper fa-2x me-3"></i>
                <div>
                    <h5 class="card-title mb-0"><?= $stats['posts'] ?></h5>
                    <div class="card-text">Posts</div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 text-end">
                <a href="<?= BASE_URL ?>/store/posts" class="text-white">View all <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card bg-warning text-white h-100">
            <div class="card-body d-flex align-items-center">
                <i class="fas fa-dollar-sign fa-2x me-3"></i>
                <div>
                    <h5 class="card-title mb-0"><?= $stats['revenue'] ?></h5>
                    <div class="card-text">Revenue</div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 text-end">
                <a href="<?= BASE_URL ?>/store/orders" class="text-white">Details <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions Panel -->
<div class="row mb-4">
    <div class="col">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                    <a href="<?= BASE_URL ?>/store/products/create" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> Add Product
                    </a>
                    <a href="<?= BASE_URL ?>/store/posts/create" class="btn btn-info text-white">
                        <i class="fas fa-plus-circle"></i> Add Post
                    </a>
                    <a href="<?= BASE_URL ?>/store/orders?status=new" class="btn btn-success">
                        <i class="fas fa-clipboard-check"></i> Process Orders
                    </a>
                    <a href="<?= BASE_URL ?>/store/albums/create" class="btn btn-secondary">
                        <i class="fas fa-images"></i> Add Album
                    </a>
                    <a href="<?= BASE_URL ?>/store/profile/edit" class="btn btn-outline-dark">
                        <i class="fas fa-cog"></i> Store Settings
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Orders -->
    <div class="col-md-7 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Orders</h5>
                <a href="<?= BASE_URL ?>/store/orders" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentOrders)): ?>
                    <div class="p-4 text-center">
                        <i class="fas fa-inbox fa-3x mb-3 text-muted"></i>
                        <p>No orders yet. When you receive orders, they will appear here.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentOrders as $order): ?>
                                <tr>
                                    <td><a href="<?= BASE_URL ?>/store/orders/view/<?= $order->getId() ?>">#<?= $order->getOrderNumber() ?></a></td>
                                    <td><?= htmlspecialchars($order->getCustomerName()) ?></td>
                                    <td><?= $order->getCreatedAt()->format('M d, Y') ?></td>
                                    <td>$<?= number_format($order->getTotal(), 2) ?></td>
                                    <td><span class="badge bg-<?= $order->getStatus() === 'completed' ? 'success' : ($order->getStatus() === 'processing' ? 'warning' : 'primary') ?>"><?= ucfirst($order->getStatus()) ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Store Activity -->
    <div class="col-md-5 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">Recent Activity</h5>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex align-items-center">
                        <span class="activity-icon bg-success text-white me-3">
                            <i class="fas fa-shopping-cart"></i>
                        </span>
                        <div>
                            <div class="fw-bold">New order received</div>
                            <small class="text-muted">2 hours ago</small>
                        </div>
                    </li>
                    <li class="list-group-item d-flex align-items-center">
                        <span class="activity-icon bg-info text-white me-3">
                            <i class="fas fa-user"></i>
                        </span>
                        <div>
                            <div class="fw-bold">New customer registered</div>
                            <small class="text-muted">Yesterday</small>
                        </div>
                    </li>
                    <li class="list-group-item d-flex align-items-center">
                        <span class="activity-icon bg-warning text-white me-3">
                            <i class="fas fa-star"></i>
                        </span>
                        <div>
                            <div class="fw-bold">New product review</div>
                            <small class="text-muted">2 days ago</small>
                        </div>
                    </li>
                    <li class="list-group-item d-flex align-items-center">
                        <span class="activity-icon bg-primary text-white me-3">
                            <i class="fas fa-box"></i>
                        </span>
                        <div>
                            <div class="fw-bold">Product inventory updated</div>
                            <small class="text-muted">3 days ago</small>
                        </div>
                    </li>
                    <li class="list-group-item d-flex align-items-center">
                        <span class="activity-icon bg-secondary text-white me-3">
                            <i class="fas fa-newspaper"></i>
                        </span>
                        <div>
                            <div class="fw-bold">New blog post published</div>
                            <small class="text-muted">5 days ago</small>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Featured Modules -->
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Store Management</h5>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="feature-card">
                            <div class="icon-wrapper bg-light">
                                <i class="fas fa-box fa-2x text-primary"></i>
                            </div>
                            <h5>Products</h5>
                            <p>Manage your product catalog, pricing, and inventory.</p>
                            <a href="<?= BASE_URL ?>/store/products" class="btn btn-sm btn-outline-primary">Manage Products</a>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="feature-card">
                            <div class="icon-wrapper bg-light">
                                <i class="fas fa-newspaper fa-2x text-info"></i>
                            </div>
                            <h5>Content</h5>
                            <p>Create blog posts, galleries, and marketing content.</p>
                            <a href="<?= BASE_URL ?>/store/posts" class="btn btn-sm btn-outline-primary">Manage Content</a>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="feature-card">
                            <div class="icon-wrapper bg-light">
                                <i class="fas fa-cog fa-2x text-secondary"></i>
                            </div>
                            <h5>Settings</h5>
                            <p>Configure your store's profile, payments, and shipping.</p>
                            <a href="<?= BASE_URL ?>/store/profile" class="btn btn-sm btn-outline-primary">Store Settings</a>
                        </div>
                    </div>


                    <!-- In the Feature Cards section -->
                    <div class="col-md-4">
                        <div class="feature-card">
                            <div class="icon-wrapper bg-light">
                                <i class="fas fa-images fa-2x text-secondary"></i>
                            </div>
                            <h5>Albums</h5>
                            <p>Manage photo collections and image galleries.</p>
                            <a href="<?= BASE_URL ?>/store/albums" class="btn btn-sm btn-outline-primary">Manage Albums</a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.stats-cards .card {
    transition: transform 0.2s;
}

.stats-cards .card:hover {
    transform: translateY(-5px);
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.feature-card {
    padding: 1.5rem;
    border-radius: 0.5rem;
    background: #f8f9fa;
    height: 100%;
    transition: box-shadow 0.3s, transform 0.3s;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.feature-card:hover {
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    transform: translateY(-5px);
}

.icon-wrapper {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
}

.feature-card h5 {
    margin-bottom: 1rem;
}

.feature-card p {
    margin-bottom: 1.5rem;
    flex-grow: 1;
}
</style>