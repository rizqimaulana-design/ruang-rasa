<?php
session_start();
require_once '../koneksi.php';

$data = mysqli_query($conn,"SELECT * FROM kontak");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Kontak</title>

    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="dashboard">

    <h1>Pesan Kontak</h1>

    <table>

        <tr>
            <th>No</th>
            <th>Nama</th>
            <th>Email</th>
            <th>No HP</th>
            <th>Pesan</th>
            <th>Aksi</th>
        </tr>

        <?php $no = 1; ?>
        <?php while($row = mysqli_fetch_assoc($data)) : ?>

        <tr>
            <td><?= $no++; ?></td>
            <td><?= $row['nama']; ?></td>
            <td><?= $row['email']; ?></td>
            <td><?= $row['no_hp']; ?></td>
            <td><?= $row['pesan']; ?></td>

            <td>
                <a href="hapus_kontak.php?id=<?= $row['id']; ?>" 
                   onclick="return confirm('Yakin ingin menghapus pesan ini?')"
                   style="
                    padding: 6px 10px;
                    background: #e74c3c;
                    color: white;
                    text-decoration: none;
                    border-radius: 5px;
                    display: inline-block;
                   ">
                    Hapus
                </a>
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