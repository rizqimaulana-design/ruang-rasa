<?php

session_start();
require_once '../koneksi.php';

$id = $_GET['id'];

/* AMBIL DATA GAMBAR */
$data = mysqli_query($conn,
"SELECT * FROM menu WHERE id='$id'");

$row = mysqli_fetch_assoc($data);

/* HAPUS FILE GAMBAR */
if(file_exists("../img/" . $row['gambar'])){

    unlink("../img/" . $row['gambar']);
}

/* HAPUS DATABASE */
mysqli_query($conn,
"DELETE FROM menu WHERE id='$id'");

/* KEMBALI */
header("Location:data_menu.php");
exit;

?>