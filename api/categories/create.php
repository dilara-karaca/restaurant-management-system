<?php
require_once __DIR__ . '/../../includes/cruds.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Only POST method is allowed');
}

$name = isset($_POST['category_name']) ? cleanInput($_POST['category_name']) : '';
$desc = isset($_POST['description']) ? cleanInput($_POST['description']) : '';
$order = isset($_POST['display_order']) ? intval($_POST['display_order']) : 0;

if ($name === '') {
    jsonResponse(false, 'Kategori adı zorunludur');
}

try {
    $crud = new CRUD();
    $result = $crud->create('MenuCategories', [
        'category_name' => $name,
        'description' => $desc,
        'display_order' => $order
    ]);
    if ($result) {
        jsonResponse(true, 'Kategori eklendi');
    } else {
        jsonResponse(false, 'Kategori eklenemedi');
    }
} catch (Exception $e) {
    jsonResponse(false, 'Hata: ' . $e->getMessage());
}
