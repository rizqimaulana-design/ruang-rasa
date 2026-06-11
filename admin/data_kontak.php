<?php
session_start();
require_once '../koneksi.php';

$stmt = $conn->prepare("SELECT id, nama, email, no_hp, pesan FROM kontak ORDER BY id DESC");
$stmt->execute();
$data = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Kontak</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/modal.css">
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
            <td><?= htmlspecialchars($row['nama'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?= htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?= htmlspecialchars($row['no_hp'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?= nl2br(htmlspecialchars($row['pesan'], ENT_QUOTES, 'UTF-8')); ?></td>
            <td>
                <button 
                    onclick="bukaModal('hapus_kontak.php?id=<?= (int)$row['id']; ?>')"
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
        <h3>🗑️ Hapus Pesan</h3>
        <p>Yakin ingin menghapus pesan ini? Tindakan ini tidak bisa dibatalkan.</p>
        <div class="modal-actions">
            <button class="btn-batal" onclick="tutupModal()">Batal</button>
            <a id="btn-konfirmasi-hapus" href="#" class="btn-hapus">Hapus</a>
        </div>
    </div>
</div>

<!-- Ganti script.js dengan nama file JS admin kamu -->
<script src="js/script.js"></script>

</body>
</html>