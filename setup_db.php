<?php
// File setup untuk membuat database dan tabel

$host = "localhost";
$user = "root";
$pass = "";
$db   = "ruang_rasa";
$port = 3306; // Default Laragon, ganti 3307 jika tidak bisa konek

// Koneksi ke MySQL tanpa memilih database dulu
$conn = mysqli_connect($host, $user, $pass, "", $port);

if (!$conn) {
    $error_code = mysqli_connect_errno();
    $error_msg  = mysqli_connect_error();
    die("❌ Koneksi gagal! (Code: $error_code) $error_msg <br><br>
        Pastikan:<br>
        1. MySQL Laragon sudah <b>Running</b><br>
        2. Port sesuai (coba ganti ke <b>3307</b> jika gagal)<br>
        3. Username & password benar
    ");
}

// Buat database jika belum ada
$query_db = "CREATE DATABASE IF NOT EXISTS $db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if (!mysqli_query($conn, $query_db)) {
    die("❌ Gagal membuat database: " . mysqli_error($conn));
}

// Pilih database
mysqli_select_db($conn, $db);

// Set charset ke utf8mb4 (penting untuk Laragon)
mysqli_set_charset($conn, "utf8mb4");

// ========== TABEL MENU ============
$query_menu = "CREATE TABLE IF NOT EXISTS menu (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    nama_menu VARCHAR(255) NOT NULL,
    harga DECIMAL(10,2) NOT NULL,
    gambar VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (!mysqli_query($conn, $query_menu)) {
    die("❌ Gagal membuat tabel menu: " . mysqli_error($conn));
}

// Cek apakah menu sudah ada data
$cek_menu = mysqli_query($conn, "SELECT COUNT(*) as jumlah FROM menu");
$row_menu = mysqli_fetch_assoc($cek_menu);

if ((int)$row_menu['jumlah'] === 0) {
    // Insert data menu contoh
    $menu_items = [
        ["Espresso", 15000, "m1.png"],
        ["Americano", 18000, "m2.png"],
        ["Cappuccino", 20000, "m3.png"],
        ["Latte", 22000, "m4.png"],
        ["Mocha", 25000, "m5.png"],
        ["Matcha", 25000, "m6.png"],
        ["Kopi Susu", 20000, "m7.png"],
        ["Teh Tarik", 18000, "m8.png"]
    ];

    foreach ($menu_items as $item) {
        $stmt = $conn->prepare("INSERT INTO menu (nama_menu, harga, gambar) VALUES (?, ?, ?)");
        $stmt->bind_param("sds", $item[0], $item[1], $item[2]);
        $stmt->execute();
        $stmt->close();
    }

    echo "✅ Data menu berhasil ditambahkan!<br>";
} else {
    echo "ℹ️ Data menu sudah ada, skip insert.<br>";
}

// ========== TABEL KONTAK ============
$query_kontak = "CREATE TABLE IF NOT EXISTS kontak (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    no_hp VARCHAR(20) NOT NULL,
    pesan TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (!mysqli_query($conn, $query_kontak)) {
    die("❌ Gagal membuat tabel kontak: " . mysqli_error($conn));
}

// ========== TABEL CHECKOUT ============
$query_checkout = "CREATE TABLE IF NOT EXISTS checkout (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    total DECIMAL(12,2) NOT NULL,
    tanggal DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (!mysqli_query($conn, $query_checkout)) {
    die("❌ Gagal membuat tabel checkout: " . mysqli_error($conn));
}

// ========== TABEL CHECKOUT_DETAIL ============
$query_checkout_detail = "CREATE TABLE IF NOT EXISTS checkout_detail (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    checkout_id INT(11) NOT NULL,
    nama_menu VARCHAR(255) NOT NULL,
    qty INT(11) NOT NULL,
    harga DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,
    CONSTRAINT fk_checkout_detail_checkout FOREIGN KEY (checkout_id) REFERENCES checkout(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (!mysqli_query($conn, $query_checkout_detail)) {
    die("❌ Gagal membuat tabel checkout_detail: " . mysqli_error($conn));
}

echo "✅ Database dan tabel berhasil disetup!<br>";
echo "📋 Tabel yang tersedia: menu, kontak, checkout, checkout_detail<br>";
echo "🔌 Menggunakan port: $port";

mysqli_close($conn);
?>