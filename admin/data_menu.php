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
    <link rel="stylesheet" href="css/modal.css">
</head>
<body>

<div class="dashboard">

    <h1>Data Menu</h1>

    <table>
        <tr>
            <th>No</th>
            <th>Gambar</th>
            <th>Nama Menu</th>
            <th>Harga</th>
            <th>Aksi</th>
        </tr>

        <?php $no = 1; ?>
        <?php while($row = mysqli_fetch_assoc($data)) : ?>
        <tr>
            <td><?= $no++; ?></td>
            <td>
                <img src="../img/<?= htmlspecialchars($row['gambar'], ENT_QUOTES, 'UTF-8'); ?>" width="80">
            </td>
            <td><?= htmlspecialchars($row['nama_menu'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td>Rp <?= number_format((float)$row['harga']); ?></td>
            <td style="white-space: nowrap;">
                <a href="edit_menu.php?id=<?= (int)$row['id']; ?>" class="btn-edit">Edit</a>
                <button
                    onclick="bukaModal('hapus_menu.php?id=<?= (int)$row['id']; ?>')"
                    class="btn-hapus-trigger">
                    Hapus
                </button>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <br>
    <a href="index.php" class="btn-kembali">Kembali</a>

</div>

<!-- MODAL KONFIRMASI HAPUS -->
<div class="modal-overlay" id="modalHapus">
    <div class="modal-box">
        <h3>🗑️ Hapus Menu</h3>
        <p>Yakin ingin menghapus menu ini? Tindakan ini tidak bisa dibatalkan.</p>
        <div class="modal-actions">
            <button class="btn-batal" onclick="tutupModal()">Batal</button>
            <a id="btn-konfirmasi-hapus" href="#" class="btn-hapus">Hapus</a>
        </div>
    </div>
</div>

<script src="js/script.js"></script>

</body>
</html>