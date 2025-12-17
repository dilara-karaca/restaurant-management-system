<?php
require_once __DIR__ . '/../../includes/cruds.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Only POST method is allowed');
}

$id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
if ($id <= 0) {
    jsonResponse(false, 'Geçersiz kategori ID');
}

try {
    $crud = new CRUD();
    $result = $crud->delete('MenuCategories', 'category_id = :id', [':id' => $id]);
    if ($result) {
        jsonResponse(true, 'Kategori silindi');
    } else {
        jsonResponse(false, 'Kategori silinemedi');
    }
} catch (Exception $e) {
    jsonResponse(false, 'Hata: ' . $e->getMessage());
}
