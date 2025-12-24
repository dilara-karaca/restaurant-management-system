<?php
require_once __DIR__ . '/../../includes/cruds.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Only POST method is allowed');
}

$name = isset($_POST['ingredient_name']) ? cleanInput($_POST['ingredient_name']) : '';
$supplierId = isset($_POST['supplier_id']) ? intval($_POST['supplier_id']) : 0;
$unit = isset($_POST['unit']) ? cleanInput($_POST['unit']) : '';
$unitPrice = isset($_POST['unit_price']) && $_POST['unit_price'] !== '' ? floatval($_POST['unit_price']) : null;
$quantity = isset($_POST['quantity']) ? floatval($_POST['quantity']) : 0;
$minimum = isset($_POST['minimum_quantity']) ? floatval($_POST['minimum_quantity']) : 0;

if ($name === '' || $supplierId <= 0 || $unit === '' || $quantity < 0 || $minimum < 0) {
    jsonResponse(false, 'Geçersiz malzeme bilgisi');
}

try {
    $crud = new CRUD();
    $ingredientId = $crud->create('Ingredients', [
        'supplier_id' => $supplierId,
        'ingredient_name' => $name,
        'unit' => $unit,
        'unit_price' => $unitPrice
    ]);

    if (!$ingredientId) {
        jsonResponse(false, 'Malzeme eklenemedi');
    }

    $stockCreated = $crud->create('Stocks', [
        'ingredient_id' => $ingredientId,
        'quantity' => $quantity,
        'minimum_quantity' => $minimum
    ]);

    if (!$stockCreated) {
        $crud->delete('Ingredients', 'ingredient_id = :id', [':id' => $ingredientId]);
        jsonResponse(false, 'Stok kaydı oluşturulamadı');
    }

    jsonResponse(true, 'Malzeme eklendi', ['ingredient_id' => $ingredientId]);
} catch (Exception $e) {
    jsonResponse(false, 'Malzeme eklenemedi: ' . $e->getMessage());
}
