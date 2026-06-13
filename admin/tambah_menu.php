<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../koneksi.php';

if (isset($_POST['submit'])) {
    $nama      = trim($_POST['nama_menu'] ?? '');
    $kategori  = $_POST['kategori'] ?? 'makanan';
    $harga     = $_POST['harga'] ?? null;

    $gambar = $_FILES['gambar']['name'];
    $tmp    = $_FILES['gambar']['tmp_name'];
    move_uploaded_file($tmp, "../img/" . $gambar);

    $stmt = $conn->prepare("INSERT INTO menu (nama_menu, kategori, harga, gambar) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssds", $nama, $kategori, $harga, $gambar);
    $stmt->execute();
    $stmt->close();

    header("Location: data_menu.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Menu — RuangRasa</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/form.css">
</head>
<body class="login-page">

<div class="login-box">

    <h2>Tambah Menu</h2>

    <form method="POST" enctype="multipart/form-data">

        <div class="form-group">
            <label for="nama_menu">Nama Menu</label>
            <input type="text" name="nama_menu" id="nama_menu"
                   placeholder="Contoh: Cappuccino" required>
        </div>

        <div class="form-group">
            <label for="kategori">Kategori</label>
            <div class="kategori-wrap">
                <label class="kategori-option">
                    <input type="radio" name="kategori" value="makanan" checked>
                    <span><i class="bi bi-egg-fried"></i> Makanan</span>
                </label>
                <label class="kategori-option">
                    <input type="radio" name="kategori" value="minuman">
                    <span><i class="bi bi-cup-hot"></i> Minuman</span>
                </label>
            </div>
        </div>

        <div class="form-group">
            <label for="harga">Harga (Rp)</label>
            <input type="number" name="harga" id="harga"
                   placeholder="Contoh: 25000" required>
        </div>

        <div class="form-group">
            <label>Foto Menu</label>
            <label class="file-label" for="gambar">
                <span class="file-btn">Pilih Foto</span>
                <span class="file-name" id="file-name-text">Belum ada foto dipilih</span>
            </label>
            <input type="file" name="gambar" id="gambar" accept="image/*" required>
        </div>

        <div class="preview-box">
            <span class="preview-placeholder" id="preview-placeholder">Preview foto akan muncul di sini</span>
            <img id="preview" src="" alt="Preview">
        </div>

        <div class="button-group">
            <button type="submit" name="submit">Simpan Menu</button>
            <a href="data_menu.php" class="btn-kembali">Kembali</a>
        </div>

    </form>

</div>

<script>
    document.getElementById('gambar').addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        document.getElementById('file-name-text').textContent = file.name;
        const reader = new FileReader();
        reader.onload = function (e) {
            const preview = document.getElementById('preview');
            const placeholder = document.getElementById('preview-placeholder');
            preview.src = e.target.result;
            preview.style.display = 'block';
            placeholder.style.display = 'none';
        };
        reader.readAsDataURL(file);
    });
</script>

</body>
</html>