<?php
require_once __DIR__ . '/../../includes/cruds.php';
require_once __DIR__ . '/../../includes/functions.php';

$movementId = isset($_POST['movement_id']) ? intval($_POST['movement_id']) : 0;
if ($movementId <= 0) {
    jsonResponse(false, 'Geçersiz hareket ID');
}

try {
    $crud = new CRUD();
    $movement = $crud->readOne('StockMovements', 'movement_id = :id', [':id' => $movementId]);
    if (!$movement) {
        jsonResponse(false, 'Stok hareketi bulunamadı');
    }

    $ingredientId = (int) $movement['ingredient_id'];
    $qty = (float) $movement['quantity'];
    $type = $movement['movement_type'];
    $delta = ($type === 'IN') ? $qty : -$qty;

    $stock = $crud->readOne('Stocks', 'ingredient_id = :id', [':id' => $ingredientId]);
    if (!$stock) {
        jsonResponse(false, 'Stok kaydı bulunamadı');
    }

    $currentQty = (float) $stock['quantity'];
    $newQty = $currentQty - $delta;
    if ($newQty < 0) {
        jsonResponse(false, 'Stok miktarı eksiye düşemez');
    }

    $deleted = $crud->delete('StockMovements', 'movement_id = :id', [':id' => $movementId]);
    if (!$deleted) {
        jsonResponse(false, 'Stok hareketi silinemedi');
    }

    $updated = $crud->update('Stocks', ['quantity' => $newQty], 'ingredient_id = :id', [':id' => $ingredientId]);
    if (!$updated) {
        jsonResponse(false, 'Stok güncellenemedi');
    }

    jsonResponse(true, 'Stok hareketi silindi', [
        'ingredient_id' => $ingredientId,
        'quantity' => $newQty
    ]);
} catch (Exception $e) {
    jsonResponse(false, 'Stok hareketi silinemedi: ' . $e->getMessage());
}
