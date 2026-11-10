<?php
require "config/db.php";
require "config/auth.php";

$page_title = "Dashboard - Sistem Antrian Gudang";
$active_page = "dashboard";

$total_barang = 0;
$res = $conn->query("SHOW TABLES LIKE 'barang'");
if ($res && $res->num_rows > 0) {
    $total_barang = (int) $conn->query("SELECT COUNT(*) AS total FROM barang")->fetch_assoc()["total"];
}

$antrian_aktif = $antrian_masuk = $antrian_keluar = $selesai_hari_ini = 0;
$aktivitas = [];
$hari_ini = new DateTimeImmutable($conn->query("SELECT CURDATE() AS tanggal")->fetch_assoc()["tanggal"]);
$awal_grafik = $hari_ini->modify("-6 days");
$grafik_antrian = [];
for ($i = 0; $i < 7; $i++) {
    $tanggal = $awal_grafik->modify("+$i days")->format("Y-m-d");
    $grafik_antrian[$tanggal] = ["bongkar" => 0, "muat" => 0];
}
$res = $conn->query("SHOW TABLES LIKE 'antrian'");
if ($res && $res->num_rows > 0) {
    $summary = $conn->query(
        "SELECT SUM(status IN ('menunggu','diproses')) AS aktif,
                SUM(jenis_kegiatan = 'bongkar') AS masuk,
                SUM(jenis_kegiatan = 'muat') AS keluar,
                SUM(status = 'selesai' AND DATE(created_at) = CURDATE()) AS selesai
         FROM antrian"
    )->fetch_assoc();
    $antrian_aktif = (int) $summary["aktif"];
    $antrian_masuk = (int) $summary["masuk"];
    $antrian_keluar = (int) $summary["keluar"];
    $selesai_hari_ini = (int) $summary["selesai"];
    $aktivitas = $conn->query(
        "SELECT nomor_antrian, driver, plat_nomor, jenis_kegiatan, status, created_at
         FROM antrian ORDER BY created_at DESC, id DESC LIMIT 5"
    )->fetch_all(MYSQLI_ASSOC);

    // Semua pendaftaran per hari, termasuk hari tanpa antrian.
    $dari_grafik = $awal_grafik->format("Y-m-d");
    $sampai_grafik = $hari_ini->modify("+1 day")->format("Y-m-d");
    $stmt_grafik = $conn->prepare(
        "SELECT DATE(created_at) AS tanggal,
                SUM(jenis_kegiatan = 'bongkar') AS bongkar,
                SUM(jenis_kegiatan = 'muat') AS muat
         FROM antrian WHERE created_at >= ? AND created_at < ?
         GROUP BY DATE(created_at) ORDER BY tanggal"
    );
    $stmt_grafik->bind_param("ss", $dari_grafik, $sampai_grafik);
    $stmt_grafik->execute();
    $hasil_grafik = $stmt_grafik->get_result();
    while ($row = $hasil_grafik->fetch_assoc()) {
        $grafik_antrian[$row["tanggal"]] = ["bongkar" => (int) $row["bongkar"], "muat" => (int) $row["muat"]];
    }
    $stmt_grafik->close();
}

require "config/header.php";
?>
<div class="welcome">
    <div>
        <span class="eyebrow">Ringkasan operasional</span>
        <h1>Selamat datang, <?= htmlspecialchars($admin_username) ?>.</h1>
        <p>Mari buat aktivitas gudang hari ini lebih tertata.</p>
    </div>
    <a href="index" class="btn btn-primary"><?= ui_icon('plus') ?> Daftarkan Kendaraan</a>
</div>

<section class="overview-banner" aria-label="Antrian aktif">
    <img class="overview-background" src="<?= ui_asset('assets/img/warehouse-background.webp') ?>" width="1800" height="1000" alt="" fetchpriority="high">
    <div class="overview-copy">
        <span class="eyebrow">Pantau alur gudang</span>
        <div class="overview-number"><strong><?= $antrian_aktif ?></strong><span>antrian aktif</span></div>
        <p>Kendaraan yang menunggu atau sedang diproses di area bongkar muat.</p>
        <a href="antrian-masuk" class="banner-link">Kelola antrian masuk <?= ui_icon('arrow') ?></a>
    </div>
</section>

