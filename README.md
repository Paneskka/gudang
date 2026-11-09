# Sistem Antrian Gudang

Aplikasi web PHP native + MySQL untuk manajemen antrian kendaraan bongkar/muat dan stok barang gudang.

## Fitur

- Login admin/petugas (role-based)
- Dashboard ringkasan stok & antrian
- Data barang (CRUD)
- Antrian masuk & keluar (nomor antrian, driver, plat, perusahaan, status)
- Papan antrian dipanggil untuk bongkar/muat, diperbarui otomatis setiap 5 detik
- Detail barang per antrian
- Laporan
- Kelola user (khusus admin)

## Teknologi

- PHP 8.x (tanpa framework, tanpa Composer)
- MySQL / MariaDB
- CSS murni

## Struktur

```
├── assets/css/       Stylesheet
├── config/           Koneksi DB, auth, header/footer, komponen
├── database/         schema.sql & seed.sql
├── index.php         Halaman antrian publik
├── login.php         Login admin
├── dashboard.php     Dashboard
├── barang.php        Data barang
├── antrian_masuk.php / antrian_keluar.php
├── laporan.php
└── kelola_user.php
```

## Setup Lokal (Laragon/XAMPP)

1. Jalankan Apache + MySQL.
2. Buat database `db_gudang` di phpMyAdmin.
3. Import `database/schema.sql`, lalu `database/seed.sql`.
4. Sesuaikan kredensial di `config/db.php`
   (atau salin `config/db.local.php.example` menjadi `config/db.local.php` dan isi di sana).
5. Buka `http://localhost/gudang`.

Login default: `admin` / `admin123` — **ganti setelah login pertama**.

## Memanggil Antrian

1. Admin atau petugas membuka **Antrian Masuk** (bongkar) atau **Antrian Keluar** (muat).
2. Klik **Panggil** pada kendaraan. Status menjadi **Diproses** dan nomor serta plat muncul di halaman pendaftaran.
3. Setiap jalur menampilkan satu panggilan terakhir. **Panggil Ulang** menampilkan kembali kendaraan yang sama.
4. Saat antrian selesai, dikembalikan ke **Menunggu**, atau dihapus, panggilannya hilang dari papan.

Papan memperbarui datanya tanpa memuat ulang formulir pendaftaran. Mengubah status lewat dropdown saja tidak membuat panggilan baru. Tabel `antrian_panggilan` dibuat otomatis saat aplikasi dibuka; data antrian lama tetap dapat dipanggil.

## Deploy ke InfinityFree

1. Buat hosting account di https://infinityfree.com, catat subdomain.
2. vPanel → **MySQL Databases** → buat database `db_gudang`.
   Catat host (`sqlXXX.infinityfree.com`), nama DB (`if0_xxxx_db_gudang`), user, dan password.
3. vPanel → **phpMyAdmin** → pilih database → tab **Import** → upload `database/schema.sql`,
   lalu ulangi untuk `database/seed.sql`.
4. Upload seluruh file project ke folder `htdocs/` (File Manager atau FTP).
   Jangan upload `a.php` dan folder `database/` bila tidak perlu.
5. Buat file `config/db.local.php` di server berisi kredensial dari langkah 2.
6. Buka `https://subdomain-kamu.infinityfreeapp.com`.

Catatan: InfinityFree tidak menyediakan SSH maupun git pull.
Update kode dilakukan dengan upload ulang via FTP/File Manager.

## Keamanan

- `config/db.local.php` dan `a.php` tidak di-commit (lihat `.gitignore`).
- Password disimpan dengan `password_hash()` (bcrypt).
