<?php
/**
 * Stok azaltma testi
 * Trigger'ın çalışıp çalışmadığını kontrol eder
 */

require_once __DIR__ . '/includes/cruds.php';

try {
    $crud = new CRUD();
    
    echo "=== STOK AZALTMA TESTİ ===\n\n";
    
    // 1. Trigger'ın varlığını kontrol et
    echo "1. Trigger kontrolü...\n";
    $triggers = $crud->customQuery("SHOW TRIGGERS LIKE 'OrderDetails'");
    if (empty($triggers)) {
        echo "❌ Trigger bulunamadı! Trigger'ı oluşturmanız gerekiyor.\n";
    } else {
        echo "✓ Trigger bulundu:\n";
        foreach ($triggers as $trigger) {
            echo "  - " . $trigger['Trigger'] . " (" . $trigger['Event'] . ")\n";
        }
    }
    echo "\n";
    
    // 2. ProductIngredients kayıtlarını kontrol et
    echo "2. Ürün içerik kayıtları kontrolü...\n";
    $productsWithIngredients = $crud->customQuery("
        SELECT 
            mp.product_id,
            mp.product_name,
            COUNT(pi.ingredient_id) as ingredient_count
        FROM MenuProducts mp
        LEFT JOIN ProductIngredients pi ON mp.product_id = pi.product_id
        GROUP BY mp.product_id, mp.product_name
        ORDER BY mp.product_id
    ");
    
    $productsWithoutIngredients = [];
    foreach ($productsWithIngredients as $product) {
        if ($product['ingredient_count'] == 0) {
            $productsWithoutIngredients[] = $product;
            echo "  ⚠ " . $product['product_name'] . " (ID: " . $product['product_id'] . ") - İçerik yok!\n";
        } else {
            echo "  ✓ " . $product['product_name'] . " (ID: " . $product['product_id'] . ") - " . $product['ingredient_count'] . " içerik\n";
        }
    }
    echo "\n";
    
    // 3. Mevcut stok durumunu göster
    echo "3. Mevcut stok durumu (ilk 5 malzeme)...\n";
    $stocks = $crud->customQuery("
        SELECT 
            i.ingredient_name,
            s.quantity,
            s.minimum_quantity
        FROM Stocks s
        JOIN Ingredients i ON s.ingredient_id = i.ingredient_id
        ORDER BY i.ingredient_name
        LIMIT 5
    ");
    foreach ($stocks as $stock) {
        echo "  - " . $stock['ingredient_name'] . ": " . $stock['quantity'] . " " . $stock['minimum_quantity'] . " (min)\n";
    }
    echo "\n";
    
    // 4. Son siparişleri kontrol et
    echo "4. Son siparişler (son 3 sipariş)...\n";
    $recentOrders = $crud->customQuery("
        SELECT 
            o.order_id,
            o.status,
            o.total_amount,
            COUNT(od.order_detail_id) as item_count
        FROM Orders o
        LEFT JOIN OrderDetails od ON o.order_id = od.order_id
        GROUP BY o.order_id, o.status, o.total_amount
        ORDER BY o.order_id DESC
        LIMIT 3
    ");
    foreach ($recentOrders as $order) {
        echo "  - Sipariş #" . $order['order_id'] . ": " . $order['status'] . " - " . $order['item_count'] . " kalem - " . $order['total_amount'] . " TL\n";
    }
    echo "\n";
    
    // 5. Son stok hareketlerini kontrol et
    echo "5. Son stok hareketleri (son 5 hareket)...\n";
    $movements = $crud->customQuery("
        SELECT 
            sm.movement_id,
            i.ingredient_name,
            sm.movement_type,
            sm.quantity,
            sm.note,
            sm.created_at
        FROM StockMovements sm
        JOIN Ingredients i ON sm.ingredient_id = i.ingredient_id
        ORDER BY sm.created_at DESC
        LIMIT 5
    ");
    if (empty($movements)) {
        echo "  ⚠ Hiç stok hareketi kaydı yok! Bu, trigger'ın çalışmadığını gösterir.\n";
    } else {
        foreach ($movements as $movement) {
            echo "  - " . $movement['ingredient_name'] . ": " . $movement['movement_type'] . " " . $movement['quantity'] . " (" . $movement['note'] . ") - " . $movement['created_at'] . "\n";
        }
    }
    echo "\n";
    
    // 6. Test siparişi için öneri
    if (!empty($productsWithoutIngredients)) {
        echo "⚠ UYARI: İçeriği olmayan ürünler var. Bu ürünler için stok azalmayacak!\n";
        echo "   İçerik eklemek için: php add_missing_ingredients.php\n\n";
    }
    
    echo "=== TEST TAMAMLANDI ===\n";
    
} catch (Exception $e) {
    echo "❌ Hata: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

