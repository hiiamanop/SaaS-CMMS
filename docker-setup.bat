@echo off
echo ==========================================
echo    Aruna CMMS - Docker Setup (Windows)
echo ==========================================
echo.

REM Check if .env exists
if not exist .env (
    echo [INFO] Copying .env.docker to .env...
    copy .env.docker .env >nul
    echo [OK] .env created.
) else (
    echo [OK] .env already exists.
)

REM Verify Docker Compose is accessible
docker compose version >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Docker / Docker Compose tidak ditemukan!
    echo Harap install dan jalankan Docker Desktop terlebih dahulu.
    echo Pastikan Docker Desktop dalam status 'Engine Running'.
    pause
    exit /b 1
)

echo.
echo [INFO] Menjalankan build dan start Docker containers...
docker compose up -d --build

echo.
echo ================================================================
echo  Container berhasil dijalankan!
echo  Proses migrasi dan seeding database (T01 - T07) akan berjalan
echo  secara otomatis di background pada container 'cmms-app'.
echo.
echo  Akses Web:  http://localhost:8000
echo  Akun Login: wakwaw@gmail.com / ayamgoyengenak
echo              admin@arunahijaupower.com / password
echo.
echo  Cek log:    docker compose logs -f app
echo ================================================================
pause