<section class="stat-grid" aria-label="Statistik gudang">
    <article class="stat-card">
        <div class="stat-card-top"><span>Total barang</span><div class="stat-icon"><?= ui_icon('box') ?></div></div>
        <strong><?= number_format($total_barang, 0, ',', '.') ?></strong><small>Jenis barang terdaftar</small>
    </article>
    <article class="stat-card">
        <div class="stat-card-top"><span>Antrian masuk</span><div class="stat-icon blue"><?= ui_icon('in') ?></div></div>
        <strong><?= number_format($antrian_masuk, 0, ',', '.') ?></strong><small>Seluruh kegiatan bongkar</small>
    </article>
    <article class="stat-card">
        <div class="stat-card-top"><span>Antrian keluar</span><div class="stat-icon amber"><?= ui_icon('out') ?></div></div>
        <strong><?= number_format($antrian_keluar, 0, ',', '.') ?></strong><small>Seluruh kegiatan muat</small>
    </article>
    <article class="stat-card">
        <div class="stat-card-top"><span>Selesai hari ini</span><div class="stat-icon"><?= ui_icon('check-circle') ?></div></div>
        <strong><?= number_format($selesai_hari_ini, 0, ',', '.') ?></strong><small>Dari antrian terdaftar hari ini</small>
    </article>
</section>

<?php require "config/chart_antrian.php"; ?>

<div class="dashboard-bottom">
    <section class="activity-panel">
        <div class="section-heading">
            <div><h2>Aktivitas terbaru</h2><p>Lima pendaftaran antrian terakhir.</p></div>
            <?php if (is_admin()): ?><a class="text-link" href="laporan">Lihat laporan <?= ui_icon('arrow') ?></a><?php endif; ?>
        </div>
        <?php if (empty($aktivitas)): ?>
            <div class="empty">
                <?= ui_icon('truck') ?>
                <strong>Siap untuk antrian pertama</strong>
                <p>Kendaraan yang didaftarkan akan muncul di sini.</p>
                <a class="btn btn-ghost" href="index">Daftarkan kendaraan <?= ui_icon('arrow') ?></a>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="data">
                    <thead><tr><th>Antrian</th><th>Kendaraan</th><th>Waktu daftar</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php foreach ($aktivitas as $item): ?>
                            <tr>
                                <td><a class="queue-number" href="<?= $item['jenis_kegiatan'] === 'bongkar' ? 'antrian-masuk' : 'antrian-keluar' ?>?q=<?= urlencode($item['nomor_antrian']) ?>"><?= htmlspecialchars($item['nomor_antrian']) ?></a><div class="cell-muted"><?= $item['jenis_kegiatan'] === 'bongkar' ? 'Bongkar' : 'Muat' ?></div></td>
                                <td><span class="cell-strong cell-nowrap"><?= htmlspecialchars($item['plat_nomor']) ?></span><div class="cell-muted"><?= htmlspecialchars($item['driver']) ?></div></td>
                                <td class="cell-muted cell-nowrap"><?= ui_date($item['created_at']) ?><?php if ($item['created_at']): ?><div><?= date('H:i', strtotime($item['created_at'])) ?></div><?php endif; ?></td>
                                <td><span class="badge badge-<?= htmlspecialchars($item['status'] ?? 'menunggu') ?>"><?= htmlspecialchars(ucfirst($item['status'] ?? 'menunggu')) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
    <div class="dashboard-aside">
        <section class="quick-panel">
            <h2>Akses cepat</h2>
            <a href="barang#form-barang" class="quick-link"><span class="stat-icon"><?= ui_icon('box') ?></span><div><strong>Tambah barang</strong><small>Kelola inventaris gudang</small></div><?= ui_icon('arrow') ?></a>
            <a href="antrian-masuk" class="quick-link"><span class="stat-icon blue"><?= ui_icon('in') ?></span><div><strong>Barang masuk</strong><small>Proses antrian bongkar</small></div><?= ui_icon('arrow') ?></a>
            <a href="antrian-keluar" class="quick-link"><span class="stat-icon amber"><?= ui_icon('out') ?></span><div><strong>Barang keluar</strong><small>Proses antrian muat</small></div><?= ui_icon('arrow') ?></a>
        </section>
        <aside class="desk-note">
            <?= ui_icon('check-circle') ?><strong>Satu langkah lebih tertib.</strong>
            <p>Periksa detail barang sebelum menyelesaikan antrian agar stok gudang tercatat dengan tepat.</p>
        </aside>
    </div>
</div>
<?php require "config/footer.php"; ?>
