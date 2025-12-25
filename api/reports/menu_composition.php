<?php
require_once __DIR__ . '/../../includes/cruds.php';
require_once __DIR__ . '/../../includes/functions.php';

$categoryId = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;

try {
    $crud = new CRUD();
    // SP-5: Menü reçete raporu (3+ tablo JOIN)
    $rows = $crud->customQuery(
        "CALL sp_report_menu_composition(:category_id)",
        [':category_id' => $categoryId > 0 ? $categoryId : null]
    );
    jsonResponse(true, 'Menü reçete raporu', $rows);
} catch (Exception $e) {
    jsonResponse(false, 'Rapor alınamadı: ' . $e->getMessage());
}
