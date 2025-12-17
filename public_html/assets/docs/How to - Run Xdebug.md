# How to run XDEBUG

## install XDEBUG
1. run php -i and place output in clipboard
- OR copy the outpot if u ran a script with phpinfo() and place content in clipboard

2. Go to https://xdebug.org/wizard
- Paste clickboard content tinto wizard and submit.
- Follow instructions

## Update php.ini
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

## PHP XDEBUG VS Code Extension
* Install PHP Debug extension by Xdebug
* A launch.json configuration is needed to make XDebug work with VS Code. 
  - Click on the Run and Debug icon in the sidebar
  - Click "create a launch.json file" link
  - Select "PHP" from the environment options
  - VS Code will create a launch.json file with basic PHP debug configuration
There is a link that will do this for ya

## How to run debug  
1. Make sure RESTART Apache after any changes to php.ini.
2. Set breakpoints by clicking in the gutter (left of the line numbers).
3. Start the debugger:
    - Go to the Run and Debug view. (click on debug...on the right icon menu)
4. Select "Listen for Xdebug" 
5. click Start Debugging (or press F5).
6. Open your PHP application in the browser (e.g., http://localhost/index.php). If everything is set up correctly, VS Code will pause execution at the breakpoints, allowing you to debug.