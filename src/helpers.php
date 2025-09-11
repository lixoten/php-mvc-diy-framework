<?php
// filepath: d:\xampp\htdocs\my_projects\mvclixo\src\helpers.php

use Core\Services\ConfigService;
use DI\Container;

if (!function_exists('app')) {
    /**
     * Get the application container or resolve a service
     *
     * @param string|null $id Service ID to resolve
     * @return mixed The container or resolved service
     */
    function app(string $id = null)
    {
        //return true;
        static $container = null;
        static $appInstance = null;

        if ($container === null) {
            // This assumes the container is stored in a global variable in your bootstrap process
            global $container;

            if (!$container) {
                throw new RuntimeException('Container not initialized');
            }

            // Create application proxy object with helper methods
            $appInstance = new class($container) {
                private $container;

                public function __construct($container) {
                    $this->container = $container;
                }

                public function __call($name, $arguments) {
                    if (method_exists($this, $name)) {
                        return call_user_func_array([$this, $name], $arguments);
                    }
                    throw new \BadMethodCallException("Method $name does not exist");
                }

                public function get($id) {
                    return $this->container->get($id);
                }

                public function isDebug() {
                    $configService = $this->container->get('config');
                    $debug = $configService->get('app.debug', false);
                    return $debug;
                }
            };
        }

        if ($id === null) {
            return $appInstance;
        }

        return $container->get($id);
    }
}

if (!function_exists('config')) {
    /**
     * Get a configuration value
     *
     * @param string $key Config key
     * @param mixed $default Default value if key doesn't exist
     * @return mixed Configuration value
     */
    function config(string $key, $default = null)
    {
        /** @var \Core\Services\ConfigService $config */
        $config = app('Core\Services\ConfigService');
        return $config->get($key, $default);
    }
}
