<?php

namespace Core;

use App\Helpers\DebugRt;

class FrontController
{
    protected $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
        $this->registerRoutes();
    }

    public function run(string $url)
    {
        // $url = $_GET['url'] ?? '';
        // DebugRt::p($_GET, 0);
        // $url = $_SERVER['QUERY_STRING'] ?? '';
        // DebugRt::p($url);
        $this->router->dispatch($url);
    }

    protected function registerRoutes()
    {
        // Register Home routes
        //$this->router->add('/posts', 'Posts@index');
        $this->router->add("", ["controller" => "Home", "action" => "index"]);
        $this->router->add('{controller}/page/{page:\d+}', ['action' => 'index']);
        //$this->router->add('admin/{controller}/{action}');
        $this->router->add("admin/{controller}/{action}", ["namespace" => "Admin"]);
        $this->router->add("admin/{controller}", ["namespace" => "Admin", "action" => "index"]);
        #Single record with ID
        $this->router->add('{controller}/{action}/{level:\d}{exe:j|n}{pageid:\d\d\d\d}/{returnid:\d\d\d\d}/{id:\d+}');


        $this->router->add('test-logger', ['controller' => 'Home', 'action' => 'testLogger']);
        /**
         * Route for handling key-value parameters
         *
         * This route handles URLs with variable key-value pairs:
         * /controller/action/param/key1/value1/key2/value2/
         *
         * - Only processes complete key-value pairs
         * - Unpaired parameters are ignored
         * - Both keys and values are restricted to word characters (letters, numbers, underscore)
         *
         * Access in controller:
         *   $this->route_params['key1'], $this->route_params['key2'], etc.
         */
        $this->router->add('{controller}/param/{args:[\w+\/\w+\/]*}', ['action' => 'index']);
        $this->router->add('{controller}/{action}/param/{args:[\w+\/\w+\/]*}');

        ## Edit page
        $this->router->add('{controller}/{action}/{id:\d+}');
        $this->router->add('{controller}/{action}/{textid:\w+}');
        $this->router->add('admin/{controller}/{action}/{id:\d+}');

        // Define the default action route (without action)
        $this->router->add('{controller}', ['action' => 'index']);

        $this->router->add("{controller}/{action}");
    }
}

/*
http://localhost/testy/param/ddd/ddddd
http://localhost/testy/test/parm/hhh/hhhh
*/
