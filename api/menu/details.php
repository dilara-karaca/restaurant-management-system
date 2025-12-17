<?php
require_once __DIR__ . '/../../includes/cruds.php';
require_once __DIR__ . '/../../includes/functions.php';

$id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
if ($id <= 0) {
    jsonResponse(false, 'Geçersiz ürün ID');
}

try {
    $crud = new CRUD();
    $product = $crud->readOne('MenuProducts', 'product_id = :id', [':id' => $id]);
    if ($product) {
        jsonResponse(true, 'Ürün detayları', $product);
    } else {
        jsonResponse(false, 'Ürün bulunamadı');
    }
} catch (Exception $e) {
    jsonResponse(false, 'Hata: ' . $e->getMessage());
}
