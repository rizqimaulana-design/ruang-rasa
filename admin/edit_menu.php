<?php
session_start();
require_once '../koneksi.php';

// ambil id dari URL
$id = $_GET['id'];

// ambil data menu berdasarkan id
$query = mysqli_query($conn, "SELECT * FROM menu WHERE id='$id'");
$data = mysqli_fetch_assoc($query);

// update data saat form disubmit
if(isset($_POST['update'])) {
    $nama = $_POST['nama_menu'];
    $harga = $_POST['harga'];

    // cek kalau gambar diubah
    if($_FILES['gambar']['name'] != "") {
        $gambar = $_FILES['gambar']['name'];
        $tmp = $_FILES['gambar']['tmp_name'];

        move_uploaded_file($tmp, "../img/" . $gambar);

        mysqli_query($conn, "UPDATE menu SET 
            nama_menu='$nama',
            harga='$harga',
            gambar='$gambar'
            WHERE id='$id'
        ");
    } else {
        mysqli_query($conn, "UPDATE menu SET 
            nama_menu='$nama',
            harga='$harga'
            WHERE id='$id'
        ");
    }

    header("Location: data_menu.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Menu</title>
</head>
<body>

<h2>Edit Menu</h2>

<form method="POST" enctype="multipart/form-data">

    <label>Nama Menu</label><br>
    <input type="text" name="nama_menu" value="<?= $data['nama_menu']; ?>" required><br><br>

    <label>Harga</label><br>
    <input type="text" name="harga" value="<?= $data['harga']; ?>" required><br><br>

    <label>Gambar (biarkan jika tidak diubah)</label><br>
    <input type="file" name="gambar"><br><br>

    <img src="../img/<?= $data['gambar']; ?>" width="120"><br><br>

   <button type="submit" name="update" style="
    padding: 8px 15px;
    background: #2ecc71;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    ">
    Update
    </button>

    <a href="data_menu.php" style="
    padding: 8px 15px;
    background: #e74c3c;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    margin-left: 10px;
    display: inline-block;
    cursor: pointer;
    ">
    Batal
    </a>

</form>

</body>
</html>