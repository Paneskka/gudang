<?php
require "config/db.php";
require "config/auth.php";
require_admin();

// ================= FILTER TANGGAL =================
$dari   = $_GET["dari"]   ?? date("Y-m-01");
$sampai = $_GET["sampai"] ?? date("Y-m-d");
$jenis  = $_GET["jenis"]  ?? "";
$status = $_GET["status"] ?? "";

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dari))   { $dari   = date("Y-m-01"); }
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $sampai)) { $sampai = date("Y-m-d"); }
if ($dari > $sampai) { [$dari, $sampai] = [$sampai, $dari]; }

$jenis_valid  = ["muat", "bongkar"];
$status_valid = ["menunggu", "diproses", "selesai"];

$where  = "DATE(created_at) BETWEEN ? AND ?";
$params = [$dari, $sampai];
$types  = "ss";

if (in_array($jenis, $jenis_valid, true)) {
    $where   .= " AND jenis_kegiatan = ?";
    $params[] = $jenis;
    $types   .= "s";
}

if (in_array($status, $status_valid, true)) {
    $where   .= " AND status = ?";
    $params[] = $status;
    $types   .= "s";
}

// ================= AMBIL DATA =================
$stmt = $conn->prepare("SELECT * FROM antrian WHERE $where ORDER BY created_at DESC, id DESC");
$stmt->bind_param($types, ...$params);
$stmt->execute();
$data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// ================= RINGKASAN =================
$ringkasan = [
    "total"    => count($data),
    "muat"     => 0,
    "bongkar"  => 0,
    "menunggu" => 0,
    "diproses" => 0,
    "selesai"  => 0,
];
$perusahaan = [];

foreach ($data as $d) {
    if (isset($ringkasan[$d["jenis_kegiatan"]])) {
        $ringkasan[$d["jenis_kegiatan"]]++;
    }
    if (isset($ringkasan[$d["status"]])) {
        $ringkasan[$d["status"]]++;
    }

    $pt = $d["perusahaan"] !== "" ? $d["perusahaan"] : "(Tanpa perusahaan)";
    if (!isset($perusahaan[$pt])) {
        $perusahaan[$pt] = ["total" => 0, "selesai" => 0];
    }
    $perusahaan[$pt]["total"]++;
    if ($d["status"] === "selesai") {
        $perusahaan[$pt]["selesai"]++;
    }
}

// Urutkan perusahaan dari antrian terbanyak
uasort($perusahaan, fn($a, $b) => $b["total"] <=> $a["total"]);

// ================= EXPORT CSV =================
if (($_GET["export"] ?? "") === "csv") {
    $nama_file = "laporan-antrian-{$dari}-sd-{$sampai}.csv";

    header("Content-Type: text/csv; charset=utf-8");
    header("Content-Disposition: attachment; filename=\"$nama_file\"");

    $out = fopen("php://output", "w");
    fwrite($out, "\xEF\xBB\xBF"); // BOM agar Excel membaca UTF-8
    fputcsv($out, ["No. Antrian", "Jenis", "Driver", "Plat Nomor", "Perusahaan", "Status", "Waktu Daftar"]);

    foreach ($data as $d) {
        fputcsv($out, [
            $d["nomor_antrian"],
            $d["jenis_kegiatan"],
            $d["driver"],
            $d["plat_nomor"],
            $d["perusahaan"],
            $d["status"],
            $d["created_at"],
        ]);
    }

    fclose($out);
    exit;
}

$page_title  = "Laporan - Sistem Antrian Gudang";
$active_page = "laporan";
require "config/header.php";

$bulan_ini = date("F Y", strtotime($dari)) === date("F Y", strtotime($sampai))
    ? date("F Y", strtotime($dari))
    : date("d/m/Y", strtotime($dari)) . " - " . date("d/m/Y", strtotime($sampai));

$qs_export = http_build_query(array_filter([
    "dari"   => $dari,
    "sampai" => $sampai,
    "jenis"  => $jenis,
    "status" => $status,
    "export" => "csv",
]));
?>

<div class="page-head">
    <div>
        <span class="eyebrow">Analisis operasional</span>
        <h1>Laporan Antrian</h1>
        <p>Rekap aktivitas antrian gudang periode <strong><?= htmlspecialchars($bulan_ini) ?></strong>.</p>
    </div>
    <div class="cell-actions no-print">
        <a href="laporan?<?= $qs_export ?>" class="btn btn-ghost"><?= ui_icon('in') ?> Export CSV</a>
        <button type="button" class="btn btn-primary" onclick="window.print()"><?= ui_icon('print') ?> Cetak</button>
    </div>
</div>

