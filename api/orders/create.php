<?php
require_once __DIR__ . '/../../includes/cruds.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Only POST method is allowed');
}

// Gelen verileri al
$customerName = isset($_POST['customer_name']) ? trim(cleanInput($_POST['customer_name'])) : '';
$firstName = isset($_POST['first_name']) ? trim(cleanInput($_POST['first_name'])) : '';
$lastName = isset($_POST['last_name']) ? trim(cleanInput($_POST['last_name'])) : '';
$orderNote = isset($_POST['order_note']) ? cleanInput($_POST['order_note']) : null;
$paymentMethod = isset($_POST['payment_method']) ? cleanInput($_POST['payment_method']) : '';
$itemsJson = isset($_POST['items']) ? $_POST['items'] : '[]';
$items = json_decode($itemsJson, true);

// JSON decode hatası kontrolü
if (json_last_error() !== JSON_ERROR_NONE) {
    jsonResponse(false, 'Sipariş verileri geçersiz: ' . json_last_error_msg());
}

// Validasyon
if (empty($customerName) || strlen($customerName) < 2) {
    jsonResponse(false, 'Lütfen adınızı ve soyadınızı giriniz');
}

if (empty($items) || !is_array($items)) {
    jsonResponse(false, 'Sepetiniz boş');
}

$allowedPayments = ['Cash', 'Credit Card', 'Debit Card', 'Mobile Payment'];
if ($paymentMethod !== '' && !in_array($paymentMethod, $allowedPayments, true)) {
    jsonResponse(false, 'Geçersiz ödeme yöntemi');
}

if ($paymentMethod !== '' && $paymentMethod !== 'Mobile Payment') {
    jsonResponse(false, 'Bu ödeme yöntemi müşteri siparişinde kullanılamaz');
}

// İsimleri temizle ve ayarla
if (empty($firstName) && empty($lastName)) {
    $nameParts = explode(' ', $customerName, 2);
    $firstName = $nameParts[0] ?? $customerName;
    $lastName = $nameParts[1] ?? 'Müşteri';
}

