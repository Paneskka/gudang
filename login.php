<?php
session_start();
require "config/db.php";
require_once "config/ui.php";

if (isset($_SESSION["admin_id"])) {
    header("Location: dashboard.php");
    exit;
}
$pesan_error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($conn->real_escape_string($_POST["username"] ?? ""));
    $password = $_POST["password"] ?? "";
    if ($username === "" || $password === "") {
        $pesan_error = "Username dan password wajib diisi.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            $password_db = $admin["password"];
            $sudah_hash = (bool) preg_match('/^\$2[aby]\$/', $password_db);
            $login_valid = false;
            if ($sudah_hash) {
                $login_valid = password_verify($password, $password_db);
            } else {
                $login_valid = $password === $password_db;
                if ($login_valid) {
                    $hash_baru = password_hash($password, PASSWORD_DEFAULT);
                    $update = $conn->prepare("UPDATE admin SET password = ? WHERE id = ?");
                    $update->bind_param("si", $hash_baru, $admin["id"]);
                    $update->execute();
                }
            }
            if ($login_valid) {
                if ((int) ($admin["aktif"] ?? 1) !== 1) {
                    $pesan_error = "Akun ini dinonaktifkan. Hubungi admin.";
                } else {
                    $_SESSION["admin_id"] = $admin["id"];
                    $_SESSION["admin_username"] = $admin["username"];
                    $_SESSION["admin_role"] = $admin["role"] ?? "admin";
                    header("Location: dashboard.php");
                    exit;
                }
            } else {
                $pesan_error = "Username atau password salah.";
            }
        } else {
            $pesan_error = "Username atau password salah.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#173F35">
    <title>Login Admin - Sistem Antrian Gudang</title>
    <link rel="icon" type="image/svg+xml" href="<?= ui_asset('assets/img/favicon.svg') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= ui_asset('assets/css/base.css') ?>">
    <link rel="stylesheet" href="<?= ui_asset('assets/css/pages.css') ?>">
    <link rel="stylesheet" href="<?= ui_asset('assets/css/login.css') ?>">
</head>
<body class="login-page">
    <a class="skip-link" href="#login-form">Lewati ke formulir login</a>
    <div class="auth-layout">
        <aside class="auth-story" aria-label="Sistem Antrian Gudang">
            <img class="auth-background" src="<?= ui_asset('assets/img/warehouse-background.webp') ?>" width="1800" height="1000" alt="" fetchpriority="high">
            <a class="brand-lockup" href="index.php"><img src="<?= ui_asset('assets/img/favicon.svg') ?>" width="40" height="40" alt=""><span><strong>Gudang.</strong><small>SISTEM ANTRIAN</small></span></a>
            <div class="auth-story-content">
                <span class="eyebrow">Ruang kerja operasional</span>
                <h2>Semua bergerak.<br><em>Semua tercatat.</em></h2>
                <p>Kelola antrian kendaraan dan inventaris gudang dalam satu ruang kerja yang teratur.</p>
                <div class="auth-features"><span><?= ui_icon('check-circle') ?> Antrian terorganisir</span><span><?= ui_icon('box') ?> Stok terkendali</span></div>
            </div>
            <div class="auth-story-footer"><span>Sistem Antrian Gudang</span><span>Operasional lebih tertata.</span></div>
        </aside>
        <main class="auth-main">
            <a class="auth-back" href="index.php"><?= ui_icon('arrow') ?> Kembali ke pendaftaran</a>
            <div class="auth-form" id="login-form" tabindex="-1">
                <span class="auth-lock"><?= ui_icon('lock') ?></span>
                <span class="eyebrow">Masuk ke ruang kerja</span>
                <h1>Selamat datang<br>kembali.</h1>
                <p class="auth-description">Gunakan akun Anda untuk mengelola aktivitas gudang hari ini.</p>
                <?php if ($pesan_error): ?><div class="alert alert-error" role="alert"><?= htmlspecialchars($pesan_error) ?></div><?php endif; ?>
                <form method="post" action="login.php">
                    <div class="field">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" autocomplete="username" placeholder="Masukkan username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                    </div>
                    <div class="field">
                        <label for="password">Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="password" name="password" autocomplete="current-password" placeholder="Masukkan password" required>
                            <button type="button" id="showPassword" aria-label="Tampilkan password" aria-pressed="false">Lihat</button>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary login-button">Masuk ke Dashboard <?= ui_icon('arrow') ?></button>
                </form>
                <details class="login-help"><summary>Butuh bantuan masuk?</summary><p>Hubungi administrator gudang untuk mendapatkan akun atau mengatur ulang password Anda.</p></details>
                <p class="auth-access"><?= ui_icon('users') ?> Akses untuk administrator dan petugas gudang.</p>
            </div>
            <footer class="auth-footer">Gudang. &nbsp; Sistem Antrian Gudang</footer>
        </main>
    </div>
    <script>
        const password = document.getElementById('password');
        const showPassword = document.getElementById('showPassword');
        showPassword.addEventListener('click', () => {
            const visible = password.type === 'password';
            password.type = visible ? 'text' : 'password';
            showPassword.textContent = visible ? 'Tutup' : 'Lihat';
            showPassword.setAttribute('aria-pressed', String(visible));
            showPassword.setAttribute('aria-label', visible ? 'Sembunyikan password' : 'Tampilkan password');
        });
    </script>
</body>
</html>
