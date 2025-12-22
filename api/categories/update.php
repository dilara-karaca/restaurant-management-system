<?php
require_once __DIR__ . '/../../includes/cruds.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Only POST method is allowed');
}


$id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
$name = isset($_POST['category_name']) ? cleanInput($_POST['category_name']) : '';
$desc = isset($_POST['description']) ? cleanInput($_POST['description']) : '';
$order = isset($_POST['display_order']) ? intval($_POST['display_order']) : 0;
$imageUrl = null;

// Fotoğraf yükleme
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $targetDir = __DIR__ . '/../../uploads/';
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    $filename = uniqid('catimg_') . '_' . basename($_FILES['image']['name']);
    $targetFile = $targetDir . $filename;
    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
        $imageUrl = 'uploads/' . $filename;
    }
}

if ($id <= 0 || $name === '') {
    jsonResponse(false, 'Geçersiz kategori ID veya isim');
}

try {
    $crud = new CRUD();
    $data = [
        'category_name' => $name,
        'description' => $desc,
        'display_order' => $order
    ];
    if ($imageUrl) {
        $data['image_url'] = $imageUrl;
    }
    $result = $crud->update('MenuCategories', $data, 'category_id = :id', [':id' => $id]);
    if ($result) {
        jsonResponse(true, 'Kategori güncellendi');
    } else {
        jsonResponse(false, 'Kategori güncellenemedi');
    }
} catch (Exception $e) {
    jsonResponse(false, 'Hata: ' . $e->getMessage());
}
