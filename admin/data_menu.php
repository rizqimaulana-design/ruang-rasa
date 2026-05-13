<?php

session_start();
require_once '../koneksi.php';

$data = mysqli_query($conn,"SELECT * FROM menu");

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Menu</title>

    <link rel="stylesheet" href="style.css">
</head>
<body class="login-page">

<div class="dashboard">

    <h1>Data Menu</h1>

    <table>

    <tr>
        <th>No</th>
        <th>Gambar</th>
        <th>Nama Menu</th>
        <th>Harga</th>
        <th></th>
    </tr>

        <?php $no = 1; ?>
        <?php while($row = mysqli_fetch_assoc($data)) : ?>

        <tr>

            <td><?= $no++; ?></td>

            <td>
                <img
                src="../img/<?= $row['gambar']; ?>"
                width="80">
            </td>

            <td><?= $row['nama_menu']; ?></td>

        <td>
            Rp <?= number_format($row['harga']); ?>
        </td>

       <td>
            <a href="edit_menu.php?id=<?= $row['id']; ?>" class="btn-edit">Edit</a>
            <a href="hapus_menu.php?id=<?= $row['id']; ?>" class="btn-hapus"
            onclick="return confirm('Yakin hapus menu ini?')">Hapus</a>
        </td>

        </tr>

        <?php endwhile; ?>

    </table>

    <br>

    <a href="index.php" class="btn-kembali">
        Kembali
    </a>

</div>

</body>
</html>