## 1. Install XAMMP

## 2. Create DB if you need one

## 2a. Extra Steps vs code Favorite files
* copy .favorites.json, this contains some files i like to have at all time at hand, notes..etc...

## 3. Create your Virtual Host
* File : d:\xampp\apache\conf\extra\httpd-vhosts.conf
* Add the following :
```apache
#################################################################
<VirtualHost *:80>
    DocumentRoot "D:\xampp\htdocs\mvc3\src\public_html"
    ServerName mvc3.tv
    <Directory "D:\xampp\htdocs\mvc3\src\public_html">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
<VirtualHost *:443>
    DocumentRoot "D:\xampp\htdocs\mvc3\src\public_html"
    ServerName mvc3.tv
    SSLEngine on
    SSLCertificateFile "D:/xampp/apache/mvc3.crt"
   SSLCertificateKeyFile "D:/xampp/apache/mvc3.key"
</VirtualHost>
#################################################################
```

## 4. Update Hosts file (as administrator privileges)
* File : c:\Windows\System32\Drivers\etc\hosts
* Add the following :
```hosts
127.0.0.1 mvc3.tv
```

## 5. (optional) Set up HTTPS on your local Apache server using XAMPP
* Read this Doc for steps :
  - How-to-set-up-HTTPS-on-your-local-Apache-server-using-XAMPP.md

## 6. (optional )PHP XDEBUG.
* note: I added it, but commented it out, I turn it on only when I need to use it.
* Download and install... See step after this one.
* File : D:\xampp\php\php.ini
* Comment out or in
```apache
;zend_extension = xdebug
```
```apache
; [XDebug]
; zend_extension="D:/xampp/php/ext/php_xdebug.dll"
; xdebug.mode=debug
; xdebug.start_with_request=yes
; xdebug.client_port=9003
; xdebug.client_host=127.0.0.1
```

## 7. (optional )PHP XDEBUG VS Code Extension
* Install PHP Debug extension by Xdebug
* A launch.json configuration is needed to make XDebug work with VS Code. 
  - Click on the Run and Debug icon in the sidebar
  - Click "create a launch.json file" link
  - Select "PHP" from the environment options
  - VS Code will create a launch.json file with basic PHP debug configuration
There is a link that will do tis for ya

## 8. Composer
* initial setup
```
composer init
```
* update it whenever u change it
```
composer update
```
* make sure that autoload file got created
```
composer dump-autoload
```

## 7. Composer Project Prep
1. `autoload` - namespaces
- this is what i have now in this point it time...
```json
    ...
    "autoload": {
        "psr-4": {
            "App\\": "src/app/",
            "Core\\": "src/core/",
            "Tests\\": "Tests/",
            "Database\\": "src/Database/"
        }
    },
    ...
```
2. `require`
```json
    ...
    "require": {
        "php": "^8.2.12",
        "ext-pdo": "*",
        "php-di/php-di": "^7.0",
        "psr/log": "^3.0",
        "psr/http-message": "^2.0",
        "nyholm/psr7": "^1.8",
        "nyholm/psr7-server": "^1.1",
        "psr/http-server-handler": "^1.0",
        "psr/http-server-middleware": "^1.0",
        "vlucas/phpdotenv": "^5.6",
        "psr/event-dispatcher": "^1.0",
        "phpmailer/phpmailer": "^6.9"
    },
    ...
```
3. `require-dev` - code sniffer....make sure to composer update
```json
    ...
    "require-dev": {
        "squizlabs/php_codesniffer": "^3.11",
        "phpunit/phpunit": "^9.5"
    }
    ...
```


### PHP UNIT TEST

## 10. Other things to check
### error_log file location exists
* make sure error_log('hi there'); locations exists. It did not on my XAMPP setup, pain in the ass.
* to find location u can:
    * look in output of phpinfo();
    * `echo "Error log path: " . ini_get('error_log');`
    * id missing created `logs` in my case it was `D:\xampp\php\logs`


=========================================================
# Change to your project directory
cd D:\xampp\htdocs\mvclixo

# Optional: Add VFS Stream for filesystem testing
composer require --dev mikey179/vfsstream ^1.6

# Verify installation
vendor\bin\phpunit --version