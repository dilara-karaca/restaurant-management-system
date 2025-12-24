<?php
require_once __DIR__ . '/../../includes/cruds.php';
require_once __DIR__ . '/../../includes/functions.php';

try {
    $crud = new CRUD();
    $suppliers = $crud->read('Suppliers', '', [], 'supplier_name ASC');
    jsonResponse(true, 'Tedarikçi listesi', $suppliers);
} catch (Exception $e) {
    jsonResponse(false, 'Tedarikçi listesi alınamadı: ' . $e->getMessage());
}
