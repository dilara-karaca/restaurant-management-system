<?php
require_once __DIR__ . '/../../includes/cruds.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Only POST method is allowed');
}

$id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
if ($id <= 0) {
    jsonResponse(false, 'Geçersiz ürün ID');
}

$isAvailable = isset($_POST['is_available']) ? intval($_POST['is_available']) : 1;

try {
    $crud = new CRUD();
    $result = $crud->update('MenuProducts', [
        'is_available' => $isAvailable
    ], 'product_id = :id', [':id' => $id]);
    if ($result) {
        jsonResponse(true, 'Ürün durumu güncellendi');
    } else {
        jsonResponse(false, 'Ürün durumu güncellenemedi');
    }
} catch (Exception $e) {
    jsonResponse(false, 'Hata: ' . $e->getMessage());
}
