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

$firstName = isset($_POST['first_name']) ? cleanInput($_POST['first_name']) : '';
$lastName = isset($_POST['last_name']) ? cleanInput($_POST['last_name']) : '';
$username = isset($_POST['username']) ? cleanInput($_POST['username']) : '';
$email = isset($_POST['email']) ? cleanInput($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$roleId = isset($_POST['role_id']) ? intval($_POST['role_id']) : 0;
$hireDate = isset($_POST['hire_date']) ? cleanInput($_POST['hire_date']) : '';
$salary = isset($_POST['salary']) ? cleanInput($_POST['salary']) : '';

if ($firstName === '' || $lastName === '' || $username === '' || $email === '' || $password === '' || $roleId <= 0 || $hireDate === '') {
    jsonResponse(false, 'Tüm zorunlu alanları doldurun');
}

if (!validateEmail($email)) {
    jsonResponse(false, 'Geçerli bir e-posta girin');
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $hireDate)) {
    jsonResponse(false, 'Geçerli bir tarih girin');
}

try {
    $crud = new CRUD();
    $role = $crud->readOne('Roles', 'role_id = :id', [':id' => $roleId]);
    if (!$role || !in_array($role['role_name'], ['Manager', 'Waiter'], true)) {
        jsonResponse(false, 'Geçersiz rol');
    }

    $existingUser = $crud->readOne('Users', 'username = :username', [':username' => $username]);
    if ($existingUser) {
        jsonResponse(false, 'Bu kullanıcı adı zaten kullanılıyor');
    }

    $existingEmail = $crud->readOne('Users', 'email = :email', [':email' => $email]);
    if ($existingEmail) {
        jsonResponse(false, 'Bu e-posta zaten kullanılıyor');
    }

    $crud->beginTransaction();
    $userId = $crud->create('Users', [
        'role_id' => $roleId,
        'username' => $username,
        'password' => hashPassword($password),
        'email' => $email
    ]);

    if (!$userId) {
        $crud->rollback();
        jsonResponse(false, 'Kullanıcı oluşturulamadı');
    }

    $personnelData = [
        'user_id' => $userId,
        'first_name' => $firstName,
        'last_name' => $lastName,
        'position' => $role['role_name'],
        'hire_date' => $hireDate
    ];

    if ($salary !== '') {
        $personnelData['salary'] = $salary;
    }

    $personnelId = $crud->create('Personnel', $personnelData);
    if (!$personnelId) {
        $crud->rollback();
        jsonResponse(false, 'Personel oluşturulamadı');
    }

    $crud->commit();

    jsonResponse(true, 'Personel oluşturuldu', [
        'user_id' => $userId,
        'personnel_id' => $personnelId
    ]);
} catch (Exception $e) {
    if (isset($crud)) {
        $crud->rollback();
    }
    jsonResponse(false, 'Hata: ' . $e->getMessage());
}
