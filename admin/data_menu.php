<?php
session_start();
require_once '../koneksi.php';

$stmt = $conn->prepare("SELECT id, nama_menu, kategori, harga, gambar FROM menu ORDER BY kategori, nama_menu ASC");
$stmt->execute();
$result = $stmt->get_result();

$makanan  = [];
$minuman  = [];

while ($row = $result->fetch_assoc()) {
    if ($row['kategori'] === 'minuman') {
        $minuman[] = $row;
    } else {
        $makanan[] = $row;
    }
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Menu — RuangRasa</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/modal.css">
    <link rel="stylesheet" href="css/data_menu.css">
</head>
<body class="dash-page">

<div class="dash-wrap">

    <!-- HEADER -->
    <div class="dash-header">
        <div class="dash-brand">
            <span class="dash-brand-italic">Ruang</span><span class="dash-brand-normal">Rasa</span><span class="dash-brand-dot">.</span>
        </div>
        <a href="index.php" class="btn-kembali">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>
    <div class="dash-divider"></div>

    <div class="dm-top">
        <div>
            <p class="dm-eyebrow">Kelola Menu</p>
            <h1 class="dm-title">Data Menu</h1>
        </div>
        <a href="tambah_menu.php" class="dash-action-btn">
            <i class="bi bi-plus-circle"></i> Tambah Menu
        </a>
    </div>

    <!-- MAKANAN -->
    <div class="dm-section">
        <div class="dm-section-header">
            <i class="bi bi-egg-fried"></i>
            <span>Makanan</span>
            <span class="dm-badge"><?= count($makanan); ?></span>
        </div>

        <?php if (empty($makanan)) : ?>
            <div class="dm-empty">Belum ada menu makanan.</div>
        <?php else : ?>
        <div class="dm-grid">
            <?php foreach ($makanan as $row) : ?>
            <div class="dm-card">
                <div class="dm-card-img-wrap">
                    <img src="../img/<?= htmlspecialchars($row['gambar'], ENT_QUOTES, 'UTF-8'); ?>"
                         alt="<?= htmlspecialchars($row['nama_menu'], ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="dm-card-body">
                    <div class="dm-card-name"><?= htmlspecialchars($row['nama_menu'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="dm-card-price">Rp <?= number_format((float)$row['harga']); ?></div>
                </div>
                <div class="dm-card-actions">
                    <a href="edit_menu.php?id=<?= (int)$row['id']; ?>" class="dm-btn-edit">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <button onclick="bukaModal('hapus_menu.php?id=<?= (int)$row['id']; ?>')" class="dm-btn-hapus">
                        <i class="bi bi-trash"></i> Hapus
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- MINUMAN -->
    <div class="dm-section">
        <div class="dm-section-header">
            <i class="bi bi-cup-hot"></i>
            <span>Minuman</span>
            <span class="dm-badge"><?= count($minuman); ?></span>
        </div>

        <?php if (empty($minuman)) : ?>
            <div class="dm-empty">Belum ada menu minuman.</div>
        <?php else : ?>
        <div class="dm-grid">
            <?php foreach ($minuman as $row) : ?>
            <div class="dm-card">
                <div class="dm-card-img-wrap">
                    <img src="../img/<?= htmlspecialchars($row['gambar'], ENT_QUOTES, 'UTF-8'); ?>"
                         alt="<?= htmlspecialchars($row['nama_menu'], ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="dm-card-body">
                    <div class="dm-card-name"><?= htmlspecialchars($row['nama_menu'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="dm-card-price">Rp <?= number_format((float)$row['harga']); ?></div>
                </div>
                <div class="dm-card-actions">
                    <a href="edit_menu.php?id=<?= (int)$row['id']; ?>" class="dm-btn-edit">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <button onclick="bukaModal('hapus_menu.php?id=<?= (int)$row['id']; ?>')" class="dm-btn-hapus">
                        <i class="bi bi-trash"></i> Hapus
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

</div>

<!-- MODAL KONFIRMASI HAPUS -->
<div class="modal-overlay" id="modalHapus">
    <div class="modal-box">
        <h3>Hapus Menu</h3>
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