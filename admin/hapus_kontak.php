<?php
require_once '../koneksi.php';

$id = $_GET['id'];

mysqli_query($conn, "DELETE FROM kontak WHERE id='$id'");

header("Location: data_kontak.php");
exit;
?>