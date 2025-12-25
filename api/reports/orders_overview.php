<?php
require_once __DIR__ . '/../../includes/cruds.php';
require_once __DIR__ . '/../../includes/functions.php';

$status = isset($_GET['status']) ? cleanInput($_GET['status']) : '';
$allowedStatuses = ['Pending', 'Preparing', 'Served', 'Completed', 'Cancelled'];
if ($status !== '' && !in_array($status, $allowedStatuses, true)) {
    jsonResponse(false, 'Geçersiz sipariş durumu');
}

try {
    $crud = new CRUD();
    // SP-1: Sipariş genel raporu (3+ tablo JOIN)
    $rows = $crud->customQuery(
        "CALL sp_report_orders_overview(:status)",
        [':status' => $status !== '' ? $status : null]
    );
    jsonResponse(true, 'Sipariş genel raporu', $rows);
} catch (Exception $e) {
    jsonResponse(false, 'Rapor alınamadı: ' . $e->getMessage());
}
