<?php
// ================= AUTH & HAK AKSES =================
// File ini wajib di-require paling atas, sebelum memproses POST/GET apa pun.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/db.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login");
    exit;
}

// Ambil user terbaru dari database supaya perubahan role / status aktif langsung berlaku
$stmt = $conn->prepare("SELECT id, username, role, aktif FROM admin WHERE id = ?");
$stmt->bind_param("i", $_SESSION["admin_id"]);
$stmt->execute();
$current_user = $stmt->get_result()->fetch_assoc();

if (!$current_user || (int) $current_user["aktif"] !== 1) {
    session_unset();
    session_destroy();
    header("Location: login");
    exit;
}

$_SESSION["admin_username"] = $current_user["username"];
$_SESSION["admin_role"]     = $current_user["role"] ?? "admin";

function is_admin(): bool
{
    return ($_SESSION["admin_role"] ?? "") === "admin";
}

function require_admin(): void
{
    if (!is_admin()) {
        header("Location: dashboard");
        exit;
    }
}
