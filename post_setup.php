<?php
/**
 * Post-setup script
 * Kurulum sonrası gerekli kontrolleri ve eklemeleri yapar:
 * - Trigger'ları kontrol eder ve kurar
 * - Eksik ürün içeriklerini ekler
 */

require_once __DIR__ . '/includes/cruds.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Post-Setup Kontrolleri</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h2 { color: #333; }
        .success { color: #10b981; }
        .warning { color: #f59e0b; }
        .error { color: #ef4444; }
        .info { color: #3b82f6; }
        pre { background: #f3f4f6; padding: 10px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Post-Setup Kontrolleri</h1>
        <hr>

<?php
try {
    $crud = new CRUD();
    
    echo "<h2>1. Trigger Kontrolü</h2>";
    
    // Trigger'ları kontrol et
    $triggers = $crud->customQuery("SHOW TRIGGERS LIKE 'OrderDetails'");
    $requiredTriggers = ['trg_orderdetails_ai', 'trg_orderdetails_au', 'trg_orderdetails_ad'];
    $existingTriggers = array_column($triggers, 'Trigger');
    
    $missingTriggers = array_diff($requiredTriggers, $existingTriggers);
    
    if (empty($missingTriggers)) {
        echo "<p class='success'>✅ Tüm trigger'lar kurulu</p>";
    } else {
        echo "<p class='warning'>⚠️ Eksik trigger'lar: " . implode(', ', $missingTriggers) . "</p>";
        echo "<p class='info'>ℹ️ Trigger'lar veritabanı şemasında tanımlı. Eğer eksikse, setup.php'yi tekrar çalıştırın.</p>";
    }
    
    echo "<hr>";
    echo "<h2>2. Ürün İçerik Kontrolü</h2>";
    
    // İçeriği olmayan ürünleri kontrol et
    $productsWithoutIngredients = $crud->customQuery("
        SELECT 
            mp.product_id,
            mp.product_name,
            COUNT(pi.ingredient_id) as ingredient_count
        FROM MenuProducts mp
        LEFT JOIN ProductIngredients pi ON mp.product_id = pi.product_id
        GROUP BY mp.product_id, mp.product_name
        HAVING ingredient_count = 0
    ");
    
    if (empty($productsWithoutIngredients)) {
        echo "<p class='success'>✅ Tüm ürünlerin içeriği tanımlı</p>";
    } else {
        echo "<p class='warning'>⚠️ İçeriği olmayan " . count($productsWithoutIngredients) . " ürün bulundu:</p>";
        echo "<ul>";
        foreach ($productsWithoutIngredients as $product) {
            echo "<li>" . htmlspecialchars($product['product_name']) . " (ID: " . $product['product_id'] . ")</li>";
        }
        echo "</ul>";
        
        echo "<p class='info'>ℹ️ Eksik içerikleri eklemek için aşağıdaki butona tıklayın:</p>";
        echo "<form method='POST' style='margin: 20px 0;'>";
        echo "<button type='submit' name='add_ingredients' style='padding: 10px 20px; background: #10b981; color: white; border: none; border-radius: 4px; cursor: pointer;'>Eksik İçerikleri Ekle</button>";
        echo "</form>";
        
        if (isset($_POST['add_ingredients'])) {
            echo "<hr>";
            echo "<h3>İçerikler ekleniyor...</h3>";
            echo "<pre>";
            
            // add_missing_ingredients.php script'ini çalıştır
            ob_start();
            include __DIR__ . '/add_missing_ingredients.php';
            $output = ob_get_clean();
            
            echo htmlspecialchars($output);
            echo "</pre>";
            
            echo "<p class='success'>✅ İşlem tamamlandı! Sayfayı yenileyin.</p>";
            echo "<script>setTimeout(function(){ location.reload(); }, 2000);</script>";
        }
    }
    
    echo "<hr>";
    echo "<h2>3. Stok Durumu</h2>";
    
    // Stok durumunu kontrol et
    $lowStock = $crud->customQuery("
        SELECT 
            i.ingredient_name,
            s.quantity,
            s.minimum_quantity
        FROM Stocks s
        JOIN Ingredients i ON s.ingredient_id = i.ingredient_id
        WHERE s.quantity <= s.minimum_quantity
        LIMIT 5
    ");
    
    if (empty($lowStock)) {
        echo "<p class='success'>✅ Tüm stoklar yeterli seviyede</p>";
    } else {
        echo "<p class='warning'>⚠️ Düşük stoklu malzemeler:</p>";
        echo "<ul>";
        foreach ($lowStock as $stock) {
            echo "<li>" . htmlspecialchars($stock['ingredient_name']) . ": " . $stock['quantity'] . " (min: " . $stock['minimum_quantity'] . ")</li>";
        }
        echo "</ul>";
    }
    
    echo "<hr>";
    echo "<h2>✅ Kontroller Tamamlandı</h2>";
    echo "<p>Eğer tüm kontroller başarılıysa, sistem kullanıma hazırdır.</p>";
    echo "<p><a href='admin/login.php'>👉 Admin Paneline Git</a></p>";
    echo "<p><a href='customer/menu.php'>👉 Müşteri Menüsüne Git</a></p>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Hata: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>

    </div>
</body>
</html>

