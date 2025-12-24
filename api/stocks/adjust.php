<?php
require_once __DIR__ . '/../../includes/cruds.php';
require_once __DIR__ . '/../../includes/functions.php';

$ingredientId = isset($_POST['ingredient_id']) ? intval($_POST['ingredient_id']) : 0;
$type = isset($_POST['movement_type']) ? trim($_POST['movement_type']) : '';
$quantity = isset($_POST['quantity']) ? floatval($_POST['quantity']) : 0;
$note = isset($_POST['note']) ? trim($_POST['note']) : '';

$allowedTypes = ['IN', 'OUT', 'USED'];
if ($ingredientId <= 0 || !in_array($type, $allowedTypes, true) || $quantity <= 0) {
    jsonResponse(false, 'Geçersiz stok hareketi bilgisi');
}

try {
    $crud = new CRUD();
    $stock = $crud->readOne('Stocks', 'ingredient_id = :id', [':id' => $ingredientId]);
    if (!$stock) {
        jsonResponse(false, 'Stok kaydı bulunamadı');
    }

    $currentQty = (float) $stock['quantity'];
    $delta = ($type === 'IN') ? $quantity : -$quantity;
    $newQty = $currentQty + $delta;

    if ($newQty < 0) {
        jsonResponse(false, 'Stok miktarı eksiye düşemez');
    }

    $updated = $crud->update('Stocks', ['quantity' => $newQty], 'ingredient_id = :id', [':id' => $ingredientId]);
    if (!$updated) {
        jsonResponse(false, 'Stok güncellenemedi');
    }

    $crud->create('StockMovements', [
        'ingredient_id' => $ingredientId,
        'movement_type' => $type,
        'quantity' => $quantity,
        'note' => $note
    ]);

    jsonResponse(true, 'Stok güncellendi', [
        'ingredient_id' => $ingredientId,
        'quantity' => $newQty
    ]);
} catch (Exception $e) {
    jsonResponse(false, 'Stok güncellenemedi: ' . $e->getMessage());
}
