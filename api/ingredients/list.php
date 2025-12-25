<?php
require_once __DIR__ . '/../../includes/cruds.php';
require_once __DIR__ . '/../../includes/functions.php';

try {
    $crud = new CRUD();
    // SP-11: Malzeme listesi (Ingredients + Suppliers)
    $ingredients = $crud->customQuery("CALL sp_list_ingredients()");

    jsonResponse(true, 'Malzeme listesi', $ingredients);
} catch (Exception $e) {
    jsonResponse(false, 'Malzeme listesi alınamadı: ' . $e->getMessage());
}
