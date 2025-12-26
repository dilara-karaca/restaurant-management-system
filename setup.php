<?php
/**
 * Veritabanı kurulum dosyası
 * Şemayı ve örnek verileri yükler
 */

// Database connection (veritabanı kurulmadan önce bağlanmak için root kullanıcı)
$host = "localhost";
$username = "root";
$password = "mysql"; // Ampps varsayılan şifresi

echo "<h2>🔧 MySQL Bağlantısı Deneniyor...</h2>";
echo "<p>Host: " . $host . " | Kullanıcı: " . $username . "</p>";

$conn = null;

// Ampps MySQL parolası
$passwords = ["mysql", "", "ampps", "password", "123456"];

foreach ($passwords as $pwd) {
    try {
        $conn = new PDO("mysql:host=" . $host, $username, $pwd, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        echo "✅ Bağlantı başarılı! (Parola: '" . $pwd . "')<br>";
        $password = $pwd;
        break;
    } catch (PDOException $e) {
        echo "⚠️ Parola '" . $pwd . "' başarısız<br>";
    }
}

if (!$conn) {
    die("<h3>❌ MySQL'e bağlanamadık!</h3><p>Ampps'te MySQL servisinin çalışıyor olduğundan emin olun. Ampps tray menüsü → Start MySQL</p>");
}

try {
    // Veritabanı var mı kontrol et
    $conn->exec("USE restaurant_db");
    echo "<h2>✅ Veritabanı zaten var</h2>";
    echo "<p><a href='admin/login.php'>👉 Admin Paneline Git</a></p>";
    exit;
} catch (PDOException $e) {
    echo "<h2>📦 Yeni veritabanı oluşturuluyor...</h2>";
}

try {
    // Şema dosyasını oku
    $schemaFile = __DIR__ . '/database/restaurant_db_schema.sql';
    
    if (!file_exists($schemaFile)) {
        die("❌ Şema dosyası bulunamadı: " . $schemaFile);
    }
    
    $schemaContent = file_get_contents($schemaFile);
    
    // SQL komutlarını parçala (çok basit parser)
    $queries = array_filter(
        array_map('trim', preg_split('/;/', $schemaContent)),
        fn($query) => !empty($query) && !str_starts_with(trim($query), '--')
    );
    
    echo "<h2>🚀 Veritabanı Kurulumu Başladı</h2>";
    echo "<p>Toplam " . count($queries) . " komut çalıştırılacak...</p>";
    echo "<hr>";
    
    $successCount = 0;
    foreach ($queries as $query) {
        try {
            $conn->exec($query);
            $successCount++;
            echo "✅ Komut başarılı<br>";
        } catch (PDOException $e) {
            echo "⚠️ Komut hatası: " . htmlspecialchars($e->getMessage()) . "<br>";
        }
    }
    
    echo "<hr>";
    echo "<h3>✨ Veritabanı Kurulumu Tamamlandı!</h3>";
    echo "<p>$successCount / " . count($queries) . " komut başarıyla çalıştırıldı.</p>";
    
    // Post-setup kontrollerini yap
    echo "<hr>";
    echo "<h3>🔧 Post-Setup Kontrolleri</h3>";
    echo "<p>Trigger'lar ve ürün içerikleri kontrol ediliyor...</p>";
    
    try {
        require_once __DIR__ . '/includes/cruds.php';
        $crud = new CRUD();
        
        // Trigger kontrolü
        $triggers = $crud->customQuery("SHOW TRIGGERS LIKE 'OrderDetails'");
        $triggerCount = count($triggers);
        if ($triggerCount >= 3) {
            echo "<p style='color: green;'>✅ Trigger'lar kurulu ($triggerCount adet)</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Eksik trigger'lar olabilir. Post-setup script'ini çalıştırın.</p>";
        }
        
        // İçerik kontrolü
        $productsWithoutIngredients = $crud->customQuery("
            SELECT COUNT(*) as count
            FROM MenuProducts mp
            LEFT JOIN ProductIngredients pi ON mp.product_id = pi.product_id
            GROUP BY mp.product_id
            HAVING COUNT(pi.ingredient_id) = 0
        ");
        
        $missingCount = count($productsWithoutIngredients);
        if ($missingCount == 0) {
            echo "<p style='color: green;'>✅ Tüm ürünlerin içeriği tanımlı</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ $missingCount ürünün içeriği eksik. Post-setup script'ini çalıştırın.</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠️ Post-setup kontrolleri yapılamadı: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    echo "<hr>";
    echo "<h3>📝 Sonraki Adımlar</h3>";
    echo "<ol>";
    echo "<li><strong>Post-Setup Kontrolleri:</strong> <a href='post_setup.php' style='color: #3b82f6;'>post_setup.php</a> dosyasını çalıştırın</li>";
    echo "<li>Eksik ürün içerikleri varsa otomatik olarak eklenecektir</li>";
    echo "<li>Kurulum tamamlandıktan sonra sistemi kullanabilirsiniz</li>";
    echo "</ol>";
    
    echo "<p><a href='post_setup.php' style='display: inline-block; padding: 10px 20px; background: #10b981; color: white; text-decoration: none; border-radius: 4px; margin: 10px 0;'>🔧 Post-Setup Kontrollerini Çalıştır</a></p>";
    
    echo "<hr>";
    echo "<h3>📝 Varsayılan Hesaplar:</h3>";
    echo "<ul>";
    echo "<li><strong>Admin</strong> - Kullanıcı: admin | Şifre: password</li>";
    echo "<li><strong>Manager</strong> - Kullanıcı: manager1 | Şifre: password</li>";
    echo "<li><strong>Waiter</strong> - Kullanıcı: waiter1 | Şifre: password</li>";
    echo "<li><strong>Customer</strong> - Kullanıcı: customer1 | Şifre: password</li>";
    echo "</ul>";
    
    echo "<p><a href='admin/login.php'>👉 Admin Paneline Git</a></p>";
    
} catch (PDOException $e) {
    die("❌ Veritabanı hatası: " . htmlspecialchars($e->getMessage()));
}
?>
```