<?php
require_once __DIR__ . '/../../includes/cruds.php';
require_once __DIR__ . '/../../includes/functions.php';

$categoryId = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
$where = '';
$params = [];
if ($categoryId > 0) {
    $where = 'category_id = :catid';
    $params[':catid'] = $categoryId;
}

try {
    $crud = new CRUD();
    $products = $crud->read('MenuProducts', $where, $params, 'product_name ASC');
    jsonResponse(true, 'Menü ürünleri listesi', $products);
} catch (Exception $e) {
    jsonResponse(false, 'Menü ürünleri listelenemedi: ' . $e->getMessage());
}
