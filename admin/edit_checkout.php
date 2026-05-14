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

// ambil checkout
$stmt = $conn->prepare("SELECT id, nama, total, tanggal FROM checkout WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$checkout = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$checkout) {
    die('Data checkout tidak ditemukan');
}

// ambil detail
$stmtDetail = $conn->prepare("SELECT id, nama_menu, qty, harga, subtotal FROM checkout_detail WHERE checkout_id = ? ORDER BY id ASC");
$stmtDetail->bind_param('i', $id);
$stmtDetail->execute();
$detailsRes = $stmtDetail->get_result();
$stmtDetail->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Checkout</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-page">

<div class="dashboard">
    <h1>Edit Checkout #<?= (int)$checkout['id']; ?></h1>

    <form method="POST" action="checkout_update.php" onsubmit="return confirm('Simpan perubahan checkout?')">
        <input type="hidden" name="checkout_id" value="<?= (int)$checkout['id']; ?>">

        <p>
            <b>Tanggal:</b> <?= htmlspecialchars($checkout['tanggal'], ENT_QUOTES, 'UTF-8'); ?><br>
            <b>Total Saat Ini:</b> Rp <?= number_format((float)$checkout['total']); ?>
        </p>

        <label><b>Nama Checkout</b></label><br>
        <input type="text" name="nama" value="<?= htmlspecialchars($checkout['nama'], ENT_QUOTES, 'UTF-8'); ?>" required style="width:100%; padding:10px; border-radius:8px; border:1px solid #444; background:#222; color:#fff;">

        <h3 style="margin-top:25px;">Detail Item</h3>

        <table>
            <tr>
                <th>Nama Menu</th>
                <th>Qty</th>
                <th>Harga</th>
                <th>Subtotal (otomatis)</th>
            </tr>

            <?php while($row = mysqli_fetch_assoc($detailsRes)) :
                $detailId = (int)$row['id'];
            ?>
            <tr>
                <td>
                    <?= htmlspecialchars($row['nama_menu'], ENT_QUOTES, 'UTF-8'); ?>
                    <input type="hidden" name="detail[<?= $detailId; ?>][nama_menu]" value="<?= htmlspecialchars($row['nama_menu'], ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="detail_ids[]" value="<?= $detailId; ?>">
                </td>
                <td>
                    <input type="number" name="detail[<?= $detailId; ?>][qty]" value="<?= (int)$row['qty']; ?>" min="1" required style="width:90px; padding:8px; border-radius:8px; border:1px solid #444; background:#222; color:#fff;">
                </td>
                <td>
                    <input type="number" step="0.01" name="detail[<?= $detailId; ?>][harga]" value="<?= htmlspecialchars($row['harga'], ENT_QUOTES, 'UTF-8'); ?>" min="0" required style="width:120px; padding:8px; border-radius:8px; border:1px solid #444; background:#222; color:#fff;">
                </td>
                <td>
                    Rp <?= number_format((float)$row['subtotal']); ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>

        <div class="button-group">
            <button type="submit" class="btn-kembali" style="background:#b6895b; margin-top:20px;">Simpan</button>
            <a href="data_checkout.php" class="btn-kembali" style="background:#444; margin-top:20px;">Batal</a>
        </div>
    </form>
</div>

</body>
</html>

