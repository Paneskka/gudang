<?php require_once __DIR__ . "/ui.php"; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#173F35">
    <title>Sistem Antrian Gudang</title>
    <link rel="icon" type="image/svg+xml" href="<?= ui_asset('assets/img/favicon.svg') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= ui_asset('assets/css/base.css') ?>">
    <link rel="stylesheet" href="<?= ui_asset('assets/css/pages.css') ?>">
    <link rel="stylesheet" href="<?= ui_asset('assets/css/home.css') ?>">
    <script src="<?= ui_asset('assets/js/panggilan.js') ?>" defer></script>
</head>
<body class="public-page">
    <a class="skip-link" href="#pendaftaran">Lewati ke formulir pendaftaran</a>
    <header class="public-header">
        <div class="public-nav">
            <a class="brand-lockup" href="index.php"><img src="<?= ui_asset('assets/img/favicon.svg') ?>" width="40" height="40" alt=""><span><strong>Gudang.</strong><small>SISTEM ANTRIAN</small></span></a>
            <nav aria-label="Navigasi halaman utama">
                <a class="public-nav-link" href="#pendaftaran">Pendaftaran</a>
                <a class="public-nav-link" href="#cari">Cek antrian</a>
                <a class="btn btn-primary" href="login.php"><?= ui_icon('lock') ?> Login <span class="login-label">Admin</span></a>
            </nav>
        </div>
    </header>
    <main class="public-main">
        <div class="public-layout">
            <section class="public-story" aria-labelledby="public-title">
                <span class="story-label"><span></span> Sistem antrian kendaraan</span>
                <h1 id="public-title">Antrian tertata.<br><em>Gudang lebih lancar.</em></h1>
                <p class="story-description">Daftarkan kendaraan, dapatkan nomor antrian, dan pantau proses bongkar muat dalam satu tempat.</p>
                <section class="call-board" id="panggilan-antrian" aria-labelledby="panggilan-title" data-endpoint="antrian_dipanggil.php">
                    <div class="call-board-header">
                        <h2 id="panggilan-title">Antrian dipanggil</h2>
                        <span class="call-live" data-live-label>Terkini</span>
                    </div>
                    <div class="call-board-grid" role="status" aria-atomic="true">
                        <?php foreach (["bongkar" => "Barang masuk", "muat" => "Barang keluar"] as $jalur => $keterangan): ?>
                            <?php $dipanggil = $panggilan[$jalur]; ?>
                            <article class="call-lane <?= $dipanggil ? '' : 'is-empty' ?>" data-call-lane="<?= $jalur ?>">
                                <h3><?= ui_icon($jalur === 'bongkar' ? 'in' : 'out') ?> <?= ucfirst($jalur) ?></h3>
                                <p class="call-lane-description"><?= $keterangan ?></p>
                                <div class="call-current">
                                    <strong class="call-number" data-call-number style="--number-length: <?= max(4, strlen($dipanggil['nomor_antrian'] ?? '')) ?>"><?= htmlspecialchars($dipanggil['nomor_antrian'] ?? '—') ?></strong>
                                    <span class="call-plate" data-call-plate><?= htmlspecialchars($dipanggil['plat_nomor'] ?? 'Belum ada panggilan') ?></span>
                                    <p class="call-note" data-call-note><?= $dipanggil ? 'Silakan menuju area ' . $jalur . '.' : 'Menunggu panggilan petugas.' ?></p>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                    <div class="call-board-footer"><?= ui_icon('clock') ?><span data-sync-message role="status">Silakan menuju area sesuai panggilan.</span></div>
                    <noscript><p class="call-note">Muat ulang halaman untuk melihat panggilan terbaru.</p></noscript>
                </section>
                <div class="operating-hours"><?= ui_icon('clock') ?><span>Jam operasional <strong>Senin–Sabtu, 08.00–17.00 WIB</strong></span></div>
            </section>
            <section class="registration-panel" id="pendaftaran" aria-labelledby="registration-title">
                <div class="form-kicker"><span>01</span> Mulai perjalanan Anda <?= ui_icon('arrow') ?></div>
                <h2 id="registration-title">Daftarkan kendaraan</h2>
                <p class="form-intro">Lengkapi data berikut untuk mendapatkan nomor antrian.</p>
                <?php if ($pesan_daftar): ?>
                    <div class="alert <?= $pendaftaran_berhasil ? 'alert-success' : 'alert-error' ?>" role="status"><?= $pesan_daftar ?></div>
                <?php endif; ?>
                <form method="post" action="index.php#pendaftaran">
                    <div class="field">
                        <label for="driver">Nama driver <span class="required-mark">*</span></label>
                        <input id="driver" name="driver" type="text" maxlength="100" autocomplete="name" required placeholder="Nama lengkap driver" value="<?= !$pendaftaran_berhasil ? htmlspecialchars($_POST['driver'] ?? '') : '' ?>">
                    </div>
                    <div class="field">
                        <label for="plate">Nomor polisi <span class="required-mark">*</span></label>
                        <input id="plate" name="plate" type="text" maxlength="20" class="uppercase-input" required placeholder="B 1234 XYZ" value="<?= !$pendaftaran_berhasil ? htmlspecialchars($_POST['plate'] ?? '') : '' ?>" aria-describedby="plate-hint">
                        <small id="plate-hint">Nomor polisi akan disimpan dalam huruf kapital.</small>
                    </div>
                    <div class="field">
                        <label for="company">Perusahaan <span class="required-mark">*</span></label>
                        <input id="company" name="company" type="text" maxlength="100" autocomplete="organization" required placeholder="Nama perusahaan atau ekspedisi" value="<?= !$pendaftaran_berhasil ? htmlspecialchars($_POST['company'] ?? '') : '' ?>">
                    </div>
                    <div class="field">
                        <label for="activity">Jenis kegiatan <span class="required-mark">*</span></label>
                        <select id="activity" name="activity" required>
                            <option value="">Pilih kegiatan kendaraan</option>
                            <option value="bongkar" <?= !$pendaftaran_berhasil && ($_POST['activity'] ?? '') === 'bongkar' ? 'selected' : '' ?>>Bongkar — barang masuk</option>
                            <option value="muat" <?= !$pendaftaran_berhasil && ($_POST['activity'] ?? '') === 'muat' ? 'selected' : '' ?>>Muat — barang keluar</option>
                        </select>
                    </div>
                    <button class="btn btn-primary queue-button" type="submit" name="daftar_antrian">Ambil Nomor Antrian <?= ui_icon('arrow') ?></button>
                </form>
                <p class="form-bottom">Sudah mendaftar? <a href="#cari">Cek status antrian <?= ui_icon('arrow') ?></a></p>
            </section>
        </div>

        <section class="journey" aria-label="Cara menggunakan antrian">
            <div><span>01</span><div><strong>Daftarkan kendaraan</strong><p>Isi data driver dan jenis kegiatan.</p></div></div>
            <div><span>02</span><div><strong>Simpan nomor antrian</strong><p>Gunakan nomor untuk cek status.</p></div></div>
            <div><span>03</span><div><strong>Ikuti proses gudang</strong><p>Tunggu arahan dari petugas.</p></div></div>
        </section>

        <section class="queue-search" id="cari" aria-labelledby="search-title">
            <div class="search-heading"><span class="search-icon"><?= ui_icon('search') ?></span><span class="eyebrow">Lacak kendaraan</span><h2 id="search-title">Sudah sampai <br>mana antrian Anda?</h2><p>Masukkan nomor yang diperoleh saat pendaftaran untuk melihat status terakhir.</p></div>
            <div class="search-content">
                <form method="post" action="index.php#cari">
                    <label for="nomor-antrian">Nomor antrian</label>
                    <div class="search-input-row"><input type="text" id="nomor-antrian" name="nomor_antrian" maxlength="10" required class="uppercase-input" placeholder="Contoh: B001 atau M001" value="<?= htmlspecialchars($_POST['nomor_antrian'] ?? '') ?>"><button type="submit" name="cek_status" class="btn btn-primary"><?= ui_icon('search') ?> Cari Status</button></div>
                </form>
                <?php if ($pesan_status): ?><p class="alert alert-error" role="status"><?= htmlspecialchars($pesan_status) ?></p><?php endif; ?>
                <?php if ($hasil_status): ?>
                    <div class="queue-result" role="status">
                        <div class="queue-result-head"><strong><?= htmlspecialchars($hasil_status['nomor_antrian']) ?></strong><span class="badge badge-<?= htmlspecialchars($hasil_status['status'] ?? 'menunggu') ?>"><?= htmlspecialchars(ucfirst($hasil_status['status'] ?? 'menunggu')) ?></span></div>
                        <dl><div><dt>Driver</dt><dd><?= htmlspecialchars($hasil_status['driver']) ?></dd></div><div><dt>Nomor polisi</dt><dd><?= htmlspecialchars($hasil_status['plat_nomor']) ?></dd></div><div><dt>Perusahaan</dt><dd><?= htmlspecialchars($hasil_status['perusahaan']) ?></dd></div><div><dt>Kegiatan</dt><dd><?= htmlspecialchars(ucfirst($hasil_status['jenis_kegiatan'])) ?></dd></div></dl>
                    </div>
                <?php else: ?>
                    <p class="search-hint"><?= ui_icon('truck') ?> Nomor diawali B untuk bongkar atau M untuk muat.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>
    <footer class="public-footer"><span><strong>Gudang.</strong> &nbsp; Sistem Antrian Gudang</span><span>Operasional lebih tertata.</span></footer>
</body>
</html>
