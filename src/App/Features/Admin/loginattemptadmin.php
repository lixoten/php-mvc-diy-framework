`<?php $this->layout('Admin/layout'); ?>
// TODO, this is a Draft id-1234

<div class="container">
    <h1>Failed Login Attempts</h1>

    <div class="row mb-4">
        <div class="col-md-8">
            <form class="row g-3">
                <div class="col-md-4">
                    <input type="text"
                           class="form-control"
                           name="username"
                           placeholder="Filter by username"
                           value="<?= htmlspecialchars($username ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <input type="text" class="form-control" name="ip"
                           placeholder="Filter by IP address" value="<?= htmlspecialchars($ip ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="/admin/login-attempts" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
        <div class="col-md-4 text-end">
            <a href="/admin/login-attempts/cleanup" class="btn btn-warning">
                <i class="fas fa-broom"></i> Clean Expired Attempts
            </a>
        </div>
    </div>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Username/Email</th>
                <th>IP Address</th>
                <th>Attempted At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($attempts as $attempt) : ?>
            <tr>
                <td><?= htmlspecialchars($attempt->getUsernameOrEmail()) ?></td>
                <td><?= htmlspecialchars($attempt->getIpAddress()) ?></td>
                <td><?= htmlspecialchars($attempt->getAttemptedAt()) ?></td>
                <td>
                    <a href="/admin/login-attempts/user/<?= urlencode($attempt->getUsernameOrEmail()) ?>"
                       class="btn btn-sm btn-info">View</a>
                    <a href="/admin/login-attempts/clear/<?= urlencode($attempt->getUsernameOrEmail()) ?>"
                       class="btn btn-sm btn-danger"
                       onclick="return confirm('Are you sure you want to clear all attempts for this user?')">Clear</a>
                </td>
            </tr>
            <?php endforeach; ?>

            <?php if (empty($attempts)) : ?>
            <tr>
                <td colspan="4" class="text-center">No login attempts found.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <?php if ($totalPages > 1) : ?>
    <nav aria-label="Page navigation">
        <ul class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                <a class="page-link"
                   href="?page=<?= $i ?>&username=<?= urlencode($username ?? '') ?>&ip=<?= urlencode($ip ?? '') ?>">
                    <?= $i ?>
                </a>
            </li>
            <?php endfor; ?>
        </ul>
    </nav>
    <?php endif; ?>
</div>