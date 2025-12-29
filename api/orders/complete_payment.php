<?php
require_once __DIR__ . '/../../includes/cruds.php';
require_once __DIR__ . '/../../includes/functions.php';

startSession();

if (
    (!isset($_SESSION['personnel_logged_in']) || $_SESSION['personnel_logged_in'] !== true)
    && (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || ($_SESSION['role_name'] ?? '') !== 'Admin')
) {
    jsonResponse(false, 'Yetkisiz erişim');
}

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
    $whereSql = 'order_id = :id';
    $params = [':id' => $orderId];
    if (isset($_SESSION['personnel_logged_in']) && $_SESSION['personnel_logged_in'] === true) {
        $personnelId = isset($_SESSION['personnel_id']) ? (int) $_SESSION['personnel_id'] : 0;
        if ($personnelId <= 0) {
            $crud->rollback();
            jsonResponse(false, 'Personel bilgisi bulunamadı');
        }
        $whereSql .= ' AND served_by = :personnel_id';
        $params[':personnel_id'] = $personnelId;
    }
    $order = $crud->readOne('Orders', $whereSql, $params);
    if (!$order) {
        $crud->rollback();
        jsonResponse(false, 'Sipariş bulunamadı');
    }

    if (in_array($order['status'], ['Completed', 'Cancelled'], true)) {
        $crud->rollback();
        jsonResponse(false, 'Bu sipariş için ödeme tamamlanamaz');
    }
    
    // Ödeme yöntemini ve durumu güncelle
    $maxDetailRow = $crud->customQuery(
        'SELECT MAX(order_detail_id) AS max_id FROM OrderDetails WHERE order_id = :id',
        [':id' => $orderId]
    );
    $crud->update('Orders', [
        'payment_method' => $paymentMethod,
        'status' => 'Completed',
        'paid_amount' => $order['total_amount'],
        'paid_detail_max_id' => $maxDetailRow[0]['max_id'] ?? null
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
