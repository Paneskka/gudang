<?php
require "config/db.php";
require "config/auth.php";
require_admin();

$pesan      = "";
$tipe_pesan = "success";
$role_valid = ["admin", "petugas"];

function jumlah_admin_aktif($conn)
{
    $q = $conn->query("SELECT COUNT(*) AS total FROM admin WHERE role = 'admin' AND aktif = 1");
    return $q ? (int) $q->fetch_assoc()["total"] : 0;
}

// ================= AKSI =================
$aksi = $_POST["aksi"] ?? "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && $aksi === "simpan") {
    $id       = (int) ($_POST["id"] ?? 0);
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";
    $role     = $_POST["role"] ?? "petugas";

    if ($username === "") {
        $pesan = "Username wajib diisi.";
        $tipe_pesan = "error";
    } elseif (!in_array($role, $role_valid, true)) {
        $pesan = "Role tidak valid.";
        $tipe_pesan = "error";
    } elseif ($id === 0 && $password === "") {
        $pesan = "Password wajib diisi untuk user baru.";
        $tipe_pesan = "error";
    } else {
        try {
            if ($id > 0) {
                $stmt = $conn->prepare("SELECT * FROM admin WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $target = $stmt->get_result()->fetch_assoc();

                if (!$target) {
                    $pesan = "User tidak ditemukan.";
                    $tipe_pesan = "error";
                } elseif ($target["role"] === "admin" && $role !== "admin"
                          && (int) $target["aktif"] === 1 && jumlah_admin_aktif($conn) <= 1) {
                    $pesan = "Tidak bisa menurunkan role admin aktif terakhir.";
                    $tipe_pesan = "error";
                } elseif ($password !== "") {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE admin SET username = ?, role = ?, password = ? WHERE id = ?");
                    $stmt->bind_param("sssi", $username, $role, $hash, $id);
                    $stmt->execute();
                    $pesan = "User berhasil diperbarui.";
                } else {
                    $stmt = $conn->prepare("UPDATE admin SET username = ?, role = ? WHERE id = ?");
                    $stmt->bind_param("ssi", $username, $role, $id);
                    $stmt->execute();
                    $pesan = "User berhasil diperbarui.";
                }
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO admin (username, password, role, aktif) VALUES (?, ?, ?, 1)");
                $stmt->bind_param("sss", $username, $hash, $role);
                $stmt->execute();
                $pesan = "User baru berhasil ditambahkan.";
            }
        } catch (mysqli_sql_exception $e) {
            $tipe_pesan = "error";
            $pesan = (int) $e->getCode() === 1062
                ? "Username \"$username\" sudah dipakai."
                : "Gagal menyimpan user.";
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && $aksi === "toggle") {
    $id = (int) ($_POST["id"] ?? 0);

    if ($id === (int) $_SESSION["admin_id"]) {
        $pesan = "Tidak bisa menonaktifkan akun sendiri.";
        $tipe_pesan = "error";
    } else {
        $stmt = $conn->prepare("SELECT * FROM admin WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $target = $stmt->get_result()->fetch_assoc();

        if (!$target) {
            $pesan = "User tidak ditemukan.";
            $tipe_pesan = "error";
        } elseif ($target["role"] === "admin" && (int) $target["aktif"] === 1
                  && jumlah_admin_aktif($conn) <= 1) {
            $pesan = "Tidak bisa menonaktifkan admin aktif terakhir.";
            $tipe_pesan = "error";
        } else {
            $baru = (int) $target["aktif"] === 1 ? 0 : 1;
            $stmt = $conn->prepare("UPDATE admin SET aktif = ? WHERE id = ?");
            $stmt->bind_param("ii", $baru, $id);
            $stmt->execute();
            $pesan = $baru === 1 ? "User diaktifkan." : "User dinonaktifkan.";
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && $aksi === "hapus") {
    $id = (int) ($_POST["id"] ?? 0);

    if ($id === (int) $_SESSION["admin_id"]) {
        $pesan = "Tidak bisa menghapus akun sendiri.";
        $tipe_pesan = "error";
    } else {
        $stmt = $conn->prepare("SELECT * FROM admin WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $target = $stmt->get_result()->fetch_assoc();

        if (!$target) {
            $pesan = "User tidak ditemukan.";
            $tipe_pesan = "error";
        } elseif ($target["role"] === "admin" && (int) $target["aktif"] === 1
                  && jumlah_admin_aktif($conn) <= 1) {
            $pesan = "Tidak bisa menghapus admin aktif terakhir.";
            $tipe_pesan = "error";
        } else {
            $stmt = $conn->prepare("DELETE FROM admin WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $pesan = "User berhasil dihapus.";
        }
    }
}

// ================= DATA =================
$daftar_user = $conn->query("SELECT id, username, role, aktif FROM admin ORDER BY role DESC, username ASC")->fetch_all(MYSQLI_ASSOC);

$edit = null;
if (isset($_GET["edit"])) {
    $id = (int) $_GET["edit"];
    $stmt = $conn->prepare("SELECT id, username, role, aktif FROM admin WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc();
}

$page_title  = "Kelola User - Sistem Antrian Gudang";
$active_page = "users";
require "config/header.php";
?>

<div class="page-head">
    <div>
        <h1>Kelola User</h1>
        <p>Atur akun admin dan petugas beserta hak aksesnya.</p>
    </div>
</div>

<?php if ($pesan !== ""): ?>
    <div class="alert alert-<?= $tipe_pesan === "error" ? "error" : "success" ?>">
        <?= htmlspecialchars($pesan) ?>
    </div>
<?php endif; ?>

<section class="panel">
    <h2>Daftar User (<?= count($daftar_user) ?>)</h2>
    <div class="table-wrap">
        <table class="data">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($daftar_user as $u): ?>
                    <tr>
                        <td class="cell-strong">
                            <?= htmlspecialchars($u["username"]) ?>
                            <?php if ((int) $u["id"] === (int) $_SESSION["admin_id"]): ?>
                                <span class="badge badge-diproses">Anda</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge <?= $u["role"] === "admin" ? "badge-selesai" : "badge-diproses" ?>">
                                <?= $u["role"] === "admin" ? "Admin" : "Petugas" ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?= (int) $u["aktif"] === 1 ? "badge-selesai" : "badge-menunggu" ?>">
                                <?= (int) $u["aktif"] === 1 ? "Aktif" : "Nonaktif" ?>
                            </span>
                        </td>
                        <td>
                            <div class="cell-actions">
                                <a class="btn btn-ghost btn-sm" href="kelola_user.php?edit=<?= (int) $u["id"] ?>">Edit</a>

                                <?php if ((int) $u["id"] !== (int) $_SESSION["admin_id"]): ?>
                                    <form method="post" action="kelola_user.php">
                                        <input type="hidden" name="aksi" value="toggle">
                                        <input type="hidden" name="id" value="<?= (int) $u["id"] ?>">
                                        <button type="submit" class="btn btn-ghost btn-sm">
                                            <?= (int) $u["aktif"] === 1 ? "Nonaktifkan" : "Aktifkan" ?>
                                        </button>
                                    </form>

                                    <form method="post" action="kelola_user.php"
                                          onsubmit="return confirm('Hapus user <?= htmlspecialchars($u["username"], ENT_QUOTES) ?>?');">
                                        <input type="hidden" name="aksi" value="hapus">
                                        <input type="hidden" name="id" value="<?= (int) $u["id"] ?>">
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
</section>

<section class="panel">
    <h2><?= $edit ? "Edit User" : "Tambah User" ?></h2>
    <p class="panel-sub">Admin punya akses penuh. Petugas tidak bisa akses laporan, kelola user, atau menghapus data.</p>

    <form method="post" action="kelola_user.php">
        <input type="hidden" name="aksi" value="simpan">
        <input type="hidden" name="id" value="<?= $edit ? (int) $edit["id"] : 0 ?>">

        <div class="form-grid">
            <div class="field">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required maxlength="60"
                       value="<?= htmlspecialchars($edit["username"] ?? "") ?>"
                       placeholder="Contoh: petugas1">
            </div>

            <div class="field">
                <label for="role">Role</label>
                <select id="role" name="role">
                    <option value="petugas" <?= ($edit["role"] ?? "petugas") === "petugas" ? "selected" : "" ?>>Petugas</option>
                    <option value="admin" <?= ($edit["role"] ?? "") === "admin" ? "selected" : "" ?>>Admin</option>
                </select>
            </div>

            <div class="field field-full">
                <label for="password">Password <?= $edit ? "(kosongkan jika tidak diubah)" : "" ?></label>
                <input type="password" id="password" name="password" <?= $edit ? "" : "required" ?> minlength="6"
                       placeholder="<?= $edit ? "Password baru (opsional)" : "Minimal 6 karakter" ?>">
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <?= $edit ? "Simpan Perubahan" : "Tambah User" ?>
            </button>
            <?php if ($edit): ?>
                <a href="kelola_user.php" class="btn btn-ghost">Batal</a>
            <?php endif; ?>
        </div>
    </form>
</section>

<?php require "config/footer.php"; ?>
