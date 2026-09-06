# Aruna CMMS — Panduan Menjalankan di Laptop Baru / Komputer Lain

Panduan ini ditujukan agar aplikasi Aruna CMMS dapat langsung dijalankan di laptop lain (Windows, Mac, atau Linux) dengan **1 langkah** menggunakan Docker.

---

## 1. Syarat Utama (Prerequisites) di Laptop Baru
Hanya butuh **1 software**:
- **Docker Desktop** (untuk Windows & Mac) atau **Docker Engine + Docker Compose** (untuk Linux).
- **Catatan Windows:** Pastikan fitur WSL 2 aktif di Docker Desktop Settings (*Use the WSL 2 based engine*).

> *Catatan:* Tidak perlu menginstall PHP, Composer, Node.js, atau MySQL di laptop host, karena semua sudah dibundle otomatis di dalam Docker container.

---

## 2. Cara Menjalankan

### Cara A — Menggunakan Script Otomatis (Paling Mudah)

**Di Windows:**
Cukup klik ganda (double-click) file:
```
docker-setup.bat
```
*(atau jalankan `.\docker-setup.bat` di Command Prompt / PowerShell)*

**Di Linux / macOS / Git Bash:**
Jalankan di terminal:
```bash
./docker-setup.sh
```

---

### Cara B — Perintah Manual

Jika ingin menjalankan secara manual langkah demi langkah:

1. **Copy file konfigurasi environment:**
   ```bash
   cp .env.docker .env
   # Di Windows Command Prompt:
   # copy .env.docker .env
   ```

2. **Jalankan Docker Compose:**
   ```bash
   docker compose up -d --build
   ```

3. **Selesai!**
   Container `cmms-app` akan otomatis:
   - Menjalankan `composer install`
   - Generate `APP_KEY`
   - Menunggu MySQL siap
   - Menjalankan migrasi database
   - Men-seed seluruh data aset & modul PV blok **T01 sampai T07**
   - Menjalankan link storage dan optimize cache

---

## 3. Akses Aplikasi & Akun Login

Buka browser dan buka:
👉 **[http://localhost:8000](http://localhost:8000)**

**Akun Login Default:**
- **Email:** `wakwaw@gmail.com`
- **Password:** `ayamgoyengenak`

*(Atau akun bawaan: `admin@arunahijaupower.com` / `password`)*

---

## 4. Perintah Berguna

- **Melihat status/log aplikasi saat startup:**
  ```bash
  docker compose logs -f app
  ```
- **Menghentikan aplikasi:**
  ```bash
  docker compose down
  ```
- **Membuka Laravel Tinker (CLI):**
  ```bash
  docker compose exec app php artisan tinker
  ```

---

## 5. Troubleshooting (Jika Ada Kendala)

1. **Port 3306 atau 8000 bentrok / already in use:**
   - Jika laptop memiliki aplikasi XAMPP/MySQL lokal yang sedang aktif, matikan MySQL lokal tersebut atau ubah port di `docker-compose.yml` (misal `"3307:3306"`).
2. **Docker Desktop belum jalan:**
   - Pastikan aplikasi Docker Desktop sudah dibuka dan indikator di pojok kiri bawah sudah berwarna hijau (*Engine running*).


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
