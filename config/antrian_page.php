<?php
// ================= HALAMAN ANTRIAN (DIPAKAI BERSAMA) =================
// Variabel yang harus sudah diisi oleh file pemanggil:
//   $jenis_kegiatan : 'bongkar' (masuk) atau 'muat' (keluar)
//   $jenis_label    : 'Masuk' / 'Keluar'
//   $page_title, $active_page

require "config/db.php";
require "config/auth.php";
require_once "config/panggilan.php";

$jenis_kegiatan = $jenis_kegiatan ?? "bongkar";
$jenis_label    = $jenis_label ?? "Masuk";

$status_valid    = ["menunggu", "diproses", "selesai"];
$status_dropdown = ["menunggu", "diproses"]; // tanpa "selesai" (pake tombol)
$pesan = $_SESSION["panggilan_pesan"] ?? "";
unset($_SESSION["panggilan_pesan"]);
$tipe_pesan = "success";
if (!isset($_SESSION["panggilan_token"])) {
    $_SESSION["panggilan_token"] = bin2hex(random_bytes(32));
}

// ================= AKSI =================
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $aksi = $_POST["aksi"] ?? "";

    if ($aksi === "panggil") {
        $token = $_POST["panggilan_token"] ?? "";
        if (!is_string($token) || !hash_equals($_SESSION["panggilan_token"], $token)) {
            $pesan = "Sesi formulir tidak valid. Muat ulang halaman lalu coba lagi.";
            $tipe_pesan = "error";
        } else {
            try {
                $nomor = panggil_antrian($conn, (int) ($_POST["id"] ?? 0), $jenis_kegiatan);
                $_SESSION["panggilan_pesan"] = "Antrian $nomor dipanggil dan ditampilkan di halaman pendaftaran. Status menjadi diproses.";
                $tujuan = $jenis_kegiatan === "bongkar" ? "antrian_masuk.php" : "antrian_keluar.php";
                $filter = http_build_query($_GET);
                header("Location: " . $tujuan . ($filter !== "" ? "?" . $filter : ""), true, 303);
                exit;
            } catch (DomainException $e) {
                $pesan = $e->getMessage();
                $tipe_pesan = "error";
            } catch (mysqli_sql_exception $e) {
                $pesan = "Gagal memanggil antrian. Silakan coba lagi.";
                $tipe_pesan = "error";
            }
        }
    }

    if ($aksi === "status") {
        $id     = (int) ($_POST["id"] ?? 0);
        $status = $_POST["status"] ?? "";

        if (!in_array($status, $status_valid, true)) {
            $pesan = "Status tidak valid.";
            $tipe_pesan = "error";
        } else {
            try {
                $conn->begin_transaction();
                $stmt = $conn->prepare("UPDATE antrian SET status = ? WHERE id = ? AND jenis_kegiatan = ? AND status <> 'selesai'");
                $stmt->bind_param("sis", $status, $id, $jenis_kegiatan);
                $stmt->execute();
                if ($status !== "diproses") {
                    hapus_panggilan($conn, $id, $jenis_kegiatan);
                }
                $conn->commit();
                $pesan = "Status antrian berhasil diperbarui.";
            } catch (mysqli_sql_exception $e) {
                $conn->rollback();
                $pesan = "Gagal memperbarui status.";
                $tipe_pesan = "error";
            }
        }
    }

    if ($aksi === "hapus") {
        if (!is_admin()) {
            $pesan = "Hanya admin yang boleh menghapus antrian.";
            $tipe_pesan = "error";
        } else {
            $id = (int) ($_POST["id"] ?? 0);
            try {
                $conn->begin_transaction();
                $stmt = $conn->prepare("SELECT id FROM antrian WHERE id = ? AND jenis_kegiatan = ? FOR UPDATE");
                $stmt->bind_param("is", $id, $jenis_kegiatan);
                $stmt->execute();
                if (!$stmt->get_result()->fetch_assoc()) {
                    throw new DomainException("Antrian tidak ditemukan pada jalur ini.");
                }
                // Hapus juga data barang terkait
                $stmt = $conn->prepare("DELETE FROM antrian_barang WHERE antrian_id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();

                $stmt = $conn->prepare("DELETE FROM antrian WHERE id = ? AND jenis_kegiatan = ?");
                $stmt->bind_param("is", $id, $jenis_kegiatan);
                $stmt->execute();
                hapus_panggilan($conn, $id, $jenis_kegiatan);
                $conn->commit();
                $pesan = "Data antrian berhasil dihapus.";
            } catch (mysqli_sql_exception | DomainException $e) {
                $conn->rollback();
                $pesan = "Gagal menghapus data antrian.";
                $tipe_pesan = "error";
            }
        }
    }

    // ================= SIMPAN BARANG & SELESAIKAN ANTRIAN =================
    if ($aksi === "simpan_barang") {
        $antrian_id = (int) ($_POST["antrian_id"] ?? 0);
        $items = $_POST["items"] ?? [];

        if ($antrian_id > 0 && !empty($items)) {
            try {
                $conn->begin_transaction();

                $stmt = $conn->prepare("SELECT id FROM antrian WHERE id = ? AND jenis_kegiatan = ? AND status <> 'selesai' FOR UPDATE");
                $stmt->bind_param("is", $antrian_id, $jenis_kegiatan);
                $stmt->execute();
                if (!$stmt->get_result()->fetch_assoc()) {
                    throw new DomainException("Antrian tidak tersedia atau sudah selesai.");
                }

                foreach ($items as $item) {
                    $barang_id = isset($item["barangId"]) && $item["barangId"] !== "" ? (int) $item["barangId"] : null;
                    $kode_brg  = isset($item["kodeBarang"]) ? trim($item["kodeBarang"]) : null;
                    $nama_brg  = isset($item["namaBarang"]) ? trim($item["namaBarang"]) : "";
                    $jumlah    = max(1, (int) ($item["jumlah"] ?? 0));
                    $satuan    = isset($item["satuan"]) ? trim($item["satuan"]) : "";

                    if ($nama_brg === "" || $jumlah <= 0) continue;

                    // Simpan ke tabel antrian_barang
                    $stmt = $conn->prepare(
                        "INSERT INTO antrian_barang (antrian_id, barang_id, kode_barang, nama_barang, jumlah, satuan)
                         VALUES (?, ?, ?, ?, ?, ?)"
                    );
                    $stmt->bind_param("iissis", $antrian_id, $barang_id, $kode_brg, $nama_brg, $jumlah, $satuan);
                    $stmt->execute();

                    // Update stok barang di tabel barang
                    if ($barang_id !== null && $barang_id > 0) {
                        if ($jenis_kegiatan === "bongkar") {
                            // Barang masuk -> Tambah stok
                            $stmt2 = $conn->prepare("UPDATE barang SET jumlah = jumlah + ? WHERE id = ?");
                            $stmt2->bind_param("ii", $jumlah, $barang_id);
                            $stmt2->execute();
                        } else {
                            // Barang keluar -> Kurangi stok (tidak kurang dari 0)
                            $stmt2 = $conn->prepare("UPDATE barang SET jumlah = GREATEST(0, jumlah - ?) WHERE id = ?");
                            $stmt2->bind_param("ii", $jumlah, $barang_id);
                            $stmt2->execute();
                        }
                    }
                }

                // Update status jadi selesai
                $stmt = $conn->prepare("UPDATE antrian SET status = 'selesai', barang_diperiksa = 1 WHERE id = ?");
                $stmt->bind_param("i", $antrian_id);
                $stmt->execute();

                hapus_panggilan($conn, $antrian_id, $jenis_kegiatan);
                $conn->commit();
                $pesan = "Barang berhasil disimpan. Antrian ditandai selesai.";
            } catch (mysqli_sql_exception | DomainException $e) {
                $conn->rollback();
                $pesan = "Gagal menyimpan data barang: " . $e->getMessage();
                $tipe_pesan = "error";
            }
        } else {
            $pesan = "Minimal harus ada 1 barang yang ditambahkan.";
            $tipe_pesan = "error";
        }
    }
}

