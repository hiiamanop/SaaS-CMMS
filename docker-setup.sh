#!/bin/bash

set -e

echo "🐳 Aruna CMMS Docker Setup"
echo "=========================="
echo ""

# Check if .env exists
if [ ! -f .env ]; then
    echo "📋 Creating .env from .env.docker..."
    cp .env.docker .env
    echo "✓ .env created"
else
    echo "✓ .env already exists"
fi

# Generate app key if needed
if grep -q "APP_KEY=$" .env; then
    echo "🔑 Generating APP_KEY..."
    APP_KEY=$(php -r "echo 'base64:' . base64_encode(random_bytes(32));")
    sed -i "s|APP_KEY=$|APP_KEY=$APP_KEY|" .env
    echo "✓ APP_KEY generated"
fi

# Start docker containers
echo ""
echo "🚀 Starting Docker containers..."
docker-compose up -d

# Wait for MySQL to be ready
echo "⏳ Waiting for MySQL to be ready..."
docker-compose exec -T mysql mysqladmin ping -h localhost -u cmms_user -psecret --wait=10

echo ""
echo "📦 Installing dependencies..."
docker-compose exec -T app composer install --no-interaction

echo ""
echo "🗄️  Running migrations..."
docker-compose exec -T app php artisan migrate --force

echo ""
echo "🌱 Seeding database..."
docker-compose exec -T app php artisan db:seed --force

echo ""
echo "✅ Setup Complete!"
echo ""
echo "Access points:"
echo "   App:          http://localhost:8000"
echo "   PHPMyAdmin:   http://localhost:8001"
echo "   MySQL:        localhost:3306 (cmms_user / secret)"
echo "   Redis:        localhost:6379"
echo ""
echo "📝 Useful commands:"
echo "   docker-compose exec app php artisan tinker     # Laravel REPL"
echo "   docker-compose exec app php artisan test       # Run tests"
echo "   docker-compose logs -f app                     # View app logs"
echo "   docker-compose down -v                         # Stop & clean up"
echo ""
