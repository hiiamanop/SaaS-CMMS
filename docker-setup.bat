@echo off
setlocal enabledelayedexpansion
title Aruna CMMS - 1-Click Launcher
color 0A

echo ================================================================
echo             ARUNA CMMS - 1-CLICK LAUNCHER (DOCKER)
echo ================================================================
echo.

REM 1. Periksa ketersediaan Docker
docker compose version >nul 2>&1
if %errorlevel% neq 0 (
    docker-compose version >nul 2>&1
    if %errorlevel% neq 0 (
        color 0C
        echo [ERROR] Docker atau Docker Compose belum terpasang di komputer ini!
        echo.
        echo Solusi:
        echo 1. Unduh dan pasang Docker Desktop dari: https://www.docker.com/products/docker-desktop/
        echo 2. Jalankan Docker Desktop, lalu buka kembali file ini.
        echo.
        pause
        exit /b 1
    )
    set DC=docker-compose
) else (
    set DC=docker compose
)

REM 2. Periksa apakah Docker Engine aktif
docker info >nul 2>&1
if %errorlevel% neq 0 (
    color 0E
    echo [PERINGATAN] Docker Desktop belum dijalankan atau sedang loading!
    echo Harap buka aplikasi Docker Desktop di komputer Anda.
    echo Pastikan ikon Docker di taskbar sudah aktif ^(hijau^).
    echo.
    echo Tekan sembarang tombol jika Docker Desktop sudah berjalan...
    pause >nul
    docker info >nul 2>&1
    if %errorlevel% neq 0 (
        color 0C
        echo [ERROR] Docker Engine masih belum aktif. Harap tunggu Docker Desktop siap lalu coba lagi.
        pause
        exit /b 1
    )
)

REM 3. Persiapan file konfigurasi .env
if not exist .env (
    echo [1/4] Membuat konfigurasi .env dari .env.docker...
    if exist .env.docker (
        copy .env.docker .env >nul
    ) else if exist .env.example (
        copy .env.example .env >nul
    )
    echo       Konfigurasi .env siap.
) else (
    echo [1/4] File .env sudah ada.
)

REM 4. Build dan jalankan seluruh container
echo.
echo [2/4] Menjalankan build dan start seluruh container Docker...
echo       (MySQL, Redis, PHP-FPM, Nginx, Node/Vite)
echo.
%DC% up -d --build

if %errorlevel% neq 0 (
    color 0C
    echo [ERROR] Gagal menjalankan container Docker. Periksa error di atas.
    pause
    exit /b 1
)

REM 5. Tunggu inisialisasi awal database dan seeding
echo.
echo [3/4] Menunggu inisialisasi awal database dan seeding modul PV (T01 - T07)...
echo       Proses ini hanya memakan waktu sebentar pada start awal...
echo.

set TIMEOUT=60
:WAIT_LOOP
curl -s -o nul -w "%%{http_code}" http://localhost:8000/login > temp_http.txt 2>nul
set /p HTTP_CODE=<temp_http.txt 2>nul
del temp_http.txt 2>nul

if "%HTTP_CODE%"=="200" goto READY
if "%HTTP_CODE%"=="302" goto READY

timeout /t 3 /nobreak >nul
set /a TIMEOUT-=3
if %TIMEOUT% gtr 0 goto WAIT_LOOP

:READY
echo.
echo [4/4] Membuka aplikasi di browser...
start http://localhost:8000

echo.
echo ================================================================
echo                 APLIKASI BERHASIL BERJALAN!
echo ================================================================
echo  Akses Web        : http://localhost:8000
echo  Database Admin   : http://localhost:8001 (PHPMyAdmin)
echo.
echo  AKUN LOGIN:
echo  - Email          : wakwaw@gmail.com
echo  - Password       : ayamgoyengenak
echo.
echo  - Alternatif     : admin@arunahijaupower.com / password
echo.
echo  Aset PV Layout   : Blok T01 s/d T07 sudah terisi lengkap otomatis!
echo.
echo  Untuk mematikan  : Klik dua kali file STOP.bat atau jalankan:
echo                     %DC% down
echo ================================================================
echo.
pause
