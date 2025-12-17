<?php
require_once __DIR__ . '/../../includes/cruds.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Only POST method is allowed');
}

$productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$ingredientId = isset($_POST['ingredient_id']) ? intval($_POST['ingredient_id']) : 0;

if ($productId <= 0 || $ingredientId <= 0) {
    jsonResponse(false, 'Geçersiz veri');
}

try {
    $crud = new CRUD();
    $result = $crud->delete('ProductIngredients', 'product_id = :pid AND ingredient_id = :iid', [':pid' => $productId, ':iid' => $ingredientId]);
    if ($result) {
        jsonResponse(true, 'Malzeme çıkarıldı');
    } else {
        jsonResponse(false, 'Malzeme çıkarılamadı');
    }
} catch (Exception $e) {
    jsonResponse(false, 'Hata: ' . $e->getMessage());
}
