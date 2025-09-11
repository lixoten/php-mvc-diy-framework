<?php

declare(strict_types=1);

namespace App\Features\Admin\Dashboard;

use App\Enums\Url;
use Core\Controller;
use App\Helpers\DebugRt;
use App\Services\Interfaces\FlashMessageServiceInterface;
use Core\Context\CurrentContext;
use Core\Http\HttpFactory;
use Core\View;
use Psr\Http\Message\ResponseInterface;
use Psr\Container\ContainerInterface;

/**
 * Home controller
 *
 */
class DashboardController extends Controller
{
    public function __construct(
        array $route_params,
        FlashMessageServiceInterface $flash,
        View $view,
        HttpFactory $httpFactory,
        ContainerInterface $container,
        CurrentContext $scrap,
    ) {
        parent::__construct(
            $route_params,
            $flash,
            $view,
            $httpFactory,
            $container,
            $scrap
        );
    }

    /**
     * Show the index page
     *
     * @return ResponseInterface
     */
    public function indexAction(): ResponseInterface
    {

        // In a controller action you want to measure:
        $startTime = microtime(true);
        // Expensive operation
        sleep(5);
        $endTime = microtime(true);
        error_log('Operation took: ' . (($endTime - $startTime) * 1000) . 'ms');

        return $this->view(Url::ADMIN_DASHBOARD->view(), [
            'title' => 'Admin Dashboard Index Action',
            'actionLinks' => $this->getActionLinks(
                Url::ADMIN_DASHBOARD,
                Url::ADMIN_USERS
            )
        ]);
    }



    public function getObjectSummary($obj) {
        if (!is_object($obj)) {
            return "Input is not an object.";
        }

        $summary = [
            'totalVariables' => 0,
            'types' => [
                'objects' => 0,
                'arrays' => 0,
                'strings' => 0,
                'integers' => 0,
                'booleans' => 0,
                'nulls' => 0,
                'other' => 0
            ]
        ];

        $vars = (array) $obj;
        $summary['totalVariables'] = count($vars);

        foreach ($vars as $key => $value) {
            // Clean up the key name from private/protected property prefixes
            $cleanKey = str_replace(array("\0", '*'), '', $key);
            $cleanKey = ltrim($cleanKey, '::');
            $cleanKey = str_replace($obj::class, '', $cleanKey);

            $type = gettype($value);
            switch ($type) {
                case 'object':
                    $summary['types']['objects']++;
                    break;
                case 'array':
                    $summary['types']['arrays']++;
                    break;
                case 'string':
                    $summary['types']['strings']++;
                    break;
                case 'integer':
                    $summary['types']['integers']++;
                    break;
                case 'boolean':
                    $summary['types']['booleans']++;
                    break;
                case 'NULL':
                    $summary['types']['nulls']++;
                    break;
                default:
                    $summary['types']['other']++;
                    break;
            }
        }

        return $summary;
    }
}
