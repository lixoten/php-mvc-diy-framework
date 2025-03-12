<?php

declare(strict_types=1);

namespace Core;

use App\Enums\FlashMessageType;
use App\Services\Interfaces\FlashMessageServiceInterface;
use InvalidArgumentException;

//use App\Views\Components\Page\MessageBox;
//use App\Views\HtmlTable;
//use App\Helpers\RtClass_ResV2;
//use Exception;
//use http\Url;
//use Twig_Environment;
//use Twig_Loader_Filesystem;
//use Pimple\Container;

/**
 * Base controller
 *
 * PHP version 7.0
 */
//abstract class Controller extends DIP
abstract class Controller
{
    protected string $pageTitle;
    public array $route_params;
    protected FlashMessageServiceInterface $flash;
    protected View $view;

    public function __construct(
        array $route_params,
        FlashMessageServiceInterface $flash,
        View $view
    ) {
        $this->route_params = $route_params;
        $this->flash = $flash;
        $this->view = $view;
    }

    /**
     * Magic method called when a non-existent or inaccessible method is
     * called on an object of this class. Used to execute before and after
     * filter methods on action methods. Action methods need to be named
     * with an "Action" suffix, e.g. indexAction, showAction etc.
     *
     * @param string $name Method name
     * @param array $args  Arguments passed to the method
     *
     * @return void
     */
    public function __call(string $name, array $args)
    {
        $method = $name . "Action";

        if (method_exists($this, $method)) {
            if ($this->before() !== false) {
                call_user_func_array([$this, $method], $args);

                $this->after();
            }
        } else {
            ####################################################################################
            // Danger Danger Danger // DANGER //
            // Question to ask, Should we throw here? or return something that we can process.
            // this __call is called when we do a dynamic call to the controller Object in
            // the router on this line: $controller_object->$action();
            // Can we place this "$controller_object->$action()" in a try/catch????
            throw new InvalidArgumentException("WTF11.....Method $name (in controller scrapObj) not found", 404);
            ###################################################################################
        }
    }

    // TODO
    // private function requiredLogin()
    // {
    //     if (! isset($_SESSION['user_id'])) {
    //         $this->flash->add('Please log in to access this page.', FlashMessageType::Warning);
    //         $this->returnPageManagerObj->setReturnToPage('/login');
    //         $this->redirectObj->to($this->returnPageManagerObj->getReturnToPage());
    //         return; // Important: Exit the method to prevent further execution
    //     }

    //     // Check user status for "pending"
    //     if (isset($_SESSION['status_code']) && $_SESSION['status_code'] === "P") {
    //         $controllerAction = $this->route_params['controller'] . '/' . $this->route_params['action'];
    //         if (!in_array($controllerAction, $this->pending_access)) {
    //             // Redirect or display access denied message
    //             $this->flash->add(
    //                 'Your account is pending approval.  Limited access.',
    //                 FlashMessageType::Warning
    //             );
    //             $this->returnPageManagerObj->setReturnToPage('/'); // Redirect to home or a specific "pending" page
    //             $this->redirectObj->to($this->returnPageManagerObj->getReturnToPage());
    //             exit; // Stop further execution
    //         }
    //     }
    // }

    /**
     * Before filter - called before an action method.
     *
     * @return void
     */
    protected function before()
    {
        // DANGER
        // if (in_array($this->route_params['controller'].'/'. $this->route_params['action'], $this->login_required)) {
        //     $this->requiredLogin();//TODO
        // }
    }

    /**
     * After filter - called after an action method.
     *
     * @return void
     */
    protected function after()
    {
    }


    protected function view(string $template, array $args = []): void
    {
        $args['flash'] = $this->flash;

        //......$args += ["scrapsArr" => $this->route_params];
        $this->view->renderWithLayout($template, $args);

        // Get common data (keep this part as it's controller-specific logic)
        //..$commonData = $this->getCommonData();
    }

    /**
     * Convert string with hyphens to camelCase
     * e.g. post-authors => postAuthors
     *
     * @param string $string The string to convert
     * @return string
     */
    protected function convertToCamelCase(string $string): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $string))));
    }
}
# End of File 1919 430 149
