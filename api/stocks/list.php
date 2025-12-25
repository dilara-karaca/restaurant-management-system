<?php
require_once __DIR__ . '/../../includes/cruds.php';
require_once __DIR__ . '/../../includes/functions.php';

try {
    $crud = new CRUD();
    // SP-13: Stok listesi (Stocks + Ingredients + Suppliers)
    $stocks = $crud->customQuery("CALL sp_list_stocks()");

    jsonResponse(true, 'Stok listesi', $stocks);
} catch (Exception $e) {
    jsonResponse(false, 'Stok listesi alınamadı: ' . $e->getMessage());
}
