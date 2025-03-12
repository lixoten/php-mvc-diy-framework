<?php

namespace Core;

interface RouterInterface
{
    public function add($route, array $controller = []);
    public function dispatch($url);
}
