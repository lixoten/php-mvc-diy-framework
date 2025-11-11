@echo off
REM make_and_move_migrations.bat
SET PHP_BIN=php
SET CONSOLE_SCRIPT=bin/console.php
SET GENERATED_DIR=src/Generated
SET MIGRATIONS_DIR=src/Database/Migrations
SET SEEDERS_DIR=src/Database/Seeders

SET entity=user
SET Entity=User

@REM DYNAMIC PATHS based on entity
SET ENTITY_CONFIG_DIR=src/App/Features/%Entity%/Config
SET ENTITY_DIR=src/App/Features/%Entity%


echo.
echo --- Cleaning Generated Files ---
echo.

@REM Delete generated directory if it exists, with prompt for each file/subdir and then the directory itself
IF EXIST "%GENERATED_DIR%\" RMDIR /S "%GENERATED_DIR%"
echo.

@REM Delete 'entity' migration files if they exist, with prompt for each matching file
SET MYFILE="%MIGRATIONS_DIR%\*Create%Entity%Table.php"
SET FILETYPE=Seeder

IF EXIST %MYFILE% (
    CHOICE /C YNQ /M "%FILETYPE% Delete %MYFILE%?"
    IF %ERRORLEVEL% EQU 1 (
        echo %Entity% chose YES. %FILETYPE% - Deleting entity file...
        DEL %MYFILE%
        echo Successfully deleted %FILETYPE% files matching %MYFILE%.
    ) ELSE IF %ERRORLEVEL% EQU 2 (
        echo %Entity% chose NO. %FILETYPE% - Delete %MYFILE%.
    ) ELSE IF %ERRORLEVEL% EQU 3 (
        echo %Entity% chose QUIT. Exiting script.
        EXIT /B
    )
    echo.
) ELSE (
    echo No %FILETYPE% files matching %MYFILE% found. Skipping deletion.
    echo.
)





@REM Delete 'entity' seeder files if they exist, with prompt for each matching file
SET MYFILE="%SEEDERS_DIR%\*%Entity%Seeder.php"
SET FILETYPE=Seeder

IF EXIST %MYFILE% (
    CHOICE /C YNQ /M "%FILETYPE% Delete %MYFILE%?"
    IF %ERRORLEVEL% EQU 1 (
        echo %Entity% chose YES. %FILETYPE% - Deleting entity file...
        DEL %MYFILE%
        echo Successfully deleted %FILETYPE% files matching %MYFILE%.
    ) ELSE IF %ERRORLEVEL% EQU 2 (
        echo %Entity% chose NO. %FILETYPE% - Delete %MYFILE%.
    ) ELSE IF %ERRORLEVEL% EQU 3 (
        echo %Entity% chose QUIT. Exiting script.
        EXIT /B
    )
    echo.
) ELSE (
    echo No %FILETYPE% files matching %MYFILE% found. Skipping deletion.
    echo.
)



@REM Delete field_entity.php
SET MYFILE="%ENTITY_CONFIG_DIR%\%Entity%_fields.php"
SET FILETYPE=EntityConfig

IF EXIST %MYFILE% (
    CHOICE /C YNQ /M "%FILETYPE% Delete %MYFILE%?"
    IF %ERRORLEVEL% EQU 1 (
        echo %Entity% chose YES. %FILETYPE% - Deleting entity file...
        DEL %MYFILE%
        echo Successfully deleted %FILETYPE% files matching %MYFILE%.
    ) ELSE IF %ERRORLEVEL% EQU 2 (
        echo %Entity% chose NO. %FILETYPE% - Delete %MYFILE%.
    ) ELSE IF %ERRORLEVEL% EQU 3 (
        echo %Entity% chose QUIT. Exiting script.
        EXIT /B
    )
    echo.
) ELSE (
    echo No %FILETYPE% files matching %MYFILE% found. Skipping deletion.
    echo.
)



@REM Delete entity.php - entity
SET MYFILE="%ENTITY_DIR%\%Entity%.php"
SET FILETYPE=EntityConfig

IF EXIST %MYFILE% (
    CHOICE /C YNQ /M "%FILETYPE% Delete %MYFILE%?"
    IF %ERRORLEVEL% EQU 1 (
        echo %Entity% chose YES. %FILETYPE% - Deleting entity file...
        DEL %MYFILE%
        echo Successfully deleted %FILETYPE% files matching %MYFILE%.
    ) ELSE IF %ERRORLEVEL% EQU 2 (
        echo %Entity% chose NO. %FILETYPE% - Delete %MYFILE%.
    ) ELSE IF %ERRORLEVEL% EQU 3 (
        echo %Entity% chose QUIT. Exiting script.
        EXIT /B
    )
    echo.
) ELSE (
    echo No %FILETYPE% files matching %MYFILE% found. Skipping deletion.
    echo.
)





@REM Prompt before generating the migration
CHOICE /C YNQ /M "Run php bin/console.php make:migration %entity%. Do it?"
IF %ERRORLEVEL% EQU 1 (
    echo %Entity% chose YES. Generating migration...
    php bin/console.php make:migration %entity%
) ELSE IF %ERRORLEVEL% EQU 2 (
    echo %Entity% chose NO. Skipping migration generation.
) ELSE IF %ERRORLEVEL% EQU 3 (
    echo %Entity% chose QUIT. Exiting script.
    EXIT /B
)
echo.

@REM Prompt before generating the seeder
CHOICE /C YNQ /M "Run php bin/console.php make:seeder %entity%. Do it?"
IF %ERRORLEVEL% EQU 1 (
    echo %Entity% chose YES. Generating seeder...
    php bin/console.php make:seeder %entity%
) ELSE IF %ERRORLEVEL% EQU 2 (
    echo %Entity% chose NO. Skipping seeder generation.
) ELSE IF %ERRORLEVEL% EQU 3 (
    echo %Entity% chose QUIT. Exiting script.
    EXIT /B
)
echo.


@REM Prompt before generating the entity
CHOICE /C YNQ /M "Run php bin/console.php make:entity %entity%. Do it?"
IF %ERRORLEVEL% EQU 1 (
    echo %Entity% chose YES. Generating entity...
    php bin/console.php make:entity %entity%
) ELSE IF %ERRORLEVEL% EQU 2 (
    echo %Entity% chose NO. Skipping %entity% generation.
) ELSE IF %ERRORLEVEL% EQU 3 (
    echo %Entity% chose QUIT. Exiting script.
    EXIT /B
)
echo.

@REM Prompt before generating the entity
CHOICE /C YNQ /M "Run php bin/console.php make:field-config %entity%. Do it?"
IF %ERRORLEVEL% EQU 1 (
    echo %Entity% chose YES. Generating field-config...
    php bin/console.php make:field-config %entity%
) ELSE IF %ERRORLEVEL% EQU 2 (
    echo %Entity% chose NO. Skipping field-config generation.
) ELSE IF %ERRORLEVEL% EQU 3 (
    echo %Entity% chose QUIT. Exiting script.
    EXIT /B
)
echo.





@REM Prompt before moving files
CHOICE /C YNQ /M "Run php bin/console.php feature:move %entity%. Do it?"
IF %ERRORLEVEL% EQU 1 (
    echo %Entity% chose YES. Moving files...
    php bin/console.php feature:move %entity%
) ELSE IF %ERRORLEVEL% EQU 2 (
    echo %Entity% chose NO. Skipping moving files.
) ELSE IF %ERRORLEVEL% EQU 3 (
    echo %Entity% chose QUIT. Exiting script.
    EXIT /B
)
echo.





@REM Any commands placed here will execute after the choice, regardless of Y or N.
echo.
echo Script finished.
