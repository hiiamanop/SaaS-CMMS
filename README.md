# Aruna CMMS (Computerized Maintenance Management System)

CMMS Aruna adalah platform manajemen pemeliharaan terpadu yang dirancang khusus untuk operasional **PT Aruna Hijau Power**. Sistem ini membantu dalam mengelola aset, menjadwalkan perawatan rutin, dan mendokumentasikan setiap aktivitas teknis secara digital dan terstruktur.

## 🚀 Fitur Utama

### 1. Manajemen Aset & Lokasi
- Pelacakan aset internal dan lokasi inspeksi.
- Dukungan untuk **Klien Eksternal**, memungkinkan pencatatan pengerjaan di lokasi luar tanpa harus terdaftar sebagai aset internal.
- Hierarki lokasi yang jelas untuk mempermudah identifikasi unit.

### 2. Penjadwalan Pemeliharaan (Preventive Maintenance)
- Pembuatan jadwal otomatis berdasarkan frekuensi (Mingguan, Bulanan, Triwulan, Semesteran, Tahunan).
- **Start Date Logic**: Jadwal hanya akan aktif dan menghasilkan checksheet setelah tanggal mulai yang ditentukan.
- Kalender pengerjaan (Timeline) untuk visualisasi beban kerja tim.

### 3. Sistem Work Order (Perintah Kerja)
- Manajemen alur kerja dari status **Open**, **In Progress**, hingga **Closed** atau **Canceled**.
- Penugasan teknisi secara spesifik untuk setiap pekerjaan.
- Fitur pembatalan (Cancel) dengan kewajiban mengisi alasan pembatalan.

### 4. Record & Dokumentasi (Maintenance Records)
- Dokumentasi hasil pengerjaan (Corrective & Preventive).
- Upload foto bukti pengerjaan (mendukung file hingga 10MB).
- Pencatatan penggunaan suku cadang (Spare Parts) secara otomatis yang memotong stok inventaris.
- Otomatisasi pengerjaan lanjutan (**Follow-up**) jika status akhir pengerjaan dinyatakan "Pending" atau "Failure".

### 5. Inventaris & Logistik
- Pengelolaan stok **Spare Parts**, **Tools** (Peralatan), dan **Consumables**.
- Riwayat penggunaan item untuk setiap aktivitas pemeliharaan.

### 6. Laporan & KPI Dashboard
- Dashboard KPI real-time (Maintenance Compliance, MTTR, MTBF).
- **Schedule Report**: Rekapitulasi tahunan pengerjaan dalam format grid minggu-bulan (W1-W4).
- Export laporan ke format PDF.

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
