<?php
// Basit test dosyası - localhost çalışıyor mu kontrol et
echo "<h2>🔍 Sistem Kontrol</h2>";

// 1. PHP versiyonu
echo "<p><strong>PHP:</strong> " . PHP_VERSION . "</p>";

// 2. Port 3306 açık mı
$connection = @fsockopen("localhost", 3306, $errno, $errstr, 2);
if ($connection) {
    echo "<p><strong>✅ MySQL Port (3306):</strong> Açık</p>";
    fclose($connection);
} else {
    echo "<p><strong>❌ MySQL Port (3306):</strong> Kapalı/Erişilemez</p>";
}

// 3. PDO MySQL extension var mı
echo "<p><strong>PDO MySQL Extension:</strong> " . (extension_loaded('pdo_mysql') ? '✅ Var' : '❌ Yok') . "</p>";

// 4. MySQLi extension var mı
echo "<p><strong>MySQLi Extension:</strong> " . (extension_loaded('mysqli') ? '✅ Var' : '❌ Yok') . "</p>";

// 5. PDO test
echo "<h3>PDO Bağlantı Testi:</h3>";
try {
    $pdo = new PDO("mysql:host=localhost;port=3306", "root", "");
    echo "<p>✅ MySQL'e başarıyla bağlandı!</p>";
} catch (PDOException $e) {
    echo "<p>❌ Hata: " . $e->getMessage() . "</p>";
}

// 6. Dosya sistemi test
echo "<h3>Dosya Sistemi:</h3>";
echo "<p><strong>Setup.php var mı:</strong> " . (file_exists(__DIR__ . '/setup.php') ? '✅' : '❌') . "</p>";
echo "<p><strong>Database klasörü var mı:</strong> " . (is_dir(__DIR__ . '/database') ? '✅' : '❌') . "</p>";
echo "<p><strong>Schema dosyası var mı:</strong> " . (file_exists(__DIR__ . '/database/restaurant_db_schema.sql') ? '✅' : '❌') . "</p>";
?>
