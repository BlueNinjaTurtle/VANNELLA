@echo off
REM =========================================================================
REM start_iot_bridge.bat
REM Lance le bridge IoT VANNELLA
REM Utilisation: Double-clic pour lancer
REM =========================================================================

setlocal enabledelayedexpansion

REM Récupérer le répertoire du script
set SCRIPT_DIR=%~dp0
set IOT_DIR=%SCRIPT_DIR%partie IOT

cd /d "%IOT_DIR%"

REM Affichage
cls
echo.
echo ========================================================================
echo       🚀 Bridge IoT VANNELLA - Lancement en cours...
echo ========================================================================
echo.

REM Vérifier Python
python --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ✗ ERREUR: Python n'est pas installé ou pas dans le PATH
    echo.
    echo Solution:
    echo 1. Installer Python 3.7+ depuis python.org
    echo 2. Vérifier que "Add Python to PATH" est coché
    echo 3. Redémarrer ce script
    echo.
    pause
    exit /b 1
)

REM Vérifier les dépendances Python
echo Vérification des dépendances Python...
python -c "import serial; import requests" >nul 2>&1
if %errorlevel% neq 0 (
    echo ✗ ERREUR: Dépendances manquantes (pyserial, requests)
    echo.
    echo Installation en cours...
    pip install -r requirements.txt
    if !errorlevel! neq 0 (
        echo ✗ Impossible d'installer les dépendances
        pause
        exit /b 1
    )
)

REM Vérifier le dossier logs
if not exist "..\logs" (
    echo Création du dossier logs...
    mkdir "..\logs"
)

REM Vérifier le fichier config
if not exist "config_iot.json" (
    echo ⚠ ATTENTION: config_iot.json non trouvé dans le répertoire racine
    echo Utilisation de la configuration par défaut...
)

REM Lancer le script
echo.
echo [%date% %time%] Lancement du bridge IoT...
echo.
python Script_python.py

REM Si erreur, pause pour lire
if %errorlevel% neq 0 (
    echo.
    echo ✗ Le bridge IoT s'est arrêté avec une erreur.
    pause
)

endlocal
exit /b %errorlevel%
