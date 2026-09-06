# Aruna CMMS — Computerized Maintenance Management System

Platform manajemen pemeliharaan terpadu yang dirancang khusus untuk operasional pembangkit listrik tenaga surya (PLTS) **PT Aruna Hijau Power**. Sistem ini mencakup pemantauan 1.881 aset fisik PLTS Lestari Pertiwi (36,01 MWp), peta modul interaktif Blok T01–T07, penjadwalan pemeliharaan preventif, perintah kerja (*Work Order*), logistik inventaris, dan pencetakan label stiker QR code industri.

---

## 🌟 Fitur Utama

### 1. Peta Interaktif & Aset Fisik PLTS (Blok T01 s/d T07)
- **1.881 Aset Terdaftar:** 1.781 string PV Module (550 Wp), 86 Inverter, 7 Step-Up Transformer (20kV), dan 7 panel Metering.
- **Kanvas Interaktif:** Fitur 2D pan/zoom, auto-scale (*FIT view*), filter status per modul (Active, Replaced, Inactive), dan *Quick Detail Modal* dengan spesifikasi elektrik lengkap.
- **Filter Blok Transformator:** Navigasi instan aset berdasarkan blok (T01 s/d T07) pada tabel inventaris aset.

### 2. Penjadwalan Pemeliharaan (Preventive Maintenance)
- **Multi-Frekuensi:** Penjadwalan mingguan, bulanan, triwulan, semesteran, dan tahunan berdasarkan kalender operasional.
- **Siklus Checksheet Digital:** Lebih dari 600 sesi checksheet untuk tahun berjalan dengan formulir verifikasi teknis dan penandatanganan digital bertingkat (Teknisi $\rightarrow$ SPV $\rightarrow$ PM).
- **Timeline Beban Kerja:** Visualisasi kalender pemeliharaan untuk mengoptimalkan rotasi tim teknisi.

### 3. Sistem Work Order & Catatan Pemeliharaan (Corrective)
- **Alur Kerja Tiket:** Status *Open*, *In Progress*, *Pending Review*, hingga *Closed* atau *Canceled*.
- **Pencatatan Shutdown:** Tracking otomatis durasi henti unit (*shutdown minutes*) untuk analisis dampak ketersediaan pembangkit.
- **Integrasi Logistik Otomatis:** Pemotongan stok suku cadang (*Spare Parts*) dan barang habis pakai (*Consumables*) langsung saat catatan pemeliharaan disimpan.

### 4. Manajemen Inventaris & Logistik (Gudang & Rak)
- **Tiga Kategori Terpisah:**
  - **Spare Parts (93 item):** Proteksi (MCCB, Fuse DC), Kabel & FO, Konektor MC4, Komponen Mekanikal, dan Instrumen.
  - **Tools (100 item):** Alat ukur (Multimeter, Megger, Thermovision), *Power Tools*, dan *Hand Tools* dengan tracking kondisi (*good/damaged/lost*) dan status pinjam.
  - **Consumables (50 item):** Perlengkapan K3 (*Safety/APD*), bahan pembersih, pelumas (*grease*), dan material elektrik.
- **Ekspor & Impor CSV Dua Arah:** Pengunduhan rekap inventaris format CSV (kompatibel penuh Microsoft Excel) dan impor pembaruan stok massal.

### 5. Cetak Massal Stiker Label QR Code Industri
- **Format Printer Thermal Stiker Roll:**
  - **70 x 40 mm (Rekomendasi Standar Industri):** 1 stiker per halaman PDF presisi, sensor gap printer langsung berhenti di sela stiker (cocok untuk Xprinter, Zebra, TSC).
  - **50 x 30 mm (Label Mini):** Format ringkas untuk penandaan kabel DC, MC4, dan kotak bin part mini.
  - **100 x 50 mm (Label Besar):** Untuk kasing Inverter dan Trafo utama.
- **Format Kertas Stiker A4 Grid:** Tata letak kisi 3 kolom x 7 baris (21 stiker/lembar) dengan batas putus-putus (*cutting guide*) untuk pencetakan menggunakan printer kantor biasa.

