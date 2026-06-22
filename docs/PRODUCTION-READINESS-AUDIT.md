# Aruna CMMS — Audit Kesiapan Produksi

**Tanggal:** 2026-06-22 · **Auditor:** CTO (Claude) · **Branch:** main · **Basis:** pembacaan kode langsung, bukan asumsi.

Severity: 🔴 Blocker (jangan rilis ke produksi sebelum beres) · 🟠 Tinggi · 🟡 Sedang · 🟢 Catatan.

---

## Ringkasan Eksekutif

Aplikasi sudah fungsional dan kaya fitur, tapi **belum siap produksi** untuk dipakai client membuat keputusan maintenance. Ada tiga blocker kelas-rilis: **otorisasi yang bocor di hampir semua resource**, **nol tes** pada logika bisnis kritikal, dan **integritas data** (stok bisa minus, sebagian besar operasi multi-langkah tidak transaksional). Sisanya utang teknis yang bisa dicicil.

---

## 🔴 BLOCKER

### B1. Otorisasi bocor di hampir semua resource
- `Route::resource` untuk assets, spare-parts, tools, consumables, maintenance-schedules, findings, work-orders, maintenance-records, daily-reports semuanya hanya di balik middleware `auth` + `verified` — **tanpa gating peran**.
- `grep authorize(/Gate::/can(/policy(` di seluruh `app/Http/Controllers` = **0 hasil**. Tidak ada `app/Policies`.
- Akibat: **teknisi (atau user terautentikasi mana pun) bisa create/update/delete aset & work order**, termasuk milik pekerjaan orang lain. Tidak ada pembatasan kepemilikan/lokasi (IDOR).
- Otorisasi yang ADA tersebar & tidak konsisten: `CheckRole` middleware (cek `user->role` string), `spatie/laravel-permission` (model `Role` punya permissions), dan cek inline manual `authorizeAdmin()` di `SettingsController`. Tiga sistem berbeda.
- **Rekomendasi:** Pilih SATU sumber kebenaran (spatie permissions), buat `Policy` per model, panggil `authorize()` di tiap aksi controller, dan tes setiap aturan. Hapus dualitas role-string vs permission.

### B2. Nol tes pada logika bisnis
- Hanya scaffolding auth Breeze + `ExampleTest`. `grep` fungsi tes bisnis = **0**.
- Tidak teruji: pemotongan stok, kalkulasi MTTR/MTBF/compliance, generasi checksheet dari jadwal, transisi status work order, follow-up otomatis.
- **Rekomendasi:** Tulis tes Feature untuk alur uang/stok & status lebih dulu (TDD untuk perubahan berikutnya). Target awal: jalur kritikal, bukan 100% coverage.

### B3. Integritas data
- Hanya **1 file** di seluruh `app/` memakai `DB::transaction`. Operasi multi-tabel lain (submit checksheet, ubah status WO + log + checklist) **tidak transaksional** → bisa setengah jadi saat error.
- Pemotongan stok di `MaintenanceRecordController` (`$sparePart->decrement('qty_actual', ...)`, baris ~186) **tidak cek kecukupan stok** dan **tanpa `lockForUpdate`** → stok bisa **minus** dan rawan race condition. (Bandingkan `SparePartController::adjustStock` yang sudah cek, baris ~106.)
- **Rekomendasi:** Bungkus semua write multi-langkah dalam transaksi; validasi stok ≥ qty sebelum decrement; pakai `lockForUpdate` pada baris stok.

---

## 🟠 TINGGI

### H1. `env()` dipakai di luar config → AI chat mati di produksi
- `GeminiService` membaca `env('GEMINI_API_KEY')` & `env('GEMINI_MODEL')` di runtime. Setelah `php artisan config:cache` (standar produksi), `env()` mengembalikan `null` → fitur AI rusak senyap.
- `GEMINI_API_KEY` juga **tidak ada di `.env.example`** (undocumented).
- **Rekomendasi:** Pindahkan ke `config/services.php`, baca via `config('services.gemini.key')`, tambahkan ke `.env.example`.

### H2. AI agent bisa menembus otorisasi
- `GeminiService` mendeklarasikan function-calling yang bisa **create/update/delete** aset, item, work order, jadwal. Karena tidak ada policy (lihat B1), perlu dipastikan eksekusi tool di `ChatController` menghormati peran user — kalau tidak, AI jadi jalur eskalasi hak akses.
- **Rekomendasi:** Jalankan setiap aksi AI lewat lapisan otorisasi yang sama dengan controller (Policy/Gate), bukan langsung ke model.

### H3. Default keamanan & konfigurasi
- `.env.example`: `APP_DEBUG=true`, `DB_CONNECTION=sqlite` (README bilang MySQL), `LOG_LEVEL=debug`. Mudah ikut terbawa ke server.
- Identitas hardcoded di kode: email `wakwaw@gmail.com` & peran `super-admin` disembunyikan di query — backdoor by obscurity, sulit diaudit/dirotasi.
- **Rekomendasi:** Sediakan `.env.production` checklist (DEBUG=false, MySQL, log warning); pindahkan super-admin ke flag/permission yang eksplisit.

---

## 🟡 SEDANG

- **M1. Tidak ada CI/CD.** `.github/workflows` kosong. Tidak ada gerbang otomatis (test, Pint, static analysis) sebelum merge ke `main`. → Tambah GitHub Actions: `pint --test`, `phpstan`, `php artisan test`.
- **M2. Tidak ada static analysis.** Tidak ada Larastan/PHPStan. → Pasang `larastan/larastan` level 5+ bertahap.
- **M3. Migrasi churn (67 file, banyak alter/rename), belum di-squash.** Memperlambat setup & rawan beda dev/prod. → Squash baseline setelah rilis pertama.
- **M4. Tailwind v3 & v4 bercampur** (`tailwindcss ^3.1.0` + `@tailwindcss/vite ^4.0.0`). → Tetapkan satu versi.
- **M5. README/todo bilang "Laravel 11", `composer.json` minta `^13.0`.** Dokumentasi tidak sinkron. → Samakan & verifikasi versi terkunci.

---

## 🟢 Yang Sudah Baik

- `$fillable` terdefinisi di model (mass-assignment terkendali).
- Validasi upload file pakai `image`/`mimes`/`max` (mis. maintenance records, avatar, daily report).
- `SettingsController` punya guard internal (`authorizeAdmin`/`authorizeDeveloper`).
- Pakai SoftDeletes pada aset; spatie permission sudah terpasang (tinggal dikonsistenkan).

---

## Urutan Kerja yang Disarankan

1. **B3 (stok/transaksi)** — risiko korupsi data, perbaikan terlokalisasi & cepat menang.
2. **B1 + H2 (otorisasi: Policy + AI)** — lubang keamanan terbesar.
3. **B2 (tes jalur kritikal)** — kunci agar perbaikan tidak regresi.
4. **H1, H3 (config/secret produksi)** — murah, dampak tinggi.
5. **M1, M2 (CI + static analysis)** — gerbang permanen.
6. **M3–M5 (utang teknis)** — setelah rilis pertama.
