<?php
require_once __DIR__ . '/../../includes/cruds.php';
require_once __DIR__ . '/../../includes/functions.php';

$status = isset($_GET['status']) ? cleanInput($_GET['status']) : '';
$allowedStatuses = ['Pending', 'Preparing', 'Served', 'Completed', 'Cancelled'];
if ($status !== '' && !in_array($status, $allowedStatuses, true)) {
    jsonResponse(false, 'Geçersiz sipariş durumu');
}

try {
    $crud = new CRUD();
    // SP-14: Sipariş listesi (3+ tablo JOIN)
    $orders = $crud->customQuery(
        "CALL sp_list_orders(:status)",
        [':status' => $status !== '' ? $status : null]
    );
    if ($orders === false) {
        $params = [];
        $whereSql = '';
        if ($status !== '') {
            $whereSql = ' WHERE o.status = :status';
            $params[':status'] = $status;
        }
        $orders = $crud->customQuery(
            "SELECT
                o.order_id,
                o.order_date,
                o.total_amount,
                o.status,
                o.payment_method,
                t.table_number,
                c.first_name AS customer_first_name,
                c.last_name AS customer_last_name,
                p.first_name AS waiter_first_name,
                p.last_name AS waiter_last_name
             FROM Orders o
             JOIN Tables t ON o.table_id = t.table_id
             JOIN Customers c ON o.customer_id = c.customer_id
             LEFT JOIN Personnel p ON o.served_by = p.personnel_id
             {$whereSql}
             ORDER BY o.order_date DESC",
            $params
        );
    }

    if ($orders === false) {
        jsonResponse(false, 'Sipariş listesi alınamadı.');
    }

    if (empty($orders)) {
        jsonResponse(true, 'Sipariş listesi', []);
    }

    $orderIds = array_column($orders, 'order_id');
    $orderIdsCsv = implode(',', $orderIds);
    // SP-17: Sipariş kalemleri (OrderDetails + MenuProducts)
    $details = $crud->customQuery(
        "CALL sp_list_order_items_for_orders(:order_ids)",
        [':order_ids' => $orderIdsCsv]
    );
    if ($details === false) {
        $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
        $details = $crud->customQuery(
            "SELECT
                od.order_detail_id,
                od.order_id,
                od.product_id,
                mp.product_name,
                od.quantity,
                od.unit_price,
                od.subtotal,
                od.special_instructions
             FROM OrderDetails od
             JOIN MenuProducts mp ON od.product_id = mp.product_id
             WHERE od.order_id IN ({$placeholders})
             ORDER BY od.order_detail_id ASC",
            $orderIds
        );
    }

    if ($details === false) {
        jsonResponse(false, 'Sipariş kalemleri listelenemedi.');
    }

    $ordersById = [];
    foreach ($orders as $order) {
        $orderId = (int) $order['order_id'];
        $ordersById[$orderId] = [
            'order_id' => $orderId,
            'order_date' => $order['order_date'],
            'total_amount' => $order['total_amount'],
            'status' => $order['status'],
            'payment_method' => $order['payment_method'],
            'table_number' => $order['table_number'],
            'customer_name' => trim($order['customer_first_name'] . ' ' . $order['customer_last_name']),
            'waiter_name' => trim($order['waiter_first_name'] . ' ' . $order['waiter_last_name']),
            'items' => []
        ];
    }

    foreach ($details as $detail) {
        $orderId = (int) $detail['order_id'];
        if (!isset($ordersById[$orderId])) {
            continue;
        }
        $ordersById[$orderId]['items'][] = [
            'order_detail_id' => (int) $detail['order_detail_id'],
            'product_id' => (int) $detail['product_id'],
            'product_name' => $detail['product_name'],
            'quantity' => (int) $detail['quantity'],
            'unit_price' => $detail['unit_price'],
            'subtotal' => $detail['subtotal'],
            'special_instructions' => $detail['special_instructions']
        ];
    }

    jsonResponse(true, 'Sipariş listesi', array_values($ordersById));
} catch (Exception $e) {
    jsonResponse(false, 'Siparişler listelenemedi: ' . $e->getMessage());
}