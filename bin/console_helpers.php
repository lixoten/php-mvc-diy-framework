<?php

declare(strict_types=1);

/**
 * Console helper functions for MVC LIXO CLI.
 *
 * This file contains utility functions used by bin/console.php for
 * formatting help output and discovering seeder files.
 *
 * Reason for this file:
 *   This file was created to separate function and symbol declarations from executable logic,
 *   following PSR-1 and SOLID best practices. According to these standards, a PHP file should
 *   either declare new symbols (such as functions, classes, or constants) or execute logic with
 *   side effects (such as running code), but not both. By moving helper functions out of
 *   bin/console.php and into this file, we ensure that bin/console.php contains only executable
 *   logic, while this file contains only reusable function declarations with no side effects.
 *
 *
 * @package   MVC LIXO Framework
 * @author    Your Name <your@email.com>
 * @copyright Copyright (c) 2025
 */

/**
 * Builds a formatted help title for console commands.
 *
 * @param string $command
 * @param string $section
 * @return string
 */
function buildHelpTitle(string $command, string $section): string
{
    $menu = '';
    if ($command === 'all' || $section === 'all') {
        $menu .= "════════════════ MVC LIXO Console Commands for ({$section})═══════════════\n\n";
    }
    // ...existing code...
    return $menu;
}

/**
 * Get all seeder files in the given directory.
 *
 * @param string $seederPath
 * @return array<int, string>
 */
function getSeederFiles(string $seederPath): array
{
    $files = scandir($seederPath);
    $seeders = [];
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            $filename = pathinfo($file, PATHINFO_FILENAME);
            $className = preg_replace('/^\d+_/', '', $filename);
            $seeders[] = $className;
        }
    }
    return $seeders;
}
