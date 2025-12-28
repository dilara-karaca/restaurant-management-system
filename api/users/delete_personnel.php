<?php
require_once __DIR__ . '/../../includes/cruds.php';
require_once __DIR__ . '/../../includes/functions.php';

startSession();

if (
    (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true)
    && (!isset($_SESSION['role_name']) || $_SESSION['role_name'] !== 'Admin')
) {
    jsonResponse(false, 'Yetkisiz erişim');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Only POST method is allowed');
}

$userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
if ($userId <= 0) {
    jsonResponse(false, 'Geçersiz kullanıcı bilgisi');
}

try {
    $crud = new CRUD();
    $user = $crud->readOne('Users', 'user_id = :id', [':id' => $userId]);
    if (!$user) {
        jsonResponse(false, 'Kullanıcı bulunamadı');
    }

    $role = $crud->readOne('Roles', 'role_id = :id', [':id' => $user['role_id']]);
    if (!$role || !in_array($role['role_name'], ['Manager', 'Waiter'], true)) {
        jsonResponse(false, 'Bu kullanıcı silinemez');
    }

    $crud->beginTransaction();
    $deleted = $crud->delete('Users', 'user_id = :id', [':id' => $userId]);
    if (!$deleted) {
        $crud->rollback();
        jsonResponse(false, 'Kullanıcı silinemedi');
    }

    $crud->commit();
    jsonResponse(true, 'Personel silindi');
} catch (Exception $e) {
    if (isset($crud)) {
        $crud->rollback();
    }
    jsonResponse(false, 'Hata: ' . $e->getMessage());
}
