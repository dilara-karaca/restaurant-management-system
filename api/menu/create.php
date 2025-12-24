<?php
require_once __DIR__ . '/../../includes/cruds.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Only POST method is allowed');
}

$categoryId = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
$name = isset($_POST['product_name']) ? cleanInput($_POST['product_name']) : '';
$desc = isset($_POST['description']) ? cleanInput($_POST['description']) : '';
$price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
$isAvailable = isset($_POST['is_available']) ? intval($_POST['is_available']) : 1;
$imageUrl = isset($_POST['image_url']) ? cleanInput($_POST['image_url']) : null;

if ($categoryId <= 0 || $name === '' || $price <= 0) {
    jsonResponse(false, 'Kategori, ürün adı ve fiyat zorunludur');
}

try {
    $crud = new CRUD();
    $result = $crud->create('MenuProducts', [
        'category_id' => $categoryId,
        'product_name' => $name,
        'description' => $desc,
        'price' => $price,
        'is_available' => $isAvailable,
        'image_url' => $imageUrl
    ]);
    if ($result) {
        jsonResponse(true, 'Ürün eklendi', ['product_id' => $result]);
    } else {
        jsonResponse(false, 'Ürün eklenemedi');
    }
} catch (Exception $e) {
    jsonResponse(false, 'Hata: ' . $e->getMessage());
}