### 6. Dashboard KPI & Laporan Bulanan Eksekutif
- **Metrik Reliabilitas Pembangkit Riil:**
  - *Maintenance Compliance* (%)
  - *Plant Availability* (%)
  - *Mean Time To Repair* (MTTR) & *Mean Time Between Failures* (MTBF)
- **Generasi Laporan PDF:** Laporan aktivitas bulanan terkompilasi otomatis (*Monthly Report PDF*) yang mencakup rekap pengerjaan dan konsumsi logistik.

---

## 🛠️ Tech Stack & Arsitektur

- **Backend:** Laravel 11.x (PHP 8.4-FPM)
- **Database & Cache:** MySQL 8.0 & Redis 7 Alpine
- **Web Server:** Nginx 1.27 Alpine (Reverse Proxy & Static Asset Server)
- **Frontend:** Tailwind CSS 3, Alpine.js, Chart.js 4, FullCalendar
- **PDF & QR Engine:** Barryvdh DomPDF, SimpleSoftwareIO QrCode (BaconQrCode)
- **Containerization:** Docker Desktop / Docker Compose v2

---

## 🚀 Panduan Instalasi dari Awal (Getting Started)

Aplikasi ini telah dikonfigurasi agar **100% berjalan mandiri di dalam Docker**. Anda **tidak perlu menginstal PHP, Composer, Node.js, atau MySQL di komputer host**.

