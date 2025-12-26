<?php
/**
 * Belirli bir sipariş için stok azalmasını kontrol eder
 */

require_once __DIR__ . '/includes/cruds.php';

try {
    $crud = new CRUD();
    
    echo "=== SİPARİŞ STOK KONTROLÜ ===\n\n";
    
    // En son siparişi al
    $lastOrder = $crud->customQuery("
        SELECT order_id, order_date, status, total_amount
        FROM Orders
        ORDER BY order_id DESC
        LIMIT 1
    ");
    
    if (empty($lastOrder)) {
        echo "❌ Hiç sipariş bulunamadı!\n";
        exit;
    }
    
    $orderId = $lastOrder[0]['order_id'];
    echo "En son sipariş: #" . $orderId . "\n";
    echo "Tarih: " . $lastOrder[0]['order_date'] . "\n";
    echo "Durum: " . $lastOrder[0]['status'] . "\n";
    echo "Toplam: " . $lastOrder[0]['total_amount'] . " TL\n\n";
    
    // Sipariş kalemlerini al
    echo "Sipariş Kalemleri:\n";
    $orderItems = $crud->customQuery("
        SELECT 
            od.order_detail_id,
            od.product_id,
            mp.product_name,
            od.quantity,
            od.unit_price,
            od.subtotal
        FROM OrderDetails od
        JOIN MenuProducts mp ON od.product_id = mp.product_id
        WHERE od.order_id = :order_id
    ", [':order_id' => $orderId]);
    
    foreach ($orderItems as $item) {
        echo "  - " . $item['product_name'] . " (ID: " . $item['product_id'] . ") x " . $item['quantity'] . " = " . $item['subtotal'] . " TL\n";
        
        // Bu ürünün içeriklerini kontrol et
        $ingredients = $crud->customQuery("
            SELECT 
                pi.ingredient_id,
                i.ingredient_name,
                pi.quantity_required,
                (pi.quantity_required * :qty) as total_needed
            FROM ProductIngredients pi
            JOIN Ingredients i ON pi.ingredient_id = i.ingredient_id
            WHERE pi.product_id = :product_id
        ", [
            ':product_id' => $item['product_id'],
            ':qty' => $item['quantity']
        ]);
        
        if (empty($ingredients)) {
            echo "    ⚠ UYARI: Bu ürünün içeriği yok! Stok azalmayacak.\n";
        } else {
            echo "    İçerikler:\n";
            foreach ($ingredients as $ing) {
                echo "      - " . $ing['ingredient_name'] . ": " . $ing['quantity_required'] . " x " . $item['quantity'] . " = " . $ing['total_needed'] . "\n";
            }
        }
    }
    
    echo "\n";
    
    // Bu sipariş için stok hareketlerini kontrol et
    echo "Bu sipariş için stok hareketleri:\n";
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
        WHERE sm.note LIKE :note
        ORDER BY sm.created_at DESC
    ", [':note' => '%Order #' . $orderId . '%']);
    
    if (empty($movements)) {
        echo "  ⚠ UYARI: Bu sipariş için hiç stok hareketi yok!\n";
        echo "  Bu, ürünlerin içeriği olmadığı veya trigger'ın çalışmadığı anlamına gelir.\n";
    } else {
        echo "  ✓ " . count($movements) . " stok hareketi bulundu:\n";
        foreach ($movements as $movement) {
            echo "    - " . $movement['ingredient_name'] . ": " . $movement['movement_type'] . " " . $movement['quantity'] . " (" . $movement['created_at'] . ")\n";
        }
    }
    
    echo "\n";
    
    // Stok durumunu kontrol et (sipariş öncesi ve sonrası karşılaştırma için)
    echo "Mevcut stok durumu (siparişte kullanılan malzemeler):\n";
    if (!empty($orderItems)) {
        foreach ($orderItems as $item) {
            $ingredients = $crud->customQuery("
                SELECT 
                    pi.ingredient_id,
                    i.ingredient_name,
                    s.quantity as current_stock,
                    s.minimum_quantity
                FROM ProductIngredients pi
                JOIN Ingredients i ON pi.ingredient_id = i.ingredient_id
                LEFT JOIN Stocks s ON pi.ingredient_id = s.ingredient_id
                WHERE pi.product_id = :product_id
            ", [':product_id' => $item['product_id']]);
            
            foreach ($ingredients as $ing) {
                echo "  - " . $ing['ingredient_name'] . ": " . $ing['current_stock'] . " (min: " . $ing['minimum_quantity'] . ")\n";
            }
        }
    }
    
    echo "\n=== KONTROL TAMAMLANDI ===\n";
    
} catch (Exception $e) {
    echo "❌ Hata: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

