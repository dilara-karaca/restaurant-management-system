<?php
require_once __DIR__ . '/../../includes/cruds.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Only POST method is allowed');
}

$orderId = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
$paymentMethod = isset($_POST['payment_method']) ? cleanInput($_POST['payment_method']) : '';

$allowedPayments = ['Cash', 'Credit Card', 'Debit Card', 'Mobile Payment'];
if ($paymentMethod === '' || !in_array($paymentMethod, $allowedPayments, true)) {
    jsonResponse(false, 'Geçerli bir ödeme yöntemi seçiniz');
}

if ($orderId <= 0) {
    jsonResponse(false, 'Geçersiz sipariş ID');
}

try {
    $crud = new CRUD();
    $crud->beginTransaction();
    
    // Siparişi kontrol et
    $order = $crud->readOne('Orders', 'order_id = :id', [':id' => $orderId]);
    if (!$order) {
        $crud->rollback();
        jsonResponse(false, 'Sipariş bulunamadı');
    }
    
    // Ödeme yöntemini ve durumu güncelle
    $crud->update('Orders', [
        'payment_method' => $paymentMethod,
        'status' => 'Completed'
    ], 'order_id = :id', [':id' => $orderId]);
    
    // Masanın durumunu "Available" yap
    $crud->update('Tables', [
        'status' => 'Available'
    ], 'table_id = :id', [':id' => $order['table_id']]);
    
    $crud->commit();
    
    jsonResponse(true, 'Ödeme başarıyla alındı ve masa boşaltıldı', [
        'order_id' => $orderId,
        'table_id' => $order['table_id']
    ]);
    
} catch (Exception $e) {
    if (isset($crud)) {
        $crud->rollback();
    }
    jsonResponse(false, 'Hata: ' . $e->getMessage());
}
?>

