# PV Map Testing with Docker — Local Setup Guide

## Prerequisites

- Docker installed: `docker --version`
- Docker Compose installed: `docker-compose --version`
- Git repository: Already cloned locally

## Quick Start

```bash
# 1. Navigate to project directory
cd /path/to/SaaS-CMMS

# 2. Run setup script (one-time)
bash docker-setup.sh

# 3. Access app
# Open browser: http://localhost:8000
# Default login: admin@example.com / password (check seeders or .env)
```

## Manual Setup (if script fails)

```bash
# 1. Copy environment file
cp .env.docker .env

# 2. Generate app key
php -r "echo 'base64:' . base64_encode(random_bytes(32));"
# Copy output and set as APP_KEY in .env

# 3. Start containers
docker-compose up -d

# 4. Wait for MySQL
docker-compose exec mysql mysqladmin ping -h localhost -u cmms_user -psecret --wait=10

# 5. Install dependencies
docker-compose exec app composer install --no-interaction

# 6. Run migrations
docker-compose exec app php artisan migrate --force

# 7. Seed database
docker-compose exec app php artisan db:seed --force
```

## Testing PV Map Feature

### Access Settings Page

```bash
# Get browser URL
echo "http://localhost:8000"

# Or use tinker to create test user
docker-compose exec app php artisan tinker
>>> $user = App\Models\User::where('email', 'admin@example.com')->first();
>>> Auth::login($user);  // If needed
```

### Test Steps

1. **Login as Admin**
   - Email: `admin@example.com` (or check seeders)
   - Password: Check `.env` or seeder defaults

2. **Navigate to Settings > Lokasi PLTS**
   - Sidebar → Settings (gear icon)
   - Click "Lokasi PLTS" tab
   - Should see locations table with "Peta" button

3. **Click "Peta" Button**
   - Modal opens: "Upload File CSV"
   - Drag-drop or click to select `docs/T01-249_PV_Layout.csv`

4. **Upload & Preview**
   - Preview shows: T01, 249 modules
   - Click "Lanjutkan Preview"
   - Grid displays 24x24 columns with modules

5. **Edit & Save**
   - Click any module box
   - Change visual_row/visual_col or status
   - Click "Simpan Peta"
   - Expect: "Peta berhasil disimpan!" alert

6. **Verify Database**
   ```bash
   docker-compose exec app php artisan tinker
   >>> App\Models\PvMap::all();
   >>> App\Models\Asset::where('transformer_block', 'T01')->select('asset_code', 'visual_row', 'visual_col')->take(3)->get();
   ```

## Troubleshooting

### Containers won't start
```bash
# Check Docker daemon
docker ps

# Check logs
docker-compose logs

# Rebuild containers
docker-compose down -v && docker-compose up -d
```

### Port 8000 already in use
```bash
# Change port in docker-compose.yml
# ports:
#   - "8001:80"  # Change 8000 to 8001
docker-compose up -d
```

### MySQL connection fails
```bash
# Wait for MySQL to be healthy
docker-compose exec mysql mysqladmin ping -h localhost -u cmms_user -psecret

# Or check logs
docker-compose logs mysql
```

### Permissions error
```bash
# Ensure .env file is readable
chmod 644 .env

# Fix storage permissions
docker-compose exec app chmod -R 775 storage
```

## Useful Commands

```bash
# View app logs in real-time
docker-compose logs -f app

# SSH into app container
docker-compose exec app bash

# Run Laravel artisan commands
docker-compose exec app php artisan [command]

# Run tests
docker-compose exec app php artisan test

# Clear cache
docker-compose exec app php artisan cache:clear

# Stop all containers
docker-compose down

# Stop and remove volumes (clean slate)
docker-compose down -v

# Rebuild after docker-compose.yml changes
docker-compose up -d --build
```

## Testing CSV Import (Advanced)

### Create test CSV
```bash
# T02 test file
cat > test-t02.csv << 'EOF'
transformer_block,string_number,module_slot,visual_row,visual_col
T02,1,1,1,1
T02,1,2,1,2
T02,2,1,2,1
EOF

# Upload via UI or API
curl -X POST http://localhost:8000/settings/locations/1/pv-maps/upload \
  -F "file=@test-t02.csv" \
  -H "X-CSRF-TOKEN: $(grep csrf-token .env | cut -d= -f2)"
```

## Cleanup

When done testing:

```bash
# Stop containers but keep volumes
docker-compose stop

# Stop and remove everything
docker-compose down -v
```

## Access Points

- **App:** http://localhost:8000
- **PHPMyAdmin:** http://localhost:8001
  - Server: mysql
  - Username: cmms_user
  - Password: secret
- **MySQL CLI:** `docker-compose exec mysql mysql -u cmms_user -psecret cmms_dev`
- **Redis CLI:** `docker-compose exec redis redis-cli`

## Notes

- MySQL accessible on **localhost:3306** (user: cmms_user, pass: secret)
- Redis accessible on **localhost:6379**
- All containers share network `cmms`
- Volumes persist data between restarts (until `down -v`)
