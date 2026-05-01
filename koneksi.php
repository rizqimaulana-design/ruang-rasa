<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "ruang_rasa";

$conn = mysqli_connect($host, $user, $pass, $db);

// === CEK KONEKSI DATABASE ===
function cekKoneksiDB($conn) {
    if (!$conn) {
        return [
            'status' => false,
            'message' => 'Koneksi ke database gagal!'
        ];
    }
    
    // Cek apakah database ada
    $db_name = "ruang_rasa";
    $cek_db = mysqli_query($conn, "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$db_name'");
    
    if (mysqli_num_rows($cek_db) == 0) {
        return [
            'status' => false,
            'message' => "Database '$db_name' belum dibuat. Jalankan setup_db.php dulu!"
        ];
    }
    
    // Cek tabel menu
    $cek_menu = mysqli_query($conn, "SHOW TABLES LIKE 'menu'");
    $tabel_menu_exists = mysqli_num_rows($cek_menu) > 0;
    
    // Cek tabel kontak
    $cek_kontak = mysqli_query($conn, "SHOW TABLES LIKE 'kontak'");
    $tabel_kontak_exists = mysqli_num_rows($cek_kontak) > 0;
    
    if (!$tabel_menu_exists || !$tabel_kontak_exists) {
        $missing = [];
        if (!$tabel_menu_exists) $missing[] = 'menu';
        if (!$tabel_kontak_exists) $missing[] = 'kontak';
        
        return [
            'status' => false,
            'message' => 'Tabel [' . implode(', ', $missing) . '] belum dibuat. Jalankan setup_db.php dulu!'
        ];
    }
    
    // Cek data menu
    $cek_data_menu = mysqli_query($conn, "SELECT COUNT(*) as jumlah FROM menu");
    $row_menu = mysqli_fetch_assoc($cek_data_menu);
    
    if ($row_menu['jumlah'] == 0) {
        return [
            'status' => false,
            'message' => 'Data menu kosong. Jalankan setup_db.php untuk insert data sample!'
        ];
    }
    
    return [
        'status' => true,
        'message' => '✅ Koneksi database OK!'
    ];
}

// Cek koneksi saat file dipanggil
if (!$conn) {
    echo "<script>alert('Koneksi ke database gagal! Periksa koneksi MySQL.');</script>";
} else {
    // Jalankan cek otomatis
    $hasil_cek = cekKoneksiDB($conn);
    if (!$hasil_cek['status']) {
        // Simpan pesan error untuk ditampilkan
        $_SESSION['db_error'] = $hasil_cek['message'];
    }
}
?>
