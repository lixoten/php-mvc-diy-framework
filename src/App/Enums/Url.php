<?php

declare(strict_types=1);

namespace App\Enums;

use App\Helpers\DebugRt;

/**
 * Enum for all application URLs and their metadata.
 *
 * @method string url(array $params = [])
 * @method string label()
 * @method string view()
 * @method string action()
 * @method string section()
 * @method bool requiresAuth()
 * @method array roles()
 * @method array data()
 * @method string paginationUrl(?int $page = null)
 * @method array toPaginationItem(?int $page = null)
 * @method array|null attributes()
 * @method array toLinkData(
 *     array $params = [],
 *     ?string $label = null,
 *     ?array $attributes = null
 * )
 */
enum Url
{
    case AUTH_LOGIN;
    case LOGIN;
    case LOGOUT;
    case AUTH_REGISTRATION;
    case AUTH_REGISTRATION_SUCCESS;
    case REGISTRATION;

    case EMAIL_VERIFICATION;
    case AUTH_VERIFICATION_PENDING;
    case AUTH_VERIFICATION_SUCCESS;
    case AUTH_VERIFICATION_ERROR;
    case AUTH_VERIFICATION_RESEND;


    case CORE_HOME;
    case CORE_HOME_ROOT;
    case CORE_HOME_INDEX; //testing
    case CORE_HOME_TEST;
    case CORE_ABOUT;
    case CORE_CONTACT;
    case CORE_CONTACT_DIRECT;

    case CORE_TESTY;
    case CORE_TESTY_LIST;
    case CORE_TESTY_CREATE;
    case CORE_TESTY_EDIT;

    // case CORE_TESTYS;
    // case CORE_TESTYS_CREATE;
    // case CORE_TESTYS_EDIT;
    case CORE_TESTY_VIEW;
    case CORE_TESTY_DELETE;



    case CORE_TESTY_PLACEHOLDER;
    case CORE_TESTY_LINKDEMO;
    case CORE_TESTY_TESTLOGGER;
    case CORE_TESTY_TESTSESSION;
    case CORE_TESTY_TESTDATABASE;
    case CORE_TESTY_EMAILTEST;
    case CORE_TESTY_PAGINATION_TEST;
    case CORE_MYNOTES;
    case CORE_GENPOST;

    case CORE_POST;
    case CORE_POST_CREATE;
    case CORE_POST_EDIT;
    case CORE_POST_VIEW;
    case CORE_POST_DELETE;




    // Account URLs
    case ACCOUNT_DASHBOARD;
    case ACCOUNT_PROFILE;
    case ACCOUNT_MYNOTES;

    case ACCOUNT_POST;
    case ACCOUNT_POST_CREATE;
    case ACCOUNT_POST_EDIT;
    case ACCOUNT_POST_VIEW;
    case ACCOUNT_POST_DELETE;

    case ACCOUNT_TESTYS;
    case ACCOUNT_TESTYS_CREATE;
    case ACCOUNT_TESTYS_EDIT;
    case ACCOUNT_TESTYS_VIEW;
    case ACCOUNT_TESTYS_DELETE;



    case ACCOUNT_ALBUMS;


    case GENERIC;
    case GENERIC_CREATE;
    case GENERIC_EDIT;
    case GENERIC_VIEW;
    case GENERIC_DELETE;

    // Store URLs
    case STORE_DASHBOARD;
    case STORE_PROFILE;
    case STORE_SETTINGS;
    case STORE_CREATE;

    case STORE_POST;
    case STORE_POST_CREATE;
    case STORE_POST_EDIT;
    case STORE_POST_VIEW;
    case STORE_POST_DELETE;

    case STORE_TESTYS;
    case STORE_TESTYS_CREATE;
    case STORE_TESTYS_EDIT;
    case STORE_TESTYS_VIEW;
    case STORE_TESTYS_DELETE;

    case STORE_ALBUMS;
    case STORE_ALBUMS_CREATE;
    case STORE_ALBUMS_EDIT;
    case STORE_ALBUMS_VIEW;
    case STORE_ALBUMS_DELETE;
    case STORE_VIEW_PUBLIC; // TODO confirm we need for future use


    // Admin URLs
    case ADMIN_DASHBOARD;
    case ADMIN_USERS;

    case BASE_ADMIN;
    case BASE_ACCOUNT;
    case BASE_STORE;


