<?php
require_once __DIR__ . '/../../includes/cruds.php';
require_once __DIR__ . '/../../includes/functions.php';

try {
    $crud = new CRUD();
    
    // Tüm masaları getir
    $tables = $crud->read('Tables', '', [], 'table_number ASC');
    
    if ($tables === false) {
        jsonResponse(false, 'Masalar listelenemedi');
    }
    
    // Her masa için aktif sipariş bilgilerini getir
    $tablesWithOrders = [];
    foreach ($tables as $table) {
        // Location bilgisini normalize et
        $location = $table['location'] ?? '';
        if (empty($location)) {
            // Location yoksa, table_number'a göre tahmin et (1-15: Main Hall, 16+: Garden)
            $location = $table['table_number'] <= 15 ? 'Main Hall' : 'Bahçe';
        }
        
        $tableInfo = [
            'table_id' => $table['table_id'],
            'table_number' => $table['table_number'],
            'capacity' => $table['capacity'],
            'status' => $table['status'],
            'location' => $location,
            'order_id' => null,
            'customer_name' => null,
            'total_amount' => 0,
            'order_status' => null,
            'items' => []
        ];
        
        // Eğer masa doluysa (Occupied), aktif sipariş bilgilerini getir
        if ($table['status'] === 'Occupied') {
            $activeOrder = $crud->customQuery(
                "SELECT 
                    o.order_id,
                    o.total_amount,
                    CONCAT(c.first_name, ' ', c.last_name) as customer_name,
                    o.status as order_status
                 FROM Orders o
                 JOIN Customers c ON o.customer_id = c.customer_id
                 WHERE o.table_id = :table_id 
                 AND o.status IN ('Pending', 'Preparing', 'Served')
                 ORDER BY o.order_date DESC
                 LIMIT 1",
                [':table_id' => $table['table_id']]
            );
            
            if ($activeOrder && count($activeOrder) > 0) {
                $order = $activeOrder[0];
                $tableInfo['order_id'] = $order['order_id'];
                $tableInfo['customer_name'] = $order['customer_name'];
                $tableInfo['total_amount'] = $order['total_amount'];
                $tableInfo['order_status'] = $order['order_status'];
                
                // Sipariş kalemlerini getir
                $orderItems = $crud->customQuery(
                    "SELECT 
                        mp.product_name,
                        od.quantity,
                        od.unit_price,
                        od.subtotal
                     FROM OrderDetails od
                     JOIN MenuProducts mp ON od.product_id = mp.product_id
                     WHERE od.order_id = :order_id",
                    [':order_id' => $order['order_id']]
                );
                
                $tableInfo['items'] = $orderItems ?: [];
            }
        }
        
        $tablesWithOrders[] = $tableInfo;
    }
    
    jsonResponse(true, 'Masalar listelendi', $tablesWithOrders);
    
} catch (Exception $e) {
    jsonResponse(false, 'Hata: ' . $e->getMessage());
}
?>

