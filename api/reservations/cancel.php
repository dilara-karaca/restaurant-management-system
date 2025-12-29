<?php
require_once __DIR__ . '/../../includes/cruds.php';
require_once __DIR__ . '/../../includes/functions.php';

startSession();

// Admin kontrolü: hem admin_logged_in hem de role_name kontrolü
$isAdmin = (
    (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) ||
    (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && ($_SESSION['role_name'] ?? '') === 'Admin')
);

if (!$isAdmin) {
    jsonResponse(false, 'Yetkisiz erişim');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Only POST method is allowed');
}

$reservationId = isset($_POST['reservation_id']) ? intval($_POST['reservation_id']) : 0;
$tableId = isset($_POST['table_id']) ? intval($_POST['table_id']) : 0;

if ($reservationId <= 0 && $tableId <= 0) {
    jsonResponse(false, 'Rezervasyon bilgisi gerekli');
}

try {
    $crud = new CRUD();
    $crud->beginTransaction();

    $reservation = null;
    if ($reservationId > 0) {
        $reservation = $crud->readOne('Reservations', 'reservation_id = :id', [':id' => $reservationId]);
    } elseif ($tableId > 0) {
        $reservation = $crud->readOne('Reservations', 'table_id = :table_id', [':table_id' => $tableId]);
    }

    if (!$reservation) {
        if ($tableId > 0) {
            $crud->update('Tables', [
                'status' => 'Available'
            ], 'table_id = :id', [':id' => $tableId]);
            $crud->commit();
            jsonResponse(true, 'Rezervasyon kaydı bulunamadı, masa boşaltıldı', [
                'table_id' => $tableId
            ]);
        }
        $crud->rollback();
        jsonResponse(false, 'Rezervasyon bulunamadı');
    }

    $crud->delete('Reservations', 'reservation_id = :id', [':id' => $reservation['reservation_id']]);
    $crud->update('Tables', [
        'status' => 'Available'
    ], 'table_id = :id', [':id' => $reservation['table_id']]);

    $crud->commit();

    jsonResponse(true, 'Rezervasyon iptal edildi', [
        'reservation_id' => $reservation['reservation_id'],
        'table_id' => $reservation['table_id']
    ]);
} catch (Exception $e) {
    if (isset($crud)) {
        $crud->rollback();
    }
    jsonResponse(false, 'Hata: ' . $e->getMessage());
}
?>
