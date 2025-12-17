<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/crud.php';

/**
 * Veritabanı bağlantısını ve CRUD işlemlerini test eden dosya
 */

$response = [
    'success' => false,
    'message' => '',
    'data' => []
];

try {
    $crud = new CRUD();
    
    // 1. Bağlantı testi
    $response['tests']['connection'] = 'OK';
    
    // 2. Users tablosunu oku
    $users = $crud->read('Users', '', [], 'user_id ASC');
    $response['tests']['read_users'] = count($users) . ' users found';
    $response['data']['users'] = $users;
    
    // 3. Menu Products tablosunu oku
    $products = $crud->read('MenuProducts', 'is_available = :available', [':available' => 1]);
    $response['tests']['read_products'] = count($products) . ' products found';
    $response['data']['products'] = $products;
    
    // 4. Orders tablosunu oku
    $orders = $crud->read('Orders', '', [], 'order_date DESC');
    $response['tests']['read_orders'] = count($orders) . ' orders found';
    $response['data']['orders'] = $orders;
    
    // 5. Toplam müşteri sayısı
    $customerCount = $crud->count('Customers');
    $response['tests']['customer_count'] = $customerCount . ' customers';
    
    // 6. Tek bir kayıt oku (test)
    $oneUser = $crud->readOne('Users', 'username = :username', [':username' => 'admin']);
    $response['tests']['read_one_user'] = $oneUser ? 'Admin user found' : 'Not found';
    $response['data']['admin_user'] = $oneUser;
    
    // 7. Custom Query Test - Orders with Customer Names
    $orderDetails = $crud->customQuery("
        SELECT 
            o.order_id,
            o.order_date,
            o.total_amount,
            o.status,
            CONCAT(c.first_name, ' ', c.last_name) as customer_name,
            t.table_number
        FROM Orders o
        JOIN Customers c ON o.customer_id = c.customer_id
        JOIN Tables t ON o.table_id = t.table_id
        ORDER BY o.order_date DESC
        LIMIT 5
    ");
    $response['tests']['join_query'] = count($orderDetails) . ' order details found';
    $response['data']['order_details'] = $orderDetails;
    
    $response['success'] = true;
    $response['message'] = 'All tests passed successfully!';
    
} catch(Exception $e) {
    $response['success'] = false;
    $response['message'] = 'Error: ' . $e->getMessage();
}

// JSON çıktısı
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>