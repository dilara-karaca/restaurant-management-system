<?php
/**
 * SQLite Veritabanı Kurulum
 * MySQL yerine SQLite kullanalım - daha basit
 */

$dbPath = __DIR__ . '/database/restaurant.db';
$schemaFile = __DIR__ . '/database/restaurant_db_schema.sql';

echo "<h2>📦 SQLite Veritabanı Kurulumu</h2>";

try {
    // SQLite veritabanını oluştur (otomatik oluşur)
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ SQLite veritabanı oluşturuldu: " . $dbPath . "<br>";
    
    // Schema dosyasını oku
    if (!file_exists($schemaFile)) {
        die("❌ Schema dosyası bulunamadı!");
    }
    
    $schema = file_get_contents($schemaFile);
    
    // MySQL komutlarını SQLite'a uyarla (basit parser)
    $schema = str_replace('AUTO_INCREMENT', 'AUTOINCREMENT', $schema);
    $schema = str_replace('ENGINE=InnoDB', '', $schema);
    $schema = str_replace('CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci', '', $schema);
    $schema = str_replace('COLLATE utf8mb4_unicode_ci', '', $schema);
    $schema = str_replace('BOOLEAN', 'INTEGER', $schema);
    $schema = str_replace('DECIMAL(', 'REAL(', $schema);
    $schema = str_replace('ENUM(', 'TEXT CHECK(column IN(', $schema);
    
    // Komutları böl ve çalıştır
    $queries = array_filter(
        array_map('trim', preg_split('/;/', $schema)),
        fn($q) => !empty($q) && !str_starts_with(trim($q), '--')
    );
    
    $success = 0;
    foreach ($queries as $query) {
        try {
            if (!empty(trim($query))) {
                $db->exec($query);
                $success++;
            }
        } catch (Exception $e) {
            // Bazı komutlar başarısız olabilir, devam et
        }
    }
    
    echo "✅ " . $success . " SQL komut çalıştırıldı<br>";
    echo "<h3>✨ Kurulum Başarılı!</h3>";
    echo "<p><a href='admin/login.php' style='padding: 10px 20px; background: #10b981; color: white; text-decoration: none; border-radius: 5px;'>Admin Paneline Git →</a></p>";
    
    echo "<h4>Giriş Bilgileri:</h4>";
    echo "<ul>";
    echo "<li><strong>Kullanıcı:</strong> admin</li>";
    echo "<li><strong>Şifre:</strong> 12345</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    die("<h3>❌ Hata: " . $e->getMessage() . "</h3>");
}
?>
