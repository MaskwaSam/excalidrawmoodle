@echo off
REM Build script for Excalidraw Moodle Plugin (Windows)
REM Run this on your local Windows machine

echo =====================================
echo Excalidraw Moodle Plugin Builder
echo =====================================
echo.

REM Check Node.js
echo Checking Node.js...
where node >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo Error: Node.js is not installed
    echo Please install Node.js 18.0 or higher from https://nodejs.org/
    pause
    exit /b 1
)

node --version
echo Node.js found!
echo.

REM Check Yarn
echo Checking Yarn...
where yarn >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo Error: Yarn is not installed
    echo Install with: npm install -g yarn
    pause
    exit /b 1
)

yarn --version
echo Yarn found!
echo.

REM Step 1: Install dependencies
echo =====================================
echo Step 1: Installing dependencies...
echo =====================================
echo This may take several minutes...
echo.

if not exist "node_modules" (
    call yarn install
    if %ERRORLEVEL% NEQ 0 (
        echo Failed to install dependencies
        pause
        exit /b 1
    )
) else (
    echo Dependencies already installed, skipping...
)

echo Dependencies installed!
echo.

REM Step 2: Build packages
echo =====================================
echo Step 2: Building Excalidraw packages...
echo =====================================
echo.

call yarn build:packages
if %ERRORLEVEL% NEQ 0 (
    echo Failed to build packages
    pause
    exit /b 1
)

echo Packages built!
echo.

REM Step 3: Build Moodle plugin
echo =====================================
echo Step 3: Building Moodle plugin bundle...
echo =====================================
echo.

node scripts\build-moodle-plugin.js
if %ERRORLEVEL% NEQ 0 (
    echo Failed to build Moodle plugin
    pause
    exit /b 1
)

echo Moodle plugin built!
echo.

REM Step 4: Verify
echo =====================================
echo Step 4: Verifying build output...
echo =====================================
echo.

if exist "mod\excalidraw\amd\src\excalidraw-bundle.js" (
    echo [OK] excalidraw-bundle.js
) else (
    echo [MISSING] excalidraw-bundle.js
)

if exist "mod\excalidraw\styles\excalidraw.css" (
    echo [OK] excalidraw.css
) else (
    echo [MISSING] excalidraw.css
)

if exist "mod\excalidraw\version.php" (
    echo [OK] version.php
) else (
    echo [MISSING] version.php
)

echo.
echo =====================================
echo BUILD SUCCESSFUL!
echo =====================================
echo.
echo Next steps:
echo 1. Copy mod\excalidraw to your Moodle installation's mod\ folder
echo 2. Or create a zip file of mod\excalidraw
echo 3. Install in Moodle via Site administration - Notifications
echo.
echo Plugin location: %CD%\mod\excalidraw
echo.
pause
