<?php

declare(strict_types=1);

namespace Core\Middleware;

use App\Services\GeoLocationService;
use Core\Session\SessionManagerInterface;
use Core\Exceptions\ServiceException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware to detect and cache geolocation in session.
 */
class GeoLocationMiddleware implements MiddlewareInterface
{
    private GeoLocationService $geoLocationService;
    private SessionManagerInterface $sessionManager;

    /**
     * @param GeoLocationService $geoLocationService
     * @param SessionManagerInterface $sessionManager
     */
    public function __construct(
        GeoLocationService $geoLocationService,
        SessionManagerInterface $sessionManager
    ) {
        $this->geoLocationService = $geoLocationService;
        $this->sessionManager = $sessionManager;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $ip = $request->getServerParams()['REMOTE_ADDR'] ?? '';
            //$result = session_destroy();

        // DEVMODE // tODO
        if ($_ENV['APP_ENV'] === 'development') {
            // $ip = '8.8.8.8'; // <-- Replace this line for testing
            // $ip = '8.8.4.4'; // <-- Replace this line for testing
            $ip = '23.240.55.34'; // Los Angeles, CA (for testing)

            // DEVMODE: Use override if set in environment (no query params)
            $geoOverride = $_ENV['GEO_LOCATION_OVERRIDE'] ?? null;
            if ($geoOverride) {
                $location = [
                    'countryCode' => $geoOverride,
                    // Add other fake location data as needed for your app
                ];
                $this->sessionManager->set('geo_location', $location);
                $this->sessionManager->set('geo_location_ip', $ip);
                $this->sessionManager->set('user_region', $geoOverride);
                $request = $request->withAttribute('geo_location', $location);
                return $handler->handle($request);
            }
        }

        //$session = $this->sessionManager->getSession();

        $geoLocation = $this->sessionManager->get('geo_location');
        $geoIp = $this->sessionManager->get('geo_location_ip');

        if ($geoLocation === null || $geoIp === null || $geoIp !== $ip) {
            try {
                $location = $this->geoLocationService->getLocationFromIp($ip);
                $this->sessionManager->set('geo_location', $location);
                $this->sessionManager->set('geo_location_ip', $ip);

                // Set user_region from countryCode if available
                if (isset($location['countryCode'])) {
                    $this->sessionManager->set('user_region', $location['countryCode']);
                }
            } catch (ServiceException $e) {
                $location = null;
            }
        } else {
            $location = $geoLocation;
            // Also ensure user_region is set if not already
            if (is_array($location) && isset($location['countryCode']) && !$this->sessionManager->get('user_region')) {
                $this->sessionManager->set('user_region', $location['countryCode']);
            }
        }

        $request = $request->withAttribute('geo_location', $location);

        return $handler->handle($request);
    }
}
