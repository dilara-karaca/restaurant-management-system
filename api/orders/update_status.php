<?php
require_once __DIR__ . '/../../includes/cruds.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Only POST method is allowed');
}

$orderId = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
$status = isset($_POST['status']) ? cleanInput($_POST['status']) : '';
$allowedStatuses = ['Pending', 'Preparing', 'Served', 'Completed', 'Cancelled'];

if ($orderId <= 0 || $status === '' || !in_array($status, $allowedStatuses, true)) {
    jsonResponse(false, 'Geçersiz sipariş ID veya durum');
}

try {
    $crud = new CRUD();
    $result = $crud->update('Orders', ['status' => $status], 'order_id = :id', [':id' => $orderId]);
    if ($result) {
        jsonResponse(true, 'Sipariş durumu güncellendi');
    }
    jsonResponse(false, 'Sipariş durumu güncellenemedi');
} catch (Exception $e) {
    jsonResponse(false, 'Hata: ' . $e->getMessage());
}
