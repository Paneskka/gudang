<?php
$host = "localhost";
$user = "root";       // sesuaikan
$pass = "";            // sesuaikan
$dbname = "db_gudang";

// Override kredensial lewat file yang tidak di-commit (lokal maupun hosting).
if (file_exists(__DIR__ . "/db.local.php")) {
    require __DIR__ . "/db.local.php";
}

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// ================= SETUP TABEL ANTRIAN BARANG =================
// Tabel untuk menyimpan detail barang per antrian
$conn->query(
    "CREATE TABLE IF NOT EXISTS antrian_barang (
        id INT AUTO_INCREMENT PRIMARY KEY,
        antrian_id INT NOT NULL,
        barang_id INT DEFAULT NULL,
        kode_barang VARCHAR(30) DEFAULT NULL,
        nama_barang VARCHAR(120) NOT NULL,
        jumlah INT NOT NULL,
        satuan VARCHAR(20) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_antrian (antrian_id),
        INDEX idx_barang (barang_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

// Tambah kolom barang_diperiksa di tabel antrian jika belum ada
$res = $conn->query("SHOW COLUMNS FROM antrian LIKE 'barang_diperiksa'");
if ($res && $res->num_rows === 0) {
    $conn->query("ALTER TABLE antrian ADD COLUMN barang_diperiksa TINYINT(1) NOT NULL DEFAULT 0 AFTER status");
}

// Satu panggilan terakhir untuk setiap jalur bongkar/muat.
$conn->query(
    "CREATE TABLE IF NOT EXISTS antrian_panggilan (
        jenis_kegiatan ENUM('muat','bongkar') NOT NULL PRIMARY KEY,
        antrian_id INT NOT NULL,
        dipanggil_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

// ================= SETUP KOLOM HAK AKSES =================
// Role default 'admin' supaya akun lama tidak kehilangan akses
$res = $conn->query("SHOW COLUMNS FROM admin LIKE 'role'");
if ($res && $res->num_rows === 0) {
    $conn->query("ALTER TABLE admin ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'admin' AFTER password");
}

$res = $conn->query("SHOW COLUMNS FROM admin LIKE 'aktif'");
if ($res && $res->num_rows === 0) {
    $conn->query("ALTER TABLE admin ADD COLUMN aktif TINYINT(1) NOT NULL DEFAULT 1 AFTER role");
}
?>
