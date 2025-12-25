<?php
require_once __DIR__ . '/../../includes/cruds.php';
require_once __DIR__ . '/../../includes/functions.php';

$productId = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
if ($productId <= 0) {
    jsonResponse(false, 'Geçersiz ürün ID');
}

try {
    $crud = new CRUD();
    // SP-10: Menü ürün malzemeleri (ProductIngredients + Ingredients)
    $ingredients = $crud->customQuery(
        "CALL sp_list_menu_ingredients(:product_id)",
        [':product_id' => $productId]
    );
    jsonResponse(true, 'Ürün reçetesi', $ingredients);
} catch (Exception $e) {
    jsonResponse(false, 'Hata: ' . $e->getMessage());
}
