<?php

declare(strict_types=1);

namespace App\Enums;

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
 * @method string|null icon()
 * @method string|null class()
 * @method array|null attributes()
 * @method array toLinkData(
 *     array $params = [],
 *     ?string $label = null,
 *     ?string $icon = null,
 *     ?string $class = null,
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
    case CORE_TESTY_CREATE;
    case CORE_TESTY_EDIT;
    case CORE_TESTY_PLACEHOLDER;
    case CORE_TESTY_LINKDEMO;
    case CORE_TESTY_TESTLOGGER;
    case CORE_TESTY_TESTSESSION;
    case CORE_TESTY_TESTDATABASE;
    case CORE_TESTY_EMAILTEST;
    case CORE_TESTY_PAGINATION_TEST;
    case CORE_MYNOTES;
    case CORE_GENPOSTS;

    case CORE_POSTS;
    case CORE_POSTS_CREATE;
    case CORE_POSTS_EDIT;
    case CORE_POSTS_VIEW;
    case CORE_POSTS_DELETE;

    case CORE_TESTYS;
    case CORE_TESTYS_CREATE;
    case CORE_TESTYS_EDIT;
    case CORE_TESTYS_VIEW;
    case CORE_TESTYS_DELETE;


    // Account URLs
    case ACCOUNT_DASHBOARD;
    case ACCOUNT_PROFILE;
    case ACCOUNT_MYNOTES;

    case ACCOUNT_POSTS;
    case ACCOUNT_POSTS_CREATE;
    case ACCOUNT_POSTS_EDIT;
    case ACCOUNT_POSTS_VIEW;
    case ACCOUNT_POSTS_DELETE;

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

    case STORE_POSTS;
    case STORE_POSTS_CREATE;
    case STORE_POSTS_EDIT;
    case STORE_POSTS_VIEW;
    case STORE_POSTS_DELETE;

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
        return match ($this) {
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

            self::CORE_TESTY => $this->routeData(
                'testys',
                'testys/index',
                'index',
                'Testy',
                [],
                'fas fa-newspaper',
                'btn btn-primary'
            ),
            self::CORE_TESTY_CREATE => $this->routeData(
                'testys/create',
                'testys/create',
                'create',
                'Create Testy',
                [],
                'fas fa-plus'
            ),
            self::CORE_TESTY_EDIT => $this->routeData(
                'testys/edit/{id}',
                'testys/edit',
                'edit',
                'Edit Testy',
                ['id'],
                'fas fa-pencil-alt'
            ),







            self::CORE_TESTY_PLACEHOLDER => $this->routeData(
                'testys/placeholder',
                'testys/placeholder',
                'placeholder',
                'Placeholder',
                [],
                null,
            ),
            self::CORE_TESTY_LINKDEMO => $this->routeData(
                'testys/linkdemo',
                'testys/linkdemo',
                'linkdemo',
                'Link Demo',
                [],
                'fas fa-images'
            ),
            self::CORE_TESTY_TESTLOGGER => $this->routeData(
                'testys/testlogger',
                'testys/testlogger',
                'testlogger',
                'Logger'
            ),
            self::CORE_TESTY_TESTSESSION => $this->routeData(
                'testys/testsession',
                'testys/testsession',
                'testsession',
                'Testsession'
            ),
            self::CORE_TESTY_TESTDATABASE => $this->routeData(
                'testys/testdatabase',
                'testys/testdatabase',
                'testdatabase',
                'Testdatabase'
            ),
            self::CORE_TESTY_EMAILTEST => $this->routeData(
                'testys/emailtest',
                'testys/emailtest',
                'emailtest',
                'Emailtest'
            ),
            self::CORE_TESTY_PAGINATION_TEST => $this->routeData(
                'testys/paginationtest',
                'testys/paginationtest',
                'paginationtest',
                'Pagination Test'
            ),


            self::CORE_MYNOTES => $this->routeData(
                'mynotes/index',
                'mynotes/index',
                'index',
                'Notes',
                [],
                null,
            ),
            self::CORE_GENPOSTS => $this->routeData(
                'genposts/index',
                'genposts/index',
                'index',
                'Posts',
                [],
                'fas fa-blog'
            ),

            self::CORE_POSTS => $this->routeData(
                'posts/index',
                'posts/index',
                'index',
                'Posts',
                [],
                'fas fa-blog',
                'btn btn-primary'
            ),
            self::CORE_POSTS_CREATE => $this->routeData(
                'posts/create',
                'posts/create',
                'create',
                'Create Post',
                [],
                'fas fa-plus',
                'btn btn-primary'
            ),
            self::CORE_POSTS_EDIT => $this->routeData(
                'posts/edit/{id}',
                'posts/edit',
                'edit',
                'Edit Post',
                ['id'],
                'fas fa-pencil-alt',
                'btn btn-primary'
            ),
            self::CORE_POSTS_VIEW => $this->routeData(
                'posts/view/{id}',
                'posts/view',
                'view',
                'VIEW Post',
                ['id'],
                'fas fa-eye',
                'btn btn-info'
            ),
            self::CORE_POSTS_DELETE => $this->routeData(
                'posts/delete/{id}',
                'posts/delete',
                'delete',
                'Delete Post',
                ['id'],
                'fas fa-trash',
                'btn btn-danger'
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
                'fas fa-pencil-alt',
                'btn btn-primary'
            ),
            self::GENERIC_VIEW => $this->routeData(
                'generic/view',
                'generic/view',
                'view',
                'View Generic',
                ['id'],
                'fas fa-eye',
                'btn btn-info'
            ),
            self::GENERIC_DELETE => $this->routeData(
                'generic/delete',
                'generic/delete',
                'delete',
                'Delete Generic',
                ['id'],
                'fas fa-trash',
                'btn btn-danger'
            ),






            // Account URLs
            self::ACCOUNT_DASHBOARD => $this->routeData(
                'account/dashboard/index',
                'account/dashboard/index',
                'index',
                'User Dashboard',
                [],
                'fas fa-tachometer-alt'
            ),
            self::ACCOUNT_PROFILE => $this->routeData(
                'account/profile/index',
                'account/profile/index',
                'index',
                'Profile',
                [],
                'fas fa-user'
            ),
            self::ACCOUNT_MYNOTES => $this->routeData(
                'account/mynotes/index',
                'account/mynotes/index',
                'index',
                'Notes',
                [],
                'fas fa-book'
            ),

            self::ACCOUNT_POSTS => $this->routeData(
                'account/posts/index',
                'posts/index',
                'index',
                'Posts',
                [],
                'fas fa-blog',
                'btn btn-primary'
            ),
            self::ACCOUNT_POSTS_CREATE => $this->routeData(
                'account/posts/create',
                'posts/create',
                'index',
                'Create Post',
                [],
                'fas fa-plus',
                'btn btn-primary'
            ),
            self::ACCOUNT_POSTS_EDIT => $this->routeData(
                'account/posts/edit/{id}',
                'posts/edit',
                'edit',
                'Edit Post',
                ['id'],
                'fas fa-pencil-alt',
                'btn btn-primary'
            ),
            self::ACCOUNT_POSTS_VIEW => $this->routeData(
                'account/posts/view/{id}',
                'posts/view',
                'view',
                'VIEW Post',
                ['id'],
                'fas fa-eye',
                'btn btn-info'
            ),
            self::ACCOUNT_POSTS_DELETE => $this->routeData(
                'account/posts/delete/{id}',
                'posts/delete',
                'delete',
                'Delete Post',
                ['id'],
                'fas fa-trash',
                'btn btn-danger'
            ),


            //////MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM
            //////MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM
            //////MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM
            //////MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM
            //////MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM
            self::CORE_TESTYS => $this->routeData(
                'testys/list',
                'testys/list',
                'index',
                'Testys',
                [],
                'fas fa-blog',
                'btn btn-primary'
            ),
            self::CORE_TESTYS_CREATE => $this->routeData(
                'testys/create',
                'testys/create',
                'create',
                'Create Testy',
                [],
                'fas fa-plus',
                'btn btn-primary'
            ),
            self::CORE_TESTYS_EDIT => $this->routeData(
                'testys/edit/{id}',
                'testys/edit',
                'edit',
                'Edit Testy',
                ['id'],
                'fas fa-pencil-alt',
                'btn btn-primary'
            ),
            self::CORE_TESTYS_VIEW => $this->routeData(
                'testys/view/{id}',
                'testys/view',
                'view',
                'VIEW Testy',
                ['id'],
                'fas fa-eye',
                'btn btn-info'
            ),
            self::CORE_TESTYS_DELETE => $this->routeData(
                'testys/delete/{id}',
                'testys/delete',
                'delete',
                'Delete Testy',
                ['id'],
                'fas fa-trash',
                'btn btn-danger'
            ),
            self::ACCOUNT_TESTYS => $this->routeData(
                'account/testys/list',
                'testys/list',
                'index',
                'Testys',
                [],
                'fas fa-blog',
                'btn btn-primary'
            ),
            self::ACCOUNT_TESTYS_CREATE => $this->routeData(
                'account/testys/create',
                'testys/create',
                'index',
                'Create Testy',
                [],
                'fas fa-plus',
                'btn btn-primary'
            ),
            self::ACCOUNT_TESTYS_EDIT => $this->routeData(
                'account/testys/edit/{id}',
                'testys/edit',
                'edit',
                'Edit Testy',
                ['id'],
                'fas fa-pencil-alt',
                'btn btn-primary'
            ),
            self::ACCOUNT_TESTYS_VIEW => $this->routeData(
                'account/testys/view/{id}',
                'testys/view',
                'view',
                'VIEW Testy',
                ['id'],
                'fas fa-eye',
                'btn btn-info'
            ),
            self::ACCOUNT_TESTYS_DELETE => $this->routeData(
                'account/testys/delete/{id}',
                'testys/delete',
                'delete',
                'Delete Testy',
                ['id'],
                'fas fa-trash',
                'btn btn-danger'
            ),



            self::STORE_TESTYS => $this->routeData(
                'stores/testys/list',
                'testys/list',
                'index',
                'Testys',
                [],
                'fas fa-blog',
                'btn btn-primary'
            ),
            self::STORE_TESTYS_CREATE => $this->routeData(
                'stores/testys/create',
                'testys/create',
                'create',
                'Create Testy',
                [],
                'fas fa-plus',
                'btn btn-primary'
            ),
            self::STORE_TESTYS_EDIT => $this->routeData(
                'stores/testys/edit/{id}',
                'testys/edit',
                'edit',
                'Edit Testy',
                ['id'],
                'fas fa-pencil-alt',
                'btn btn-primary'
            ),
            self::STORE_TESTYS_VIEW => $this->routeData(
                'stores/testys/view/{id}',
                'testys/view',
                'view',
                'VIEW Testy',
                ['id'],
                'fas fa-eye',
                'btn btn-info'
            ),
            self::STORE_TESTYS_DELETE => $this->routeData(
                'stores/testys/delete/{id}',
                'testys/delete',
                'delete',
                'Delete Testy',
                ['id'],
                'fas fa-trash',
                'btn btn-danger'
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
                'fas fa-images'
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
            //     'fas fa-pencil-alt'
            // ),








            // self::ACCOUNT_ALBUMS => $this->routeData(
            //     'account/albums',
            //     // 'albums/index',
            //     'stores/albums/index',
            //     'Albums',
            //     [],
            //     'fas fa-images',
            // ),


            ######################################################################
            // Store URLs
            self::STORE_DASHBOARD => $this->routeData(
                'stores/dashboard/index',
                'stores/dashboard/index',
                'index',
                'Store Dashboard',
                [],
                'fas fa-tachometer-alt'
            ),
            self::STORE_PROFILE => $this->routeData(
                'stores/profile/index',
                'stores/profile/index',
                'index',
                'Profile',
                [],
                'fas fa-store'
            ),
            self::STORE_SETTINGS => $this->routeData(
                'stores/settings/index',
                'stores/settings/index',
                'index',
                'Settings',
                [],
                'fas fa-store'
            ),
            self::STORE_CREATE => $this->routeData(
                'stores/profile/create',
                'stores/profile/create',
                'index',
                'Create Store',
                [],
                'fas fa-plus'
            ),
            // self::STORE_POSTS => $this->routeData(
            //     'stores/posts/index',
            //     'posts/index',
                // 'index',
            //     'Posts',
            //     [],
            //     'fas fa-newspaper'
            // ),
            // self::STORE_POSTS_CREATE => $this->routeData(
            //     'stores/posts/create',
            //     'posts/create',
                // 'index',
            //     'Create Post',
            //     [],
            //     'fas fa-plus'
            // ),
            // self::STORE_POSTS_EDIT => $this->routeData(
            //     'stores/posts/edit/{id}',
            //     'posts/edit',
                // 'index',
            //     'Edit Post',
            //     ['id'],
            //     'fas fa-pencil-alt'
            // ),


            self::STORE_POSTS => $this->routeData(
                'stores/posts/index',
                'posts/index',
                'index',
                'Posts',
                [],
                'fas fa-blog',
                'btn btn-primary'
            ),
            self::STORE_POSTS_CREATE => $this->routeData(
                'stores/posts/create',
                'posts/create',
                'create',
                'Create Post',
                [],
                'fas fa-plus',
                'btn btn-primary'
            ),
            self::STORE_POSTS_EDIT => $this->routeData(
                'stores/posts/edit/{id}',
                'posts/edit',
                'edit',
                'Edit Post',
                ['id'],
                'fas fa-pencil-alt',
                'btn btn-primary'
            ),
            self::STORE_POSTS_VIEW => $this->routeData(
                'stores/posts/view/{id}',
                'posts/view',
                'view',
                'VIEW Post',
                ['id'],
                'fas fa-eye',
                'btn btn-info'
            ),
            self::STORE_POSTS_DELETE => $this->routeData(
                'stores/posts/delete/{id}',
                'posts/delete',
                'delete',
                'Delete Post',
                ['id'],
                'fas fa-trash',
                'btn btn-danger'
            ),








            self::STORE_ALBUMS => $this->routeData(
                'stores/albums',
                'albums/index',
                'index',
                'Albums',
                [],
                'fas fa-images'
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
                'fas fa-pencil-alt'
            ),
            self::STORE_ALBUMS_VIEW => $this->routeData(
                'stores/albums/view/{id}',
                'albums/view',
                'view',
                'VIEW Album',
                ['id'],
                'fas fa-eye',
                'btn btn-info'
            ),
            self::STORE_ALBUMS_DELETE => $this->routeData(
                'stores/albums/delete/{id}',
                'albums/delete',
                'delete',
                'Delete Album',
                ['id'],
                'fas fa-trash',
                'btn btn-danger'
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
                'fas fa-tachometer-alt'
            ),
            self::ADMIN_USERS => $this->routeData(
                'admin/users/index',
                'admin/users/index',
                'index',
                'Manage Users',
                [],
                'fas fa-users',
                'xxxbtn xxxbtn-danger'
            ),
        };
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
        ?string $icon = null,
        ?string $class = null
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


        // Only add icon if provided.
        if ($icon !== null) {
            $data['icon'] = $icon;
        }

        // Only add class if provided.
        if ($class !== null) {
            $data['class'] = $class;
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



    // public function toLinkData(?string $label = null, array $params = []): array
    // {
    //     return [
    //         // 'url' => $this,
    //         'url' => $this->url($params),
    //         'label' => $label ?? $this->label(),
    //         'icon' => $this->icon(),
    //         // 'params' => $params,
    //     ];
    // }


    public function toLinkData(
        array $params = [],
        // string $action = null,
        ?string $label = null,
        ?string $icon = null,
        ?string $class = null,
        ?array $attributes = null
    ): array {
        return [
            'url' => $this->url($params),
            // 'action' => $action ?? $this->action(),
            'label' => $label ?? $this->label(),
            'icon' => $icon ?? $this->icon(),
            'class' => $class ?? $this->class(),
            'attributes' => $attributes ?? $this->attributes(),
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
     * example: $template = Url::STORE_POSTS->paginationUrl();
     *        : $page3 = Url::STORE_POSTS->paginationUrl(3);
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
    // Returns: /account/stores/posts/page/{page}

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
     * Get the icon for this URL
     */
    public function icon(): ?string
    {
        $data = $this->data();
        return $data['icon'] ?? null;
    }


    /**
     * Get the class for this URL
     */
    public function class(): ?string
    {
        $data = $this->data();
        return $data['class'] ?? null;
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
