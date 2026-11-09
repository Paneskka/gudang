<?php
// Jalankan: php tests/panggilan.php
// Tabel sementara menutupi tabel aplikasi pada koneksi ini saja.
if (PHP_SAPI !== "cli") {
    http_response_code(404);
    exit;
}

require __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/panggilan.php";

function periksa(bool $valid, string $pesan): void
{
    if (!$valid) throw new RuntimeException($pesan);
}

try {
    foreach (["antrian", "antrian_panggilan"] as $table) {
        $ddl = $conn->query("SHOW CREATE TABLE $table")->fetch_assoc()["Create Table"];
        $conn->query(str_replace("CREATE TABLE", "CREATE TEMPORARY TABLE", $ddl));
    }
    $conn->query(
        "INSERT INTO antrian (id, nomor_antrian, driver, plat_nomor, perusahaan, jenis_kegiatan, status) VALUES
         (1, 'B001', 'Uji', 'B 1001 QA', 'Uji', 'bongkar', 'menunggu'),
         (2, 'B002', 'Uji', 'B 1002 QA', 'Uji', 'bongkar', 'diproses'),
         (3, 'M001', 'Uji', 'B 1003 QA', 'Uji', 'muat', 'menunggu'),
         (4, 'B003', 'Uji', 'B 1004 QA', 'Uji', 'bongkar', 'selesai')"
    );
    periksa(panggilan_aktif($conn) === ["bongkar" => null, "muat" => null], "Status diproses saja bukan panggilan.");

    periksa(panggil_antrian($conn, 1, "bongkar") === "B001", "Nomor panggilan salah.");
    periksa($conn->query("SELECT status FROM antrian WHERE id = 1")->fetch_assoc()["status"] === "diproses", "Panggil harus memulai proses.");
    panggil_antrian($conn, 3, "muat");
    $aktif = panggilan_aktif($conn);
    periksa($aktif["bongkar"]["id"] === 1 && $aktif["muat"]["id"] === 3, "Kedua jalur harus independen.");

    panggil_antrian($conn, 2, "bongkar");
    hapus_panggilan($conn, 1, "bongkar");
    periksa(panggilan_aktif($conn)["bongkar"]["id"] === 2, "Menghapus panggilan lama tidak boleh menghapus panggilan baru.");
    $conn->query("UPDATE antrian_panggilan SET dipanggil_at = '2000-01-01' WHERE jenis_kegiatan = 'bongkar'");
    panggil_antrian($conn, 2, "bongkar");
    periksa(panggilan_aktif($conn)["bongkar"]["dipanggil_at"] > "2000-01-02", "Panggil ulang harus memperbarui waktu.");

    foreach ([[999, "bongkar"], [1, "muat"], [4, "bongkar"]] as [$id, $jenis]) {
        $sebelum = panggilan_aktif($conn);
        $ditolak = false;
        try { panggil_antrian($conn, $id, $jenis); }
        catch (DomainException $e) { $ditolak = true; }
        periksa($ditolak && panggilan_aktif($conn) === $sebelum, "ID salah, jalur salah, atau antrian selesai harus ditolak tanpa mengubah papan.");
    }

    $conn->query("UPDATE antrian SET status = 'selesai' WHERE id = 2");
    periksa(panggilan_aktif($conn)["bongkar"] === null, "Panggilan selesai tidak boleh menampilkan kembali panggilan lama.");
    hapus_panggilan($conn, 2, "bongkar");
    $conn->query("UPDATE antrian SET status = 'diproses' WHERE id = 2");
    periksa(panggilan_aktif($conn)["bongkar"] === null, "Panggilan yang dihapus tidak boleh muncul tanpa Panggil.");
    $conn->query("DELETE FROM antrian WHERE id = 3");
    periksa(panggilan_aktif($conn)["muat"] === null, "Antrian terhapus tidak boleh ditampilkan.");

    // Paksa kegagalan penulisan papan untuk memastikan perubahan status ikut rollback.
    $conn->query("ALTER TABLE antrian_panggilan ADD CONSTRAINT uji_panggilan CHECK (antrian_id <> 1)");
    $conn->query("UPDATE antrian SET status = 'menunggu' WHERE id = 1");
    $gagal = false;
    try { panggil_antrian($conn, 1, "bongkar"); }
    catch (mysqli_sql_exception $e) { $gagal = true; }
    periksa($gagal, "Simulasi kegagalan penulisan tidak berjalan.");
    periksa($conn->query("SELECT status FROM antrian WHERE id = 1")->fetch_assoc()["status"] === "menunggu", "Status harus rollback jika papan gagal diperbarui.");
    echo "PASS: panggilan, pemisahan jalur, panggil ulang, validasi, pembersihan, dan rollback.\n";
} finally {
    $conn->query("DROP TEMPORARY TABLE IF EXISTS antrian_panggilan");
    $conn->query("DROP TEMPORARY TABLE IF EXISTS antrian");
    $conn->close();
}