### Syarat Awal (Prerequisites)
Pastikan komputer telah terpasang:
- **[Docker Desktop](https://www.docker.com/products/docker-desktop/)** (untuk Windows dan macOS) atau **Docker Engine + Docker Compose** (untuk Linux).
- *Pengguna Windows:* Pastikan Docker Desktop dalam keadaan aktif (*Engine running* bertanda hijau di taskbar) dan opsi WSL 2 diaktifkan.

---

### Metode 1 — Peluncur 1-Klik (Paling Mudah)

#### A. Pada Windows:
Cukup klik dua kali (*double-click*) file:
```text
START.bat
```
*(atau jalankan `docker-setup.bat`)*

Script ini secara otomatis:
1. Memeriksa kesiapan Docker Desktop.
2. Menyiapkan berkas konfigurasi `.env`.
3. Membangun (*build*) dan menyalakan seluruh container di background.
4. Menjalankan Composer install & migrasi database.
5. **Men-seed seluruh data awal:** 1.881 aset PLTS T01–T07, inventaris, jadwal, dan akun pengguna.
6. **Otomatis membuka browser ke `http://localhost:8000`** begitu sistem siap digunakan.

**Untuk Menghentikan:**
Klik dua kali file:
```text
STOP.bat
```

#### B. Pada Linux / macOS / Git Bash:
Buka terminal di folder project, lalu jalankan:
```bash
chmod +x docker-setup.sh
./docker-setup.sh
```

---

### Metode 2 — Instalasi Manual via Terminal

Jika ingin menjalankan setiap tahapan secara manual:

1. **Salin file konfigurasi environment:**
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
   Container `cmms-app` memiliki script inisialisasi cerdas (`entrypoint.sh`) yang otomatis menunggu database siap, melakukan migrasi, dan memasukkan seluruh dataset tanpa perintah tambahan.

---

## 🔑 Informasi Akses & Akun Login

Buka browser dan akses alamat:
👉 **[http://localhost:8000](http://localhost:8000)**

| Role Akun | Alamat Email | Kata Sandi | Deskripsi Akses |
| :--- | :--- | :--- | :--- |
| **Super Admin** | `wakwaw@gmail.com` | `ayamgoyengenak` | Akses penuh sistem, bypass verifikasi, & konfigurasi |
| **Admin Support** | `admin@arunahijaupower.com` | `password` | Pengelolaan aset, inventaris, audit, & hak akses |
| **Supervisor ONM** | `spv@cmms.com` | `password` | Pembuatan & persetujuan WO, approval checksheet |
| **Teknisi ONM** | `teknisi1@cmms.com` | `password` | Pengisian checksheet mobile & eksekusi perbaikan |

### Port Layanan Tambahan
- **Aplikasi Web Utama:** `http://localhost:8000`
- **Database GUI (PHPMyAdmin):** `http://localhost:8001` (User: `root`, Password: `root`)
- **Port Database Langsung:** `localhost:3306` (User: `cmms_user`, Pass: `secret`, DB: `cmms_dev`)
- **Redis Cache:** `localhost:6379`

---

## 🌐 Akses Jaringan Remote (Tailscale / Wi-Fi LAN)

Aplikasi ini sudah dioptimalkan agar aset CSS dan JavaScript dapat dimuat sempurna saat diakses dari perangkat lain (HP teknisi, tablet, atau laptop rekan) melalui Tailscale atau IP LAN:

1. Pastikan port `8000` dapat diakses dari jaringan.
2. Buka browser pada perangkat klien dengan format IP host:
   ```text
   http://<IP_TAILSCALE_ATAU_LAN>:8000
   Contoh: http://100.72.251.23:8000
   ```
3. Seluruh antarmuka Tailwind CSS, tombol cetak, modal interaktif, dan grafik akan tampil lengkap tanpa error *missing asset*.

---

## 💻 Perintah CLI Berguna (Maintenance)

Seluruh perintah dapat dijalankan melalui Docker Compose:

- **Menjalankan Automated Test Suite (40 Tests):**
  ```bash
  docker compose exec app php artisan test
  ```
- **Membuka Laravel Interactive Shell (Tinker):**
  ```bash
  docker compose exec app php artisan tinker
  ```
- **Memantau Log Aplikasi Secara Realtime:**
  ```bash
  docker compose logs -f app
  ```
- **Mengulang Proses Seeding Database:**
  ```bash
  docker compose exec app php artisan db:seed --force
  ```
- **Mengenerate Laporan Bulanan (PDF Archive):**
  ```bash
  docker compose exec app php artisan cmms:generate-monthly-report --year=2026 --month=8
  ```
- **Menghentikan Seluruh Container:**
  ```bash
  docker compose down
  ```

---

## 📂 Struktur Direktori Utama

```text
├── app/
│   ├── Http/Controllers/       # Logika resource controller (Assets, WO, Inventory, Labels, KPI)
│   ├── Models/                 # Eloquent Models & relasi database
│   └── Services/               # Business services (StockService, ReportService)
├── database/
│   ├── migrations/             # Struktur skema tabel database
│   └── seeders/                # Seeder PLTS T01–T07, inventaris, & akun operasional
├── docker/
│   ├── Dockerfile              # Image PHP 8.4-FPM dengan ekstensi lengkap
│   ├── nginx/default.conf      # Konfigurasi reverse proxy Nginx
│   └── php/entrypoint.sh       # Script automasi startup container
├── docs/                       # CSV Layout PV T01–T07, CSV Stock Opname, & Diagram PLTS
├── resources/
│   ├── views/                  # Tampilan Blade template (Dashboard, Labels, Reports, PDF)
│   └── js/ & css/              # Konfigurasi frontend Alpine.js & Tailwind CSS
├── routes/                     # Definisi routing web & konsol scheduler
├── docker-compose.yml          # Orkestrasi multi-container Docker
├── START.bat / STOP.bat        # Peluncur 1-klik untuk Windows
├── docker-setup.sh             # Script instalasi otomatis untuk Linux/Mac
└── README.md                   # Dokumentasi resmi proyek
```

---

## 📄 Lisensi & Hak Cipta
Hak Cipta © 2026 **PT Aruna Hijau Power**. Seluruh hak cipta dilindungi undang-undang. Export laporan ke format PDF.

## 🛠️ Teknologi yang Digunakan

- **Framework**: [Laravel 11](https://laravel.com)
- **Frontend**: [Tailwind CSS](https://tailwindcss.com) & [Alpine.js](https://alpinejs.dev)
- **Database**: MySQL
- **Icons & UI Components**: Heroicons & SweetAlert2

## 📦 Instalasi & Persiapan

1. Clone repository ini.
2. Jalankan `composer install` dan `npm install`.
3. Salin `.env.example` menjadi `.env` dan sesuaikan konfigurasi database.
4. Jalankan `php artisan key:generate`.
5. Jalankan migrasi: `php artisan migrate`.
6. (Opsional) Jalankan seeder untuk data awal: `php artisan db:seed`.
7. Jalankan server: `php artisan serve` dan `npm run dev`.

---
Dikembangkan untuk efisiensi operasional **PT Aruna Hijau Power**.
