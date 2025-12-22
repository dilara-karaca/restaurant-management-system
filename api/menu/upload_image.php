<?php
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Only POST method is allowed');
}

if (!isset($_FILES['image']) || !isset($_POST['product_id'])) {
    jsonResponse(false, 'Resim dosyası ve ürün ID zorunludur');
}

$productId = intval($_POST['product_id']);
if ($productId <= 0) {
    jsonResponse(false, 'Geçersiz ürün ID');
}

$targetDir = __DIR__ . '/../../uploads/';
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}

$filename = basename($_FILES['image']['name']);
$targetFile = $targetDir . uniqid('img_') . '_' . $filename;

if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
    // Dosya yolu veritabanına kaydedilebilir (isteğe bağlı)
    jsonResponse(true, 'Resim yüklendi', ['image_url' => str_replace(__DIR__ . '/../../', '', $targetFile)]);
} else {
    jsonResponse(false, 'Resim yüklenemedi');
}
