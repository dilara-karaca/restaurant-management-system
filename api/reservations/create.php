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
$firstName = isset($_POST['first_name']) ? trim(cleanInput($_POST['first_name'])) : '';
$lastName = isset($_POST['last_name']) ? trim(cleanInput($_POST['last_name'])) : '';
$reservedAt = isset($_POST['reserved_at']) ? trim(cleanInput($_POST['reserved_at'])) : '';

if ($tableId <= 0) {
    jsonResponse(false, 'Geçersiz masa');
}
if ($firstName === '' || $lastName === '') {
    jsonResponse(false, 'Ad ve soyad gerekli');
}
if ($reservedAt === '') {
    jsonResponse(false, 'Rezervasyon saati gerekli');
}

if (!preg_match('/^\d{2}:\d{2}$/', $reservedAt)) {
    jsonResponse(false, 'Geçerli bir saat giriniz (HH:MM)');
}

$timezone = new DateTimeZone('Europe/Istanbul');
$reservedDateTime = DateTime::createFromFormat('H:i', $reservedAt, $timezone);
if (!$reservedDateTime) {
    jsonResponse(false, 'Rezervasyon saati geçersiz');
}
$reservedDateTime->setDate(
    (int) date('Y'),
    (int) date('m'),
    (int) date('d')
);
$reservedAtValue = $reservedDateTime->format('Y-m-d H:i:s');

try {
    $crud = new CRUD();
    $crud->beginTransaction();

    $table = $crud->readOne('Tables', 'table_id = :id', [':id' => $tableId]);
    if (!$table) {
        $crud->rollback();
        jsonResponse(false, 'Masa bulunamadı');
    }

    if ($table['status'] !== 'Available') {
        $crud->rollback();
        jsonResponse(false, 'Masa müsait değil');
    }

    $reservationId = $crud->create('Reservations', [
        'table_id' => $tableId,
        'first_name' => $firstName,
        'last_name' => $lastName,
        'reserved_at' => $reservedAtValue
    ]);

    if (!$reservationId) {
        $crud->rollback();
        jsonResponse(false, 'Rezervasyon oluşturulamadı');
    }

    $crud->update('Tables', [
        'status' => 'Reserved'
    ], 'table_id = :id', [':id' => $tableId]);

    $crud->commit();

    jsonResponse(true, 'Rezervasyon oluşturuldu', [
        'reservation_id' => $reservationId,
        'table_id' => $tableId
    ]);
} catch (Exception $e) {
    if (isset($crud)) {
        $crud->rollback();
    }
    jsonResponse(false, 'Hata: ' . $e->getMessage());
}
?>
