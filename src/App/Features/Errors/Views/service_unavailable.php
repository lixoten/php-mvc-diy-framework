<?php

declare(strict_types=1);

ob_clean();

$mainContent = '';

$head = "ERROR";
$title = '503 Service Unavailable';
$h1 = '503 Service Unavailable';
$paragraph = 'Database service temporarily unavailable. Please try again later.';
## mysql 2002

$path = realpath(__DIR__ . '/../base7Error.html');
if ($path && file_exists($path)) {
    //print "<br />aaaaaa:$path";
    require $path;
} else {
    echo "File not found or path is incorrect: " . __DIR__ . '/../base7Error.html';
}
