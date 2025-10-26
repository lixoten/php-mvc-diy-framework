<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$builder = new \DI\ContainerBuilder();
$container = $builder->build();
echo "container built\n";