    /**
     * Get URL data as an array with all properties
     */
    public function data(): array
    {


        $foo =  match ($this) {
            // Core URLs
            self::BASE_ADMIN => $this->routeData(
                'admin/',
                'admin/',
                'index',
                ''
            ),
            self::BASE_ACCOUNT => $this->routeData(
                'account/',
                'account/',
                'index',
                ''
            ),
            self::BASE_STORE => $this->routeData(
                'stores/',
                'stores/',
                'index',
                ''
            ),

            self::AUTH_LOGIN => $this->routeData(
                'Auth/login',
                'Auth/login',
                'index',
                ''
            ),
            self::LOGIN => $this->routeData(
                'login',
                'login',
                'index',
                'Login'
                // section, requiresAuth, roles omitted (public)
            ),
            self::LOGOUT => $this->routeData(
                'logout',
                'logout',
                'logout',
                'Logout'
            ),
            self::REGISTRATION => $this->routeData(
                'registration',
                'registration',
                'index',
                'Register'
            ),
            self::AUTH_REGISTRATION => $this->routeData(
                'auth/registration',
                'auth/registration',
                'index',
                'Register'
            ),
            self::AUTH_REGISTRATION_SUCCESS => $this->routeData(
                'Auth/registration_success',
                'Auth/registration_success',
                'index',
                'Register'
            ),
            self::EMAIL_VERIFICATION => $this->routeData(
                'verify-email/pending',
                'verify-email/pending',
                'pending',
                'Email Verification'
            ),

            self::AUTH_VERIFICATION_PENDING => $this->routeData(
                'verify-email/pending',
                'Auth/verification_pending',
                'index',
                'Email Verification Pending'
            ),
            self::AUTH_VERIFICATION_SUCCESS => $this->routeData(
                'Auth/verification_success',
                'Auth/verification_success',
                'index',
                'Auth Verification Success'
            ),
            self::AUTH_VERIFICATION_ERROR => $this->routeData(
                'Auth/verification_error',
                'Auth/verification_error',
                'index',
                'Auth Verification Error'
            ),
            self::AUTH_VERIFICATION_RESEND => $this->routeData(
                'Auth/verification_resend',
                'Auth/verification_resend',
                'index',
                'Auth Verification Resend'
            ),


            self::CORE_HOME => $this->routeData(
                'home',
                'home/index',
                'index',
                'Home-just',
                [],
            ),
            self::CORE_HOME_ROOT => $this->routeData(
                '',
                'home/index',
                'index',
                'Home-root'
            ),
            self::CORE_HOME_INDEX => $this->routeData(
                'home/index',
                'home/index',
                'index',
                'Home-Index'
            ),
            self::CORE_HOME_TEST => $this->routeData(
                'home/test',
                'home/test',
                'test',
                'Test',
                [],
                null,
            ),

            self::CORE_ABOUT => $this->routeData(
                'about/index',
                'about/index',
                'index',
                'About',
                [],
                null,
            ),
            self::CORE_CONTACT => $this->routeData(
                'contact/index',
                'contact/index',
                'index',
                'Contact',
                [],
                null,
            ),
            self::CORE_CONTACT_DIRECT => $this->routeData(
                'contact/direct',
                'contact/direct',
                'direct',
                'ContactDir'
            ),







            self::CORE_MYNOTES => $this->routeData(
                'mynotes/index',
                'mynotes/index',
                'index',
                'Notes',
                [],
                null,
            ),
            self::CORE_GENPOST => $this->routeData(
                'genpost/index',
                'genpost/index',
                'index',
                'Post',
                [],
            ),

            self::CORE_POST => $this->routeData(
                'post',
                // 'post/index',
                'post/list',
                'index',
                'Post',
                [],
            ),
            self::CORE_POST_CREATE => $this->routeData(
                'post/create',
                'post/create',
                'create',
                'Create Post',
                [],
            ),
            self::CORE_POST_EDIT => $this->routeData(
                'post/edit/{id}',
                'post/edit',
                'edit',
                'Edit Post',
                ['id'],
            ),
            self::CORE_POST_VIEW => $this->routeData(
                'post/view/{id}',
                'post/view',
                'view',
                'VIEW Post',
                ['id'],
            ),
            self::CORE_POST_DELETE => $this->routeData(
                'post/delete/{id}',
                'post/delete',
                'delete',
                'Delete Post',
                ['id'],
            ),

            self::GENERIC => $this->routeData(
                'generic/index',
                'generic/index',
                'index',
                'Generic something',
                [],
                'fa-newspaper'
            ),
            self::GENERIC_CREATE => $this->routeData(
                'generic/create',
                'generic/create',
                'index',
                'Create Generic'
            ),
            self::GENERIC_EDIT => $this->routeData(
                'generic/edit',
                'generic/edit',
                'edit',
                'Edit Generic',
                ['id'],
            ),
            self::GENERIC_VIEW => $this->routeData(
                'generic/view',
                'generic/view',
                'view',
                'View Generic',
                ['id'],
            ),
            self::GENERIC_DELETE => $this->routeData(
                'generic/delete',
                'generic/delete',
                'delete',
                'Delete Generic',
                ['id'],
            ),






            // Account URLs
            self::ACCOUNT_DASHBOARD => $this->routeData(
                'account/dashboard/index',
                'account/dashboard/index',
                'index',
                'User Dashboard',
                [],
            ),
            self::ACCOUNT_PROFILE => $this->routeData(
                'account/profile/index',
                'account/profile/index',
                'index',
                'Profile',
                [],
            ),
            self::ACCOUNT_MYNOTES => $this->routeData(
                'account/mynotes/index',
                'account/mynotes/index',
                'index',
                'Notes',
                [],
            ),

            self::ACCOUNT_POST => $this->routeData(
                'account/post/list',
                'post/list',
                // 'account/post/index',
                // 'post/index',
                'index',
                'Post',
                [],
            ),
            self::ACCOUNT_POST_CREATE => $this->routeData(
                'account/post/create',
                'post/create',
                'index',
                'Create Post',
                [],
            ),
            self::ACCOUNT_POST_EDIT => $this->routeData(
                'account/post/edit/{id}',
                'post/edit',
                'edit',
                'Edit Post',
                ['id'],
            ),
            self::ACCOUNT_POST_VIEW => $this->routeData(
                'account/post/view/{id}',
                'post/view',
                'view',
                'VIEW Post',
                ['id'],
            ),
            self::ACCOUNT_POST_DELETE => $this->routeData(
                'account/post/delete/{id}',
                'post/delete',
                'delete',
                'Delete Post',
                ['id'],
            ),


            //////MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM
            //////MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM
            //////MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM
            //////MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM
            //////MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM
            self::CORE_TESTY => $this->routeData(
                'testy',
                'testy/index',
                'index',
                'Testy',
                [],
            ),
            self::CORE_TESTY_LIST => $this->routeData(
                'testy/list',
                'testy/list',
                'list',
                'Testy',
                [],
            ),
            self::CORE_TESTY_CREATE => $this->routeData(
                'testy/create',
                'testy/create',
                'create',
                'Create Testy',
                [],
            ),
            self::CORE_TESTY_EDIT => $this->routeData(
                'testy/edit/{id}',
                'testy/edit',
                'edit',
                'Edit Testy',
                ['id'],
            ),

            self::CORE_TESTY_VIEW => $this->routeData(
                'testy/view/{id}',
                'testy/view',
                'view',
                'VIEW Testy',
                ['id'],
            ),
            self::CORE_TESTY_DELETE => $this->routeData(
                'testy/delete/{id}',
                'testy/delete',
                'delete',
                'Delete Testy',
                ['id'],
            ),





            self::CORE_TESTY_PLACEHOLDER => $this->routeData(
                'testy/placeholder',
                'testy/placeholder',
                'placeholder',
                'Placeholder',
                [],
                null,
            ),
            self::CORE_TESTY_LINKDEMO => $this->routeData(
                'testy/linkdemo',
                'testy/linkdemo',
                'linkdemo',
                'Link Demo',
                [],
            ),
            self::CORE_TESTY_TESTLOGGER => $this->routeData(
                'testy/testlogger',
                'testy/testlogger',
                'testlogger',
                'Logger'
            ),
            self::CORE_TESTY_TESTSESSION => $this->routeData(
                'testy/testsession',
                'testy/testsession',
                'testsession',
                'Testsession'
            ),
            self::CORE_TESTY_TESTDATABASE => $this->routeData(
                'testy/testdatabase',
                'testy/testdatabase',
                'testdatabase',
                'Testdatabase'
            ),
            self::CORE_TESTY_EMAILTEST => $this->routeData(
                'testy/emailtest',
                'testy/emailtest',
                'emailtest',
                'Emailtest'
            ),
            self::CORE_TESTY_PAGINATION_TEST => $this->routeData(
                'testy/paginationtest',
                'testy/paginationtest',
                'paginationtest',
                'Pagination Test'
            ),









            self::ACCOUNT_TESTYS => $this->routeData(
                'account/testy/list',
                'testy/list',
                'index',
                'Testy',
                [],
            ),
            self::ACCOUNT_TESTYS_CREATE => $this->routeData(
                'account/testy/create',
                'testy/create',
                'index',
                'Create Testy',
                [],
            ),
            self::ACCOUNT_TESTYS_EDIT => $this->routeData(
                'account/testy/edit/{id}',
                'testy/edit',
                'edit',
                'Edit Testy',
                ['id'],
            ),
            self::ACCOUNT_TESTYS_VIEW => $this->routeData(
                'account/testy/view/{id}',
                'testy/view',
                'view',
                'VIEW Testy',
                ['id'],
            ),
            self::ACCOUNT_TESTYS_DELETE => $this->routeData(
                'account/testy/delete/{id}',
                'testy/delete',
                'delete',
                'Delete Testy',
                ['id'],
            ),



            self::STORE_TESTYS => $this->routeData(
                'stores/testy/list',
                'testy/list',
                'index',
                'Testy',
                [],
            ),
            self::STORE_TESTYS_CREATE => $this->routeData(
                'stores/testy/create',
                'testy/create',
                'create',
                'Create Testy',
                [],
            ),
            self::STORE_TESTYS_EDIT => $this->routeData(
                'stores/testy/edit/{id}',
                'testy/edit',
                'edit',
                'Edit Testy',
                ['id'],
            ),
            self::STORE_TESTYS_VIEW => $this->routeData(
                'stores/testy/view/{id}',
                'testy/view',
                'view',
                'VIEW Testy',
                ['id'],
            ),
            self::STORE_TESTYS_DELETE => $this->routeData(
                'stores/testy/delete/{id}',
                'testy/delete',
                'delete',
                'Delete Testy',
                ['id'],
            ),


            //////MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM
            //////MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM
            //////MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM
            //////MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM
            //////MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM
            //////MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM



            self::ACCOUNT_ALBUMS => $this->routeData(
                'account/albums',
                'albums/index',
                'index',
                'Albums',
                [],
            ),
            // self::ACCOUNT_ALBUMS_CREATE => $this->routeData(
            //     'account/albums/create',
            //     'albums/create',
            //     'Create Album',
            //     [],
            // ),
            // self::ACCOUNT_ALBUMS_EDIT => $this->routeData(
            //     'account/albums/edit/{id}',
            //     'albums/edit',
            //     'Edit Album',
            //     ['id'],
            // ),








            // self::ACCOUNT_ALBUMS => $this->routeData(
            //     'account/albums',
            //     // 'albums/index',
            //     'stores/albums/index',
            //     'Albums',
            //     [],
            // ),


            ######################################################################
            // Store URLs
            self::STORE_DASHBOARD => $this->routeData(
                'stores/dashboard/index',
                'stores/dashboard/index',
                'index',
                'Store Dashboard',
                [],
            ),
            self::STORE_PROFILE => $this->routeData(
                'stores/profile/index',
                'stores/profile/index',
                'index',
                'Profile',
                [],
            ),
            self::STORE_SETTINGS => $this->routeData(
                'stores/settings/index',
                'stores/settings/index',
                'index',
                'Settings',
                [],
            ),
            self::STORE_CREATE => $this->routeData(
                'stores/profile/create',
                'stores/profile/create',
                'index',
                'Create Store',
                [],
            ),
            // self::STORE_POST => $this->routeData(
            //     'stores/post/index',
            //     'post/index',
                // 'index',
            //     'Post',
            //     [],
            // ),
            // self::STORE_POST_CREATE => $this->routeData(
            //     'stores/post/create',
            //     'post/create',
                // 'index',
            //     'Create Post',
            //     [],
            // ),
            // self::STORE_POST_EDIT => $this->routeData(
            //     'stores/post/edit/{id}',
            //     'post/edit',
                // 'index',
            //     'Edit Post',
            //     ['id'],
            // ),


            self::STORE_POST => $this->routeData(
                'stores/post/list',
                'post/list',
                // 'stores/post/index',
                // 'post/index',
                'index',
                'Post',
                [],
            ),
            self::STORE_POST_CREATE => $this->routeData(
                'stores/post/create',
                'post/create',
                'create',
                'Create Post',
                [],
            ),
            self::STORE_POST_EDIT => $this->routeData(
                'stores/post/edit/{id}',
                'post/edit',
                'edit',
                'Edit Post',
                ['id'],
            ),
            self::STORE_POST_VIEW => $this->routeData(
                'stores/post/view/{id}',
                'post/view',
                'view',
                'VIEW Post',
                ['id'],
            ),
            self::STORE_POST_DELETE => $this->routeData(
                'stores/post/delete/{id}',
                'post/delete',
                'delete',
                'Delete Post',
                ['id'],
            ),








            self::STORE_ALBUMS => $this->routeData(
                'stores/albums',
                'albums/index',
                'index',
                'Albums',
                [],
            ),
            self::STORE_ALBUMS_CREATE => $this->routeData(
                'stores/albums/create',
                'albums/create',
                'create',
                'Create Album',
                [],
            ),
            self::STORE_ALBUMS_EDIT => $this->routeData(
                'stores/albums/edit/{id}',
                'albums/edit',
                'edit',
                'Edit Album',
                ['id'],
            ),
            self::STORE_ALBUMS_VIEW => $this->routeData(
                'stores/albums/view/{id}',
                'albums/view',
                'view',
                'VIEW Album',
                ['id'],
            ),
            self::STORE_ALBUMS_DELETE => $this->routeData(
                'stores/albums/delete/{id}',
                'albums/delete',
                'delete',
                'Delete Album',
                ['id'],
            ),




            self::STORE_VIEW_PUBLIC => $this->routeData( // TODO - Confirm we need
                '{slug}',
                'store/public_view',
                'index',
                'View Store',
                ['slug'] // required parameter
            ),



            // Admin URLs
            self::ADMIN_DASHBOARD => $this->routeData(
                'admin/dashboard/index',
                'admin/dashboard/index',
                'index',
                'Admin Dashboard',
                [],
            ),
            self::ADMIN_USERS => $this->routeData(
                'admin/users/index',
                'admin/users/index',
                'index',
                'Manage Users',
                [],
            ),
        };

        return $foo;
    }



