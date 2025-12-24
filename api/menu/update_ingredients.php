<?php
require_once __DIR__ . '/../../includes/cruds.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Only POST method is allowed');
}

$productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$ingredientsJson = isset($_POST['ingredients']) ? $_POST['ingredients'] : '[]';
$ingredients = json_decode($ingredientsJson, true);

if ($productId <= 0 || !is_array($ingredients)) {
    jsonResponse(false, 'Geçersiz ürün veya malzeme listesi');
}

try {
    $crud = new CRUD();
    $crud->delete('ProductIngredients', 'product_id = :pid', [':pid' => $productId]);

    foreach ($ingredients as $item) {
        $ingredientId = isset($item['ingredient_id']) ? intval($item['ingredient_id']) : 0;
        $quantity = isset($item['quantity_required']) ? floatval($item['quantity_required']) : 0;
        if ($ingredientId <= 0 || $quantity <= 0) {
            continue;
        }
        $crud->create('ProductIngredients', [
            'product_id' => $productId,
            'ingredient_id' => $ingredientId,
            'quantity_required' => $quantity
        ]);
    }

    jsonResponse(true, 'Ürün reçetesi güncellendi');
} catch (Exception $e) {
    jsonResponse(false, 'Ürün reçetesi güncellenemedi: ' . $e->getMessage());
}
