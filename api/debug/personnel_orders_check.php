<?php
require_once __DIR__ . '/../../includes/cruds.php';
require_once __DIR__ . '/../../includes/functions.php';

startSession();

$personnelId = isset($_SESSION['personnel_id']) ? (int) $_SESSION['personnel_id'] : 0;
$debug = [
    'session_personnel_id' => $personnelId,
];

try {
    $crud = new CRUD();
    $direct = $crud->customQuery(
        'SELECT order_id, served_by FROM Orders WHERE served_by = ' . $personnelId . ' ORDER BY order_id DESC'
    );

    $join = $crud->customQuery(
        'SELECT o.order_id FROM Orders o JOIN Tables t ON o.table_id = t.table_id JOIN Customers c ON o.customer_id = c.customer_id WHERE o.served_by = ' . $personnelId
    );

    $debug['orders_direct_count'] = $direct ? count($direct) : 0;
    $debug['orders_direct'] = $direct;
    $debug['orders_join_count'] = $join ? count($join) : 0;
    $debug['orders_join'] = $join;

    jsonResponse(true, 'Debug orders', $debug);
} catch (Exception $e) {
    jsonResponse(false, 'Hata: ' . $e->getMessage(), $debug);
}
