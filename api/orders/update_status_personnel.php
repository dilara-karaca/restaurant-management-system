<?php
require_once __DIR__ . '/../../includes/cruds.php';
require_once __DIR__ . '/../../includes/functions.php';

startSession();

if (!isset($_SESSION['personnel_logged_in']) || $_SESSION['personnel_logged_in'] !== true) {
    jsonResponse(false, 'Personel girişi gerekli');
}

$personnelId = isset($_SESSION['personnel_id']) ? (int) $_SESSION['personnel_id'] : 0;
if ($personnelId <= 0) {
    jsonResponse(false, 'Personel bilgisi bulunamadı');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Only POST method is allowed');
}

$orderId = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
$status = isset($_POST['status']) ? cleanInput($_POST['status']) : '';
$paymentMethod = isset($_POST['payment_method']) ? cleanInput($_POST['payment_method']) : '';
$allowedStatuses = ['Pending', 'Preparing', 'Served', 'Completed', 'Cancelled'];
if ($paymentMethod !== '') {
    $allowedPayments = ['Cash', 'Credit Card', 'Debit Card', 'Mobile Payment'];
    if (!in_array($paymentMethod, $allowedPayments, true)) {
        jsonResponse(false, 'Geçersiz ödeme yöntemi');
    }
}

if ($orderId <= 0 || $status === '' || !in_array($status, $allowedStatuses, true)) {
    jsonResponse(false, 'Geçersiz sipariş ID veya durum');
}

try {
    $crud = new CRUD();
    $order = $crud->readOne('Orders', 'order_id = :id AND served_by = :personnel_id', [
        ':id' => $orderId,
        ':personnel_id' => $personnelId
    ]);

    if (!$order) {
        jsonResponse(false, 'Sipariş bulunamadı veya yetkiniz yok');
    }

    $updateData = ['status' => $status];
    if ($paymentMethod !== '') {
        $updateData['payment_method'] = $paymentMethod;
    }

    $result = $crud->update('Orders', $updateData, 'order_id = :id', [':id' => $orderId]);
    if ($result) {
        jsonResponse(true, 'Sipariş durumu güncellendi');
    }
    jsonResponse(false, 'Sipariş durumu güncellenemedi');
} catch (Exception $e) {
    jsonResponse(false, 'Hata: ' . $e->getMessage());
}
