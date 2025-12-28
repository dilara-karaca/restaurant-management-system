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
$roleId = isset($_POST['role_id']) ? intval($_POST['role_id']) : 0;

if ($userId <= 0 || $roleId <= 0) {
    jsonResponse(false, 'Geçersiz kullanıcı veya rol bilgisi');
}

try {
    $crud = new CRUD();
    $role = $crud->readOne('Roles', 'role_id = :id', [':id' => $roleId]);
    if (!$role || !in_array($role['role_name'], ['Manager', 'Waiter'], true)) {
        jsonResponse(false, 'Geçersiz rol');
    }

    $user = $crud->readOne('Users', 'user_id = :id', [':id' => $userId]);
    if (!$user) {
        jsonResponse(false, 'Kullanıcı bulunamadı');
    }

    $crud->beginTransaction();
    $crud->update('Users', ['role_id' => $roleId], 'user_id = :id', [':id' => $userId]);
    $crud->update('Personnel', ['position' => $role['role_name']], 'user_id = :id', [':id' => $userId]);
    $crud->commit();

    jsonResponse(true, 'Rol güncellendi');
} catch (Exception $e) {
    if (isset($crud)) {
        $crud->rollback();
    }
    jsonResponse(false, 'Hata: ' . $e->getMessage());
}
