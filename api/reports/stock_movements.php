<?php
require_once __DIR__ . '/../../includes/cruds.php';
require_once __DIR__ . '/../../includes/functions.php';

$ingredientId = isset($_GET['ingredient_id']) ? intval($_GET['ingredient_id']) : 0;
$movementType = isset($_GET['movement_type']) ? trim($_GET['movement_type']) : '';
$dateFrom = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
$dateTo = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
if ($limit <= 0) {
    $limit = 20;
}

try {
    $crud = new CRUD();
    // SP-4: Stok hareket raporu (3+ tablo JOIN)
    $rows = $crud->customQuery(
        "CALL sp_report_stock_movements(:ingredient_id, :movement_type, :date_from, :date_to, :limit_value)",
        [
            ':ingredient_id' => $ingredientId > 0 ? $ingredientId : null,
            ':movement_type' => $movementType !== '' ? $movementType : null,
            ':date_from' => $dateFrom !== '' ? $dateFrom : null,
            ':date_to' => $dateTo !== '' ? $dateTo : null,
            ':limit_value' => $limit
        ]
    );
    jsonResponse(true, 'Stok hareket raporu', $rows);
} catch (Exception $e) {
    jsonResponse(false, 'Rapor alınamadı: ' . $e->getMessage());
}
