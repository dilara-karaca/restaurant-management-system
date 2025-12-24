<?php
require_once __DIR__ . '/../../includes/cruds.php';
require_once __DIR__ . '/../../includes/functions.php';

$movementId = isset($_POST['movement_id']) ? intval($_POST['movement_id']) : 0;
$type = isset($_POST['movement_type']) ? trim($_POST['movement_type']) : '';
$quantity = isset($_POST['quantity']) ? floatval($_POST['quantity']) : 0;
$note = isset($_POST['note']) ? trim($_POST['note']) : '';

$allowedTypes = ['IN', 'OUT', 'USED'];
if ($movementId <= 0 || !in_array($type, $allowedTypes, true) || $quantity <= 0) {
    jsonResponse(false, 'Geçersiz hareket bilgisi');
}

try {
    $crud = new CRUD();
    $movement = $crud->readOne('StockMovements', 'movement_id = :id', [':id' => $movementId]);
    if (!$movement) {
        jsonResponse(false, 'Stok hareketi bulunamadı');
    }

    $ingredientId = (int) $movement['ingredient_id'];
    $oldQty = (float) $movement['quantity'];
    $oldType = $movement['movement_type'];

    $oldDelta = ($oldType === 'IN') ? $oldQty : -$oldQty;
    $newDelta = ($type === 'IN') ? $quantity : -$quantity;
    $deltaChange = $newDelta - $oldDelta;

    $stock = $crud->readOne('Stocks', 'ingredient_id = :id', [':id' => $ingredientId]);
    if (!$stock) {
        jsonResponse(false, 'Stok kaydı bulunamadı');
    }

    $currentQty = (float) $stock['quantity'];
    $newStockQty = $currentQty + $deltaChange;
    if ($newStockQty < 0) {
        jsonResponse(false, 'Stok miktarı eksiye düşemez');
    }

    $updatedMovement = $crud->update('StockMovements', [
        'movement_type' => $type,
        'quantity' => $quantity,
        'note' => $note
    ], 'movement_id = :id', [':id' => $movementId]);

    if (!$updatedMovement) {
        jsonResponse(false, 'Stok hareketi güncellenemedi');
    }

    $updatedStock = $crud->update('Stocks', ['quantity' => $newStockQty], 'ingredient_id = :id', [':id' => $ingredientId]);
    if (!$updatedStock) {
        jsonResponse(false, 'Stok güncellenemedi');
    }

    jsonResponse(true, 'Stok hareketi güncellendi', [
        'ingredient_id' => $ingredientId,
        'quantity' => $newStockQty
    ]);
} catch (Exception $e) {
    jsonResponse(false, 'Stok hareketi güncellenemedi: ' . $e->getMessage());
}