<section class="panel no-print">
    <h2>Filter Laporan</h2>
    <form method="get" class="toolbar">
        <div class="field">
            <label for="dari">Dari Tanggal</label>
            <input type="date" id="dari" name="dari" value="<?= htmlspecialchars($dari) ?>">
        </div>
        <div class="field">
            <label for="sampai">Sampai Tanggal</label>
            <input type="date" id="sampai" name="sampai" value="<?= htmlspecialchars($sampai) ?>">
        </div>
        <div class="field">
            <label for="jenis">Jenis Kegiatan</label>
            <select id="jenis" name="jenis">
                <option value="">Semua</option>
                <option value="bongkar" <?= $jenis === "bongkar" ? "selected" : "" ?>>Masuk (Bongkar)</option>
                <option value="muat"    <?= $jenis === "muat" ? "selected" : "" ?>>Keluar (Muat)</option>
            </select>
        </div>
        <div class="field">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="">Semua</option>
                <?php foreach ($status_valid as $s): ?>
                    <option value="<?= $s ?>" <?= $status === $s ? "selected" : "" ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Tampilkan</button>
        <a href="laporan" class="btn btn-ghost">Reset</a>
    </form>
</section>

<div class="tile-row">
    <div class="tile">
        <span class="tile-value"><?= $ringkasan["total"] ?></span>
        <span class="tile-label">Total antrian</span>
    </div>
    <div class="tile">
        <span class="tile-value"><?= $ringkasan["bongkar"] ?></span>
        <span class="tile-label">Antrian masuk (bongkar)</span>
    </div>
    <div class="tile">
        <span class="tile-value"><?= $ringkasan["muat"] ?></span>
        <span class="tile-label">Antrian keluar (muat)</span>
    </div>
    <div class="tile">
        <span class="tile-value"><?= $ringkasan["selesai"] ?></span>
        <span class="tile-label">Selesai</span>
    </div>
</div>

<section class="panel">
    <h2>Rekap Status</h2>
    <div class="table-wrap">
        <table class="data" style="min-width:420px;">
            <thead>
                <tr><th>Status</th><th>Jumlah</th><th>Persentase</th></tr>
            </thead>
            <tbody>
                <?php foreach (["menunggu", "diproses", "selesai"] as $s): ?>
                    <tr>
                        <td><span class="badge badge-<?= $s ?>"><?= ucfirst($s) ?></span></td>
                        <td class="cell-strong"><?= $ringkasan[$s] ?></td>
                        <td class="cell-muted">
                            <?= $ringkasan["total"] > 0
                                ? number_format($ringkasan[$s] / $ringkasan["total"] * 100, 1) . "%"
                                : "0%" ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="panel">
    <h2>Rekap per Perusahaan</h2>
    <?php if (empty($perusahaan)): ?>
        <div class="empty">Tidak ada data pada periode ini.</div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data" style="min-width:520px;">
                <thead>
                    <tr><th>Perusahaan</th><th>Total Antrian</th><th>Selesai</th><th>Belum Selesai</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($perusahaan as $nama => $p): ?>
                        <tr>
                            <td class="cell-strong"><?= htmlspecialchars($nama) ?></td>
                            <td><?= $p["total"] ?></td>
                            <td><?= $p["selesai"] ?></td>
                            <td class="cell-muted"><?= $p["total"] - $p["selesai"] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<section class="panel">
    <h2>Detail Antrian (<?= $ringkasan["total"] ?>)</h2>
    <?php if (empty($data)): ?>
        <div class="empty">Tidak ada antrian pada periode dan filter yang dipilih.</div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>No. Antrian</th>
                        <th>Jenis</th>
                        <th>Driver</th>
                        <th>Plat</th>
                        <th>Perusahaan</th>
                        <th>Status</th>
                        <th>Waktu Daftar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $d): ?>
                        <tr>
                            <td class="cell-strong"><?= htmlspecialchars($d["nomor_antrian"]) ?></td>
                            <td>
                                <span class="badge <?= $d["jenis_kegiatan"] === "bongkar" ? "badge-masuk" : "badge-keluar" ?>">
                                    <?= $d["jenis_kegiatan"] === "bongkar" ? "Masuk" : "Keluar" ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($d["driver"]) ?></td>
                            <td class="cell-strong cell-nowrap"><?= htmlspecialchars($d["plat_nomor"]) ?></td>
                            <td><?= htmlspecialchars($d["perusahaan"]) ?></td>
                            <td><span class="badge badge-<?= $d["status"] ?>"><?= ucfirst($d["status"]) ?></span></td>
                            <td class="cell-muted cell-nowrap"><?= date("d/m/Y H:i", strtotime($d["created_at"])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php require "config/footer.php"; ?>
