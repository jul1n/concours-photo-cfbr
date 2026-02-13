@echo off
echo ========================================
echo Database Repair Tool - CFBR Photo Contest
echo ========================================
echo.

cd /d "%~dp0"

echo Step 1: Creating backup...
if exist "data\concours.db" (
    set BACKUP_FILE=data\concours_backup_%date:~-4,4%%date:~-7,2%%date:~-10,2%_%time:~0,2%%time:~3,2%%time:~6,2%.db
    set BACKUP_FILE=%BACKUP_FILE: =0%
    copy "data\concours.db" "%BACKUP_FILE%" >nul
    if errorlevel 1 (
        echo [ERROR] Failed to create backup!
        pause
        exit /b 1
    )
    echo [OK] Backup created: %BACKUP_FILE%
) else (
    echo [WARNING] Database file not found!
    pause
    exit /b 1
)

echo.
echo Step 2: Checking database integrity...
echo.

:: Create a temporary PHP script to check integrity
echo ^<?php > temp_check.php
echo $dbPath = __DIR__ . '/data/concours.db'; >> temp_check.php
echo try { >> temp_check.php
echo     $pdo = new PDO("sqlite:$dbPath"); >> temp_check.php
echo     $pdo-^>setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); >> temp_check.php
echo     $result = $pdo-^>query("PRAGMA integrity_check")-^>fetch(PDO::FETCH_COLUMN); >> temp_check.php
echo     if ($result === 'ok') { >> temp_check.php
echo         echo "[OK] Database integrity is GOOD!\n"; >> temp_check.php
echo         echo "The error might be transient. Try the maintenance page again.\n"; >> temp_check.php
echo         exit(0); >> temp_check.php
echo     } else { >> temp_check.php
echo         echo "[ERROR] Integrity check failed: $result\n"; >> temp_check.php
echo         echo "\nThe database is corrupted. Options:\n"; >> temp_check.php
echo         echo "1. Delete the database and recreate it (LOSES ALL DATA)\n"; >> temp_check.php
echo         echo "2. Restore from backup if you have one\n"; >> temp_check.php
echo         exit(1); >> temp_check.php
echo     } >> temp_check.php
echo } catch (Exception $e) { >> temp_check.php
echo     echo "[ERROR] " . $e-^>getMessage() . "\n"; >> temp_check.php
echo     echo "\nThe database is severely corrupted.\n"; >> temp_check.php
echo     echo "Recommendation: Delete and recreate the database.\n"; >> temp_check.php
echo     exit(1); >> temp_check.php
echo } >> temp_check.php

:: Run the check
php temp_check.php
set CHECK_RESULT=%errorlevel%

:: Clean up temp file
del temp_check.php

echo.
if %CHECK_RESULT%==0 (
    echo ========================================
    echo Database is healthy! You can continue.
    echo ========================================
) else (
    echo ========================================
    echo Database is corrupted!
    echo ========================================
    echo.
    echo Would you like to DELETE and RECREATE the database?
    echo WARNING: This will erase ALL data!
    echo.
    set /p CONFIRM="Type 'YES' to confirm: "
    
    if /i "%CONFIRM%"=="YES" (
        echo.
        echo Deleting corrupted database...
        del "data\concours.db"
        echo [OK] Database deleted.
        echo.
        echo Please visit: http://localhost/maintenance/?token=cfbr_repair_2026
        echo to reinitialize the database.
    ) else (
        echo.
        echo Operation cancelled. Database not modified.
    )
)

echo.
pause
