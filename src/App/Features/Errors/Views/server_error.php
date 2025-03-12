<?php

declare(strict_types=1);

ob_clean();

$mainContent = '';

$head = "ERROR";
$title = '500 Internal Server Error';
$h1 = '500 Internal Server Error';
$paragraph = 'Something went wrong on our end. Please try again later.';
## An internal server error occurred.

$path = realpath(__DIR__ . '/../base7Error.html');
if ($path && file_exists($path)) {
    //print "<br />aaaaaa:$path";
    require $path;
} else {
    echo "File not found or path is incorrect: " . __DIR__ . '/../base7Error.html';
}