    /**
     * Helper method to create route data without repeating empty params
     */
    private function routeData(
        string $path,
        string $view,
        string $action,
        string $label,
        array $params = [],
    ): array {
        $data = [
            'path' => $path,
            'view' => $view,
            'label' => $label,
            'action' => $action,
        ];

        // Only add params key if there are actually parameters.
        if (!empty($params)) {
            $data['params'] = $params;
        }

        // DangerDanger we might not need this shit
        if (str_starts_with($this->name, "CORE_")) {
            $data['section'] = "PUBLIC";
            $data['requiresAuth'] = false;
            $data['roles'] = [];
        } elseif (str_starts_with($this->name, "ADMIN_")) {
            $data['section'] = "ADMIN";
            $data['requiresAuth'] = true;
            $data['roles'] = ['admin'];
        } elseif (str_starts_with($this->name, "ACCOUNT_")) {
            $data['section'] = "ACCOUNT";
            $data['requiresAuth'] = true;
            $data['roles'] = ['user'];
        } elseif (str_starts_with($this->name, "STORE_")) {
            $data['section'] = "STORE";
            $data['requiresAuth'] = true;
            $data['roles'] = ['store_owner'];
        } elseif (str_starts_with($this->name, "GENER")) {
            $data['section'] = "STORE";
            $data['requiresAuth'] = true;
            $data['roles'] = ['store_owner'];
        } elseif (str_starts_with($this->name, "AUTH_")) {
            $data['section'] = "STORE";
            $data['requiresAuth'] = true;
            $data['roles'] = ['user'];
        } elseif ($this->name === "BASE_ADMIN") {
            $data['section'] = "ADMIN";
            $data['requiresAuth'] = true;
            $data['roles'] = ['admin'];
        } elseif ($this->name === "BASE_STORE") {
            $data['section'] = "STORE";
            $data['requiresAuth'] = true;
            $data['roles'] = ['user'];
        } elseif ($this->name === "BASE_ACCOUNT") {
            $data['section'] = "ACCOUNT";
            $data['requiresAuth'] = true;
            $data['roles'] = ['user'];
        } elseif ($this->name === "LOGIN") {
            $data['section'] = "GUEST";
            $data['requiresAuth'] = true;
            $data['roles'] = ['user'];
        } elseif ($this->name === "LOGOUT") {
            $data['section'] = "GUEST";
            $data['requiresAuth'] = true;
            $data['roles'] = ['user'];
        } elseif ($this->name === "REGISTRATION") {
            $data['section'] = "GUEST";
            $data['requiresAuth'] = true;
            $data['roles'] = ['user'];
        } else {
            $data['section'] = null;
            $data['requiresAuth'] = false;
            $data['roles'] = [];
        }

        return $data;
    }

