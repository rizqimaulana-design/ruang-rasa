<?php
session_start();
require_once '../koneksi.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    die('checkout_id tidak valid');
}

$stmt = $conn->prepare("SELECT id, nama, total, tanggal FROM checkout WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$checkout = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$checkout) {
    die('Data checkout tidak ditemukan');
}

$stmtDetail = $conn->prepare("SELECT nama_menu, qty, harga, subtotal FROM checkout_detail WHERE checkout_id = ? ORDER BY id ASC");
$stmtDetail->bind_param('i', $id);
$stmtDetail->execute();
$details = $stmtDetail->get_result();
$stmtDetail->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Checkout</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-page">

<div class="dashboard">
    <h1>Detail Checkout #<?= (int)$checkout['id']; ?></h1>

    <p>
        <b>Tanggal:</b> <?= htmlspecialchars($checkout['tanggal'], ENT_QUOTES, 'UTF-8'); ?><br>
        <b>Nama:</b> <?= htmlspecialchars($checkout['nama'], ENT_QUOTES, 'UTF-8'); ?><br>
        <b>Total:</b> Rp <?= number_format((float)$checkout['total']); ?>
    </p>

    <table>
        <tr>
            <th>No</th>
            <th>Nama Menu</th>
            <th>Qty</th>
            <th>Harga</th>
            <th>Subtotal</th>
        </tr>

        <?php $no = 1; while($row = mysqli_fetch_assoc($details)) : ?>
            <tr>
                <td><?= $no++; ?></td>
                <td><?= htmlspecialchars($row['nama_menu'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?= (int)$row['qty']; ?></td>
                <td>Rp <?= number_format((float)$row['harga']); ?></td>
                <td>Rp <?= number_format((float)$row['subtotal']); ?></td>
            </tr>
        <?php endwhile; ?>
    </table>

    <br>

    <a href="data_checkout.php" class="btn-kembali">Kembali</a>
</div>

</body>
</html>

