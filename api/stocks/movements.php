<?php
require_once __DIR__ . '/../../includes/cruds.php';
require_once __DIR__ . '/../../includes/functions.php';

$ingredientId = isset($_GET['ingredient_id']) ? intval($_GET['ingredient_id']) : 0;
$movementType = isset($_GET['movement_type']) ? trim($_GET['movement_type']) : '';
$dateFrom = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
$dateTo = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 12;
if ($limit <= 0) {
    $limit = 12;
}
if ($limit > 100) {
    $limit = 100;
}

try {
    $crud = new CRUD();
    $where = [];
    $params = [];

    if ($ingredientId > 0) {
        $where[] = 'sm.ingredient_id = :ingredient_id';
        $params[':ingredient_id'] = $ingredientId;
    }

    if ($movementType !== '') {
        $where[] = 'sm.movement_type = :movement_type';
        $params[':movement_type'] = $movementType;
    }

    if ($dateFrom !== '') {
        $where[] = 'DATE(sm.created_at) >= :date_from';
        $params[':date_from'] = $dateFrom;
    }

    if ($dateTo !== '') {
        $where[] = 'DATE(sm.created_at) <= :date_to';
        $params[':date_to'] = $dateTo;
    }

    $query = 'SELECT sm.movement_id,
                     sm.ingredient_id,
                     sm.movement_type,
                     sm.quantity,
                     sm.note,
                     sm.created_at,
                     i.ingredient_name,
                     i.unit
              FROM StockMovements sm
              JOIN Ingredients i ON sm.ingredient_id = i.ingredient_id';

    if (!empty($where)) {
        $query .= ' WHERE ' . implode(' AND ', $where);
    }

    $query .= ' ORDER BY sm.created_at DESC LIMIT ' . $limit;

    $movements = $crud->customQuery($query, $params);
    jsonResponse(true, 'Stok hareketleri', $movements);
} catch (Exception $e) {
    jsonResponse(false, 'Stok hareketleri alınamadı: ' . $e->getMessage());
}
