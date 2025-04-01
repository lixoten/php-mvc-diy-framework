<?php

declare(strict_types=1);

use Core\Auth\AuthenticationServiceInterface;
use DI\Container;

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get container instance for dependency injection
global $container; // This should be available since we're including this file after container setup

// Get auth service from container
$authService = $container->get(AuthenticationServiceInterface::class);

// Define menu items by section
$publicItems = [
    ['url' => '/', 'label' => 'Home'],
    ['url' => '/about', 'label' => 'About'],
    ['url' => '/posts', 'label' => 'Posts'],
    ['url' => '/test', 'label' => 'Test'],
    ['url' => '/testy', 'label' => 'Testy'],
];

$authItems = [
    ['url' => '/admin/dashboard', 'label' => 'Dashboard'],
    ['url' => '/admin/profile/index', 'label' => 'Profile'],
];

$adminItems = [
    ['url' => '/users', 'label' => 'Users'],
];

$guestItems = [
    ['url' => '/signup/new', 'label' => 'Signup'],
];

// Current path for highlighting active item
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Start menu HTML
echo '<nav><ul>';

// Always show public items
foreach ($publicItems as $item) {
    $isActive = ($currentPath === $item['url']) ? ' class="active"' : '';
    echo "<li{$isActive}><a href=\"{$item['url']}\">{$item['label']}</a></li>";
}

// Show items based on authentication status
if ($authService->isAuthenticated()) {
    // Show auth items
    foreach ($authItems as $item) {
        $isActive = ($currentPath === $item['url']) ? ' class="active"' : '';
        echo "<li{$isActive}><a href=\"{$item['url']}\">{$item['label']}</a></li>";
    }

    // Show admin items if user has admin role
    if ($authService->hasRole('admin')) {
        foreach ($adminItems as $item) {
            $isActive = ($currentPath === $item['url']) ? ' class="active"' : '';
            echo "<li{$isActive}><a href=\"{$item['url']}\">{$item['label']}</a></li>";
        }
    }

    // Add logout with container div
    echo '<div class="last-items-container">';
    echo '<li><a href="/logout">Logout</a></li>';
    echo '</div>';
} else {
    // Show guest-only items
    foreach ($guestItems as $item) {
        $isActive = ($currentPath === $item['url']) ? ' class="active"' : '';
        echo "<li{$isActive}><a href=\"{$item['url']}\">{$item['label']}</a></li>";
    }

    // Add login with container div
    echo '<div class="last-items-container">';
    $loginActive = ($currentPath === '/login') ? ' class="active"' : '';
    echo "<li{$loginActive}><a href=\"/login\">Login</a></li>";
    echo '</div>';
}

echo '</ul></nav>';