    // helpers
    public function requiresAuth(): bool
    {
        return (bool)($this->data()['requiresAuth'] ?? false);
    }
    public function roles(): array
    {
        return $this->data()['roles'] ?? [];
    }
    public function section(): ?string
    {
        // return $this->data()['section'] ?? null;
        return $this->data()['section'];
    }

    public function view(): string
    {
        // $view = $this->data()['view'];
        // return $view === '' ? 'home' : $view;
        return $this->data()['view'];
    }


    public function url(array $params = []): string
    {
        $data = $this->data();

        // Get base path and check if it ends with "/index"
        $path = $data['path'];
        if (str_ends_with($path, '/index')) {
            $path = substr($path, 0, -6);
        }

        // Validate required params
        $requiredParams = $data['params'] ?? [];
        $pathParams = [];
        $queryParams = [];

        // Separate path params from query params
        foreach ($params as $key => $value) {
            if (in_array($key, $requiredParams)) {
                $pathParams[$key] = $value;  // Path parameter like {slug}
            } else {
                $queryParams[$key] = $value;  // Query parameter like ?page=2
            }
        }

        // Validate required path params
        foreach ($requiredParams as $param) {
            if (!array_key_exists($param, $pathParams)) {
                throw new \InvalidArgumentException("Missing required parameter: {$param}");
            }
        }

        $url = '/' . $path;

        // Replace path parameters
        foreach ($pathParams as $key => $value) {
            $url = str_replace('{' . $key . '}', (string)$value, $url);
        }

        // Add query parameters
        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }

