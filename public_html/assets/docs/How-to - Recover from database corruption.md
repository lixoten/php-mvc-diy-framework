
# Steps to Restore MySQL from a Copied MySQL Folder:
* Download XAMPP zip - We want the **zip**
    - https://sourceforge.net/projects/xampp/files/ - in my case Windows
* Backup Existing Data (if any):
    - backup **mysql/data** folder to a safe location.
    -


* Stop MySQL Service
* Copy from the **zip** the **mysql** Folder and replace the one in `D:\xampp\mysql`
* From the data backup copy your database, it should be a folder in my case it was `mvclixotest`
into the `D:\xampp\mysql\data` folder.
* Also from the data backup copy the InnoDB data files (ibdata1, ib_logfile0, ib_logfile1, etc.)
into the `D:\xampp\mysql` folder.
* restart MySQL Service, and click **Admin**
* in phpmyadmin check you database, click on tables to see if data is there


* not an actual recover
`D:\xampp\mysql-recover`
- In here we have:
    - the downloaded zip file for `xampp`
    - and the extracted `mysql' folder that was extracted from the above zip-. This is the folder we will use.
Manuallt create the database `mvclixo` with collation: `utf8mb4_unicode_ci`
Now u can run console migrate