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

        ## Edit page
        $this->router->add('{controller}/{action}/{id:\d+}');
        $this->router->add('{controller}/{action}/{textid:\w+}');
        $this->router->add('admin/{controller}/{action}/{id:\d+}');

        // Define the default action route (without action)
        $this->router->add('{controller}', ['action' => 'index']);

        $this->router->add("{controller}/{action}");
    }
}
