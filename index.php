<?php
require "config/db.php";
require_once "config/panggilan.php";

$pesan_daftar = "";
$pendaftaran_berhasil = false;
$hasil_status = null;
$pesan_status = "";

// Proses pendaftaran, penomoran berurutan per jenis kegiatan.
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["daftar_antrian"])) {
    $driver = trim($conn->real_escape_string($_POST["driver"] ?? ""));
    $plate = strtoupper(trim($conn->real_escape_string($_POST["plate"] ?? "")));
    $company = trim($conn->real_escape_string($_POST["company"] ?? ""));
    $activity = $_POST["activity"] ?? "";

    if ($driver === "" || $plate === "" || $company === "" || !in_array($activity, ["muat", "bongkar"], true)) {
        $pesan_daftar = "Lengkapi data kendaraan dan pilih jenis kegiatan.";
    } else {
        $prefix = $activity === "muat" ? "M" : "B";
        $stmtCount = $conn->prepare(
            "SELECT MAX(CAST(SUBSTRING(nomor_antrian, 2) AS UNSIGNED)) AS max_no
             FROM antrian WHERE nomor_antrian LIKE ?"
        );
        $likePrefix = $prefix . "%";
        $stmtCount->bind_param("s", $likePrefix);
        $stmtCount->execute();
        $row = $stmtCount->get_result()->fetch_assoc();
        $nomor_urut = ($row["max_no"] ?? 0) + 1;
        $nomor_antrian = $prefix . str_pad($nomor_urut, 3, "0", STR_PAD_LEFT);

        $stmt = $conn->prepare(
            "INSERT INTO antrian (nomor_antrian, driver, plat_nomor, perusahaan, jenis_kegiatan) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("sssss", $nomor_antrian, $driver, $plate, $company, $activity);
        if ($stmt->execute()) {
            $pendaftaran_berhasil = true;
            $pesan_daftar = "Pendaftaran berhasil! Nomor antrian Anda: <strong>$nomor_antrian</strong>";
        } else {
            $pesan_daftar = "Gagal menyimpan data. Silakan coba lagi.";
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["cek_status"])) {
    $nomor_cari = strtoupper(trim($conn->real_escape_string($_POST["nomor_antrian"] ?? "")));
    if ($nomor_cari === "") {
        $pesan_status = "Masukkan nomor antrian terlebih dahulu.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM antrian WHERE nomor_antrian = ? ORDER BY created_at DESC LIMIT 1");
        $stmt->bind_param("s", $nomor_cari);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) $hasil_status = $result->fetch_assoc();
        else $pesan_status = "Nomor antrian \"$nomor_cari\" tidak ditemukan.";
    }
}

$panggilan = panggilan_aktif($conn);
require "config/public_page.php";
