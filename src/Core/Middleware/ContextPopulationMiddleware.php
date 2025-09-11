<?php

declare(strict_types=1);

namespace Core\Middleware;

use App\Helpers\DebugRt;
use App\Enums\FlashMessageType;
use App\Services\Interfaces\FlashMessageServiceInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Core\Auth\AuthenticationServiceInterface;
use Core\Context\CurrentContext;
use Psr\Container\ContainerInterface;

use function PHPUnit\Framework\isNull;

class ContextPopulationMiddleware implements MiddlewareInterface
{
    private AuthenticationServiceInterface $authService;
    private FlashMessageServiceInterface $flash;
    private CurrentContext $currentContext;
    // private ContainerInterface $container;

    public function __construct(
        AuthenticationServiceInterface $authService,
        FlashMessageServiceInterface $flash,
        CurrentContext $currentContext,
        // ContainerInterface $container
    ) {
        $this->authService = $authService;
        $this->flash = $flash;
        $this->currentContext = $currentContext;
        // $this->container = $container;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // 1. Populate User
        $user = $this->authService->getCurrentUser();
        if ($user) {
            $this->currentContext->setCurrentUser($user);

        }


        // DebugRt::j('1', 'All Request Attributes', $request->getAttributes());

        // 2. Populate from Route Parameters (Requires access to matched route)
        // How you access route params depends on your router/framework integration
        // Assuming they are stored in the container or request attributes
        $routeParams = $request->getAttribute('route_params');
        $pageName = $request->getAttribute('page_name'); // Get individual attribute
        $entityId = $request->getAttribute('id');     // Get individual attribute
        $actionName = $request->getAttribute('action'); // Get individual attribute

        $namespace =  $request->getAttribute('namespace');
        $controller =  $request->getAttribute('controller');
        $route_id =  $request->getAttribute('route_id');

        // Add flash message if the flash service is available
        if (isset($this->flash)) {
            // DangerDanger - Revisit to put Debug or Dev Flag check. maybe log instead of flashmsg
            if (is_null($route_id)) {
                // DebugRt::Boom("Route_id not covered ");
                $this->flash->add('Danger Danger create a store first', FlashMessageType::Error);
            }
        }

        // Set context values (null if attributes don't exist)
        $this->currentContext->setPageKey($pageName);
        $this->currentContext->setEntityId($entityId !== null ? (int)$entityId : null);
        $this->currentContext->setActionName($actionName);

        $this->currentContext->setNamespaceName($namespace);
        $this->currentContext->setControllerName($controller);
        $this->currentContext->setRouteParams($routeParams);
        $this->currentContext->setRouteId($route_id);


        // scrap-99
        // $storeId = $request->getAttribute('store_id');
        // $storeName = $request->getAttribute('store_name');
        // $this->currentContext->setStoreId($storeId);
        // $this->currentContext->setStoreName($storeName);




        // 3. Populate other context if needed (e.g., store IDs)
        // Get the current path
        $path = $request->getUri()->getPath();

        // Determine route type based on URL pattern
        if (strpos($path, '/admin/') === 0) {
            $this->currentContext->setRouteType('admin');
            $this->currentContext->setRouteType('admin');
        } elseif (strpos($path, '/stores/') === 0) {
            $this->currentContext->setRouteType('store');
        } elseif (strpos($path, '/account/') === 0) {
            $this->currentContext->setRouteType('account');
        } else {
            $this->currentContext->setRouteType('public'); // Default for public routes
        }

        // Continue processing the request
        return $handler->handle($request);
    }
}
