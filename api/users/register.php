<?php
require_once __DIR__ . '/../../includes/user_repository.php';
require_once __DIR__ . '/../../includes/functions.php';

// Sadece POST isteklerine izin ver
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Only POST method is allowed');
}

// Form veya JSON gövdesinden gelen veriyi al
// (Şimdilik klasik form POST üzerinden gideceğiz)
$username   = isset($_POST['username']) ? cleanInput($_POST['username']) : '';
$email      = isset($_POST['email']) ? cleanInput($_POST['email']) : '';
$password   = isset($_POST['password']) ? $_POST['password'] : '';
$firstName  = isset($_POST['first_name']) ? cleanInput($_POST['first_name']) : '';
$lastName   = isset($_POST['last_name']) ? cleanInput($_POST['last_name']) : '';
$phone      = isset($_POST['phone']) ? cleanInput($_POST['phone']) : null;
$address    = isset($_POST['address']) ? cleanInput($_POST['address']) : null;

// Basit validasyonlar
if ($username === '' || $email === '' || $password === '' || $firstName === '' || $lastName === '') {
    jsonResponse(false, 'Zorunlu alanlar boş bırakılamaz (username, email, password, first_name, last_name)');
}

if (!validateEmail($email)) {
    jsonResponse(false, 'Geçersiz email formatı');
}

if (strlen($password) < 6) {
    jsonResponse(false, 'Şifre en az 6 karakter olmalıdır');
}

try {
    $userRepo = new UserRepository();

    // Username zaten var mı?
    $existingByUsername = $userRepo->findByUsernameOrEmail($username);
    if ($existingByUsername && $existingByUsername['username'] === $username) {
        jsonResponse(false, 'Bu kullanıcı adı zaten kayıtlı');
    }

    // Email zaten var mı?
    $existingByEmail = $userRepo->findByUsernameOrEmail($email);
    if ($existingByEmail && $existingByEmail['email'] === $email) {
        jsonResponse(false, 'Bu email adresi zaten kayıtlı');
    }

    // Customer rolü ile birlikte Users + Customers kayıtlarını oluştur
    $newUserId = $userRepo->createCustomerUserWithProfile(
        $username,
        $email,
        $password,
        $firstName,
        $lastName,
        $phone,
        $address
    );

    if (!$newUserId) {
        jsonResponse(false, 'Kayıt sırasında bir hata oluştu');
    }

    // Başarılı
    jsonResponse(true, 'Kayıt başarılı', [
        'user_id'    => $newUserId,
        'username'   => $username,
        'email'      => $email,
        'first_name' => $firstName,
        'last_name'  => $lastName
    ]);

} catch (Exception $e) {
    jsonResponse(false, 'Sunucu hatası: ' . $e->getMessage());
}