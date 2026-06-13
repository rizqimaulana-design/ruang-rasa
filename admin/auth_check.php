<?php
// File: admin/auth_check.php
// Include file ini di setiap halaman admin yang butuh proteksi
// Penggunaan: require_once __DIR__ . '/auth_check.php';

session_start();

if (!isset($_SESSION['user_role'])) {
    header('Location: login.php');
    exit;
}

// Cek role — gunakan $required_role sebelum include file ini
// Contoh: $required_role = 'admin'; require_once 'auth_check.php';
if (isset($required_role) && $_SESSION['user_role'] !== $required_role) {
    // Role tidak sesuai, redirect ke halaman yang benar
    if ($_SESSION['user_role'] === 'admin') {
        header('Location: index.php');
    } else {
        header('Location: dapur.php');
    }
    exit;
}