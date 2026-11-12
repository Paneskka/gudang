<img src="assets/logo.svg" alt="Gudang Logo" width="200">

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

## Tech Stack

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

## Memanggil Antrian

1. Admin atau petugas membuka **Antrian Masuk** (bongkar) atau **Antrian Keluar** (muat).
2. Klik **Panggil** pada kendaraan. Status menjadi **Diproses** dan nomor serta plat muncul di halaman pendaftaran.
3. Setiap jalur menampilkan satu panggilan terakhir. **Panggil Ulang** menampilkan kembali kendaraan yang sama.
4. Saat antrian selesai, dikembalikan ke **Menunggu**, atau dihapus, panggilannya hilang dari papan.

Papan memperbarui datanya tanpa memuat ulang formulir pendaftaran. Mengubah status lewat dropdown saja tidak membuat panggilan baru. Tabel `antrian_panggilan` dibuat otomatis saat aplikasi dibuka; data antrian lama tetap dapat dipanggil.

Login default: `admin` / `admin123` — **ganti setelah login pertama**.
