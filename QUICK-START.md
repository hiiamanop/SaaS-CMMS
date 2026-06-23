# PV Map Feature — Quick Start

**Goal:** Test PV Map import/edit feature locally with minimal setup

## Option A: Laravel Sail (Recommended - Automatic Setup)

```bash
# From project root
composer require laravel/sail --dev

# Auto-configure
php artisan sail:install
# (press enter for default options)

# Start containers (includes MySQL + Redis + PHPMyAdmin)
./vendor/bin/sail up -d

# Run migrations
./vendor/bin/sail artisan migrate

# Seed database (optional - creates admin user & sample locations)
./vendor/bin/sail artisan db:seed

# Access:
#   App:         http://localhost
#   PHPMyAdmin:  http://localhost:8080
#   MySQL:       localhost:3306 (sail / password)
```

**Stop when done:**
```bash
./vendor/bin/sail down
```

---

## Option B: Manual Docker Compose

```bash
# Start services
docker-compose up -d

# Install dependencies (in container)
docker-compose exec app composer install

# Run migrations
docker-compose exec app php artisan migrate --force

# Access:
#   App:         http://localhost:8000
#   PHPMyAdmin:  http://localhost:8001
#   MySQL:       localhost:3306 (cmms_user / secret)
```

**Stop:**
```bash
docker-compose down
```

---

## Option C: Local PHP (if you have XAMPP/Valet/Herd)

```bash
# Install dependencies
composer install

# Create .env file
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate

# Start server
php artisan serve
# App at http://localhost:8000
```

---

## Testing PV Map Feature

1. **Login as Admin**
   - URL: `http://localhost:8000` (or :80 for Sail)
   - Email: `admin@example.com` (or check seeders)
   - Password: `password` (or check .env)

2. **Navigate to PV Map**
   - Sidebar → Settings (gear icon)
   - Click tab: "Lokasi PLTS"
   - Button: "Peta" (blue button in action column)

3. **Upload CSV**
   - Modal opens: "Upload File CSV"
   - Drag-drop: `docs/T01-249_PV_Layout.csv`
   - Preview shows: T01, 249 modules

4. **Edit & Save**
   - Click "Lanjutkan Preview"
   - Grid displays with modules
   - Click module → edit visual_row/visual_col
   - Click "Simpan Peta"
   - Alert: "Peta berhasil disimpan!"

5. **Verify in Database**
   ```bash
   # Via PHPMyAdmin UI (easier)
   # Go to pv_maps table → see record with transformer_block T01

   # Or via CLI:
   ./vendor/bin/sail tinker
   >>> App\Models\PvMap::all();
   >>> App\Models\Asset::where('transformer_block', 'T01')->select('asset_code', 'visual_row', 'visual_col')->take(3)->get();
   ```

---

## Troubleshooting

| Problem | Solution |
|---------|----------|
| Port 8000 in use | Change `ports: ["8001:80"]` in docker-compose.yml |
| MySQL won't start | `docker-compose logs mysql` → check volume permissions |
| Composer error | `composer install --ignore-platform-req=php` |
| Admin login fails | Run `php artisan db:seed` to create test user |
| PHPMyAdmin blank | Wait 30s for MySQL to be ready |

---

## Files Involved

- **Backend:** `app/Models/PvMap.php`, `app/Http/Controllers/PvMapController.php`
- **Frontend:** `resources/views/components/pv-map-modal.blade.php`
- **Database:** `database/migrations/2026_06_22_000001_create_pv_maps_table.php`
- **Test Data:** `docs/T01-249_PV_Layout.csv`, `docs/T02-249_PV_Layout.csv`
- **Routes:** `routes/web.php` (PvMap routes)

---

**Recommend starting with Option A (Sail) — it's tested and handles all dependencies automatically.**