// ================= FILTER & PAGINATION =================
$status  = $_GET["status"] ?? "";
$q       = trim($_GET["q"] ?? "");
$tanggal = $_GET["tanggal"] ?? "";
$page    = max(1, (int) ($_GET["page"] ?? 1));
$per_page = 15;
$offset   = ($page - 1) * $per_page;

$where  = "jenis_kegiatan = ?";
$params = [$jenis_kegiatan];
$types  = "s";

if (in_array($status, $status_valid, true)) {
    $where   .= " AND status = ?";
    $params[] = $status;
    $types   .= "s";
}

if ($q !== "") {
    $where   .= " AND (nomor_antrian LIKE ? OR driver LIKE ? OR plat_nomor LIKE ? OR perusahaan LIKE ?)";
    $like     = "%" . $q . "%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types   .= "ssss";
}

if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
    $where   .= " AND DATE(created_at) = ?";
    $params[] = $tanggal;
    $types   .= "s";
}

// Hitung total baris
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM antrian WHERE $where");
$stmt->bind_param($types, ...$params);
$stmt->execute();
$total_rows = (int) $stmt->get_result()->fetch_assoc()["total"];
$total_pages = max(1, (int) ceil($total_rows / $per_page));

if ($page > $total_pages) {
    $page = $total_pages;
    $offset = ($page - 1) * $per_page;
}

