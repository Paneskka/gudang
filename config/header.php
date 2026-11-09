<?php
// ================= SHARED HEADER + AUTH =================
require_once __DIR__ . "/auth.php";

$admin_username = $_SESSION["admin_username"] ?? "Admin";
$admin_role     = $_SESSION["admin_role"] ?? "petugas";
$page_title     = $page_title ?? "Sistem Antrian Gudang";
$active_page    = $active_page ?? "";

$nav_items = [
    "dashboard" => ["label" => "Dashboard",      "href" => "dashboard.php"],
    "barang"    => ["label" => "Data Barang",    "href" => "barang.php"],
    "masuk"     => ["label" => "Antrian Masuk",  "href" => "antrian_masuk.php"],
    "keluar"    => ["label" => "Antrian Keluar", "href" => "antrian_keluar.php"],
    "laporan"   => ["label" => "Laporan",        "href" => "laporan.php"],
    "users"     => ["label" => "Kelola User",    "href" => "kelola_user.php"],
];

// Petugas tidak boleh lihat menu Laporan & Kelola User
if (!is_admin()) {
    unset($nav_items["laporan"], $nav_items["users"]);
}

$role_label = $admin_role === "admin" ? "Admin" : "Petugas";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/pages.css">
</head>
<body>

    <div class="page">

        <header class="topnav">
            <div class="brand">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M3 9.5 12 4l9 5.5V19a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V9.5Z"/>
                </svg>
                <span>Gudang Antrian</span>
            </div>

            <nav class="nav-links">
                <?php foreach ($nav_items as $key => $item): ?>
                    <a href="<?= $item["href"] ?>" class="<?= $active_page === $key ? "active" : "" ?>">
                        <?= htmlspecialchars($item["label"]) ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="user-menu">
                <span class="user-name">
                    <?= htmlspecialchars($admin_username) ?>
                    <span class="role-badge role-<?= htmlspecialchars($admin_role) ?>"><?= htmlspecialchars($role_label) ?></span>
                </span>
                <a href="logout.php" class="logout-btn">Keluar</a>
            </div>
        </header>

        <main class="content">
