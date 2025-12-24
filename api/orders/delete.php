<?php
require_once __DIR__ . '/../../includes/cruds.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Only POST method is allowed');
}

$orderId = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
if ($orderId <= 0) {
    jsonResponse(false, 'Geçersiz sipariş ID');
}

try {
    $crud = new CRUD();
    $deleted = $crud->delete('Orders', 'order_id = :id', [':id' => $orderId]);
    if ($deleted) {
        jsonResponse(true, 'Sipariş silindi');
    }
    jsonResponse(false, 'Sipariş silinemedi');
} catch (Exception $e) {
    jsonResponse(false, 'Hata: ' . $e->getMessage());
}
