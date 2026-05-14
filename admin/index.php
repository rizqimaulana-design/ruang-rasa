<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit;
}

require_once '../koneksi.php';

/* TOTAL MENU */
$stmtMenu = $conn->prepare("SELECT COUNT(*) AS total FROM menu");
$stmtMenu->execute();
$totalMenu = (int)($stmtMenu->get_result()->fetch_assoc()['total'] ?? 0);
$stmtMenu->close();

/* TOTAL KONTAK */
$stmtKontak = $conn->prepare("SELECT COUNT(*) AS total FROM kontak");
$stmtKontak->execute();
$totalKontak = (int)($stmtKontak->get_result()->fetch_assoc()['total'] ?? 0);
$stmtKontak->close();


?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>

<link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="dashboard">

    <h1>RUANG RASA</h1>

    <p>Kelola menu, pelanggan, dan pengalaman terbaik untuk setiap secangkir kopi.
        <b><?= htmlspecialchars($_SESSION['admin'] ?? '', ENT_QUOTES, 'UTF-8'); ?></b>
    </p>

    <div class="card-container">

        <a href="data_menu.php" class="card-link">
    <div class="card">
        <h2>Total Menu</h2>
        <p><?= $totalMenu; ?></p>
    </div>
        </a>

        <a href="data_kontak.php" class="card-link">
    <div class="card">
        <h2>Pesan Kontak</h2>
        <p><?= $totalKontak; ?></p>
    </div>
        </a>
    </div>

    <div class="card-container">
        <a href="data_checkout.php" class="card-link">
            <div class="card">
                <h2>Data Checkout</h2>
                <?php
                    $stmtCheckout = $conn->prepare("SELECT COUNT(*) AS total FROM checkout");
                    $stmtCheckout->execute();
                    $totalCheckout = (int)($stmtCheckout->get_result()->fetch_assoc()['total'] ?? 0);
                    $stmtCheckout->close();
                ?>
                <p><?= $totalCheckout; ?></p>
            </div>
        </a>
    </div>

    <div class="menu-admin">

        <a href="tambah_menu.php">
            Tambah Menu
        </a>

        <a href="data_checkout.php">
            Lihat Checkout
        </a>

        <a href="logout.php">
            Logout
        </a>

    </div>

</div>

</body>
</html>
