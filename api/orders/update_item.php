<?php
require_once __DIR__ . '/../../includes/cruds.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Only POST method is allowed');
}

$detailId = isset($_POST['order_detail_id']) ? intval($_POST['order_detail_id']) : 0;
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
$productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$specialInstructions = isset($_POST['special_instructions']) ? cleanInput($_POST['special_instructions']) : null;

if ($detailId <= 0 || $quantity <= 0) {
    jsonResponse(false, 'Geçersiz sipariş kalemi veya miktar');
}

try {
    $crud = new CRUD();
    $detail = $crud->readOne('OrderDetails', 'order_detail_id = :id', [':id' => $detailId]);
    if (!$detail) {
        jsonResponse(false, 'Sipariş kalemi bulunamadı');
    }

    $orderId = (int) $detail['order_id'];
    $finalProductId = $productId > 0 ? $productId : (int) $detail['product_id'];
    $unitPrice = (float) $detail['unit_price'];

    if ($productId > 0 && $productId !== (int) $detail['product_id']) {
        $product = $crud->readOne('MenuProducts', 'product_id = :id', [':id' => $productId]);
        if (!$product) {
            jsonResponse(false, 'Ürün bulunamadı');
        }
        $unitPrice = (float) $product['price'];
    }

    $subtotal = $unitPrice * $quantity;

    $crud->beginTransaction();
    $crud->update('OrderDetails', [
        'product_id' => $finalProductId,
        'quantity' => $quantity,
        'unit_price' => $unitPrice,
        'subtotal' => $subtotal,
        'special_instructions' => $specialInstructions
    ], 'order_detail_id = :id', [':id' => $detailId]);

    $totalRow = $crud->customQuery(
        'SELECT COALESCE(SUM(subtotal), 0) AS total FROM OrderDetails WHERE order_id = :id',
        [':id' => $orderId]
    );
    $totalAmount = $totalRow[0]['total'] ?? 0;

    $crud->update('Orders', ['total_amount' => $totalAmount], 'order_id = :id', [':id' => $orderId]);
    $crud->commit();

    jsonResponse(true, 'Sipariş kalemi güncellendi', ['order_total' => $totalAmount]);
} catch (Exception $e) {
    jsonResponse(false, 'Hata: ' . $e->getMessage());
}
