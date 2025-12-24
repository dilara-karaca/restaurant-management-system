<?php
require_once __DIR__ . '/../../includes/cruds.php';
require_once __DIR__ . '/../../includes/functions.php';

try {
    $crud = new CRUD();
    $ingredients = $crud->customQuery(
        'SELECT i.ingredient_id,
                i.ingredient_name,
                i.unit,
                i.unit_price,
                s.supplier_name
         FROM Ingredients i
         LEFT JOIN Suppliers s ON i.supplier_id = s.supplier_id
         ORDER BY i.ingredient_name ASC'
    );

    jsonResponse(true, 'Malzeme listesi', $ingredients);
} catch (Exception $e) {
    jsonResponse(false, 'Malzeme listesi alınamadı: ' . $e->getMessage());
}
