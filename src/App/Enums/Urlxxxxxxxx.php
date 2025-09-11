<?php

declare(strict_types=1);

namespace App\Enums;

enum Url
{
    // Core URLs
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
    case CORE_TESTY_PLACEHOLDER;
    case CORE_TESTY_LINKDEMO;
    case CORE_TESTY_TESTLOGGER;
    case CORE_TESTY_TESTSESSION;
    case CORE_TESTY_TESTDATABASE;
    case CORE_TESTY_EMAILTEST;
    case CORE_TESTY_PAGINATION_TEST;

    // Account URLs
    case ACCOUNT_DASHBOARD;
    case ACCOUNT_PROFILE;
    case ACCOUNT_MYNOTES;
    case ACCOUNT_POSTS;
    case ACCOUNT_ALBUMS;


    case GENERIC;
    case GENERIC_ADD;
    case GENERIC_EDIT;


    // Store URLs
    case STORE_DASHBOARD;
    case STORE_PROFILE;
    case STORE_CREATE;
    case STORE_POSTS;
    case STORE_POSTS_ADD;
    case STORE_POSTS_EDIT;
    case STORE_ALBUMS;
    case STORE_ALBUMS_ADD;
    case STORE_ALBUMS_EDIT;
    case STORE_VIEW_PUBLIC; // TODO confirm we need for future use


