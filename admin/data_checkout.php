<?php
session_start();
require_once '../koneksi.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

$stmt = $conn->prepare("SELECT id, nama, total, tanggal FROM checkout ORDER BY id DESC");
$stmt->execute();
$data = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Checkout</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-page">

<div class="dashboard">
    <h1>Data Checkout</h1>

    <table>
        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>Nama</th>
            <th>Total</th>
            <th>Aksi</th>
        </tr>

        <?php $no = 1; while($row = mysqli_fetch_assoc($data)) : ?>
            <tr>
                <td><?= $no++; ?></td>
                <td><?= htmlspecialchars($row['tanggal'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?= htmlspecialchars($row['nama'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td>Rp <?= number_format((float)$row['total']); ?></td>
                <td>
                    <a href="checkout_detail.php?id=<?= (int)$row['id']; ?>" class="btn-edit">Detail</a>
                    <a href="edit_checkout.php?id=<?= (int)$row['id']; ?>" class="btn-edit">Edit</a>
                    <a href="hapus_checkout.php?id=<?= (int)$row['id']; ?>" class="btn-hapus" onclick="return confirm('Hapus checkout ini beserta detailnya?')">Hapus</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <br>

    <a href="index.php" class="btn-kembali">Kembali</a>
</div>

</body>
</html>

