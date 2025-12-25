<?php
require_once __DIR__ . '/../../includes/cruds.php';
require_once __DIR__ . '/../../includes/functions.php';

$personnelId = isset($_GET['personnel_id']) ? intval($_GET['personnel_id']) : 0;

try {
    $crud = new CRUD();
    // SP-7: Personel performans raporu (3+ tablo JOIN)
    $rows = $crud->customQuery(
        "CALL sp_report_personnel_performance(:personnel_id)",
        [':personnel_id' => $personnelId > 0 ? $personnelId : null]
    );
    jsonResponse(true, 'Personel performans raporu', $rows);
} catch (Exception $e) {
    jsonResponse(false, 'Rapor alınamadı: ' . $e->getMessage());
}
