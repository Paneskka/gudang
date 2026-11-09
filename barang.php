<?php
require "config/db.php";
require "config/auth.php";

// ================= PASTIKAN TABEL BARANG ADA =================
$conn->query(
    "CREATE TABLE IF NOT EXISTS barang (
        id INT AUTO_INCREMENT PRIMARY KEY,
        kode_barang VARCHAR(30) NOT NULL UNIQUE,
        nama_barang VARCHAR(120) NOT NULL,
        kategori VARCHAR(60) DEFAULT NULL,
        satuan VARCHAR(20) DEFAULT NULL,
        jumlah INT NOT NULL DEFAULT 0,
        lokasi VARCHAR(60) DEFAULT NULL,
        keterangan VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

$pesan = "";
$tipe_pesan = "success";

// ================= AKSI =================
$aksi = $_POST["aksi"] ?? "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && $aksi === "simpan") {
    $id          = (int) ($_POST["id"] ?? 0);
    $kode        = strtoupper(trim($_POST["kode_barang"] ?? ""));
    $nama        = trim($_POST["nama_barang"] ?? "");
    $kategori    = trim($_POST["kategori"] ?? "");
    $satuan      = trim($_POST["satuan"] ?? "");
    $jumlah      = max(0, (int) ($_POST["jumlah"] ?? 0));
    $lokasi      = trim($_POST["lokasi"] ?? "");
    $keterangan  = trim($_POST["keterangan"] ?? "");

    if ($kode === "" || $nama === "") {
        $pesan = "Kode dan nama barang wajib diisi.";
        $tipe_pesan = "error";
    } else {
        try {
            if ($id > 0) {
                $stmt = $conn->prepare(
                    "UPDATE barang SET kode_barang=?, nama_barang=?, kategori=?, satuan=?, jumlah=?, lokasi=?, keterangan=? WHERE id=?"
                );
                $stmt->bind_param("ssssissi", $kode, $nama, $kategori, $satuan, $jumlah, $lokasi, $keterangan, $id);
                $stmt->execute();
                $pesan = "Data barang berhasil diperbarui.";
            } else {
                $stmt = $conn->prepare(
                    "INSERT INTO barang (kode_barang, nama_barang, kategori, satuan, jumlah, lokasi, keterangan)
                     VALUES (?, ?, ?, ?, ?, ?, ?)"
                );
                $stmt->bind_param("ssssiss", $kode, $nama, $kategori, $satuan, $jumlah, $lokasi, $keterangan);
                $stmt->execute();
                $pesan = "Barang baru berhasil ditambahkan.";
            }
        } catch (mysqli_sql_exception $e) {
            $tipe_pesan = "error";
            $pesan = (int) $e->getCode() === 1062
                ? "Kode barang \"$kode\" sudah dipakai. Gunakan kode lain."
                : "Gagal menyimpan data barang.";
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && $aksi === "hapus") {
    if (!is_admin()) {
        $pesan = "Hanya admin yang boleh menghapus barang.";
        $tipe_pesan = "error";
    } else {
        $id = (int) ($_POST["id"] ?? 0);
        $stmt = $conn->prepare("DELETE FROM barang WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $pesan = "Data barang berhasil dihapus.";
        } else {
            $pesan = "Gagal menghapus data.";
            $tipe_pesan = "error";
        }
    }
}

// ================= FILTER =================
$q = trim($_GET["q"] ?? "");

if ($q !== "") {
    $like = "%" . $q . "%";
    $stmt = $conn->prepare(
        "SELECT * FROM barang
         WHERE kode_barang LIKE ? OR nama_barang LIKE ? OR kategori LIKE ? OR lokasi LIKE ?
         ORDER BY nama_barang ASC"
    );
    $stmt->bind_param("ssss", $like, $like, $like, $like);
    $stmt->execute();
    $daftar_barang = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    $daftar_barang = $conn->query("SELECT * FROM barang ORDER BY nama_barang ASC")->fetch_all(MYSQLI_ASSOC);
}

// ================= DATA EDIT =================
$edit = null;
if (isset($_GET["edit"])) {
    $stmt = $conn->prepare("SELECT * FROM barang WHERE id = ?");
    $stmt->bind_param("i", $_GET["edit"]);
    $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc();
}

$total_item = count($daftar_barang);

// ================= GRAFIK BARANG MASUK / KELUAR (BULANAN) =================
$grafik_labels = [];
$grafik_masuk = [];
$grafik_keluar = [];

$page_title  = "Data Barang - Sistem Antrian Gudang";
$active_page = "barang";
require "config/header.php";
?>

<div class="page-head">
    <div>
        <h1>Data Barang</h1>
        <p>Kelola daftar barang dan stok yang tersimpan di gudang.</p>
    </div>
</div>

<?php if ($pesan !== ""): ?>
    <div class="alert alert-<?= $tipe_pesan === "error" ? "error" : "success" ?>">
        <?= htmlspecialchars($pesan) ?>
    </div>
<?php endif; ?>



<section class="panel">
    <div class="page-head" style="margin-bottom:16px;">
        <h2 style="margin:0;">Daftar Barang (<?= $total_item ?>)</h2>
        <form method="get" action="barang.php" class="toolbar" style="margin:0;">
            <div class="field">
                <input type="text" name="q" value="<?= htmlspecialchars($q) ?>"
                       placeholder="Cari kode / nama / kategori / lokasi">
            </div>
            <button type="submit" class="btn btn-ghost">Cari</button>
            <?php if ($q !== ""): ?>
                <a href="barang.php" class="btn btn-ghost">Reset</a>
            <?php endif; ?>
        </form>
    </div>
    <div class="page-head" style="margin-bottom:16px;">
        <h2 style="margin:0;">Daftar Barang (<?= $total_item ?>)</h2>
        <form method="get" action="barang.php" class="toolbar" style="margin:0;">
            <div class="field">
                <input type="text" name="q" value="<?= htmlspecialchars($q) ?>"
                       placeholder="Cari kode / nama / kategori / lokasi">
            </div>
            <button type="submit" class="btn btn-ghost">Cari</button>
            <?php if ($q !== ""): ?>
                <a href="barang.php" class="btn btn-ghost">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if (empty($daftar_barang)): ?>
        <div class="empty">
            <?= $q !== "" ? "Tidak ada barang yang cocok dengan pencarian." : "Belum ada data barang." ?>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Barang</th>
                        <th>Kategori</th>
                        <th>Stok</th>
                        <th>Lokasi</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($daftar_barang as $b): ?>
                        <?php
                            $jumlah = (int) $b["jumlah"];
                            $stok_class = $jumlah <= 0 ? "badge-menunggu" : ($jumlah <= 10 ? "badge-diproses" : "badge-selesai");
                            $stok_label = $jumlah <= 0 ? "Habis" : ($jumlah <= 10 ? "Menipis" : "Tersedia");
                        ?>
                        <tr>
                            <td class="cell-strong"><?= htmlspecialchars($b["kode_barang"]) ?></td>
                            <td><?= htmlspecialchars($b["nama_barang"]) ?></td>
                            <td class="cell-muted"><?= htmlspecialchars($b["kategori"] ?: "-") ?></td>
                            <td>
                                <span class="badge <?= $stok_class ?>">
                                    <?= $jumlah ?> <?= htmlspecialchars($b["satuan"] ?: "") ?> &middot; <?= $stok_label ?>
                                </span>
                            </td>
                            <td class="cell-muted"><?= htmlspecialchars($b["lokasi"] ?: "-") ?></td>
                            <td class="cell-muted"><?= htmlspecialchars($b["keterangan"] ?: "-") ?></td>
                            <td>
                                <div class="cell-actions">
                                    <a class="btn btn-ghost btn-sm"
                                       href="barang.php?edit=<?= (int) $b["id"] ?><?= $q !== "" ? "&q=" . urlencode($q) : "" ?>">Edit</a>
                                    <?php if (is_admin()): ?>
                                        <form method="post" action="barang.php"
                                              onsubmit="return confirm('Hapus barang <?= htmlspecialchars($b["kode_barang"], ENT_QUOTES) ?>?');">
                                            <input type="hidden" name="aksi" value="hapus">
                                            <input type="hidden" name="id" value="<?= (int) $b["id"] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<section class="panel">
    <h2><?= $edit ? "Edit Barang" : "Tambah Barang" ?></h2>
    <p class="panel-sub">Kode barang harus unik. Jumlah stok tidak boleh negatif.</p>

    <form method="post" action="barang.php">
        <input type="hidden" name="aksi" value="simpan">
        <input type="hidden" name="id" value="<?= $edit ? (int) $edit["id"] : 0 ?>">

        <div class="form-grid">
            <div class="field">
                <label for="kode_barang">Kode Barang</label>
                <input type="text" id="kode_barang" name="kode_barang" required maxlength="30"
                       value="<?= htmlspecialchars($edit["kode_barang"] ?? "") ?>"
                       placeholder="Contoh: BRG-001">
            </div>

            <div class="field">
                <label for="nama_barang">Nama Barang</label>
                <input type="text" id="nama_barang" name="nama_barang" required maxlength="120"
                       value="<?= htmlspecialchars($edit["nama_barang"] ?? "") ?>"
                       placeholder="Contoh: Karton Box 40x40">
            </div>

            <div class="field">
                <label for="kategori">Kategori</label>
                <input type="text" id="kategori" name="kategori" maxlength="60"
                       value="<?= htmlspecialchars($edit["kategori"] ?? "") ?>"
                       placeholder="Contoh: Kemasan">
            </div>

            <div class="field">
                <label for="satuan">Satuan</label>
                <input type="text" id="satuan" name="satuan" maxlength="20"
                       value="<?= htmlspecialchars($edit["satuan"] ?? "") ?>"
                       placeholder="Contoh: pcs / dus / kg">
            </div>

            <div class="field">
                <label for="jumlah">Jumlah Stok</label>
                <input type="number" id="jumlah" name="jumlah" min="0" step="1"
                       value="<?= htmlspecialchars((string) ($edit["jumlah"] ?? 0)) ?>">
            </div>

            <div class="field">
                <label for="lokasi">Lokasi Rak</label>
                <input type="text" id="lokasi" name="lokasi" maxlength="60"
                       value="<?= htmlspecialchars($edit["lokasi"] ?? "") ?>"
                       placeholder="Contoh: Rak A-2">
            </div>

            <div class="field field-full">
                <label for="keterangan">Keterangan</label>
                <textarea id="keterangan" name="keterangan" rows="2" maxlength="255"
                          placeholder="Opsional"><?= htmlspecialchars($edit["keterangan"] ?? "") ?></textarea>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <?= $edit ? "Simpan Perubahan" : "Tambah Barang" ?>
            </button>
            <?php if ($edit): ?>
                <a href="barang.php" class="btn btn-ghost">Batal</a>
            <?php endif; ?>
        </div>
    </form>
</section>

<?php require "config/footer.php"; ?>
