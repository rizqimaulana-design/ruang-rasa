<?php
session_start();
require_once '../koneksi.php';

// ambil id dari URL
$id = (int)($_GET['id'] ?? 0);

// ambil data menu berdasarkan id
$stmt = $conn->prepare("SELECT * FROM menu WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();


// update data saat form disubmit
if(isset($_POST['update'])) {
    $nama = $_POST['nama_menu'];
    $harga = $_POST['harga'];

    // cek kalau gambar diubah
    if(isset($_FILES['gambar']['name']) && $_FILES['gambar']['name'] != "") {
        $gambar = $_FILES['gambar']['name'];
        $tmp = $_FILES['gambar']['tmp_name'];

        move_uploaded_file($tmp, "../img/" . $gambar);

        $stmt = $conn->prepare("UPDATE menu SET nama_menu = ?, harga = ?, gambar = ? WHERE id = ?");
        $stmt->bind_param("sdsi", $nama, $harga, $gambar, $id);
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt = $conn->prepare("UPDATE menu SET nama_menu = ?, harga = ? WHERE id = ?");
        $stmt->bind_param("sdi", $nama, $harga, $id);
        $stmt->execute();
        $stmt->close();
    }


    header("Location: data_menu.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Menu</title>
</head>
<body>

<h2>Edit Menu</h2>

<form method="POST" enctype="multipart/form-data">

    <label>Nama Menu</label><br>
    <input type="text" name="nama_menu" value="<?= htmlspecialchars($data['nama_menu'], ENT_QUOTES, 'UTF-8'); ?>" required><br><br>

    <label>Harga</label><br>
    <input type="text" name="harga" value="<?= htmlspecialchars($data['harga'], ENT_QUOTES, 'UTF-8'); ?>" required><br><br>

    <label>Gambar (biarkan jika tidak diubah)</label><br>
    <input type="file" name="gambar"><br><br>

    <img src="../img/<?= htmlspecialchars($data['gambar'], ENT_QUOTES, 'UTF-8'); ?>" width="120"><br><br>

   <button type="submit" name="update" style="
    padding: 8px 15px;
    background: #2ecc71;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    ">
    Update
    </button>

    <a href="data_menu.php" style="
    padding: 8px 15px;
    background: #e74c3c;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    margin-left: 10px;
    display: inline-block;
    cursor: pointer;
    ">
    Batal
    </a>

</form>

</body>
</html>