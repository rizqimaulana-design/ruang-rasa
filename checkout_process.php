<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

// Debug ke error_log (lebih aman dari file permission)
error_log('checkout_process hit: ' . json_encode([
    'time' => date('c'),
    'raw_len' => strlen((string)$raw),
    'decoded_is_array' => is_array($data),
    'nama' => trim($data['nama'] ?? '')
]));

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON body']);
    exit;
}

$nama = trim($data['nama'] ?? '');
$items = $data['items'] ?? [];
$total = $data['total'] ?? null;

if ($nama === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Nama wajib diisi']);
    exit;
}

if (!is_array($items) || count($items) === 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Keranjang kosong']);
    exit;
}

$numericTotal = is_numeric($total) ? (float)$total : 0;
if ($numericTotal <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Total tidak valid']);
    exit;
}

// Validasi item + hitung subtotal dari server
$validatedItems = [];
$computedTotal = 0;

foreach ($items as $it) {
    $nama_menu = trim($it['nama_menu'] ?? '');
    $qty = isset($it['qty']) ? (int)$it['qty'] : 0;
    $harga = isset($it['harga']) ? (float)$it['harga'] : 0;

    if ($nama_menu === '' || $qty <= 0 || $harga <= 0) {
        continue;
    }

    $subtotal = $harga * $qty;
    $computedTotal += $subtotal;

    $validatedItems[] = [
        'nama_menu' => $nama_menu,
        'qty' => $qty,
        'harga' => $harga,
        'subtotal' => $subtotal,
    ];
}

if (count($validatedItems) === 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Data item tidak valid']);
    exit;
}

// Transaksi database
$conn->begin_transaction();
try {
    // Simpan checkout (pakai NOW() agar konsisten dengan skema yang ada)
    $stmtCheckout = $conn->prepare("INSERT INTO checkout (nama, total, tanggal) VALUES (?, ?, NOW())");
    $computedTotalInt = (float)$computedTotal;
    $stmtCheckout->bind_param('sd', $nama, $computedTotalInt);

    if (!$stmtCheckout->execute()) {
        throw new Exception('Gagal menyimpan checkout: ' . ($stmtCheckout->error ?: $conn->error));
    }

    $checkoutId = $conn->insert_id;
    $stmtCheckout->close();

    $stmtDetail = $conn->prepare("INSERT INTO checkout_detail (checkout_id, nama_menu, qty, harga, subtotal) VALUES (?, ?, ?, ?, ?)");

    foreach ($validatedItems as $row) {
        $checkout_id = $checkoutId;
        $nama_menu = $row['nama_menu'];
        $qty = (int)$row['qty'];
        $harga = (float)$row['harga'];
        $subtotal = (float)$row['subtotal'];

        $stmtDetail->bind_param('isddd', $checkout_id, $nama_menu, $qty, $harga, $subtotal);

        if (!$stmtDetail->execute()) {
            throw new Exception('Gagal menyimpan checkout detail: ' . ($stmtDetail->error ?: $conn->error));
        }
    }

    $stmtDetail->close();

    $conn->commit();

    echo json_encode(['success' => true, 'checkout_id' => $checkoutId]);
} catch (Throwable $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage(), 'sqlstate' => ($stmtCheckout ? ($stmtCheckout->errno ?? null) : null)]);
}


