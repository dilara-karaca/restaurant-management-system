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
    $order = $crud->readOne('Orders', 'order_id = :id', [':id' => $orderId]);
    if (!$order) {
        jsonResponse(false, 'Sipariş bulunamadı');
    }

    if (in_array($order['status'], ['Completed', 'Cancelled'], true)) {
        jsonResponse(false, 'Bu sipariş için ödeme güncellenemez');
    }

    if (!empty($order['payment_method'])) {
        jsonResponse(true, 'Ödeme zaten alınmış', [
            'order_id' => $orderId,
            'payment_method' => $order['payment_method']
        ]);
    }

    $result = $crud->update('Orders', [
        'payment_method' => 'Mobile Payment'
    ], 'order_id = :id', [':id' => $orderId]);

    if (!$result) {
        jsonResponse(false, 'Ödeme durumu güncellenemedi');
    }

    jsonResponse(true, 'Ödeme alındı', [
        'order_id' => $orderId,
        'payment_method' => 'Mobile Payment'
    ]);
} catch (Exception $e) {
    jsonResponse(false, 'Hata: ' . $e->getMessage());
}
?>
