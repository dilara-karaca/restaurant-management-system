<?php
require_once __DIR__ . '/../../includes/cruds.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Only POST method is allowed');
}

$orderId = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
$productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
$specialInstructions = isset($_POST['special_instructions']) ? cleanInput($_POST['special_instructions']) : null;

if ($orderId <= 0 || $productId <= 0 || $quantity <= 0) {
    jsonResponse(false, 'Geçersiz sipariş, ürün veya miktar');
}

try {
    $crud = new CRUD();
    $product = $crud->readOne('MenuProducts', 'product_id = :id', [':id' => $productId]);
    if (!$product) {
        jsonResponse(false, 'Ürün bulunamadı');
    }

    $unitPrice = (float) $product['price'];
    $subtotal = $unitPrice * $quantity;

    $crud->beginTransaction();

    $detailId = $crud->create('OrderDetails', [
        'order_id' => $orderId,
        'product_id' => $productId,
        'quantity' => $quantity,
        'unit_price' => $unitPrice,
        'subtotal' => $subtotal,
        'special_instructions' => $specialInstructions
    ]);

    if (!$detailId) {
        $crud->rollback();
        jsonResponse(false, 'Ürün siparişe eklenemedi');
    }

    $totalRow = $crud->customQuery(
        'SELECT COALESCE(SUM(subtotal), 0) AS total FROM OrderDetails WHERE order_id = :id',
        [':id' => $orderId]
    );
    $totalAmount = $totalRow[0]['total'] ?? 0;

    $crud->update('Orders', ['total_amount' => $totalAmount], 'order_id = :id', [':id' => $orderId]);
    $crud->commit();

    jsonResponse(true, 'Ürün siparişe eklendi', ['order_total' => $totalAmount]);
} catch (Exception $e) {
    jsonResponse(false, 'Hata: ' . $e->getMessage());
}
