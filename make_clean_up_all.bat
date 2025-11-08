@echo off
REM make_and_move_migrations.bat
SET PHP_BIN=php
SET CONSOLE_SCRIPT=bin/console.php
SET GENERATED_DIR=src/Generated
SET MIGRATIONS_DIR=src/Database/Migrations

echo --- Cleaning Generated Files ---

REM Move 'user' migration
REM DEL /Q "%MIGRATIONS_DIR%\*CreateUserTable.php"
RMDIR /S "%MIGRATIONS_DIR%"

echo.