// Ambil data
$list_params = array_merge($params, [$per_page, $offset]);
$list_types  = $types . "ii";
$stmt = $conn->prepare("SELECT * FROM antrian WHERE $where ORDER BY created_at DESC, id DESC LIMIT ? OFFSET ?");
$stmt->bind_param($list_types, ...$list_params);
$stmt->execute();
$daftar = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Ambil jumlah barang per antrian (dengan full detail)
$id_list = array_column($daftar, "id");
$jumlah_barang_per_antrian = [];
$detail_barang_per_antrian = [];
if (!empty($id_list)) {
    $placeholders = str_repeat("?,", count($id_list) - 1) . "?";
    $stmt_barang = $conn->prepare(
        "SELECT ab.antrian_id, ab.id as ab_id, ab.barang_id, ab.kode_barang, ab.nama_barang, ab.jumlah, ab.satuan,
                b.nama_barang as nama_dari_db, b.satuan as satuan_db
         FROM antrian_barang ab
         LEFT JOIN barang b ON ab.barang_id = b.id
         WHERE ab.antrian_id IN ($placeholders)
         ORDER BY ab.antrian_id, ab.id"
    );
    $stmt_barang->bind_param(str_repeat("i", count($id_list)), ...$id_list);
    $stmt_barang->execute();
    $hasil_barang = $stmt_barang->get_result();
    while ($row = $hasil_barang->fetch_assoc()) {
        $aid = (int) $row["antrian_id"];
        if (!isset($jumlah_barang_per_antrian[$aid])) {
            $jumlah_barang_per_antrian[$aid] = ["item" => 0, "total" => 0];
            $detail_barang_per_antrian[$aid] = [];
        }
        $jumlah_barang_per_antrian[$aid]["item"]++;
        $jumlah_barang_per_antrian[$aid]["total"] += (int) $row["jumlah"];
        $detail_barang_per_antrian[$aid][] = [
            "id" => (int) $row["ab_id"],
            "barang_id" => $row["barang_id"] ? (int) $row["barang_id"] : null,
            "kode" => $row["kode_barang"],
            "nama" => $row["nama_barang"] ?: ($row["nama_dari_db"] ?? ""),
            "jumlah" => (int) $row["jumlah"],
            "satuan" => $row["satuan"] ?: ($row["satuan_db"] ?? "")
        ];
    }
}

// Ringkasan status
$stmt = $conn->prepare("SELECT status, COUNT(*) AS jumlah FROM antrian WHERE jenis_kegiatan = ? GROUP BY status");
$stmt->bind_param("s", $jenis_kegiatan);
$stmt->execute();
$ringkasan = ["menunggu" => 0, "diproses" => 0, "selesai" => 0];
$hasil_ringkasan = $stmt->get_result();
while ($row = $hasil_ringkasan->fetch_assoc()) {
    $ringkasan[$row["status"]] = (int) $row["jumlah"];
}
$total_jenis = array_sum($ringkasan);

// Query string untuk pagination
$query_params = [];
if ($status !== "")  { $query_params["status"]  = $status; }
if ($q !== "")       { $query_params["q"]       = $q; }
if ($tanggal !== "") { $query_params["tanggal"] = $tanggal; }
$base_qs = http_build_query($query_params);

