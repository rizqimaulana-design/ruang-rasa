<?php

session_start();
require_once '../koneksi.php';

$stmt = $conn->prepare("SELECT id, nama_menu, harga, gambar FROM menu ORDER BY id DESC");
$stmt->execute();
$data = $stmt->get_result();


?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Menu</title>

    <link rel="stylesheet" href="css/style.css">
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
                src="../img/<?= htmlspecialchars($row['gambar'], ENT_QUOTES, 'UTF-8'); ?>"
                width="80">
            </td>

            <td><?= htmlspecialchars($row['nama_menu'], ENT_QUOTES, 'UTF-8'); ?></td>

        <td>
            Rp <?= number_format((float)$row['harga']); ?>
        </td>

       <td>
            <a href="edit_menu.php?id=<?= (int)$row['id']; ?>" class="btn-edit">Edit</a>
            <a href="hapus_menu.php?id=<?= (int)$row['id']; ?>" class="btn-hapus"
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