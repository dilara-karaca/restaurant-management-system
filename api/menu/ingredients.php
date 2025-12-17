<?php
require_once __DIR__ . '/../../includes/cruds.php';
require_once __DIR__ . '/../../includes/functions.php';

$productId = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
if ($productId <= 0) {
    jsonResponse(false, 'Geçersiz ürün ID');
}

try {
    $crud = new CRUD();
    $ingredients = $crud->customQuery(
        'SELECT pi.ingredient_id, i.ingredient_name, pi.quantity_required
         FROM ProductIngredients pi
         JOIN Ingredients i ON pi.ingredient_id = i.ingredient_id
         WHERE pi.product_id = :pid',
        [':pid' => $productId]
    );
    jsonResponse(true, 'Ürün reçetesi', $ingredients);
} catch (Exception $e) {
    jsonResponse(false, 'Hata: ' . $e->getMessage());
}
