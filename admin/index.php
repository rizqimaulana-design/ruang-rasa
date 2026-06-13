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

    <!-- DIVIDER -->
    <div class="dash-divider"></div>

    <!-- GREETING -->
    <div class="dash-greeting">
        <p class="dash-greeting-sub">Panel Administrasi</p>
        <h1 class="dash-greeting-title">Selamat datang, <?= $admin; ?></h1>
        <p class="dash-greeting-desc">Kelola menu, pesanan, dan pesan kontak dari sini.</p>
    </div>

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

    </div>

</div>

</body>
</html>