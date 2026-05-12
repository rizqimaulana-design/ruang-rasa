<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../koneksi.php';

if(isset($_POST['submit'])){

    $nama = $_POST['nama_menu'];
    $harga = $_POST['harga'];

    $gambar = $_FILES['gambar']['name'];
    $tmp = $_FILES['gambar']['tmp_name'];

    move_uploaded_file($tmp, "../img/" . $gambar);

    mysqli_query($conn,
    "INSERT INTO menu(nama_menu,harga,gambar)
    VALUES('$nama','$harga','$gambar')");

    header("Location:index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Menu</title>

    <link rel="stylesheet" href="style.css">
</head>
<body class="login-page">

<div class="login-box">

    <h2>Tambah Menu</h2>

    <form method="POST" enctype="multipart/form-data">

        <input
        type="text"
        name="nama_menu"
        placeholder="Nama Menu"
        required>

        <input
        type="number"
        name="harga"
        placeholder="Harga"
        required>

        <input
        type="file"
        name="gambar"
        id="gambar"
        required>

        <img id="preview" width="200">

        <div class="button-group">

    <button type="submit" name="submit">
        Simpan Menu
    </button>

    <a href="index.php" class="btn-kembali">
        Kembali
    </a>

</div>

    </form>

</div>

<script src="preview.js"></script>

</body>
</html>