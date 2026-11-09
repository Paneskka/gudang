<?php
session_start();
require "config/db.php";

// Kalau sudah login, langsung ke dashboard
if (isset($_SESSION["admin_id"])) {
    header("Location: dashboard.php");
    exit;
}

$pesan_error = "";

// ================= PROSES LOGIN =================
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

            // Cek apakah password di database sudah berbentuk hash bcrypt
            // Hash bcrypt selalu diawali $2y$, $2a$, atau $2b$
            $sudah_hash = (bool) preg_match('/^\$2[aby]\$/', $password_db);

            $login_valid = false;

            if ($sudah_hash) {
                // Cara normal: cocokkan dengan password_verify
                $login_valid = password_verify($password, $password_db);
            } else {
                // Password lama masih teks biasa: cocokkan langsung
                $login_valid = ($password === $password_db);

                if ($login_valid) {
                    // Login berhasil dengan password teks biasa
                    // -> otomatis konversi ke hash dan simpan ke database
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
                    // Login berhasil
                    $_SESSION["admin_id"]       = $admin["id"];
                    $_SESSION["admin_username"] = $admin["username"];
                    $_SESSION["admin_role"]     = $admin["role"] ?? "admin";

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

    <title>Login Admin - Sistem Antrian Gudang</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/login.css">
</head>

<body>

    <div class="login-page">

        <div class="login-card">

            <div class="login-icon">
                <svg viewBox="0 0 24 24"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="1.8">

                    <rect
                        x="5"
                        y="10"
                        width="14"
                        height="10"
                        rx="2"
                    />

                    <path d="M8 10V7a4 4 0 0 1 8 0v3"/>

                </svg>
            </div>

            <h1>Login Admin</h1>

            <p class="login-description">
                Masuk ke dashboard untuk mengelola sistem antrian gudang.
            </p>

            <?php if ($pesan_error): ?>
                <div class="login-error">
                    <?= htmlspecialchars($pesan_error) ?>
                </div>
            <?php endif; ?>


            <form method="POST" action="login.php">

                <div class="form-group">

                    <label for="username">
                        Username
                    </label>

                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Masukkan username"
                        value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="password">
                        Password
                    </label>

                    <div class="password-wrapper">

                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Masukkan password"
                            required
                        >

                        <button
                            type="button"
                            id="showPassword"
                        >
                            Lihat
                        </button>

                    </div>

                </div>


                <div class="login-options">

                    <label class="remember">

                        <input
                            type="checkbox"
                            id="remember"
                            name="remember"
                        >

                        <span>Ingat saya</span>

                    </label>

                    <a href="#">
                        Lupa password?
                    </a>

                </div>


                <button
                    type="submit"
                    class="login-button"
                >
                    Login
                </button>

            </form>


            <a href="index.php" class="back-link">
                ← Kembali ke halaman utama
            </a>

        </div>

    </div>


    <script>

        const password =
            document.getElementById("password");

        const showPassword =
            document.getElementById("showPassword");

        showPassword.addEventListener("click", function () {

            if (password.type === "password") {

                password.type = "text";

                this.textContent = "Sembunyikan";

            } else {

                password.type = "password";

                this.textContent = "Lihat";

            }

        });

    </script>

</body>
</html>