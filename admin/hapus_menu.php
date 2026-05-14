<?php
session_start();
require_once '../koneksi.php';

$id = (int)($_GET['id'] ?? 0);

/* AMBIL DATA GAMBAR */
$stmt = $conn->prepare("SELECT id, gambar FROM menu WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result ? $result->fetch_assoc() : null;
$stmt->close();

/* HAPUS FILE GAMBAR */
if ($row && !empty($row['gambar'])) {
    $filePath = "../img/" . $row['gambar'];
    if (file_exists($filePath)) {
        unlink($filePath);
    }
}

/* HAPUS DATABASE */
$del = $conn->prepare("DELETE FROM menu WHERE id = ?");
$del->bind_param("i", $id);
$del->execute();
$del->close();

/* KEMBALI */
header("Location:data_menu.php");
exit;
?>
