<?php
session_start();
require_once '../koneksi.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    die('checkout_id tidak valid');
}

$conn->begin_transaction();
try {
    $stmtDelDetail = $conn->prepare("DELETE FROM checkout_detail WHERE checkout_id = ?");
    $stmtDelDetail->bind_param('i', $id);
    $stmtDelDetail->execute();
    $stmtDelDetail->close();

    $stmtDel = $conn->prepare("DELETE FROM checkout WHERE id = ?");
    $stmtDel->bind_param('i', $id);
    $stmtDel->execute();
    $stmtDel->close();

    $conn->commit();
    header("Location: data_checkout.php");
    exit;
} catch (Throwable $e) {
    $conn->rollback();
    die('Gagal menghapus checkout: ' . $e->getMessage());
}

