<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

require_once '../koneksi.php';

$stmtMenu = $conn->prepare("SELECT COUNT(*) AS total FROM menu");
$stmtMenu->execute();
$totalMenu = (int)($stmtMenu->get_result()->fetch_assoc()['total'] ?? 0);
$stmtMenu->close();

$stmtKontak = $conn->prepare("SELECT COUNT(*) AS total FROM kontak");
$stmtKontak->execute();
$totalKontak = (int)($stmtKontak->get_result()->fetch_assoc()['total'] ?? 0);
$stmtKontak->close();

$stmtCheckout = $conn->prepare("SELECT COUNT(*) AS total FROM checkout");
$stmtCheckout->execute();
$totalCheckout = (int)($stmtCheckout->get_result()->fetch_assoc()['total'] ?? 0);
$stmtCheckout->close();

// Statistik dapur
$stmtDapur = $conn->query("SELECT
    SUM(status_dapur='menunggu') AS menunggu,
    SUM(status_dapur='diproses') AS diproses,
    SUM(status_dapur='selesai')  AS selesai,
    SUM(status_ambil='belum')    AS belum_ambil
FROM checkout");
$dapur = $stmtDapur ? $stmtDapur->fetch_assoc() : [];
$totalMenunggu  = (int)($dapur['menunggu']    ?? 0);
$totalDiproses  = (int)($dapur['diproses']    ?? 0);
$totalSelesai   = (int)($dapur['selesai']     ?? 0);
$totalBelumAmbil= (int)($dapur['belum_ambil'] ?? 0);

$admin = htmlspecialchars($_SESSION['admin'] ?? '', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — RuangRasa</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,500;0,600;0,700;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">

    <style>
        /* ── Stat Dapur ── */
        .dash-section-label {
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #8a6245;
            margin: 2rem 0 0.75rem;
        }

        .dapur-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 0.5rem;
        }

        .dapur-stat {
            background: #2a1a0e;
            border: 1px solid #3a2518;
            border-radius: 12px;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            transition: border-color .2s, background .2s;
        }

        .dapur-stat:hover {
            background: #321f12;
            border-color: #c8863c;
        }

        .dapur-stat-icon {
            width: 38px; height: 38px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .dapur-stat-icon.menunggu { background: rgba(231,76,60,0.15); }
        .dapur-stat-icon.diproses { background: rgba(230,126,34,0.15); }
        .dapur-stat-icon.selesai  { background: rgba(39,174,96,0.15);  }
        .dapur-stat-icon.ambil    { background: rgba(52,152,219,0.15); }

        .dapur-stat-body { display: flex; flex-direction: column; }
        .dapur-stat-label { font-size: 11px; color: #8a6245; margin-bottom: 2px; }
        .dapur-stat-value { font-size: 22px; font-weight: 700; }
        .dapur-stat-value.menunggu { color: #e74c3c; }
        .dapur-stat-value.diproses { color: #e67e22; }
        .dapur-stat-value.selesai  { color: #27ae60; }
        .dapur-stat-value.ambil    { color: #3498db; }

        /* Badge notif pesanan menunggu */
        .notif-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(231,76,60,0.15);
            border: 1px solid rgba(231,76,60,0.3);
            color: #e74c3c;
            font-size: 12px;
            font-weight: 600;
            padding: 6px 14px;
            border-radius: 20px;
            margin-bottom: 1.5rem;
        }

        .notif-badge i { font-size: 13px; }

        /* Tombol aksi dapur */
        .dash-action-btn.dapur {
            background: rgba(230,126,34,0.12);
            border-color: rgba(230,126,34,0.3);
            color: #e67e22;
        }

        .dash-action-btn.dapur:hover {
            background: rgba(230,126,34,0.2);
            border-color: #e67e22;
        }

        .dash-action-btn.konfirmasi {
            background: rgba(39,174,96,0.1);
            border-color: rgba(39,174,96,0.3);
            color: #27ae60;
        }

        .dash-action-btn.konfirmasi:hover {
            background: rgba(39,174,96,0.2);
            border-color: #27ae60;
        }

        @media (max-width: 600px) {
            .dapur-stats { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body class="dash-page">

<div class="dash-wrap">

    <!-- HEADER -->
    <div class="dash-header">
        <div class="dash-brand">
            <span class="dash-brand-italic">Ruang</span><span class="dash-brand-normal">Rasa</span><span class="dash-brand-dot">.</span>
        </div>
        <div class="dash-meta">
            <i class="bi bi-person-circle"></i>
            <span><?= $admin; ?></span>
            <a href="logout.php" class="dash-logout">
                <i class="bi bi-box-arrow-right"></i>
                Logout
            </a>
        </div>
    </div>

    <div class="dash-divider"></div>

    <!-- GREETING -->
    <div class="dash-greeting">
        <p class="dash-greeting-sub">Panel Administrasi</p>
        <h1 class="dash-greeting-title">Selamat datang, <?= $admin; ?></h1>
        <p class="dash-greeting-desc">Kelola menu, pesanan, dan pesan kontak dari sini.</p>
    </div>

    <!-- NOTIF jika ada pesanan menunggu -->
    <?php if ($totalMenunggu > 0): ?>
    <div class="notif-badge">
        <i class="bi bi-exclamation-circle-fill"></i>
        <?= $totalMenunggu ?> pesanan menunggu diproses dapur
    </div>
    <?php endif; ?>

    <!-- STAT CARDS -->
    <div class="dash-cards">

        <a href="data_menu.php" class="dash-card">
            <div class="dash-card-icon"><i class="bi bi-cup-hot"></i></div>
            <div class="dash-card-body">
                <span class="dash-card-label">Total Menu</span>
                <span class="dash-card-value"><?= $totalMenu; ?></span>
            </div>
            <i class="bi bi-chevron-right dash-card-arrow"></i>
        </a>

        <a href="data_kontak.php" class="dash-card">
            <div class="dash-card-icon"><i class="bi bi-envelope"></i></div>
            <div class="dash-card-body">
                <span class="dash-card-label">Pesan Kontak</span>
                <span class="dash-card-value"><?= $totalKontak; ?></span>
            </div>
            <i class="bi bi-chevron-right dash-card-arrow"></i>
        </a>

        <a href="data_checkout.php" class="dash-card">
            <div class="dash-card-icon"><i class="bi bi-bag-check"></i></div>
            <div class="dash-card-body">
                <span class="dash-card-label">Data Checkout</span>
                <span class="dash-card-value"><?= $totalCheckout; ?></span>
            </div>
            <i class="bi bi-chevron-right dash-card-arrow"></i>
        </a>

    </div>

    <!-- STATUS DAPUR -->
    <div class="dash-section-label">Status Dapur</div>
    <div class="dapur-stats">

        <a href="pesanan_admin.php?dapur=menunggu" class="dapur-stat">
            <div class="dapur-stat-icon menunggu">⏳</div>
            <div class="dapur-stat-body">
                <span class="dapur-stat-label">Menunggu</span>
                <span class="dapur-stat-value menunggu"><?= $totalMenunggu ?></span>
            </div>
        </a>

        <a href="pesanan_admin.php?dapur=diproses" class="dapur-stat">
            <div class="dapur-stat-icon diproses">🔥</div>
            <div class="dapur-stat-body">
                <span class="dapur-stat-label">Diproses</span>
                <span class="dapur-stat-value diproses"><?= $totalDiproses ?></span>
            </div>
        </a>

        <a href="pesanan_admin.php?dapur=selesai" class="dapur-stat">
            <div class="dapur-stat-icon selesai">✅</div>
            <div class="dapur-stat-body">
                <span class="dapur-stat-label">Selesai</span>
                <span class="dapur-stat-value selesai"><?= $totalSelesai ?></span>
            </div>
        </a>

        <a href="pesanan_admin.php?ambil=belum" class="dapur-stat">
            <div class="dapur-stat-icon ambil">🛍️</div>
            <div class="dapur-stat-body">
                <span class="dapur-stat-label">Belum Diambil</span>
                <span class="dapur-stat-value ambil"><?= $totalBelumAmbil ?></span>
            </div>
        </a>

    </div>

    <!-- QUICK ACTIONS -->
    <div class="dash-section-label">Aksi Cepat</div>
    <div class="dash-actions">

        <a href="tambah_menu.php" class="dash-action-btn">
            <i class="bi bi-plus-circle"></i>
            Tambah Menu
        </a>

        <a href="data_menu.php" class="dash-action-btn">
            <i class="bi bi-list-ul"></i>
            Kelola Menu
        </a>

        <a href="data_checkout.php" class="dash-action-btn">
            <i class="bi bi-receipt"></i>
            Lihat Checkout
        </a>

        <a href="data_kontak.php" class="dash-action-btn">
            <i class="bi bi-chat-dots"></i>
            Pesan Kontak
        </a>

        <a href="pesanan_admin.php" class="dash-action-btn dapur">
            <i class="bi bi-fire"></i>
            Status Dapur
        </a>

        <a href="pesanan_admin.php?ambil=belum" class="dash-action-btn konfirmasi">
            <i class="bi bi-bag-check"></i>
            Konfirmasi Ambil
        </a>

    </div>

</div>

</body>
</html>