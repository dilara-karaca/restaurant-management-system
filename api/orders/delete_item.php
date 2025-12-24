<?php
require_once __DIR__ . '/../../includes/cruds.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Only POST method is allowed');
}

$detailId = isset($_POST['order_detail_id']) ? intval($_POST['order_detail_id']) : 0;
if ($detailId <= 0) {
    jsonResponse(false, 'Geçersiz sipariş kalemi');
}

try {
    $crud = new CRUD();
    $detail = $crud->readOne('OrderDetails', 'order_detail_id = :id', [':id' => $detailId]);
    if (!$detail) {
        jsonResponse(false, 'Sipariş kalemi bulunamadı');
    }

    $orderId = (int) $detail['order_id'];

    $crud->beginTransaction();
    $crud->delete('OrderDetails', 'order_detail_id = :id', [':id' => $detailId]);

    $totalRow = $crud->customQuery(
        'SELECT COALESCE(SUM(subtotal), 0) AS total FROM OrderDetails WHERE order_id = :id',
        [':id' => $orderId]
    );
    $totalAmount = $totalRow[0]['total'] ?? 0;

    $crud->update('Orders', ['total_amount' => $totalAmount], 'order_id = :id', [':id' => $orderId]);
    $crud->commit();

    jsonResponse(true, 'Sipariş kalemi silindi', ['order_total' => $totalAmount]);
} catch (Exception $e) {
    jsonResponse(false, 'Hata: ' . $e->getMessage());
}
