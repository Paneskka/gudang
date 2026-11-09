<?php
require "config/db.php";

$page_title  = "Dashboard - Sistem Antrian Gudang";
$active_page = "dashboard";

// ================= DATA RINGKASAN =================
// Total barang
$total_barang = 0;
$res = $conn->query("SHOW TABLES LIKE 'barang'");
if ($res && $res->num_rows > 0) {
    $q = $conn->query("SELECT COUNT(*) AS total FROM barang");
    if ($q) {
        $total_barang = (int) $q->fetch_assoc()["total"];
    }
}

// Data antrian
$antrian_aktif   = 0;
$antrian_masuk   = 0;
$antrian_keluar  = 0;
$selesai_hari_ini = 0;

$resAntrian = $conn->query("SHOW TABLES LIKE 'antrian'");
if ($resAntrian && $resAntrian->num_rows > 0) {
    // Antrian aktif: menunggu + diproses
    $q = $conn->query("SELECT COUNT(*) AS total FROM antrian WHERE status IN ('menunggu','diproses')");
    if ($q && ($row = $q->fetch_assoc())) {
        $antrian_aktif = (int) $row["total"];
    }

    // Antrian masuk (bongkar)
    $q = $conn->query("SELECT COUNT(*) AS total FROM antrian WHERE jenis_kegiatan = 'bongkar'");
    if ($q && ($row = $q->fetch_assoc())) {
        $antrian_masuk = (int) $row["total"];
    }

    // Antrian keluar (muat)
    $q = $conn->query("SELECT COUNT(*) AS total FROM antrian WHERE jenis_kegiatan = 'muat'");
    if ($q && ($row = $q->fetch_assoc())) {
        $antrian_keluar = (int) $row["total"];
    }

    // Selesai hari ini
    $q = $conn->query("SELECT COUNT(*) AS total FROM antrian WHERE status = 'selesai' AND DATE(created_at) = CURDATE()");
    if ($q && ($row = $q->fetch_assoc())) {
        $selesai_hari_ini = (int) $row["total"];
    }
}

require "config/header.php";
?>

            <section class="hero-stat">
                <p class="hero-label">Antrian aktif sekarang</p>
                <p class="hero-number"><?= $antrian_aktif ?></p>
                <p class="hero-sub">Kendaraan/barang sedang menunggu diproses di area bongkar muat.</p>
            </section>

            <section class="stat-row">
                <div class="stat">
                    <span class="stat-value"><?= $total_barang ?></span>
                    <span class="stat-label">Total barang</span>
                </div>
                <div class="stat">
                    <span class="stat-value"><?= $antrian_masuk ?></span>
                    <span class="stat-label">Antrian masuk</span>
                </div>
                <div class="stat">
                    <span class="stat-value"><?= $antrian_keluar ?></span>
                    <span class="stat-label">Antrian keluar</span>
                </div>
                <div class="stat">
                    <span class="stat-value"><?= $selesai_hari_ini ?></span>
                    <span class="stat-label">Selesai hari ini</span>
                </div>
            </section>

            <section class="quick-links">
                <a href="barang.php" class="quick-link">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                        <path d="M21 8 12 3 3 8l9 5 9-5Z"/>
                        <path d="M3 8v8l9 5 9-5V8"/>
                        <path d="M12 13v8"/>
                    </svg>
                    <span>Data Barang</span>
                </a>
                <a href="antrian_masuk.php" class="quick-link">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                        <path d="M3 13h11l3 5h4"/>
                        <path d="M3 13V6h9v7"/>
                        <circle cx="7" cy="18" r="2"/>
                        <circle cx="17" cy="18" r="2"/>
                    </svg>
                    <span>Antrian Masuk</span>
                </a>
                <a href="antrian_keluar.php" class="quick-link">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                        <path d="M21 13H10l-3 5H3"/>
                        <path d="M21 13V6h-9v7"/>
                        <circle cx="7" cy="18" r="2"/>
                        <circle cx="17" cy="18" r="2"/>
                    </svg>
                    <span>Antrian Keluar</span>
                </a>
                <?php if (is_admin()): ?>
                    <a href="laporan.php" class="quick-link">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                            <path d="M4 19V10M11 19V5M18 19v-7"/>
                        </svg>
                        <span>Laporan</span>
                    </a>
                <?php endif; ?>
            </section>

            <section class="activity">
                <div class="activity-head">
                    <h2>Aktivitas terbaru</h2>
                </div>

                <div class="activity-empty">
                    <p>Belum ada aktivitas antrian yang tercatat.</p>
                    <p class="activity-empty-sub">Aktivitas akan muncul di sini setelah tabel antrian tersambung.</p>
                </div>
            </section>

<?php require "config/footer.php"; ?>
