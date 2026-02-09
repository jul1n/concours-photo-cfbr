@echo off
chcp 65001 >nul
setlocal EnableDelayedExpansion

echo ========================================
echo Compression des Photos - Concours Photo
echo ========================================
echo.

REM Tester pip directement (plus fiable que python --version)
pip --version >nul 2>&1
if errorlevel 1 (
    echo [ERREUR] Python/pip n est pas installe correctement.
    echo.
    echo SOLUTIONS RAPIDES:
    echo.
    echo   Option 1: Microsoft Store ^(Le plus simple^)
    echo      1. Ouvrez le Microsoft Store
    echo      2. Recherchez "Python 3.12"
    echo      3. Cliquez sur Installer
    echo      4. Relancez ce script
    echo.
    echo   Option 2: Site officiel Python
    echo      1. Allez sur https://www.python.org/downloads/
    echo      2. Telechargez Python 3.11 ou superieur
    echo      3. IMPORTANT: Cochez "Add Python to PATH"
    echo      4. Installez et relancez ce script
    echo.
    echo   Option 3: Winget ^(si disponible^)
    echo      - Ouvrez PowerShell et tapez:
    echo      - winget install Python.Python.3.12
    echo.
    echo   Option 4: Executer manuellement
    echo      - Installez Python avec pip
    echo      - Ouvrez PowerShell ici et tapez:
    echo      - pip install Pillow
    echo      - python compress_photos.py
    echo.
    pause
    exit /b 1
)

echo [OK] Python/pip detecte
pip --version
echo.

REM Verifier si Pillow est installe
python -c "import PIL" 2>nul
if errorlevel 1 (
    echo [INFO] Installation de Pillow ^(bibliotheque d images^)...
    echo Cela peut prendre 1-2 minutes...
    echo.
    pip install Pillow
    if errorlevel 1 (
        echo.
        echo [ERREUR] Installation de Pillow echouee
        echo.
        echo Essayez manuellement:
        echo   pip install --user Pillow
        echo.
        pause
        exit /b 1
    )
    echo.
    echo [OK] Pillow installe avec succes!
    echo.
) else (
    echo [OK] Pillow est deja installe
    echo.
)

REM Lancer le script de compression
echo ========================================
echo Lancement de la compression...
echo ========================================
echo.

python compress_photos.py

if errorlevel 1 (
    echo.
    echo [ERREUR] La compression a rencontre un probleme
    echo Verifiez que vos photos sont dans: data\slide\
    echo.
    pause
    exit /b 1
)

echo.
echo ========================================
echo TERMINE AVEC SUCCES!
echo ========================================
echo.
echo Les photos compressees sont dans: photos\slides_optimized\
echo.
pause