        return $url;
    }




    public function action(): string
    {
        return $this->data()['action'];
    }

    public function label(): string
    {
        return $this->data()['label'];
    }

    public function pageName(): string
    {
        $pageName = $this->data()['view'];
        $pageName = str_replace('/', '_', $pageName);
        return $pageName;
    }


    // public function toLinkData(?string $label = null, array $params = []): array
    // {
    //     return [
    //         // 'url' => $this,
    //         'url' => $this->url($params),
    //         'label' => $label ?? $this->label(),
    //         // 'params' => $params,
    //     ];
    // }


    public function toLinkData(
        array $params = [],
        // string $action = null,
        ?string $label = null,
        ?array $attributes = null,
        ?array $action = null
    ): array {
        return [
            'url' => $this->url($params),
            // 'action' => $action ?? $this->action(),
            'label' => $label ?? $this->label(),
            'attributes' => $attributes ?? $this->attributes(),
            'action' => $action ?? $this->action(),
        ];
    }


    /**
     * Helper method to get all URLs in a specific section
     */
    public static function getSection(string $prefix): array
    {
        $urls = [];
        foreach (self::cases() as $case) {
            if (strpos($case->name, $prefix) === 0) {
                $urls[$case->action()] = $case;
            }
        }
        return $urls;
    }

    /**
     * Get all core URLs
     */
    public static function core(): array
    {
        return self::getSection('CORE_');
    }

    /**
     * Get all store URLs
     */
    public static function store(): array
    {
        return self::getSection('STORE_');
    }

    /**
     * Generate a pagination URL for this route
     * example: $template = Url::STORE_POST->paginationUrl();
     *        : $page3 = Url::STORE_POST->paginationUrl(3);
     *
     * @param int|null $page The current page number (null for template with {page} placeholder)
     * @return string The pagination URL
     */
    public function paginationUrl(?int $page = null): string
    {
        $baseUrl = $this->url();
        $paginationUrl = rtrim($baseUrl, '/') . '/page/' . ($page ?? '{page}');

        return $paginationUrl;
    }
    // Returns: /account/stores/post/page/{page}

    /**
     * Get pagination URL and label as an array suitable for pagination controls
     *
     * @param int|null $page The page number (null for template)
     * @return array Array with 'url' and 'label' keys
     */
    public function toPaginationItem(?int $page = null): array
    {
        return [
            'url' => $this->paginationUrl($page),
            'label' => $page ? "Page {$page}" : 'Page {page}',
            'page' => $page ?? '{page}'
        ];
    }

    /**
     * Get the attributes for this URL
     */
    public function attributes(): ?array
    {
        $data = $this->data();
        return $data['attributes'] ?? null;
    }
}
// 1257 1123
