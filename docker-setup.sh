#!/bin/bash

set -e

echo "🐳 Aruna CMMS Docker Setup"
echo "=========================="
echo ""

# Detect docker compose command
if docker compose version >/dev/null 2>&1; then
    DC="docker compose"
elif docker-compose version >/dev/null 2>&1; then
    DC="docker-compose"
else
    echo "❌ Error: Docker / Docker Compose tidak ditemukan. Harap install Docker Desktop terlebih dahulu."
    exit 1
fi

# Check if .env exists
if [ ! -f .env ]; then
    echo "📋 Creating .env from .env.docker..."
    cp .env.docker .env
    echo "✓ .env created"
else
    echo "✓ .env already exists"
fi

# Generate app key if needed
if grep -q "APP_KEY=$" .env || grep -q "APP_KEY=base64:YOUR_APP_KEY_HERE" .env; then
    echo "🔑 Generating APP_KEY..."
    if command -v openssl >/dev/null 2>&1; then
        APP_KEY="base64:$(openssl rand -base64 32)"
        sed -i "s|APP_KEY=.*|APP_KEY=$APP_KEY|" .env
        echo "✓ APP_KEY generated via openssl"
    elif command -v php >/dev/null 2>&1; then
        APP_KEY=$(php -r "echo 'base64:' . base64_encode(random_bytes(32));")
        sed -i "s|APP_KEY=.*|APP_KEY=$APP_KEY|" .env
        echo "✓ APP_KEY generated via php"
    fi
fi

# Start docker containers
echo ""
echo "🚀 Starting Docker containers (build & up)..."
$DC up -d --build

echo ""
echo "⏳ Waiting for application container to finish initialization & seeding..."
sleep 5
$DC logs -f app | while read -r line; do
    echo "$line"
    if echo "$line" | grep -q "Starting PHP-FPM"; then
        pkill -P $$ docker 2>/dev/null || true
        break
    fi
done || true

echo ""
echo "✅ Setup Complete!"
echo ""
echo "Access points:"
echo "   App:          http://localhost:8000"
echo "   MySQL:        localhost:3306 (cmms_user / secret)"
echo "   Redis:        localhost:6379"
echo ""
echo "Akun Login Default:"
echo "   Admin:        wakwaw@gmail.com / ayamgoyengenak"
echo "   Alternative:  admin@arunahijaupower.com / password"
echo ""
echo "📝 Perintah Berguna:"
echo "   $DC exec app php artisan tinker     # Laravel REPL"
echo "   $DC logs -f app                     # Pantau log container"
echo "   $DC down                            # Stop container"
echo ""
