@echo off
title Aruna CMMS - Stop Containers
echo ================================================================
echo             ARUNA CMMS - MENGHENTIKAN CONTAINER
echo ================================================================
echo.

docker compose down 2>nul || docker-compose down 2>nul

echo.
echo Container telah berhasil dihentikan.
echo Untuk menjalankan kembali, buka file START.bat
echo.
pause
