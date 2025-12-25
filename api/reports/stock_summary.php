<?php
require_once __DIR__ . '/../../includes/cruds.php';
require_once __DIR__ . '/../../includes/functions.php';

try {
    $crud = new CRUD();
    // SP-3: Stok özeti raporu (3+ tablo JOIN)
    $rows = $crud->customQuery("CALL sp_report_stock_summary()");
    jsonResponse(true, 'Stok özeti raporu', $rows);
} catch (Exception $e) {
    jsonResponse(false, 'Rapor alınamadı: ' . $e->getMessage());
}
