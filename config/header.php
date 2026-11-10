<?php
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/ui.php";

$admin_username = $_SESSION["admin_username"] ?? "Admin";
$admin_role = $_SESSION["admin_role"] ?? "petugas";
$page_title = $page_title ?? "Sistem Antrian Gudang";
$active_page = $active_page ?? "";
$nav_items = [
    "dashboard" => ["label" => "Dashboard", "href" => "dashboard", "icon" => "grid"],
    "barang" => ["label" => "Data Barang", "href" => "barang", "icon" => "box"],
    "masuk" => ["label" => "Antrian Masuk", "href" => "antrian-masuk", "icon" => "in"],
    "keluar" => ["label" => "Antrian Keluar", "href" => "antrian-keluar", "icon" => "out"],
    "laporan" => ["label" => "Laporan", "href" => "laporan", "icon" => "chart"],
    "users" => ["label" => "Kelola User", "href" => "kelola-user", "icon" => "users"],
];
if (!is_admin()) {
    unset($nav_items["laporan"], $nav_items["users"]);
}
$role_label = $admin_role === "admin" ? "Administrator" : "Petugas";
$section_label = $nav_items[$active_page]["label"] ?? "Operasional";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#173F35">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= ui_asset('assets/img/favicon.svg') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= ui_asset('assets/css/base.css') ?>">
    <link rel="stylesheet" href="<?= ui_asset('assets/css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= ui_asset('assets/css/pages.css') ?>">
    <script src="<?= ui_asset('assets/js/app.js') ?>" defer></script>
</head>
<body class="admin-page">
    <a class="skip-link" href="#main-content">Lewati ke konten</a>
    <button class="sidebar-backdrop" type="button" aria-label="Tutup navigasi" tabindex="-1" hidden></button>
    <aside class="sidebar" id="sidebar" aria-label="Navigasi utama">
        <div class="sidebar-brand">
            <a class="brand-lockup" href="dashboard">
                <img src="<?= ui_asset('assets/img/favicon.svg') ?>" width="40" height="40" alt="">
                <span><strong>Gudang<span class="brand-dot">.</span></strong><small>SISTEM ANTRIAN</small></span>
            </a>
            <button class="sidebar-close icon-button" type="button" aria-label="Tutup navigasi"><?= ui_icon('close') ?></button>
        </div>
        <p class="sidebar-label">Ruang kerja</p>
        <nav class="nav-links" aria-label="Menu operasional">
            <?php foreach ($nav_items as $key => $item): ?>
                <a href="<?= $item['href'] ?>" class="<?= $active_page === $key ? 'active' : '' ?>" <?= $active_page === $key ? 'aria-current="page"' : '' ?>>
                    <?= ui_icon($item['icon']) ?><span><?= htmlspecialchars($item['label']) ?></span>
                    <?php if ($active_page === $key): ?><span class="nav-active-dot"></span><?php endif; ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="sidebar-bottom">
            <div class="sidebar-help">
                <span class="sidebar-help-icon"><?= ui_icon('truck') ?></span>
                <strong>Mulai dari antrian.</strong>
                <p>Daftarkan kendaraan untuk proses bongkar atau muat.</p>
                <a href="index">Halaman pendaftaran <?= ui_icon('external') ?></a>
            </div>
            <div class="sidebar-user">
                <span class="avatar"><?= ui_icon('users') ?></span>
                <div><strong><?= htmlspecialchars($admin_username) ?></strong><small><?= $role_label ?></small></div>
                <a href="logout" class="icon-button" aria-label="Keluar dari akun" title="Keluar"><?= ui_icon('logout') ?></a>
            </div>
        </div>
    </aside>
    <div class="workspace">
        <header class="workspace-header">
            <div class="header-start">
                <button class="menu-toggle icon-button" type="button" aria-label="Buka navigasi" aria-expanded="false" aria-controls="sidebar"><?= ui_icon('menu') ?></button>
                <nav class="breadcrumb" aria-label="Breadcrumb"><span>Ruang kerja</span><span aria-hidden="true">/</span><strong><?= htmlspecialchars($section_label) ?></strong></nav>
            </div>
            <div class="header-end"><span class="header-date"><?= ui_icon('calendar') ?> <?= ui_date() ?></span><span class="header-role"><?= $admin_role === 'admin' ? 'Admin' : 'Petugas' ?></span></div>
        </header>
        <div class="page">
            <main class="content" id="main-content" tabindex="-1">
