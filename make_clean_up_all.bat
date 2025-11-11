@echo off
REM make_and_move_migrations.bat
SET PHP_BIN=php
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
@REM IF EXIST "%GENERATED_DIR%\" RMDIR /S "%GENERATED_DIR%"
@REM echo.
IF EXIST "%GENERATED_DIR%\" (
    CHOICE /C YNQ /M "Delete Folder %GENERATED_DIR%?"
    IF %ERRORLEVEL% EQU 1 (
        echo You chose YES.Deleting folder...
        RMDIR /S "%GENERATED_DIR%"
        echo Successfully deleted %GENERATED_DIR% folder.
    ) ELSE IF %ERRORLEVEL% EQU 2 (
        echo You chose NO. Nothing deleted
    ) ELSE IF %ERRORLEVEL% EQU 3 (
        echo You chose QUIT. Exiting script.
        EXIT /B
    )
) ELSE (
    echo Directory %GENERATED_DIR% not present.
)
echo.

        echo Successfully.
        echo Successfully.
        echo Successfully.
        echo Successfully.
        echo Successfully.
        echo Successfully.


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




@REM Any commands placed here will execute after the choice, regardless of Y or N.
echo.
echo Script finished.
