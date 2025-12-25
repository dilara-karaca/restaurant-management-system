<?php
require_once __DIR__ . '/../../includes/cruds.php';
require_once __DIR__ . '/../../includes/functions.php';

// Tüm kategorileri listele
try {
    $crud = new CRUD();
    // SP-8: Kategori listesi (tek tablo)
    $categories = $crud->customQuery("CALL sp_list_categories()");
    jsonResponse(true, 'Kategori listesi', $categories);
} catch (Exception $e) {
    jsonResponse(false, 'Kategori listelenemedi: ' . $e->getMessage());
}
