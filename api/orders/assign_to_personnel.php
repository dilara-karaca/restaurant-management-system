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
if ($orderId <= 0) {
    jsonResponse(false, 'Geçersiz sipariş ID');
}

try {
    $crud = new CRUD();
    $order = $crud->readOne('Orders', 'order_id = :id', [':id' => $orderId]);
    if (!$order) {
        jsonResponse(false, 'Sipariş bulunamadı');
    }

    if (!empty($order['served_by'])) {
        jsonResponse(false, 'Bu sipariş zaten atanmış');
    }

    $result = $crud->update('Orders', ['served_by' => $personnelId], 'order_id = :id', [':id' => $orderId]);
    if ($result) {
        jsonResponse(true, 'Sipariş üzerinize alındı');
    }
    jsonResponse(false, 'Sipariş atanamadı');
} catch (Exception $e) {
    jsonResponse(false, 'Hata: ' . $e->getMessage());
}
