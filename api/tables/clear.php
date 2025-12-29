<?php
require_once __DIR__ . '/../../includes/cruds.php';
require_once __DIR__ . '/../../includes/functions.php';

startSession();

if (
    !isset($_SESSION['logged_in'])
    || $_SESSION['logged_in'] !== true
    || ($_SESSION['role_name'] ?? '') !== 'Admin'
) {
    jsonResponse(false, 'Yetkisiz erişim');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Only POST method is allowed');
}

$tableId = isset($_POST['table_id']) ? intval($_POST['table_id']) : 0;

if ($tableId <= 0) {
    jsonResponse(false, 'Geçersiz masa ID');
}

try {
    $crud = new CRUD();
    $crud->beginTransaction();
    
    // Masayı kontrol et
    $table = $crud->readOne('Tables', 'table_id = :id', [':id' => $tableId]);
    if (!$table) {
        $crud->rollback();
        jsonResponse(false, 'Masa bulunamadı');
    }
    
    // Masanın durumunu "Available" yap
    $crud->update('Tables', [
        'status' => 'Available'
    ], 'table_id = :id', [':id' => $tableId]);
    
    // Eğer masada aktif bir sipariş varsa, onu da Completed yap
    $activeOrders = $crud->customQuery(
        "SELECT order_id FROM Orders 
         WHERE table_id = :table_id 
         AND status IN ('Pending', 'Preparing', 'Served')
         LIMIT 1",
        [':table_id' => $tableId]
    );
    
    if ($activeOrders && count($activeOrders) > 0) {
        $activeOrder = $activeOrders[0];
        $crud->update('Orders', [
            'status' => 'Completed'
        ], 'order_id = :id', [':id' => $activeOrder['order_id']]);
    }
    
    $crud->commit();
    
    jsonResponse(true, 'Masa başarıyla boşaltıldı', [
        'table_id' => $tableId
    ]);
    
} catch (Exception $e) {
    if (isset($crud)) {
        $crud->rollback();
    }
    jsonResponse(false, 'Hata: ' . $e->getMessage());
}
?>