$badge_jenis = $jenis_kegiatan === "bongkar" ? "badge-masuk" : "badge-keluar";
$panggilan_saat_ini = panggilan_aktif($conn)[$jenis_kegiatan];

// Encode detail barang untuk JS
$detail_barang_json = json_encode($detail_barang_per_antrian, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

require "config/header.php";
?>

<script>
window.detailBarangData = <?= $detail_barang_json ?>;
</script>

<div class="page-head">
    <div>
        <span class="eyebrow">Operasional gudang</span>
        <h1>Antrian <?= htmlspecialchars($jenis_label) ?></h1>
        <p>
            Daftar kendaraan <strong><?= $jenis_kegiatan === "bongkar" ? "bongkar" : "muat" ?></strong>
            (barang <?= strtolower($jenis_label) ?>) beserta status prosesnya.
        </p>
    </div>
    <a href="index.php" class="btn btn-primary" target="_blank" rel="noopener"><?= ui_icon('plus') ?> Daftarkan Kendaraan</a>
</div>

<?php if ($pesan !== ""): ?>
    <div class="alert alert-<?= $tipe_pesan === "error" ? "error" : "success" ?>">
        <?= htmlspecialchars($pesan) ?>
    </div>
<?php endif; ?>

<div class="tile-row">
    <div class="tile">
        <span class="tile-value"><?= $total_jenis ?></span>
        <span class="tile-label">Total antrian <?= strtolower($jenis_label) ?></span>
    </div>
    <div class="tile">
        <span class="tile-value"><?= $ringkasan["menunggu"] ?></span>
        <span class="tile-label">Menunggu</span>
    </div>
    <div class="tile">
        <span class="tile-value"><?= $ringkasan["diproses"] ?></span>
        <span class="tile-label">Diproses</span>
    </div>
    <div class="tile">
        <span class="tile-value"><?= $ringkasan["selesai"] ?></span>
        <span class="tile-label">Selesai</span>
    </div>
</div>

<section class="panel">
    <form method="get" class="toolbar">
        <div class="field">
            <label for="q">Cari</label>
            <input type="text" id="q" name="q" value="<?= htmlspecialchars($q) ?>"
                   placeholder="Nomor / driver / plat / perusahaan">
        </div>
        <div class="field">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="">Semua status</option>
                <?php foreach ($status_valid as $s): ?>
                    <option value="<?= $s ?>" <?= $status === $s ? "selected" : "" ?>>
                        <?= ucfirst($s) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label for="tanggal">Tanggal</label>
            <input type="date" id="tanggal" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>">
        </div>
        <button type="submit" class="btn btn-ghost">Terapkan</button>
        <?php if ($status !== "" || $q !== "" || $tanggal !== ""): ?>
            <a href="<?= htmlspecialchars(basename($_SERVER["PHP_SELF"])) ?>" class="btn btn-ghost">Reset</a>
        <?php endif; ?>
    </form>
</section>

<section class="panel">
    <h2>Daftar Kendaraan <span class="count-label"><?= $total_rows ?></span></h2>
    <p class="panel-sub">Klik Panggil untuk menampilkan nomor di papan antrian publik. Panggilan terbaru menggantikan nomor pada jalur yang sama.</p>
    <?php if (empty($daftar)): ?>
        <div class="empty">
            <?= ($status !== "" || $q !== "" || $tanggal !== "")
                ? "Tidak ada antrian yang cocok dengan filter."
                : "Belum ada antrian " . strtolower($jenis_label) . " yang tercatat." ?>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>No. Antrian</th>
                        <th>Driver</th>
                        <th>Plat</th>
                        <th>Perusahaan</th>
                        <th>Barang</th>
                        <th>Waktu Daftar</th>
                        <th>Status</th>
                        <th class="no-print">Ubah Status</th>
                        <?php if (is_admin()): ?>
                            <th class="no-print">Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($daftar as $a): ?>
                        <?php
                        $jml = $jumlah_barang_per_antrian[$a["id"]] ?? null;
                        $sedang_dipanggil = ($panggilan_saat_ini["id"] ?? null) === (int) $a["id"];
                        ?>
                        <tr>
                            <td class="cell-strong">
                                <?= htmlspecialchars($a["nomor_antrian"]) ?>
                                <span class="badge <?= $badge_jenis ?>"><?= ucfirst($jenis_kegiatan) ?></span>
                            </td>
                            <td><?= htmlspecialchars($a["driver"]) ?></td>
                            <td class="cell-strong cell-nowrap"><?= htmlspecialchars($a["plat_nomor"]) ?></td>
                            <td><?= htmlspecialchars($a["perusahaan"]) ?></td>
                            <td>
                                <?php if ($jml && $jml["item"] > 0): ?>
                                    <span class="badge badge-selesai"><?= $jml["item"] ?> jenis &middot; <?= $jml["total"] ?> unit</span>
                                <?php elseif ($a["status"] === "selesai"): ?>
                                    <span class="badge badge-menunggu">Belum diinput</span>
                                <?php else: ?>
                                    <span class="cell-muted" style="font-size:12px;">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="cell-muted cell-nowrap">
                                <?= date("d/m/Y H:i", strtotime($a["created_at"])) ?>
                            </td>
                            <td>
                                <span class="badge badge-<?= htmlspecialchars($a["status"]) ?>">
                                    <?= ucfirst(htmlspecialchars($a["status"])) ?>
                                </span>
                                <?php if ($sedang_dipanggil): ?><div class="cell-muted">Tampil di papan antrian</div><?php endif; ?>
                            </td>
                            <td class="no-print">
                                <?php if ($a["status"] !== "selesai"): ?>
                                    <div class="cell-actions">
                                        <form method="post" class="call-form">
                                            <input type="hidden" name="aksi" value="panggil">
                                            <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                                            <input type="hidden" name="panggilan_token" value="<?= htmlspecialchars($_SESSION['panggilan_token']) ?>">
                                            <button type="submit" class="btn btn-info btn-sm" aria-label="<?= $sedang_dipanggil ? 'Panggil ulang' : 'Panggil' ?> antrian <?= htmlspecialchars($a['nomor_antrian']) ?>"><?= ui_icon('arrow') ?> <?= $sedang_dipanggil ? 'Panggil Ulang' : 'Panggil' ?></button>
                                        </form>
                                        <form method="post" class="status-form">
                                            <input type="hidden" name="aksi" value="status">
                                            <input type="hidden" name="id" value="<?= (int) $a["id"] ?>">
                                            <select name="status" aria-label="Status antrian <?= htmlspecialchars($a['nomor_antrian']) ?>">
                                                <?php foreach ($status_dropdown as $s): ?>
                                                    <option value="<?= $s ?>" <?= $a["status"] === $s ? "selected" : "" ?>>
                                                        <?= ucfirst($s) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" class="btn btn-ghost btn-sm">Simpan</button>
                                        </form>
                                        <button type="button" class="btn btn-primary btn-sm"
                                                onclick="bukaModal(<?= (int) $a["id"] ?>, '<?= $jenis_kegiatan ?>', '<?= $a["status"] ?>')">
                                            Selesaikan + Barang
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <?php if ($jml && $jml["item"] > 0): ?>
                                        <button type="button" class="btn btn-info btn-sm"
                                                onclick="bukaDetail(<?= (int) $a["id"] ?>, '<?= htmlspecialchars($a["nomor_antrian"]) ?>')">
                                            Lihat Barang
                                        </button>
                                    <?php else: ?>
                                        <span class="cell-muted" style="font-size:12px;">-</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <?php if (is_admin()): ?>
                                <td class="no-print">
                                    <form method="post"
                                          onsubmit="return confirm('Hapus antrian <?= htmlspecialchars($a["nomor_antrian"], ENT_QUOTES) ?>?');">
                                        <input type="hidden" name="aksi" value="hapus">
                                        <input type="hidden" name="id" value="<?= (int) $a["id"] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php
                        $qs = $base_qs !== "" ? $base_qs . "&" : "";
                    ?>
                    <?php if ($i === $page): ?>
                        <span class="current"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?<?= $qs ?>page=<?= $i ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</section>

<?php require "config/modal_barang.php"; ?>
<?php require "config/footer.php"; ?>
