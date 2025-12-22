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
    echo "<h3>✨ Kurulum Tamamlandı!</h3>";
    echo "<p>$successCount / " . count($queries) . " komut başarıyla çalıştırıldı.</p>";
    echo "<p><a href='admin/login.php'>👉 Admin Paneline Git</a></p>";
    
    // Varsayılan hesapları göster
    echo "<h3>📝 Varsayılan Hesaplar:</h3>";
    echo "<ul>";
    echo "<li><strong>Admin</strong> - Kullanıcı: admin | Şifre: password</li>";
    echo "<li><strong>Manager</strong> - Kullanıcı: manager1 | Şifre: password</li>";
    echo "<li><strong>Waiter</strong> - Kullanıcı: waiter1 | Şifre: password</li>";
    echo "<li><strong>Customer</strong> - Kullanıcı: customer1 | Şifre: password</li>";
    echo "</ul>";
    
} catch (PDOException $e) {
    die("❌ Veritabanı hatası: " . htmlspecialchars($e->getMessage()));
}
?>
```