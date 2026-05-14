<?php
require_once '../koneksi.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare("DELETE FROM kontak WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

header("Location: data_kontak.php");
exit;
?>
