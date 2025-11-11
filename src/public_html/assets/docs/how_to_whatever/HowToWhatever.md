HowToWhatever.md

  D:\xampp\htdocs\my_projects\temp\HowToWhatever.md

  This Doc uses **Obsidian App** to manage it

 whatthefook

1. [What are we currently doing.](#what-are-we-currently-doing)
2. [Lets get started](#lets-get-started)
    1. [Start XAMPP with Admin Privileges](#start-xampp-with-admin-privileges)
    2. [Try Running the app](#try-running-the-app)
    3. [Login](#login)
3. [Quick Troubleshooting](#quick-troubleshooting)
    1. [Clear DNS Cache](#clear-dns-cache)
    2. [Steps to Restore MySQL from a Copied MySQL Folder](#steps-to-restore-mysql-from-a-copied-mysql-folder)
    3. [Clear VS-Code Cache](#clear-vs-code-cache)
4. [VS-CODE](#vs-code)
    1. [Exclude Files](#exclude-files)
    2. [Getting Around](#getting-around)
    3. [Understanding how files are matched:](#understanding-how-files-are-matched)
5. [Troubleshooting Doc](#troubleshooting-doc)
    1. [How to recover from XAMPP database corruption](#how-to-recover-from-xampp-database-corruption)
6. [How to Shit](#how-to-shit)
    1. [How-to-use-vscode-snippets](#how-to-use-vscode-snippets)
    2. [How-to-bring-in-favorites-json-to-new-project](#how-to-bring-in-favorites-json-to-new-project)
    3. [vs code setup and tips.md](#vs-code-setup-and-tipsmd)
7. [Application Processes](#application-processes)
    1. [MVC Migrations - Complete Reference Guide](#mvc-migrations---complete-reference-guide)
        1. [Quick Steps to create a new field recreate ble with new data](#quick-steps-to-create-a-new-field-recreate-ble-with-new-data)
8. [Apache Server](#apache-server)
9. [XAMPP - Recover](#xampp---recover)
10. [Run Composer](#run-composer)
11. [File-Program Locations](#file-program-locations)
     1. [hosts](#hosts)
     2. [Terminal History](#terminal-history)
12. [Terminal History](#terminal-history-1)
13. [Abbreviations](#abbreviations)
14. [Ideas](#ideas)
15. [Redirect URLS](#redirect-urls)
16. [Start here](#start-here)
     1. [External Doc 'whatever\_notes'](#external-doc-whatever_notes)
     2. [**Current Status:**](#current-status)
     3. [**Missing View Helpers:**](#missing-view-helpers)
         1. [**1. BreadcrumbHelper**](#1-breadcrumbhelper)
         2. [**2. PaginationHelper**](#2-paginationhelper)
         3. [**3. ActionButtonHelper**](#3-actionbuttonhelper)
         4. [**4. AlertHelper**](#4-alerthelper)
     4. [**Should We Create These View Helpers Now?**](#should-we-create-these-view-helpers-now)


# What are we currently doing.
---
---
---
---
---

# Lets get started

## Start XAMPP with Admin Privileges
  - Start Apache
  - Start MySql

## Try Running the app
  - http://localhost/
  - http://mvclixo.tv/

## Login
  - admin / password123   - admin
  - storeadmin / password123   - admin | store owner
  - storejohn / q1Q! - store owner


<br><br><br><br>

# Quick Troubleshooting

## Clear DNS Cache
  - `ipconfig /flushdns`
  - make sure in `hosts` you have
      - 127.0.0.1  mvclixo.tv
      - 127.0.0.1  www.mvclixo.tv

## Steps to Restore MySQL from a Copied MySQL Folder

## Clear VS-Code Cache
  - Run dialog (**`Windows Key + R`**) and typing `%APPDATA%`
  - navigating to the `Code` folder.
  - Delete content of each folder:
    - `%APPDATA%\Code\Cache`
    - `%APPDATA%\Code\CachedData`
    - `%APPDATA%\Code\CachedExtensionVSIXs`



<br><br><br><br>

# VS-CODE
Understanding Settings.json

## Exclude Files
  - Apply to all 3 of these and also add it to **.gitignore**

    ```
    "files.exclude": {
        "**/folder01": true,
        //...
    },
    "search.exclude": {
        "**/folder01": true,
        //...
    },
    "search.watcherExclude": {
        "**/folder01": true,
        //...
    }
    ```
## Getting Around
    // FindIt Entry Point of thing i wanna go to
    // Fik - asdsdf

## Understanding how files are matched:

  - `"**/folder01": true,`
      - For example, this will hide:
        - `folder01/`
        - `src/folder01/`
        - `dist/js/folder01/`

  - `"folder01": true,`
     - For example, this will hide:
         - `folder01/`
     - This will not hide:
         - `src/folder01/`
         - `dist/js/folder01/`

  - In most cases, the (**/) glob pattern is the most flexible and widely used option because it ensures the folder is hidden regardless of its location.
  - **Note** Search options in vs-code is located  Left side on Ctrl+Shft+f
      - In *files to include*, click *Use Exclude Setting*, if u want to use Settings.json
      - Ese you can type in that u want to exclude as in `*old.php, *xxxx.php,  **/old_shit/**`.


<br><br><br><br>

# Troubleshooting Doc

## [How to recover from XAMPP database corruption](<How-to - Recover from database corruption.md>)

- The App as off 7/28/2025

<br><br><br><br>

# How to Shit

## [How-to-use-vscode-snippets](<How-to-use-vscode-snippets.md>)

## [How-to-bring-in-favorites-json-to-new-project](<How-to-bring-in-favorites-json-to-new-project.md>)

## [vs code setup and tips.md](<vs code setup and tips.md>)


# Application Processes

## [MVC Migrations - Complete Reference Guide](<MVC Migrations - Complete Reference Guide.md>)
### Quick Steps to create a new field recreate ble with new data
1. Make sure database is exists.  `mvclixo`
    - HeidiSQL
        - drop DB and recreate it with collation `utf8mb4_unicode_ci`
2. Prep Folder and Files
    - in `src\Database\Migrations`
        - **do not delete**
            - `20251101_000003_CreateRateLimitAttemptsTable.php`
            - `99999999_999999_CreateRememberTokenTable.php`
        - Delete all others as in create testy, user, store
    - delete folder 'Generated', it will be recreated automatically
3. Edit all the file u need as in create, seed and schema

4. Lets create migration files... ex: `20251108_103708_CreateUserTable.php`
    - Run all:
        - `php bin/console.php make:migration user`
        - `php bin/console.php make:migration store`
        - `php bin/console.php make:migration testy`
    - outputs to:
        - `src\Generated\User\20251108_110116_CreateUserTable.php`
        - `src\Generated\Store\20251108_110135_CreateStoreTable.php`
        - `src\Generated\Testy\20251108_110142_CreateTestyTable.php`

5. Lets create seeder files... ex: `20251108_110524_UserSeeder`
    - Run all:
        - `php bin/console.php make:seeder user`
        - `php bin/console.php make:seeder store`
        - `php bin/console.php make:seeder testy`
    - outputs to:
        - `src\Generated\User\20251108_110524_UserSeeder.php`
        - `src\Generated\User\20251108_110713_StoreSeeder.php`
        - `src\Generated\User\20251108_110737_TestySeeder.php`

6. Lets move the files... ex: `20251`
    - Run all:
        - `php bin/console.php feature:move user`
        - `php bin/console.php feature:move store`
        - `php bin/console.php feature:move testy`
    - It moves the files"
        - From: `src/Generated/User/` <<<< User, Store, Testy
        - To:   `src/Database/Migrations/`
        - To:   `src/Database/Seeders\/`

7. Next we nee to run `migrate` or `migrate:one`
    - `php bin/console.php migrate`
        - This will rum all migration files,..... will run the creates to create tables
    - `php bin/console.php migrate:one 20251102_084221_CreateUserTable`
        - This will run a SINGLE migration files. You will need full file name.
        - if you use 'one' make sure to run the rest individually too


8. Lets create migration files... ex: `20251`
    - Run all:
        - `php`
    - outputs to:
        - `srcGenerate`
        - `srcGenerate`
        - `srcGenerate`


5. Update Create Table
    - I modified `src\Database\Migrations\004_CreateTestysTable.php`
        - In this case i added 2 new columns
```php
$table->date('date_of_birth')->nullable()->comment('Date of Birth');
$table->string('telephone', 30)->nullable()->comment('Telephone number');
```
1. Update Seeder
    - I modified `src\Database\Seeders\Test
        - In this case for each record i added the 2 columns for, i created data
        - If adding new make sure slug is unique
```php
'generic_text' => 'Hello',
'date_of_birth' => '1990-01-01',
```

1. I backed up my table....just incase

2. Open Terminal and run
    - Run to populate data. this drops the table and recreates it
        - `php bin/console.php migrate:one 'Database\Migrations\CreateTestysTable' --force`
    - Run to populate data in seeder.
        - `php bin/console.php seed TestysSeeder`

3. Entity - `src\App\Entities\Testy.php`

4. Update REPOS `src\App\Repository\TestyRepository.php`
    - create
    - update
    - mapToEntity
    - toArray

5. src\Config\view_options\testys_edit.php
  - add field to `'form_fields' => [` to see it and test it

6. src\Config\list_fields\testys_edit.php
   - add the new field with all of it's attributes

// todo fff
Validation adding

- src\Core\Form\Validation\Validator.php
- new validator in rules folder - src\Core\Form\Validation\Rules
  - Example DateValidator.php
- new field type - src\Core\Form\Field\Type\DateType.php
- in dependencies.php
    - // Field Types                    - must
    - // Field Type Registry            - must
    - // Register the ValidatorRegistry - if needed
    - // Static Single-Field Validators - if needed
- in Bootstrap->renderField()
    - add new `switch ($type) {` for it.



# Apache Server

 - ## [How-to-set-up-HTTPS-on-your-local-Apache-server-using-XAMPP](<How-to-set-up-HTTPS-on-your-local-Apache-server-using-XAMPP.md>)

# XAMPP - Recover

- ## [Xampp - database corruption.md](<How-to - Recover from database corruption.md>)

---

---

---

# Run Composer

```bash
# autoload
composer dump-autoload

# autoload optimized
composer dump-autoload -o
# The -o or --optimize flag is used to optimize the autoloader by converting PSR-4 and PSR-0 autoloads into a class map.
```

---

---

---

---

---

---

# File-Program Locations

## hosts

- The hosts file in Windows is located at `C:\Windows\System32\drivers\etc\hosts`

## Terminal History

- # Powershell

---

---

---

# Terminal History

- # Powershell
    - `C:\Users\rudyt\AppData\Roaming\Microsoft\Windows\PowerShell\PSReadline\ConsoleHost_history.txt`
- # Git Bash ()
    - `C:\Users\rudyt\.bash_history`
    - For History Save git bash
        - `history -a`

---

---

---

# Abbreviations

- ## acr - AbstractColumnRegistry
- ## ecfg - From Config value

---

---

---

# Ideas

---

---

---

# Redirect URLS

Generate a URL from controller, action and parameters

```php
// For home page
return $this->redirect($this->url('Home'));

// For account page
return $this->redirect($this->url('Profile', 'index', [], 'Account'));

// For editing a post
return $this->redirect($this->url('Posts', 'edit', ['id' => 42]));
```

- See : D:\xampp\htdocs\my_projects\temp\GenerateURL.md

---

---

---

# Start here

- ## Lost no idea
- XAMPP Control Panel - **Run as Administrator**
    - `Start Apache`
    - `Start MySQL`
- Database name
    - `mvclixotest`, this can change but as of 5/31/2025 is what i am using

## External Doc 'whatever_notes'

- "D:\Documents\whatever_notes.rtf"

    -----------------------------------------

    -----------------------------------------

    -----------------------------------------

    -----------------------------------------

    -----------------------------------------

    -----------------------------------------

    -----------------------------------------

    -----------------------------------------

    -----------------------------------------

    -----------------------------------------

    -----------------------------------------

    -----------------------------------------

    -----------------------------------------

    -----------------------------------------

    -----------------------------------------

    -----------------------------------------

    -----------------------------------------

    -----------------------------------------

    -----------------------------------------

    -----------------------------------------

    **YES! You're absolutely right!** 🎯

**We focused on the core LinkBuilderService and menu integration, but we haven't created the View Helpers yet.**

## **Current Status:**

✅ **LinkBuilderService** - Core service complete

✅ **Menu Integration** - Working with LinkBuilderService

❌ **View Helpers** - Not created yet

## **Missing View Helpers:**

### **1. BreadcrumbHelper**

```php
// Usage in views:
<?= BreadcrumbHelper::render([
    Url::CORE_HOME => 'Home',
    Url::ACCOUNT_DASHBOARD => 'Dashboard',
    null => 'Current Page'  // Current page (no link)
]) ?>
```

### **2. PaginationHelper**

```php
// Usage in views:
<?= PaginationHelper::render(Url::STORE_POST, $currentPage, $totalPages) ?>
```

### **3. ActionButtonHelper**

```php
// Usage in views:
<?= ActionButtonHelper::edit(Url::STORE_POST_EDIT, ['id' => $post->getId()]) ?>
<?= ActionButtonHelper::delete(Url::STORE_POST_DELETE, ['id' => $post->getId()]) ?>
<?= ActionButtonHelper::create(Url::STORE_POST_ADD, 'Add New Post') ?>
```

### **4. AlertHelper**

```php
// Usage in views:
<?= AlertHelper::success('Post saved successfully!') ?>
<?= AlertHelper::error('Validation failed!') ?>
```

## **Should We Create These View Helpers Now?**

**These helpers would all use your LinkBuilderService internally for consistent HTML generation.**

**Which helpers would be most useful for your current development?**

1. **Breadcrumbs** - For navigation context
2. **Pagination** - For post/album listings
3. **Action Buttons** - For CRUD operations
4. **All of them** - Complete view helper system

**Your menu integration is solid - let's build the view helpers next!** 🚀

**Which helper should we start with?**