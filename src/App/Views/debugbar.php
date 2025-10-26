<?php

declare(strict_types=1);

/**
 * @var array<string, mixed> $debugBar
 */

?>

<div class="debugbar card mb-1 border-primary" stxyle="max-width: 100%; background: var(--base-light); box-shadow: var(--nav-shadow);">
    <!-- <div class="card-header bg-primary text-white py-2 px-3" style="font-size: 1rem; font-weight: 600;">
        Debug Info
    </div> -->
    <div class="card-body py-2 px-3" style="font-size: 0.95rem;">
        <div class="row mb-1">
            <div class="col-auto">
                <span class="fw-bold">Role:</span>
                <span><?= htmlspecialchars($debug['role'] ?? 'guest') ?></span>
            </div>
            <div class="col-auto">
                <span class="fw-bold">User:</span>
                <span>
                    <?php if (!empty($debug['user_id'])): ?>
                        <?= htmlspecialchars($debug['user_id']) ?> - <?= htmlspecialchars($debug['username']) ?>
                    <?php else: ?>
                        none
                    <?php endif; ?>
                </span>
            </div>
            <?php if (!empty($debug['active_store_id'])): ?>
            <div class="col-auto">
                <span class="fw-bold">Active Store:</span>
                <span><?= htmlspecialchars($debug['active_store_id']) ?> - <?= htmlspecialchars($debug['active_store_name']) ?></span>
            </div>
            <?php endif; ?>
        </div>
        <div class="row">
            <div class="col-auto">
                <span class="fw-bold">Page:</span>
                <span>
                    <?= htmlspecialchars($debug['namespace'] ?? '') ?> -
                    <?= htmlspecialchars($debug['controller'] ?? '') ?> -
                    <?= htmlspecialchars($debug['action'] ?? '') ?> -
                    <?= htmlspecialchars($debug['route_id'] ?? '') ?>
                </span>
            </div>
        </div>
    </div>
</div>