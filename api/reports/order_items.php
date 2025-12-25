<?php
require_once __DIR__ . '/../../includes/cruds.php';
require_once __DIR__ . '/../../includes/functions.php';

$orderId = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

try {
    $crud = new CRUD();
    // SP-2: Sipariş kalem raporu (3+ tablo JOIN)
    $rows = $crud->customQuery(
        "CALL sp_report_order_items(:order_id)",
        [':order_id' => $orderId > 0 ? $orderId : null]
    );
    jsonResponse(true, 'Sipariş kalem raporu', $rows);
} catch (Exception $e) {
    jsonResponse(false, 'Rapor alınamadı: ' . $e->getMessage());
}
