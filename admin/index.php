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
$totalMenu = mysqli_num_rows(
    mysqli_query($conn, "SELECT * FROM menu")
);

/* TOTAL KONTAK */
$totalKontak = mysqli_num_rows(
    mysqli_query($conn, "SELECT * FROM kontak")
);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>

    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="dashboard">

    <h1>RUANG RASA</h1>

    <p>Kelola menu, pelanggan, dan pengalaman terbaik untuk setiap secangkir kopi.
        <b><?= $_SESSION['admin']; ?></b>
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

    <div class="menu-admin">

        <a href="tambah_menu.php">
            Tambah Menu
        </a>

        <a href="logout.php">
            Logout
        </a>

    </div>

</div>

</body>
</html>