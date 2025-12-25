<?php
/**
 * Helper Functions
 * Genel yardımcı fonksiyonlar
 */

/**
 * JSON response döndür
 */
function jsonResponse($success, $message, $data = []) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Input temizleme
 */
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Şifre hash'le
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Şifre doğrula
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Email validasyonu
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Session başlat (eğer başlamadıysa)
 */
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Kullanıcı login mi kontrol et
 */
function isLoggedIn() {
    startSession();
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        return true;
    }
    return isset($_SESSION['user_id']);
}

/**
 * Kullanıcı rolünü kontrol et
 */
function hasRole($role) {
    startSession();
    return isset($_SESSION['role_name']) && $_SESSION['role_name'] === $role;
}

/**
 * Login gerekli sayfalar için koruma
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /pages/login.php');
        exit;
    }
}

/**
 * Rol kontrolü gerekli sayfalar için koruma
 */
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        header('Location: /pages/unauthorized.php');
        exit;
    }
}

/**
 * Login gerekli sayfalar için koruma (özel redirect ile)
 */
function requireLoginWithRedirect(string $redirectPath) {
    if (!isLoggedIn()) {
        header('Location: ' . $redirectPath);
        exit;
    }
}

/**
 * Rol kontrolü gerekli sayfalar için koruma (özel redirect ile)
 */
function requireRoleWithRedirect(string $role, string $redirectPath) {
    requireLoginWithRedirect($redirectPath);
    if (!hasRole($role)) {
        header('Location: ' . $redirectPath);
        exit;
    }
}

/**
 * Birden fazla role izin veren koruma
 */
function requireAnyRole(array $roles, string $redirectPath) {
    requireLoginWithRedirect($redirectPath);
    $currentRole = $_SESSION['role_name'] ?? null;
    if ($currentRole === null || !in_array($currentRole, $roles, true)) {
        header('Location: ' . $redirectPath);
        exit;
    }
}

/**
 * Admin sayfaları için kısa koruma
 */
function requireAdmin() {
    requireRoleWithRedirect('Admin', '/Restaurant-Management-System/admin/login.php');
}

/**
 * Personel sayfaları için kısa koruma
 */
function requirePersonnel() {
    requireAnyRole(['Waiter', 'Manager'], '/Restaurant-Management-System/personnel/login.php');
}
?>
