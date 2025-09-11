HowToWhatever.md

  D:\xampp\htdocs\my_projects\temp\HowToWhatever.md

  This Doc uses **Obsidian App** to manage it

 whatthefook

- [What are we currently doing.](#what-are-we-currently-doing)
- [Lets get started](#lets-get-started)
  - [Start XAMPP with Admin Privileges](#start-xampp-with-admin-privileges)
  - [Try Running the app](#try-running-the-app)
  - [Login](#login)
- [Quick Troubleshooting](#quick-troubleshooting)
  - [Clear DNS Cache](#clear-dns-cache)
  - [Steps to Restore MySQL from a Copied MySQL Folder](#steps-to-restore-mysql-from-a-copied-mysql-folder)
  - [Clear VS-Code Cache](#clear-vs-code-cache)
- [VS-CODE](#vs-code)
  - [Excluse Files](#excluse-files)
  - [Understanding how files are matched:](#understanding-how-files-are-matched)
- [Troubleshooting Doc](#troubleshooting-doc)
  - [How to recover from XAMPP database corruption](#how-to-recover-from-xampp-database-corruption)
- [How to Shit](#how-to-shit)
  - [How-to-use-vscode-snippets](#how-to-use-vscode-snippets)
  - [How-to-bring-in-favorites-json-to-new-project](#how-to-bring-in-favorites-json-to-new-project)
  - [vs code setup and tips.md](#vs-code-setup-and-tipsmd)
- [Application Processes](#application-processes)
- [Apache Server](#apache-server)
- [XAMPP - Recover](#xampp---recover)
- [Run Composer](#run-composer)
- [File-Program Locations](#file-program-locations)
  - [hosts](#hosts)
  - [Terminal History](#terminal-history)
- [Terminal History](#terminal-history-1)
- [Abbreviations](#abbreviations)
- [Ideas](#ideas)
- [Redirect URLS](#redirect-urls)
- [Start here](#start-here)
  - [External Doc 'whatever\_notes'](#external-doc-whatever_notes)
  - [**Current Status:**](#current-status)
  - [**Missing View Helpers:**](#missing-view-helpers)
    - [**1. BreadcrumbHelper**](#1-breadcrumbhelper)
    - [**2. PaginationHelper**](#2-paginationhelper)
    - [**3. ActionButtonHelper**](#3-actionbuttonhelper)
    - [**4. AlertHelper**](#4-alerthelper)
  - [**Should We Create These View Helpers Now?**](#should-we-create-these-view-helpers-now)


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

## Excluse Files
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

- ## [MVC Migrations - Complete Reference Guide](<MVC Migrations - Complete Reference Guide.md>)

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
<?= PaginationHelper::render(Url::STORE_POSTS, $currentPage, $totalPages) ?>
```

### **3. ActionButtonHelper**

```php
// Usage in views:
<?= ActionButtonHelper::edit(Url::STORE_POSTS_EDIT, ['id' => $post->getId()]) ?>
<?= ActionButtonHelper::delete(Url::STORE_POSTS_DELETE, ['id' => $post->getId()]) ?>
<?= ActionButtonHelper::create(Url::STORE_POSTS_ADD, 'Add New Post') ?>
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