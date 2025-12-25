<?php
require_once __DIR__ . '/../../includes/cruds.php';
require_once __DIR__ . '/../../includes/functions.php';

$customerId = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : 0;

try {
    $crud = new CRUD();
    // SP-6: Müşteri sipariş geçmişi (3+ tablo JOIN)
    $rows = $crud->customQuery(
        "CALL sp_report_customer_history(:customer_id)",
        [':customer_id' => $customerId > 0 ? $customerId : null]
    );
    jsonResponse(true, 'Müşteri sipariş geçmişi', $rows);
} catch (Exception $e) {
    jsonResponse(false, 'Rapor alınamadı: ' . $e->getMessage());
}
