<?php
require_once __DIR__ . '/../../includes/cruds.php';
require_once __DIR__ . '/../../includes/functions.php';

// Tüm kategorileri listele
try {
    $crud = new CRUD();
    $categories = $crud->read('MenuCategories', '', [], 'display_order ASC, category_name ASC');
    jsonResponse(true, 'Kategori listesi', $categories);
} catch (Exception $e) {
    jsonResponse(false, 'Kategori listelenemedi: ' . $e->getMessage());
}
