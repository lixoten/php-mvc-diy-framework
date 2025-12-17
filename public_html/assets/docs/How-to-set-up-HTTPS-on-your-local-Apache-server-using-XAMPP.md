cd # The Goal
To setup 2 separate certificates for 2 virtual hosts, mvclixo.tv and testhttp.tv


# How to set up HTTPS on your local Apache server using XAMPP,
Follow these steps:

## Generate a Self-Signed SSL Certificate:
Open a terminal and navigate to the apache directory in your XAMPP installation. Run the following commands to generate a self-signed SSL certificate:
note: pay attention to crt, and key file,
```bash
cd D:/xampp/apache

"C:\Program Files\Git\usr\bin\openssl.exe" req -x509 -newkey rsa:2048 -keyout "D:/xampp/apache/mvc3.key" -out "D:/xampp/apache/mvc3.crt" -days 365 -nodes -subj "/CN=mvc3.tv" -addext "subjectAltName=DNS:mvc3.tv"

"C:\Program Files\Git\usr\bin\openssl.exe" req -x509 -newkey rsa:2048 -keyout "D:/xampp/apache/mvclixo.key" -out "D:/xampp/apache/mvclixo.crt" -days 365 -nodes -subj "/CN=mvclixo.tv" -addext "subjectAltName=DNS:mvclixo.tv"
```

===========================================================================
# Configure Apache to Use SSL

Edit the `httpd.conf` file and the `httpd-ssl.conf` file to configure Apache to use the SSL certificate.

## Edit `httpd.conf`

Open `httpd.conf` (located in `xampp/apache/conf/httpd.conf`) and ensure the following lines are uncommented:

```apache
LoadModule ssl_module modules/mod_ssl.so
Include conf/extra/httpd-ssl.conf
```

## Edit `httpd-ssl.conf`
Open `httpd-ssl.conf` (located in xampp/apache/conf/extra/httpd-ssl.conf) and update/add the following lines to point to your SSL certificate and key:
```apache
SSLCertificateFile "D:/xampp/apache/mvc3.crt"
SSLCertificateFile "D:/xampp/apache/testhttp.crt"

SSLCertificateKeyFile "D:/xampp/apache/mvc3.key"
SSLCertificateKeyFile "D:/xampp/apache/testhttp.key"
```

## Edit `httpd-vhosts.conf`
Open `httpd-vhosts.conf` (located in xampp/apache/conf/extra/httpd-vhosts.conf) and update / add:
note: 2 for each host, :80 and :443
```bash
### for mvc3 ################################################################
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
    DocumentRoot "D:\xampp\htdocs\mvc2\src\public_html"
    ServerName mvc2.tv
    SSLEngine on
    SSLCertificateFile "D:/xampp/apache/mvc3.crt"
    SSLCertificateKeyFile "D:/xampp/apache/mvc3.key"
</VirtualHost>
### for testhttp ############################################################
<VirtualHost *:80>
    DocumentRoot "D:/xampp/htdocs/testhttp/src/public_html"
    ServerName testhttp.tv
    <Directory "D:/xampp/htdocs/testhttp/src/public_html">
    </Directory>
</VirtualHost>
<VirtualHost *:443>
    DocumentRoot "D:\xampp\htdocs\testhttp\src\public_html"
    ServerName testhttp.tv
    SSLEngine on
    SSLCertificateFile "D:/xampp/apache/testhttp.crt"
    SSLCertificateKeyFile "D:/xampp/apache/testhttp.key"
</VirtualHost>
###################################################################
```

## Edit `.HOST`
Open `C:\Windows\System32\drivers\etc\hosts` and add what you need.
```bash
#127.0.0.1 farhan.cw
#127.0.0.1 booboo.cw
127.0.0.1 mvclogin.tv
127.0.0.1 mvc1.tv
127.0.0.1 mvc2.tv
127.0.0.1 mvc3.tv
127.0.0.1 testhttp.tv
```

## Restart xampp
Check Apache Error Logs after all done.
- Look at the Apache error logs for any SSL-related errors. The logs are located in xampp/apache/logs/error.log.

===========================================================================
# How to Use the MMC Console to Manage Certificates

In the MMC console, "expand" means to click on the small arrow or plus sign next to a folder or node to reveal its contents. Here are the steps:

1. Open the Run dialog (Win + R), type `mmc`, and press Enter.
2. In the MMC console, go to `File > Add/Remove Snap-in`.
3. Select `Certificates` and click `Add`.
4. Choose `Computer account` and click `Next`, then `Finish` and then `Ok`.
5. In the MMC console, you will see a tree structure on the left side. Find `Certificates (Local Computer)`.
6. Click on the small arrow or plus sign next to `Certificates (Local Computer)` to expand it.
7. Under `Certificates (Local Computer)`, find `Trusted Root Certification Authorities`.

8. Click on the small arrow or plus sign next to `Trusted Root Certification Authorities` to expand it.

9. Right-click on `Certificates` under `Trusted Root Certification Authorities`, select `All Tasks > Import`, and follow the wizard to import your `mvc3.crt` file.

10. Right-click on **AGAIN**`Certificates` under `Trusted Root Certification Authorities`, select `All Tasks > Import`, and follow the wizard to import your `testhttp.crt` file.

11. When prompted for file" select the d:\xampp\apache\server.crt. This is the self-signed certificate file that you need to add to the trusted root certificates.

12. Restart browser.