    // Admin URLs
    case ADMIN_DASHBOARD;
    case ADMIN_USERS;
    // case ADMIN_POSTS;
    // case ADMIN_ALBUMS;

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
                ''
            ),
            self::BASE_ACCOUNT => $this->routeData(
                'account/',
                'account/',
                ''
            ),
            self::BASE_STORE => $this->routeData(
                'account/stores/',
                'account/stores/',
                ''
            ),

            self::AUTH_LOGIN => $this->routeData(
                'Auth/login',
                'Auth/login',
                ''
            ),
            self::LOGIN => $this->routeData(
                'login',
                'login',
                'Login'
            ),
            self::LOGOUT => $this->routeData(
                'logout',
                'logout',
                'Logout'
            ),
            self::REGISTRATION => $this->routeData(
                'registration',
                'registration',
                'Register'
            ),
            self::AUTH_REGISTRATION => $this->routeData(
                'auth/registration',
                'auth/registration',
                'Register'
            ),
            self::AUTH_REGISTRATION_SUCCESS => $this->routeData(
                'Auth/registration_success',
                'Auth/registration_success',
                'Register'
            ),
            self::EMAIL_VERIFICATION => $this->routeData(
                'verify-email/pending',
                'verify-email/pending',
                'Email Verification'
            ),

            self::AUTH_VERIFICATION_PENDING => $this->routeData(
                'verify-email/pending',
                'Auth/verification_pending',
                'Email Verification Pending'
            ),
            self::AUTH_VERIFICATION_SUCCESS => $this->routeData(
                'Auth/verification_success',
                'Auth/verification_success',
                'Auth Verification Success'
            ),
            self::AUTH_VERIFICATION_ERROR => $this->routeData(
                'Auth/verification_error',
                'Auth/verification_error',
                'Auth Verification Error'
            ),
            self::AUTH_VERIFICATION_RESEND => $this->routeData(
                'Auth/verification_resend',
                'Auth/verification_resend',
                'Auth Verification Resend'
            ),


            self::CORE_HOME => $this->routeData(
                'home',
                'home/index',
                'Home-just'
            ),
            self::CORE_HOME_ROOT => $this->routeData(
                '',
                'home/index',
                'Home-root'
            ),
            self::CORE_HOME_INDEX => $this->routeData(
                'home/index',
                'home/index',
                'Home-Index'
            ),
            self::CORE_HOME_TEST => $this->routeData(
                'home/test',
                'home/test',
                'Test'
            ),

            self::CORE_ABOUT => $this->routeData(
                'about/index',
                'about/index',
                'About'
            ),
            self::CORE_CONTACT => $this->routeData(
                'contact/index',
                'contact/index',
                'Contact'
            ),
            self::CORE_CONTACT_DIRECT => $this->routeData(
                'contact/direct',
                'contact/direct',
                'ContactDir'
            ),

            self::CORE_TESTY => $this->routeData(
                'testy',
                'testy/index',
                'Testy'
            ),
            self::CORE_TESTY_PLACEHOLDER => $this->routeData(
                'testy/placeholder',
                'testy/placeholder',
                'Placeholder'
            ),
            self::CORE_TESTY_LINKDEMO => $this->routeData(
                'testy/linkdemo',
                'testy/linkdemo',
                'Link Demo',
                [],
                'fas fa-images'
            ),
            self::CORE_TESTY_TESTLOGGER => $this->routeData(
                'testy/testlogger',
                'testy/testlogger',
                'Logger'
            ),
            self::CORE_TESTY_TESTSESSION => $this->routeData(
                'testy/testsession',
                'testy/testsession',
                'Testsession'
            ),
            self::CORE_TESTY_TESTDATABASE => $this->routeData(
                'testy/testdatabase',
                'testy/testdatabase',
                'Testdatabase'
            ),
            self::CORE_TESTY_EMAILTEST => $this->routeData(
                'testy/emailtest',
                'testy/emailtest',
                'Emailtest'
            ),
            self::CORE_TESTY_PAGINATION_TEST => $this->routeData(
                'testy/paginationtest',
                'testy/paginationtest',
                'Pagination Test'
            ),





            self::GENERIC => $this->routeData(
                'generic/index',
                'generic/index',
                'Posts',
                [],
                'fa-newspaper'
            ),
            self::GENERIC_ADD => $this->routeData(
                'generic/add',
                'generic/add',
                'Add Post'
            ),
            self::GENERIC_EDIT => $this->routeData(
                'generic/edit',
                'generic/edit',
                'Edit Post',
                ['id']
            ),






            // Account URLs
            self::ACCOUNT_DASHBOARD => $this->routeData(
                'account/dashboard/index',
                'account/dashboard/index',
                'User Dashboard'
            ),
            self::ACCOUNT_PROFILE => $this->routeData(
                'account/profile/index',
                'account/profile/index',
                'Profile'
            ),
            self::ACCOUNT_MYNOTES => $this->routeData(
                'account/mynotes/index',
                'account/mynotes/index',
                'Notes'
            ),
            self::ACCOUNT_POSTS => $this->routeData(
                'account/posts',
                'account/stores/posts/index',
                'Posts',
                [],
                'fas fa-newspaper'
            ),
            self::ACCOUNT_ALBUMS => $this->routeData(
                'account/albums',
                // 'albums/index',
                'account/stores/albums/index',
                'Albums',
                [],
                'fas fa-images'
            ),
            // Store URLs
            self::STORE_DASHBOARD => $this->routeData(
                'account/stores/dashboard/index',
                'account/stores/dashboard/index',
                'Store Dashboard',
                [],
                'fas fa-tachometer-alt'
            ),
            self::STORE_PROFILE => $this->routeData(
                'account/stores/profile/index',
                'account/stores/profile/index',
                'Profile',
                [],
                'fas fa-store'
            ),
            self::STORE_CREATE => $this->routeData(
                'account/stores/profile/create',
                'account/stores/profile/create',
                'Create Store',
                [],
                'fas fa-plus'
            ),
            self::STORE_POSTS => $this->routeData(
                'account/stores/posts/index',
                'account/stores/posts/index',
                'Posts',
                [],
                'fas fa-newspaper'
            ),
            self::STORE_POSTS_ADD => $this->routeData(
                'account/stores/posts/create',
                'account/stores/posts/create',
                'Add Post',
                [],
                'fas fa-plus'
            ),
            self::STORE_POSTS_EDIT => $this->routeData(
                'account/stores/posts/edit/{id}',
                'account/stores/posts/edit',
                'Edit Post',
                ['id'],
                'fas fa-pencil-alt'
            ),
            self::STORE_ALBUMS => $this->routeData(
                'account/stores/albums',
                // 'albums/index',
                'account/stores/albums/index',
                'Albums',
                [],
                'fas fa-images'
            ),
            self::STORE_ALBUMS_ADD => $this->routeData(
                'account/stores/albums/add',
                'account/stores/albums/add',
                'Add Album',
                [],
            ),
            self::STORE_ALBUMS_EDIT => $this->routeData(
                'account/stores/albums/edit/{id}',
                'account/stores/albums/edit',
                'Edit Album',
                ['id'],
                'fas fa-pencil-alt'
            ),
            self::STORE_VIEW_PUBLIC => $this->routeData( // TODO - Confirm we need
                '{slug}',
                'store/public_view',
                'View Store',
                ['slug'] // required parameter
            ),

            // Admin URLs
            self::ADMIN_DASHBOARD => $this->routeData(
                'admin/dashboard/index',
                'admin/dashboard/index',
                'Admin Dashboard',
                [],
                'fas fa-tachometer-alt'
            ),

            self::ADMIN_USERS => $this->routeData(
                'admin/users/index',
                'admin/users/index',
                'Manage Users'
            ),
            self::ADMIN_POSTS => $this->routeData(
                'admin/posts/index',
                'account/stores/posts/index',
                'Posts',
                [],
                'fas fa-newspaper'
            ),
            self::ADMIN_ALBUMS => $this->routeData(
                'admin/albums/index',
                'account/stores/albums/index',
                'Albums',
                [],
                'fa-tachometer-alt'
            ),
        };
    }



    /**
     * Helper method to create route data without repeating empty params
     */
    private function routeData(
        string $path,
        string $view,
        string $label,
        array $params = [],
        ?string $icon = null
    ): array {
        $data = [
            'path' => $path,
            'view' => $view,
            'label' => $label,
        ];

        // Only add params key if there are actually parameters
        if (!empty($params)) {
            $data['params'] = $params;
        }

        // Only add icon if provided
        if ($icon !== null) {
            $data['icon'] = $icon;
        }

        return $data;
    }


    public function view(): string
    {
        $view = $this->data()['view'];
        return $view === '' ? 'home' : $view;
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




    public function label(): string
    {
        return $this->data()['label'];
    }



    public function toLinkData(?string $text = null, array $params = []): array
    {
        return [
            'url' => $this,
            'href' => $this->url($params),
            'text' => $text ?? $this->label(),
            'icon' => $this->icon(),
            'params' => $params,
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
                $urls[] = $case;
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
}
