<?php
require_once __DIR__ . '/../../includes/cruds.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Only POST method is allowed');
}

$productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$ingredientId = isset($_POST['ingredient_id']) ? intval($_POST['ingredient_id']) : 0;
$quantity = isset($_POST['quantity_required']) ? floatval($_POST['quantity_required']) : 0;

if ($productId <= 0 || $ingredientId <= 0 || $quantity <= 0) {
    jsonResponse(false, 'Geçersiz veri');
}

try {
    $crud = new CRUD();
    $result = $crud->create('ProductIngredients', [
        'product_id' => $productId,
        'ingredient_id' => $ingredientId,
        'quantity_required' => $quantity
    ]);
    if ($result) {
        jsonResponse(true, 'Malzeme eklendi');
    } else {
        jsonResponse(false, 'Malzeme eklenemedi');
    }
} catch (Exception $e) {
    jsonResponse(false, 'Hata: ' . $e->getMessage());
}
