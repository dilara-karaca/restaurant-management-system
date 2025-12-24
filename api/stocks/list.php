<?php
require_once __DIR__ . '/../../includes/cruds.php';
require_once __DIR__ . '/../../includes/functions.php';

try {
    $crud = new CRUD();
    $stocks = $crud->customQuery(
        'SELECT s.stock_id,
                s.ingredient_id,
                i.ingredient_name,
                i.unit,
                i.unit_price,
                s.quantity,
                s.minimum_quantity,
                s.last_updated,
                sup.supplier_name
         FROM Stocks s
         JOIN Ingredients i ON s.ingredient_id = i.ingredient_id
         LEFT JOIN Suppliers sup ON i.supplier_id = sup.supplier_id
         ORDER BY i.ingredient_name ASC'
    );

    jsonResponse(true, 'Stok listesi', $stocks);
} catch (Exception $e) {
    jsonResponse(false, 'Stok listesi alınamadı: ' . $e->getMessage());
}
