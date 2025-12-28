<?php
require_once __DIR__ . '/../../includes/cruds.php';
require_once __DIR__ . '/../../includes/functions.php';

startSession();

$isAdmin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
$isPersonnel = isset($_SESSION['personnel_logged_in']) && $_SESSION['personnel_logged_in'] === true;

if (!$isAdmin && !$isPersonnel) {
    jsonResponse(false, 'Yetkisiz erişim');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Only POST method is allowed');
}

$orderId = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
$tableId = isset($_POST['table_id']) ? intval($_POST['table_id']) : 0;

if ($orderId <= 0 || $tableId <= 0) {
    jsonResponse(false, 'Geçersiz sipariş veya masa bilgisi');
}

try {
    $crud = new CRUD();

    $order = $crud->readOne('Orders', 'order_id = :id', [':id' => $orderId]);
    if (!$order) {
        jsonResponse(false, 'Sipariş bulunamadı');
    }

    if ($isPersonnel) {
        $personnelId = isset($_SESSION['personnel_id']) ? (int) $_SESSION['personnel_id'] : 0;
        if ($personnelId <= 0 || (int) $order['served_by'] !== $personnelId) {
            jsonResponse(false, 'Sipariş için yetkiniz yok');
        }
    }

    if (in_array($order['status'], ['Completed', 'Cancelled'], true)) {
        jsonResponse(false, 'Tamamlanan siparişin masası değiştirilemez');
    }

    $targetTable = $crud->readOne('Tables', 'table_id = :id', [':id' => $tableId]);
    if (!$targetTable) {
        jsonResponse(false, 'Masa bulunamadı');
    }

    $currentTableId = (int) $order['table_id'];
    if ($currentTableId === $tableId) {
        jsonResponse(true, 'Masa zaten seçili');
    }

    $activeOnTarget = $crud->customQuery(
        "SELECT order_id FROM Orders
         WHERE table_id = :table_id
         AND status IN ('Pending', 'Preparing', 'Served')
         AND order_id <> :order_id
         LIMIT 1",
        [
            ':table_id' => $tableId,
            ':order_id' => $orderId
        ]
    );
    if ($activeOnTarget && count($activeOnTarget) > 0) {
        jsonResponse(false, 'Seçilen masa başka bir aktif siparişe ait');
    }

    $crud->beginTransaction();
    $crud->update('Orders', ['table_id' => $tableId], 'order_id = :id', [':id' => $orderId]);

    $remainingOnOld = $crud->customQuery(
        "SELECT order_id FROM Orders
         WHERE table_id = :table_id
         AND status IN ('Pending', 'Preparing', 'Served')
         AND order_id <> :order_id
         LIMIT 1",
        [
            ':table_id' => $currentTableId,
            ':order_id' => $orderId
        ]
    );
    if (!$remainingOnOld || count($remainingOnOld) === 0) {
        $crud->update('Tables', ['status' => 'Available'], 'table_id = :id', [':id' => $currentTableId]);
    }

    $crud->update('Tables', ['status' => 'Occupied'], 'table_id = :id', [':id' => $tableId]);
    $crud->commit();

    jsonResponse(true, 'Masa güncellendi');
} catch (Exception $e) {
    if (isset($crud)) {
        $crud->rollback();
    }
    jsonResponse(false, 'Hata: ' . $e->getMessage());
}
