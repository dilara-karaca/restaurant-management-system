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

try {
    $crud = new CRUD();
    $personnel = $crud->customQuery(
        "SELECT
            p.personnel_id,
            p.first_name,
            p.last_name,
            p.position,
            u.user_id,
            u.username,
            u.password AS password_hash,
            r.role_id,
            r.role_name
         FROM Personnel p
         JOIN Users u ON p.user_id = u.user_id
         JOIN Roles r ON u.role_id = r.role_id
         WHERE r.role_name IN ('Manager', 'Waiter')
         ORDER BY p.personnel_id ASC"
    );

    if ($personnel === false) {
        jsonResponse(false, 'Personel listesi alınamadı');
    }

    $roles = $crud->customQuery(
        "SELECT role_id, role_name
         FROM Roles
         WHERE role_name IN ('Manager', 'Waiter')
         ORDER BY role_name ASC"
    );

    if ($roles === false) {
        jsonResponse(false, 'Rol listesi alınamadı');
    }

    jsonResponse(true, 'Personel listesi', [
        'personnel' => $personnel,
        'roles' => $roles
    ]);
} catch (Exception $e) {
    jsonResponse(false, 'Hata: ' . $e->getMessage());
}
