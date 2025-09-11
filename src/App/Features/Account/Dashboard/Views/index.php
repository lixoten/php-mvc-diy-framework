<?php

declare(strict_types=1);

/**
 * @var array $data
 * @var string $title - Page title
 */
?>

<h1><?= $title ?></h1>

<div class="card mb-4">
    <div class="card-body">
        <p class="lead">Welcome to your Dashboard!</p>
        <p>Your central hub for all account features.</p>
    </div>
</div>

<div class="row">
    <!-- Profile -->
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">My Profile</h5>
                <p class="card-text">View and edit your personal information.</p>
                <a href="<?= BASE_URL ?>/account/profile" class="btn btn-primary">Go to Profile</a>
            </div>
        </div>
    </div>

    <!-- Notes -->
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">My Notes</h5>
                <p class="card-text">Manage your personal notes and reminders.</p>
                <a href="<?= BASE_URL ?>/account/mynotes" class="btn btn-primary">Go to Notes</a>
            </div>
        </div>
    </div>

    <!-- Settings -->
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Settings</h5>
                <p class="card-text">Configure your account preferences.</p>
                <a href="<?= BASE_URL ?>/account/settings" class="btn btn-primary">Go to Settings</a>
            </div>
        </div>
    </div>
</div>

<!-- Store Creation CTA -->
<div class="card mt-4 border-primary">
    <div class="card-body text-center">
        <h4 class="card-title">Ready to start selling?</h4>
        <p class="card-text">Create your own store and reach customers worldwide.</p>
        <a href="<?= BASE_URL ?>/account/stores/create" class="btn btn-lg btn-primary">
            Create Your Store
        </a>
    </div>
</div>