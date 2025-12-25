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
