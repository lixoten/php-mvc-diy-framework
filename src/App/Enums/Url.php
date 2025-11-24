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

    case CORE_USER;
    case CORE_USER_LIST;
    case CORE_USER_CREATE;
    case CORE_USER_EDIT;
    case CORE_USER_VIEW;
    case CORE_USER_DELETE;
    case CORE_USER_DELETE_CONFIRM;

    // - 6
    case CORE_TESTY;
    case CORE_TESTY_LIST;
    case CORE_TESTY_CREATE;
    case CORE_TESTY_EDIT;
    case CORE_TESTY_VIEW;
    case CORE_TESTY_DELETE;
    case CORE_TESTY_DELETE_CONFIRM;

    //--
    case CORE_TESTY_PLACEHOLDER;
    case CORE_TESTY_LINKDEMO;
    case CORE_TESTY_TESTLOGGER;
    case CORE_TESTY_TESTFORMATTER;
    case CORE_TESTY_TESTSESSION;
    case CORE_TESTY_TESTDATABASE;
    case CORE_TESTY_EMAILTEST;
    case CORE_TESTY_PAGINATION_TEST;

    // - 6
    case CORE_GALLERY;
    case CORE_GALLERY_LIST;
    case CORE_GALLERY_CREATE;
    case CORE_GALLERY_EDIT;
    case CORE_GALLERY_VIEW;
    case CORE_GALLERY_DELETE;
    case CORE_GALLERY_DELETE_CONFIRM;

    // - 6
    case CORE_IMAGE;
    case CORE_IMAGE_LIST;
    case CORE_IMAGE_CREATE;
    case CORE_IMAGE_EDIT;
    case CORE_IMAGE_VIEW;
    case CORE_IMAGE_DELETE;

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

    case CORE_ALBUMS;
    case CORE_ALBUMS_CREATE;
    case CORE_ALBUMS_EDIT;
    case CORE_ALBUMS_VIEW;
    case CORE_ALBUMS_DELETE;
    case STORE_VIEW_PUBLIC; // TODO confirm we need for future use

    // Admin URLs
    case ADMIN_DASHBOARD;
    case ADMIN_USERS;

    case BASE_ADMIN;
    case BASE_ACCOUNT;
    case BASE_STORE;



    /**
     * Helper to derive a translation key for common CRUD actions based on enum case name.
     *
     * Example: CORE_TESTY_DELETE -> 'testy.button.text.delete'
     */
    public function buildTranslationKeyForCrudAction(string $entityName, string $action): string // fixme we do not need $entityName
    {
        // return "{$entityName}.button.{$action}";
        return "button.{$action}";
    }


    /**
     * Helper to derive a translation key for common CRUD actions based on enum case name.
     *
     * Example: CORE_TESTY_DELETE -> 'testy.button.text.delete'
     */
    public function extraEntity(): array
    {
        // Example: $this->name could be 'CORE_TESTY_DELETE'
        // We want to extract 'testy' and 'delete'
        $parts = explode('_', $this->name);

        // This pattern expects CORE_{ENTITY_NAME}_{ACTION_NAME}
        if (count($parts) >= 3 && $parts[0] === 'CORE') {
            $entityName = strtolower($parts[1]); // e.g., 'testy' from 'TESTY'
            $action = strtolower($parts[2]);    // e.g., 'delete' from 'DELETE'

            // For now, assuming simple actions like 'delete', 'create', 'edit', 'view'
            // You can refine this logic if actions also have sub-segments (e.g., 'delete_confirm')
            return ['entity' => $entityName, 'action' => $action];
        }

        // Fallback for cases that do not match the expected CRUD pattern.
        // Returning the lowercased enum name itself, or you might throw an exception.
        return [];
    }




    /**
     * Get URL data as an array with all properties
     */
    public function data(): array
    {
        $rrr = $this->extraEntity();

        $entity = '';
        $action = '';
        if (isset($rrr['entity'])) {
            $entity = $rrr['entity'];
        }
        if (isset($rrr['action'])) {
            $action = $rrr['action'];
        }

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
                'store/',
                'store/',
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
                'login/login',
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
                'menu.home',
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
                // 'Test',
                'menu.test',
                [],
                null,
            ),

            self::CORE_ABOUT => $this->routeData(
                'about/index',
                'about/index',
                'index',
                // 'About',
                'menu.about',
                [],
                null,
            ),
            self::CORE_CONTACT => $this->routeData(
                'contact/index',
                'contact/index',
                'index',
                // 'Contact',
                'menu.contact',
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
                // 'User Dashboard',
                'menu.user_dashboard',
                [],
            ),
            self::ACCOUNT_PROFILE => $this->routeData(
                'account/profile/index',
                'account/profile/index',
                'index',
                // 'Profile',
                'menu.user_profile',
                [],
            ),
            self::ACCOUNT_MYNOTES => $this->routeData(
                'account/mynotes/index',
                'account/mynotes/index',
                'index',
                // 'Notes',
                'menu.user_notes',
                [],
            ),



            //////MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM
            //////MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM
            //////MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM
            //////MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM
            //////MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM
            self::CORE_USER => $this->routeData(
                'user',
                'user/index',
                'index',
                'User',
                [],
            ),
            self::CORE_USER_LIST => $this->routeData(
                'user/list',
                'user/list',
                'list',
                // 'User List',
                'menu.user_list',
                [],
            ),
            self::CORE_USER_CREATE => $this->routeData(
                'user/create',
                'user/create',
                'create',
                'Create User',
                [],
            ),
            self::CORE_USER_EDIT => $this->routeData(
                'user/edit/{id}',
                'user/edit',
                'edit',
                'Edit User',
                ['id'],
            ),

            self::CORE_USER_VIEW => $this->routeData(
                'user/view/{id}',
                'user/view',
                'view',
                'VIEW User',
                ['id'],
            ),
            self::CORE_USER_DELETE => $this->routeData(
                'user/delete/{id}',
                'user/delete',
                'delete',
                'Delete User',
                ['id'],
            ),
            self::CORE_USER_DELETE_CONFIRM => $this->routeData(
                'user/delete/{id}/confirm',
                'user/delete_confirm',
                'deleteConfirm',
                'Confirm Delete User',
                ['id'],
            ),

            //////MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM
            //////MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM
            self::CORE_TESTY => $this->routeData(
                'testy',
                'testy/list',
                'index',
                // 'Testy',
                'menu.testy',
                [],
            ),
            self::CORE_TESTY_LIST => $this->routeData(
                // 'testy/list',
                // 'testy/list',
                // 'list',
                // 'Testy',
                "$entity/$action",
                "$entity/$action",
                $action,
                $this->buildTranslationKeyForCrudAction($entity, $action),
                [],
            ),
            self::CORE_TESTY_CREATE => $this->routeData(
                // 'testy/create',
                // 'testy/create',
                // 'create',
                // 'Create Testy',
                "$entity/$action",
                "$entity/$action",
                $action,
                $this->buildTranslationKeyForCrudAction($entity, $action),
                [],
            ),
            self::CORE_TESTY_EDIT => $this->routeData(
                // 'testy/edit/{id}',
                // 'testy/edit',
                // 'edit',
                // 'Edit Testy',
                "$entity/$action/{id}",
                "$entity/$action",
                $action,
                $this->buildTranslationKeyForCrudAction($entity, $action),
                ['id'],
            ),

            self::CORE_TESTY_VIEW => $this->routeData(
                // 'testy/view/{id}',
                // 'testy/view',
                // 'view',
                // 'VIEW Testy',
                "$entity/$action/{id}",
                "$entity/$action",
                $action,
                $this->buildTranslationKeyForCrudAction($entity, $action),
                ['id'],
            ),
            self::CORE_TESTY_DELETE => $this->routeData(
                // 'testy/delete/{id}',
                // 'testy/delete',
                // 'delete',
                "$entity/$action/{id}",
                "$entity/$action",
                $action,
                // 'Delete Testy',
                $this->buildTranslationKeyForCrudAction($entity, $action),
                ['id'],
            ),
            self::CORE_TESTY_DELETE_CONFIRM => $this->routeData(
                'testy/delete/{id}/confirm',
                'testy/delete_confirm',
                'deleteConfirm',
                'Confirm Delete Testy',
                ['id'],
            ),
            //////MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM
            //////MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM
            self::CORE_ALBUMS => $this->routeData(
                'albums',
                'albums/index',
                'index',
                'Testy',
                [],
            ),
            // self::CORE_ALBUMS_LIST => $this->routeData(
            //     'testy/list',
            //     'testy/list',
            //     'list',
            //     'Testy',
            //     [],
            // ),\
            self::CORE_ALBUMS_CREATE => $this->routeData(
                'albums/create',
                'albums/create',
                'create',
                'Create Testy',
                [],
            ),
            self::CORE_ALBUMS_EDIT => $this->routeData(
                'albums/edit/{id}',
                'albums/edit',
                'edit',
                'Edit Testy',
                ['id'],
            ),

            self::CORE_ALBUMS_VIEW => $this->routeData(
                'albums/view/{id}',
                'albums/view',
                'view',
                'VIEW Testy',
                ['id'],
            ),
            self::CORE_ALBUMS_DELETE => $this->routeData(
                'albums/delete/{id}',
                'albums/delete',
                'albums',
                'Delete album',
                ['id'],
            ),


            ///////////////////////////////////////////////////////////////////////////////
            ///////////////////////////////////////////////////////////////////////////////
            ///////////////////////////////////////////////////////////////////////////////
            /// GALLERY /////////////////////////////////////////////////////////////////////
            //////MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM
            //////MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM
            //////MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM
            //////MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM
            //////MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM
            self::CORE_GALLERY => $this->routeData(
                'gallery',
                'gallery/index',
                'index',
                'Gallery',
                [],
            ),
            self::CORE_GALLERY_LIST => $this->routeData(
                'gallery/list',
                'gallery/list',
                'list',
                'Gallery',
                [],
            ),
            self::CORE_GALLERY_CREATE => $this->routeData(
                'gallery/create',
                'gallery/create',
                'create',
                'Create Gallery',
                [],
            ),
            self::CORE_GALLERY_EDIT => $this->routeData(
                'gallery/edit/{id}',
                'gallery/edit',
                'edit',
                'Edit Gallery',
                ['id'],
            ),

            self::CORE_GALLERY_VIEW => $this->routeData(
                'gallery/view/{id}',
                'gallery/view',
                'view',
                'VIEW Gallery',
                ['id'],
            ),
            self::CORE_GALLERY_DELETE => $this->routeData(
                'gallery/delete/{id}',
                'gallery/delete',
                'delete',
                'Delete Gallery',
                ['id'],
            ),
            self::CORE_GALLERY_DELETE_CONFIRM => $this->routeData(
                'gallery/delete/{id}/confirm',
                'gallery/delete_confirm',
                'deleteConfirm',
                'Confirm Delete Gallery',
                ['id'],
            ),


            ///////////////////////////////////////////////////////////////////////////////
            ///////////////////////////////////////////////////////////////////////////////
            ///////////////////////////////////////////////////////////////////////////////
            /// IMAGE /////////////////////////////////////////////////////////////////////
            //////MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM
            //////MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM
            //////MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM
            //////MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM
            //////MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM
            self::CORE_IMAGE => $this->routeData(
                'image',
                'image/index',
                'index',
                'Image',
                [],
            ),
            self::CORE_IMAGE_LIST => $this->routeData(
                'image/list',
                'image/list',
                'list',
                'Image',
                [],
            ),
            self::CORE_IMAGE_CREATE => $this->routeData(
                'image/create',
                'image/create',
                'create',
                'Create Image',
                [],
            ),
            self::CORE_IMAGE_EDIT => $this->routeData(
                'image/edit/{id}',
                'image/edit',
                'edit',
                'Edit Image',
                ['id'],
            ),

            self::CORE_IMAGE_VIEW => $this->routeData(
                'image/view/{id}',
                'image/view',
                'view',
                'VIEW Image',
                ['id'],
            ),
            self::CORE_IMAGE_DELETE => $this->routeData(
                'image/delete/{id}',
                'image/delete',
                'delete',
                'Delete Image',
                ['id'],
            ),



            /// IMAGE /////////////////////////////////////////////////////////////////////
            ///////////////////////////////////////////////////////////////////////////////
            ///////////////////////////////////////////////////////////////////////////////
            ///////////////////////////////////////////////////////////////////////////////




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
            self::CORE_TESTY_TESTFORMATTER => $this->routeData(
                'testy/testformatter',
                'testy/testformatter',
                'testformatter',
                'formatter'
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









            //////MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM
            //////MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM
            //////MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM
            //////MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM
            //////MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM
            //////MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM


            ######################################################################
            // Store URLs
            self::STORE_DASHBOARD => $this->routeData(
                'store/dashboard/index',
                'store/dashboard/index',
                'index',
                // 'Store Dashboard2',
                'menu.store_dashboard',
                [],
            ),
            self::STORE_PROFILE => $this->routeData(
                'store/profile/index',
                'store/profile/index',
                'index',
                // 'Profile',
                'menu.store_profile',
                [],
            ),
            self::STORE_SETTINGS => $this->routeData(
                'store/settings/index',
                'store/settings/index',
                'index',
                // 'Settings',
                'menu.store_settings',
                [],
            ),
            self::STORE_CREATE => $this->routeData(
                'store/profile/create',
                'store/profile/create',
                'index',
                'Create Store',
                [],
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
                // 'Admin Dashboard',
                'menu.admin_dashboard',
                [],
            ),
            self::ADMIN_USERS => $this->routeData(
                'admin/user/index',
                'admin/user/index',
                'index',
                // 'Manage Users',
                'menu.admin_dashboard',
                [],
            ),
        };

        return $foo;
    }


    /**
     * Helper to derive a translation key for common CRUD actions based on enum case name.
     *
     * Example: CORE_TESTY_DELETE -> 'testy.button.text.delete'
     */
    private function buildTranslationKeyForCrudActionOld(): string
    {
        // Example: $this->name could be 'CORE_TESTY_DELETE'
        // We want to extract 'testy' and 'delete'
        $parts = explode('_', $this->name);

        // This pattern expects CORE_{ENTITY_NAME}_{ACTION_NAME}
        if (count($parts) >= 3 && $parts[0] === 'CORE') {
            $entityName = strtolower($parts[1]); // e.g., 'testy' from 'TESTY'
            $action = strtolower($parts[2]);    // e.g., 'delete' from 'DELETE'

            // For now, assuming simple actions like 'delete', 'create', 'edit', 'view'
            // You can refine this logic if actions also have sub-segments (e.g., 'delete_confirm')
            // return "{$entityName}.button.text.{$action}";
            return "button.text.{$action}";
        }

        // Fallback for cases that do not match the expected CRUD pattern.
        // Returning the lowercased enum name itself, or you might throw an exception.
        return strtolower($this->name);
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
        } elseif (str_starts_with($this->name, "GENERIC_")) {
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


    public function url(array $params = [], string $routeType = null): string
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

        // fixme shit

        if (isset($routeType) && ($routeType === 'account' || $routeType === 'store')) {
            // return $routeType . '/' . $url;
            return '/' . $routeType . $url;
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

    public function pageKey(): string
    {
        $pageKey = $this->data()['view'];
        $pageKey = str_replace('/', '_', $pageKey);
        return $pageKey;
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
        ?array $action = null,
        string $routeType = null
    ): array {
        return [
            'url' => $this->url($params, $routeType),
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
     * Generate a pagination URL for this route, supporting path-based page numbers
     * and optional query parameters.
     *
     * example: $template = Url::CORE_TESTY_LIST->paginationUrl(); // /testy/list/page/{page}
     *        : $page3 = Url::CORE_TESTY_LIST->paginationUrl(3); // /testy/list/page/3
     *        : $page3WithFilter = Url::CORE_TESTY_LIST->paginationUrl(3, ['filter' => 'active']);
     *                             // /testy/list/page/3?filter=active
     *
     * @param int|null $page The current page number (null for template with {page} placeholder)
     * @param array<string, mixed> $queryParams Additional query parameters to append.
     * @return string The pagination URL
     */
    public function paginationUrl(?int $page = null, array $queryParams = []): string
    {
        $baseUrl = $this->url([]);  // Empty array = no query params

        // Append the page segment. Ensure no double slashes.
        $paginationUrl = rtrim($baseUrl, '/') . '/page/' . ($page ?? '{page}');

        if (!empty($queryParams)) {
            $paginationUrl .= '?' . http_build_query($queryParams);
        }

        return $paginationUrl;
    }

    /**
     * Get pagination URL and label as an array suitable for pagination controls
     *
     * @param int|null $page The page number (null for template)
     * @param array<string, mixed> $queryParams Additional query parameters to append.
     * @return array<string, mixed> Array with 'url', 'label', and 'page' keys
     */
    public function toPaginationItem(?int $page = null, array $queryParams = []): array
    {
        return [
            'url' => $this->paginationUrl($page, $queryParams), // Pass queryParams here
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
// 1257 1123 1404 1336 1107
