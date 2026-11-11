@echo off
title Menjalankan Aethereal Joki Desktop
echo ========================================================
echo   MENJALANKAN AETHEREAL JOKI DESKTOP (DEVELOPMENT)
echo ========================================================
echo.

set PATH=D:\laragon\bin\composer;D:\laragon\bin\php\php-8.3.30-Win32-vs16-x64;%PATH%

call "D:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe" artisan native:run
pause
