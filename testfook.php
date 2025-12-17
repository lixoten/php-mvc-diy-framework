<?php

declare(strict_types=1);

phpinfo();


$gdInfo = gd_info();
if (isset($gdInfo['AVIF Support'])) {
    echo "GD is installed and supports AVIF files.\n";
} else {
    echo "GD is installed but does not support AVIF files.\n";
}

if (extension_loaded('gd')) {
    echo "GD extension is loaded.\n";
} else {
    echo "GD extension is not loaded.\n";
}

// // Test GD support for AVIF files
// function testAvifSupport(): void
// {
//     $image = imagecreatefromstring(file_get_contents('path/to/your/avif/image.avif'));
//     if ($image !== false) {
//         echo "AVIF support is enabled.\n";
//     } else {
//         echo "AVIF support is not enabled.\n";
//     }
// }

// testAvifSupport();