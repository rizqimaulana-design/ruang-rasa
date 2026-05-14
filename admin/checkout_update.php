<?php
session_start();
require_once '../koneksi.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    die('Method not allowed');
}

$checkoutId = (int)($_POST['checkout_id'] ?? 0);
$nama = trim($_POST['nama'] ?? '');

if ($checkoutId <= 0) {
    die('checkout_id tidak valid');
}
if ($nama === '') {
    die('Nama wajib diisi');
}

$detail = $_POST['detail'] ?? [];
if (!is_array($detail) || count($detail) === 0) {
    die('Detail checkout tidak valid');
}

// Validasi detail dan hitung total
$validated = [];
$computedTotal = 0.0;

foreach ($detail as $k => $row) {
    if (!is_array($row)) continue;

    $nama_menu = trim($row['nama_menu'] ?? '');
    $qty = isset($row['qty']) ? (int)$row['qty'] : 0;
    $harga = isset($row['harga']) ? (float)$row['harga'] : 0;

    if ($nama_menu === '' || $qty <= 0 || $harga < 0) {
        continue;
    }

    $subtotal = $harga * $qty;
    $computedTotal += $subtotal;

    $validated[] = [
        'nama_menu' => $nama_menu,
        'qty' => $qty,
        'harga' => $harga,
        'subtotal' => $subtotal,
    ];
}

if (count($validated) === 0) {
    die('Tidak ada detail item yang valid');
}

$conn->begin_transaction();
try {
    $stmt = $conn->prepare("UPDATE checkout SET nama = ?, total = ? WHERE id = ?");
    $totalFloat = (float)$computedTotal;
    $stmt->bind_param('sdi', $nama, $totalFloat, $checkoutId);
    $stmt->execute();
    $stmt->close();

    $stmtDel = $conn->prepare("DELETE FROM checkout_detail WHERE checkout_id = ?");
    $stmtDel->bind_param('i', $checkoutId);
    $stmtDel->execute();
    $stmtDel->close();

    $stmtIns = $conn->prepare("INSERT INTO checkout_detail (checkout_id, nama_menu, qty, harga, subtotal) VALUES (?, ?, ?, ?, ?)");

    foreach ($validated as $row) {
        $cid = $checkoutId;
        $nama_menu = $row['nama_menu'];
        $qty = (int)$row['qty'];
        $harga = (float)$row['harga'];
        $subtotal = (float)$row['subtotal'];

        $stmtIns->bind_param('isddd', $cid, $nama_menu, $qty, $harga, $subtotal);
        if (!$stmtIns->execute()) {
            throw new Exception('Gagal menyimpan checkout_detail: ' . ($stmtIns->error ?: $conn->error));
        }
    }

    $stmtIns->close();
    $conn->commit();

    header('Location: data_checkout.php');
    exit;
} catch (Throwable $e) {
    $conn->rollback();
    die('Gagal update checkout: ' . $e->getMessage());
}

