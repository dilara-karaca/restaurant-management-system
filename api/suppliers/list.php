<?php
require_once __DIR__ . '/../../includes/cruds.php';
require_once __DIR__ . '/../../includes/functions.php';

try {
    $crud = new CRUD();
    // SP-12: Tedarikçi listesi (tek tablo)
    $suppliers = $crud->customQuery("CALL sp_list_suppliers()");
    jsonResponse(true, 'Tedarikçi listesi', $suppliers);
} catch (Exception $e) {
    jsonResponse(false, 'Tedarikçi listesi alınamadı: ' . $e->getMessage());
}
