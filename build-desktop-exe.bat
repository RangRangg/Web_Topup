@echo off
title Membangun Aplikasi Desktop Aethereal Joki (.exe)
echo ========================================================
echo   AETHEREAL JOKI - WINDOWS DESKTOP INSTALLER BUILDER
echo ========================================================
echo.

set PATH=D:\laragon\bin\composer;D:\laragon\bin\php\php-8.3.30-Win32-vs16-x64;%PATH%

echo [1/3] Mengompilasi frontend assets (Vite)...
call npm run build

echo [2/3] Membersihkan cache aplikasi...
call "D:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe" artisan optimize:clear

echo [3/3] Membangun installer Windows Desktop (.exe)...
call "D:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe" artisan native:build win x64

if exist "nativephp\electron\dist\Aethereal Joki-1.0.0-setup.exe" (
    copy /y "nativephp\electron\dist\Aethereal Joki-1.0.0-setup.exe" "Aethereal-Joki-Installer.exe" >nul
)

echo.
echo ========================================================
echo Selesai! File installer .exe dapat ditemukan di:
echo - Aethereal-Joki-Installer.exe (Root Folder)
echo - nativephp\electron\dist\Aethereal Joki-1.0.0-setup.exe
echo ========================================================
