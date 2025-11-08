<?php

declare(strict_types=1);

/**
 * @var array<string, mixed> $debugBar
 */

?>

<div class="debugbar card mb-1 border-primary" style="max-width: 100%; background: var(--base-light);
                                                                                        box-shadow: var(--nav-shadow);">
    <div class="card-header bg-primary text-white py-2 px-3" style="font-size: 1rem; font-weight: 600;">
        Debug Info
    </div>
    <div class="card-body py-2 px-3" style="font-size: 0.95rem;">
        <div class="row mb-1">
            <div class="col-auto">
                <span class="fw-bold">Role:</span>
                <span><?= htmlspecialchars($debugBar['role'] ?? 'guest') ?></span>
            </div>
            <div class="col-auto">
                <span class="fw-bold">User:</span>
                <span>
                    <?php if (!empty($debugBar['user_id'])) : ?>
                        <?= htmlspecialchars((string)$debugBar['user_id']) ?> -
                                                                         <?= htmlspecialchars($debugBar['username']) ?>
                    <?php else : ?>
                        none
                    <?php endif; ?>
                </span>
            </div>
            <?php if (!empty($debugBar['active_store_id'])) : ?>
            <div class="col-auto">
                <span class="fw-bold">Active Store:</span>
                <span><?= htmlspecialchars((string)$debugBar['active_store_id']) ?> -
                                                          <?= htmlspecialchars($debugBar['active_store_name']) ?></span>
            </div>
            <?php endif; ?>
        </div>
        <div class="row">
            <div class="col-auto">
                <span class="fw-bold">Page:</span>
                <span>
                    <?= htmlspecialchars($debugBar['namespace'] ?? '') ?> -
                    <?= htmlspecialchars($debugBar['controller'] ?? '') ?> -
                    <?= htmlspecialchars($debugBar['action'] ?? '') ?> -
                    <?= htmlspecialchars($debugBar['route_id'] ?? '') ?>
                </span>
            </div>
        </div>
    </div>
</div>