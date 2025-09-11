<?php

declare(strict_types=1);

namespace Core\Middleware;

use App\Enums\FlashMessageType;
use App\Services\Interfaces\FlashMessageServiceInterface;
use App\Services\StoreContext;
use Core\Context\CurrentContext;
use Core\Http\ResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware to enforce store context for store-related routes
 */
class StoreContextMiddleware implements MiddlewareInterface
{
    private FlashMessageServiceInterface $flash;
    private StoreContext $storeContext;
    private ResponseFactory $responseFactory;
    private CurrentContext $currentContext;
    private string $noStoreRedirectUrl;

    /**
     * Constructor
     */
    public function __construct(
        FlashMessageServiceInterface $flash,
        StoreContext $storeContext,
        ResponseFactory $responseFactory,
        CurrentContext $currentContext,
        string $noStoreRedirectUrl = '/stores/stores/create'
    ) {
        $this->flash = $flash;
        $this->storeContext = $storeContext;
        $this->responseFactory = $responseFactory;
        $this->currentContext = $currentContext;
        $this->noStoreRedirectUrl = $noStoreRedirectUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        // Check if we're in a store context route
        $path = $request->getUri()->getPath();

        //if (
        //    strpos($path, '/stores/') === 0 &&
        //    strpos($path, '/stores/stores/create') !== 0
        //) {
            // Get current store - this will find or create store context
            $store = $this->storeContext->getCurrentStore();


            // If no store is found, redirect to create store page
            if (!$store) {
                // Add flash message if the flash service is available
                if (isset($this->flash)) {
                    $this->flash->add('Please create a store first', FlashMessageType::Warning);
                }

                // return $this->responseFactory->redirect($this->noStoreRedirectUrl);
                return $this->responseFactory->redirect('/login');
            }


            // scrap99 --------------------------------------------------------------------
            // Store exists, add it to request attributes for controllers
            // $request = $request->withAttribute('store', $store);
            // $request = $request->withAttribute('store_id', $store->getStoreId());
            // $request = $request->withAttribute('store_name', $store->getName());
            $this->currentContext->setStoreObj($store);
            $this->currentContext->setStoreId($store->getStoreId());
            $this->currentContext->setStoreName($store->getName());

            $this->currentContext->setBoo("BBBBBOOOOOOOOOOOOOOOOOOOOOOOOOOOOOO");
            // scrap99 --------------------------------------------------------------------
        //}

        // Process the request with store context
        return $handler->handle($request);
    }
}
