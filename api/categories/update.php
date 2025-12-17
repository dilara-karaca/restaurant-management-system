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

if ($id <= 0 || $name === '') {
    jsonResponse(false, 'Geçersiz kategori ID veya isim');
}

try {
    $crud = new CRUD();
    $result = $crud->update('MenuCategories', [
        'category_name' => $name,
        'description' => $desc,
        'display_order' => $order
    ], 'category_id = :id', [':id' => $id]);
    if ($result) {
        jsonResponse(true, 'Kategori güncellendi');
    } else {
        jsonResponse(false, 'Kategori güncellenemedi');
    }
} catch (Exception $e) {
    jsonResponse(false, 'Hata: ' . $e->getMessage());
}
