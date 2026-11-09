<?php
require "config/db.php";

$pesan_daftar = "";
$hasil_status = null;
$pesan_status = "";

// ================= PROSES FORM PENDAFTARAN =================
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["daftar_antrian"])) {

    $driver   = trim($conn->real_escape_string($_POST["driver"]));
    $plate    = strtoupper(trim($conn->real_escape_string($_POST["plate"])));
    $company  = trim($conn->real_escape_string($_POST["company"]));
    $activity = $_POST["activity"]; // "muat" atau "bongkar"

    if ($driver === "" || $plate === "" || $company === "" || $activity === "") {
        $pesan_daftar = "Semua field wajib diisi.";
    } else {
        $prefix = ($activity === "muat") ? "M" : "B";

        // Hitung nomor urut untuk hari ini berdasarkan jenis kegiatan
        $today = date("Y-m-d");
        $stmtCount = $conn->prepare(
            "SELECT COUNT(*) AS total FROM antrian
             WHERE jenis_kegiatan = ? AND DATE(created_at) = ?"
        );
        $stmtCount->bind_param("ss", $activity, $today);
        $stmtCount->execute();
        $row = $stmtCount->get_result()->fetch_assoc();
        $nomor_urut = $row["total"] + 1;
        $nomor_antrian = $prefix . str_pad($nomor_urut, 3, "0", STR_PAD_LEFT);

        $stmt = $conn->prepare(
            "INSERT INTO antrian (nomor_antrian, driver, plat_nomor, perusahaan, jenis_kegiatan)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("sssss", $nomor_antrian, $driver, $plate, $company, $activity);

        if ($stmt->execute()) {
            $pesan_daftar = "Pendaftaran berhasil! Nomor antrian Anda: <strong>$nomor_antrian</strong>";
        } else {
            $pesan_daftar = "Gagal menyimpan data. Silakan coba lagi.";
        }
    }
}

// ================= PROSES CEK STATUS =================
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["cek_status"])) {

    $nomor_cari = strtoupper(trim($conn->real_escape_string($_POST["nomor_antrian"])));

    if ($nomor_cari === "") {
        $pesan_status = "Masukkan nomor antrian terlebih dahulu.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM antrian WHERE nomor_antrian = ? ORDER BY created_at DESC LIMIT 1");
        $stmt->bind_param("s", $nomor_cari);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $hasil_status = $result->fetch_assoc();
        } else {
            $pesan_status = "Nomor antrian \"$nomor_cari\" tidak ditemukan.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Sistem Antrian Gudang</title>

    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/home.css">
</head>

<body>

    <!-- ================= NAVBAR ================= -->
    <header class="navbar">
        <div class="nav-container">

            <a href="#" class="brand">

                <div class="brand-icon">
                    <svg viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="1.8">

                        <path d="M3 6.5h11v10H3z"/>
                        <path d="M14 10h3.5l3.5 3.5v3H14z"/>
                        <circle cx="7" cy="18" r="1.8"/>
                        <circle cx="18" cy="18" r="1.8"/>

                    </svg>
                </div>

                <div class="brand-text">
                    <strong>Sistem Antrian Gudang</strong>
                    <span>Warehouse Queue System</span>
                </div>

            </a>


            <nav class="nav-menu">

                <a href="#layar" class="nav-link">

                    <svg viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="1.8">

                        <path d="M3 12h3l2-6 4 12 2-6h7"/>

                    </svg>

                    Layar
                </a>


                <a href="#cari" class="nav-link">

                    <svg viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="1.8">

                        <circle cx="11" cy="11" r="6.5"/>
                        <path d="m16 16 4 4"/>

                    </svg>

                    Cari
                </a>


                <a href="login.php" class="login-admin">

                    <svg viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="1.8">

                        <rect x="5"
                              y="10"
                              width="14"
                              height="10"
                              rx="2"/>

                        <path d="M8 10V7a4 4 0 0 1 8 0v3"/>

                    </svg>

                    Login Admin

                </a>

            </nav>

        </div>
    </header>


    <!-- ================= HERO ================= -->

    <main>

        <section class="hero">

            <div class="hero-container">

                <h1>
                    Pendaftaran Antrian Kendaraan
                </h1>

                <p class="hero-subtitle">
                    Ambil nomor antrian untuk kegiatan muat / bongkar di gudang.
                </p>


                <!-- ================= CONTENT ================= -->

                <div class="content-grid">


                    <!-- ================= INFO CARD ================= -->

                    <div class="info-card">

                        <div class="info-content">

                            <div class="truck-icon">

                                <svg viewBox="0 0 24 24"
                                     fill="none"
                                     stroke="currentColor"
                                     stroke-width="1.8">

                                    <path d="M3 6.5h11v10H3z"/>
                                    <path d="M14 10h3.5l3.5 3.5v3H14z"/>
                                    <circle cx="7" cy="18" r="1.8"/>
                                    <circle cx="18" cy="18" r="1.8"/>

                                </svg>

                            </div>


                            <h2>
                                PT. Logistik
                            </h2>

                            <p class="address">
                                Gudang Pusat — Jl. Raya Industri No. 67, Surabaya
                            </p>


                            <div class="feature">

                                <span>✓</span>

                                Nomor antrian (M001 / B001)

                            </div>


                            <div class="feature">

                                <span>✓</span>

                                Status real-time selama proses gudang

                            </div>


                            <div class="operational-hours">

                                Jam operasional:

                                <strong>
                                    Senin – Sabtu, 08.00 – 17.00 WIB
                                </strong>

                            </div>

                        </div>

                    </div>


                    <!-- ================= FORM CARD ================= -->

                    <div class="form-card">


                        <div class="form-header">

                            <div class="clipboard-icon">

                                <svg viewBox="0 0 24 24"
                                     fill="none"
                                     stroke="currentColor"
                                     stroke-width="1.8">

                                    <rect x="5"
                                          y="4"
                                          width="14"
                                          height="17"
                                          rx="2"/>

                                    <path d="M9 4.5V3h6v1.5"/>

                                </svg>

                            </div>


                            <div>

                                <h2>
                                    Form Pendaftaran
                                </h2>

                                <p>
                                    Lengkapi data kendaraan Anda
                                </p>

                            </div>

                        </div>

                        <?php if ($pesan_daftar): ?>
                            <div class="form-message">
                                <?= $pesan_daftar /* nomor_antrian dibuat sistem, aman ditampilkan */ ?>
                            </div>
                        <?php endif; ?>

                        <!-- FORM -->

                        <form method="POST" action="index.php">


                            <!-- DRIVER -->

                            <div class="form-group">

                                <label for="driver">
                                    Nama Driver
                                    <span>*</span>
                                </label>

                                <input
                                    type="text"
                                    id="driver"
                                    name="driver"
                                    placeholder="Contoh: Budi Santoso"
                                >

                            </div>


                            <!-- NOMOR POLISI -->

                            <div class="form-group">

                                <label for="plate">
                                    Nomor Polisi
                                    <span>*</span>
                                </label>

                                <input
                                    type="text"
                                    id="plate"
                                    name="plate"
                                    placeholder="Contoh: B 1234 XYZ"
                                >

                                <small>
                                    Huruf otomatis menjadi kapital.
                                </small>

                            </div>


                            <!-- PERUSAHAAN -->

                            <div class="form-group">

                                <label for="company">
                                    Perusahaan
                                    <span>*</span>
                                </label>

                                <input
                                    type="text"
                                    id="company"
                                    name="company"
                                    placeholder="Contoh: PT. Maju Jaya"
                                >

                            </div>


                            <!-- JENIS KEGIATAN -->

                            <div class="form-group">

                                <label for="activity">
                                    Jenis Kegiatan
                                    <span>*</span>
                                </label>


                                <div class="select-wrapper">

                                    <select
                                        id="activity"
                                        name="activity"
                                    >

                                        <option value="">
                                            -- Pilih Jenis Kegiatan --
                                        </option>

                                        <option value="muat">
                                            Muat
                                        </option>

                                        <option value="bongkar">
                                            Bongkar
                                        </option>

                                    </select>

                                </div>

                            </div>


                            <!-- BUTTON -->

                            <button
                                type="submit"
                                name="daftar_antrian"
                                class="queue-button"
                            >

                                Ambil Nomor Antrian

                            </button>

                        </form>


                        <div class="status-link">

                            Sudah punya nomor?

                            <a href="#cari">
                                Cek status antrian
                            </a>

                        </div>

                    </div>

                </div>

            </div>

        </section>


        <!-- ================= CEK STATUS ================= -->

        <section
            class="search-section"
            id="cari"
        >

            <div class="search-box">

                <h2>
                    Cek Status Antrian
                </h2>

                <p>
                    Masukkan nomor antrian untuk melihat status kendaraan.
                </p>


                <form method="POST" action="index.php#cari" class="search-form">

                    <input
                        type="text"
                        name="nomor_antrian"
                        placeholder="Contoh: M001 atau B001"
                        value="<?= isset($_POST['nomor_antrian']) ? htmlspecialchars($_POST['nomor_antrian']) : '' ?>"
                    >

                    <button type="submit" name="cek_status">
                        Cari Status
                    </button>

                </form>

                <?php if ($pesan_status): ?>
                    <p class="status-message"><?= htmlspecialchars($pesan_status) ?></p>
                <?php endif; ?>

                <?php if ($hasil_status): ?>
                    <div class="status-result">
                        <p><strong>Nomor Antrian:</strong> <?= htmlspecialchars($hasil_status["nomor_antrian"]) ?></p>
                        <p><strong>Driver:</strong> <?= htmlspecialchars($hasil_status["driver"]) ?></p>
                        <p><strong>Plat Nomor:</strong> <?= htmlspecialchars($hasil_status["plat_nomor"]) ?></p>
                        <p><strong>Perusahaan:</strong> <?= htmlspecialchars($hasil_status["perusahaan"]) ?></p>
                        <p><strong>Jenis Kegiatan:</strong> <?= htmlspecialchars(ucfirst($hasil_status["jenis_kegiatan"])) ?></p>
                        <p><strong>Status:</strong> <?= htmlspecialchars(ucfirst($hasil_status["status"])) ?></p>
                    </div>
                <?php endif; ?>

            </div>

        </section>

    </main>

</body>
</html>