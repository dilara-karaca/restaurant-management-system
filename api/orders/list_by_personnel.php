<?php
require_once __DIR__ . '/../../includes/cruds.php';
require_once __DIR__ . '/../../includes/functions.php';

startSession();

if (!isset($_SESSION['personnel_logged_in']) || $_SESSION['personnel_logged_in'] !== true) {
    jsonResponse(false, 'Personel girişi gerekli');
}

$personnelId = isset($_SESSION['personnel_id']) ? (int) $_SESSION['personnel_id'] : 0;
if ($personnelId <= 0) {
    jsonResponse(false, 'Personel bilgisi bulunamadı');
}

$status = isset($_GET['status']) ? cleanInput($_GET['status']) : '';
$allowedStatuses = ['Pending', 'Preparing', 'Served', 'Completed', 'Cancelled'];
if ($status !== '' && !in_array($status, $allowedStatuses, true)) {
    jsonResponse(false, 'Geçersiz sipariş durumu');
}

try {
    $crud = new CRUD();
    
    // SP-15: Personel sipariş listesi (3+ tablo JOIN)
    $orders = $crud->customQuery(
        "CALL sp_list_orders_by_personnel(:personnel_id, :status)",
        [
            ':personnel_id' => $personnelId,
            ':status' => $status !== '' ? $status : null
        ]
    );
    if ($orders === false) {
        $params = [':personnel_id' => $personnelId];
        $whereSql = ' WHERE o.served_by = :personnel_id';
        if ($status !== '') {
            $whereSql .= ' AND o.status = :status';
            $params[':status'] = $status;
        }
        $orders = $crud->customQuery(
            "SELECT
                o.order_id,
                o.order_date,
                o.total_amount,
                o.status,
                o.payment_method,
                o.table_id,
                t.table_number,
                c.first_name AS customer_first_name,
                c.last_name AS customer_last_name
             FROM Orders o
             JOIN Tables t ON o.table_id = t.table_id
             JOIN Customers c ON o.customer_id = c.customer_id
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

    $tableIds = [];
    foreach ($orders as $order) {
        $tableIds[] = (int) ($order['table_id'] ?? 0);
    }
    $tableIds = array_values(array_unique(array_filter($tableIds)));
    $tableStatusById = [];
    if (!empty($tableIds)) {
        $placeholders = implode(',', array_fill(0, count($tableIds), '?'));
        $tableRows = $crud->customQuery(
            "SELECT table_id, status FROM Tables WHERE table_id IN ({$placeholders})",
            $tableIds
        );
        if ($tableRows !== false) {
            foreach ($tableRows as $tableRow) {
                $tableStatusById[(int) $tableRow['table_id']] = $tableRow['status'];
            }
        }
    }

    $filteredOrders = [];
    foreach ($orders as $order) {
        $tableId = (int) ($order['table_id'] ?? 0);
        $tableStatus = $tableStatusById[$tableId] ?? null;
        if ($order['status'] === 'Completed' && $tableStatus === 'Available') {
            continue;
        }
        $order['table_status'] = $tableStatus;
        $filteredOrders[] = $order;
    }

    if (empty($filteredOrders)) {
        jsonResponse(true, 'Sipariş listesi', []);
    }

    $orderIds = array_column($filteredOrders, 'order_id');
    $paidAmountByOrder = [];
    $paidDetailMaxByOrder = [];
    if (!empty($orderIds)) {
        $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
        $paidRows = $crud->customQuery(
            "SELECT order_id, paid_amount, paid_detail_max_id FROM Orders WHERE order_id IN ({$placeholders})",
            $orderIds
        );
        if ($paidRows === false) {
            $paidRows = $crud->customQuery(
                "SELECT order_id, paid_amount FROM Orders WHERE order_id IN ({$placeholders})",
                $orderIds
            );
        }
        if ($paidRows !== false) {
            foreach ($paidRows as $paidRow) {
                $paidAmountByOrder[(int) $paidRow['order_id']] = (float) ($paidRow['paid_amount'] ?? 0);
                if (array_key_exists('paid_detail_max_id', $paidRow)) {
                    $paidDetailMaxByOrder[(int) $paidRow['order_id']] = $paidRow['paid_detail_max_id'] ?? null;
                }
            }
        }
    }
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
    foreach ($filteredOrders as $order) {
        $orderId = (int) $order['order_id'];
        $ordersById[$orderId] = [
            'order_id' => $orderId,
            'order_date' => $order['order_date'],
            'total_amount' => (float) ($order['total_amount'] ?? 0),
            'status' => $order['status'],
            'payment_method' => $order['payment_method'] ?? null,
            'table_id' => (int) ($order['table_id'] ?? 0),
            'table_number' => $order['table_number'] ?? '',
            'table_status' => $order['table_status'] ?? null,
            'paid_amount' => $paidAmountByOrder[$orderId] ?? 0,
            'paid_detail_max_id' => $paidDetailMaxByOrder[$orderId] ?? null,
            'customer_name' => trim(($order['customer_first_name'] ?? '') . ' ' . ($order['customer_last_name'] ?? '')),
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