try {
    $crud = new CRUD();
    $crud->beginTransaction();

    // Otomatik olarak müsait bir masa bul
    // Önce "Available" durumundaki masaları kontrol et
    $availableTables = $crud->read('Tables', 'status = :status', [':status' => 'Available'], 'table_number ASC');
    
    if (empty($availableTables)) {
        // Müsait masa yoksa, en küçük masa numarasını seç (veya hata ver)
        $allTables = $crud->read('Tables', '', [], 'table_number ASC');
        if (empty($allTables)) {
            $crud->rollback();
            jsonResponse(false, 'Sistemde masa bulunamadı');
        }
        // En küçük masa numarasını seç
        $table = $allTables[0];
    } else {
        // İlk müsait masayı seç
        $table = $availableTables[0];
    }
    
    $tableId = $table['table_id'];
    $tableNumber = $table['table_number'];
    
    // Masanın durumunu "Occupied" olarak güncelle
    $crud->update('Tables', ['status' => 'Occupied'], 'table_id = :id', [':id' => $tableId]);

    // Müşteri oluştur veya güncelle
    // Her sipariş için yeni bir müşteri kaydı oluştur (Guest müşterisi olarak)
    // Customer rolünü bul
    $customerRole = $crud->readOne('Roles', 'role_name = :name', [':name' => 'Customer']);
    if (!$customerRole) {
        $crud->rollback();
        jsonResponse(false, 'Customer rolü bulunamadı');
    }
    
    // Benzersiz email oluştur (timestamp + random)
    $uniqueEmail = 'guest_' . time() . '_' . rand(1000, 9999) . '@restaurant.com';
    $uniqueUsername = 'guest_' . time() . '_' . rand(1000, 9999);
    
    // Guest User oluştur
    $guestUserId = $crud->create('Users', [
        'role_id' => $customerRole['role_id'],
        'username' => $uniqueUsername,
        'password' => password_hash('guest123', PASSWORD_DEFAULT), // Geçici şifre
        'email' => $uniqueEmail
    ]);
    
    if (!$guestUserId) {
        $crud->rollback();
        jsonResponse(false, 'Müşteri kullanıcı oluşturulamadı');
    }
    
    // Müşteri kaydı oluştur (müşterinin girdiği isimle)
    $customerId = $crud->create('Customers', [
        'user_id' => $guestUserId,
        'first_name' => $firstName,
        'last_name' => $lastName,
        'phone' => null,
        'address' => null
    ]);
    
    if (!$customerId) {
        $crud->rollback();
        jsonResponse(false, 'Müşteri kaydı oluşturulamadı');
    }

    // Sipariş oluştur (mobil ödeme için payment_method işlenebilir)
    $orderData = [
        'customer_id' => $customerId,
        'table_id' => $tableId,
        'served_by' => null, // Henüz atanmamış
        'total_amount' => 0, // Trigger ile güncellenecek
        'status' => 'Pending'
    ];
    if ($paymentMethod !== '') {
        $orderData['payment_method'] = $paymentMethod;
    }
    // payment_method NULL olarak eklenmeyecek (ENUM NULL kabul etmeyebilir)
    
    $orderId = $crud->create('Orders', $orderData);

    if (!$orderId) {
        $crud->rollback();
        jsonResponse(false, 'Sipariş oluşturulamadı');
    }

    // Sipariş kalemlerini ekle
    foreach ($items as $item) {
        $productId = isset($item['productId']) ? intval($item['productId']) : 0;
        $quantity = isset($item['quantity']) ? intval($item['quantity']) : 0;
        $price = isset($item['price']) ? floatval($item['price']) : 0;
        $specialInstructions = isset($item['specialInstructions']) ? cleanInput($item['specialInstructions']) : null;

        if ($productId <= 0 || $quantity <= 0) {
            continue;
        }

        // Ürün bilgilerini kontrol et
        $product = $crud->readOne('MenuProducts', 'product_id = :id', [':id' => $productId]);
        if (!$product) {
            continue;
        }

        // Fiyatı veritabanından al (güvenlik için)
        $unitPrice = (float) $product['price'];
        $subtotal = $unitPrice * $quantity;

        $detailId = $crud->create('OrderDetails', [
            'order_id' => $orderId,
            'product_id' => $productId,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'subtotal' => $subtotal,
            'special_instructions' => $specialInstructions ?: $orderNote
        ]);

        if (!$detailId) {
            $crud->rollback();
            jsonResponse(false, 'Sipariş kalemleri eklenemedi');
        }
    }

    // Toplam tutarı hesapla
    $totalRow = $crud->customQuery(
        'SELECT COALESCE(SUM(subtotal), 0) AS total FROM OrderDetails WHERE order_id = :id',
        [':id' => $orderId]
    );
    $totalAmount = $totalRow[0]['total'] ?? 0;

    // Sipariş notunu güncelle (eğer varsa)
    if ($orderNote) {
        // OrderDetails'e özel not eklenmişti, eğer genel not varsa ilk kaleme ekle
        $firstDetail = $crud->readOne('OrderDetails', 'order_id = :id', [':id' => $orderId]);
        if ($firstDetail && !$firstDetail['special_instructions']) {
            $crud->update('OrderDetails', 
                ['special_instructions' => $orderNote], 
                'order_detail_id = :id', 
                [':id' => $firstDetail['order_detail_id']]
            );
        }
    }

    $crud->commit();

    jsonResponse(true, 'Siparişiniz başarıyla oluşturuldu', [
        'order_id' => $orderId,
        'total_amount' => $totalAmount,
        'table_number' => $tableNumber
    ]);

} catch (Exception $e) {
    if (isset($crud)) {
        $crud->rollback();
    }
    // Hata loglama
    error_log("Order Create Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    jsonResponse(false, 'Hata: ' . $e->getMessage());
} catch (PDOException $e) {
    if (isset($crud)) {
        $crud->rollback();
    }
    // Veritabanı hatası loglama
    error_log("Order Create DB Error: " . $e->getMessage());
    error_log("SQL State: " . $e->getCode());
    jsonResponse(false, 'Veritabanı hatası: ' . $e->getMessage());
}
?>
