@echo off
setlocal
set "ROOT=%~dp0..\.."
set "JAVA_DIR="
for /d %%D in ("%ROOT%\tools\java\*-jre") do set "JAVA_DIR=%%~fD"

if "%JAVA_DIR%"=="" (
  echo Java portable introuvable dans tools\java.
  exit /b 1
)

"%JAVA_DIR%\bin\java.exe" -jar "%ROOT%\tools\plantuml\plantuml.jar" %*
