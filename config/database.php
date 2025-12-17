<?php
/**
 * Database Connection Class
 * PDO ile güvenli MySQL bağlantısı
 */
class Database {
    // Veritabanı bilgileri
    private $host = "localhost";
    private $db_name = "restaurant_db";
    private $username = "root";
    private $password = "123456";
    private $conn;

    /**
     * Veritabanı bağlantısını döndürür
     * @return PDO|null
     */
    public function getConnection() {
        $this->conn = null;

        try {
            // PDO bağlantısı oluştur
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password
            );
            
            // Hata modunu exception olarak ayarla
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Fetch modunu associative array olarak ayarla
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            // Emulated prepares'i kapat (güvenlik)
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            
        } catch(PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
            return null;
        }

        return $this->conn;
    }

    /**
     * Bağlantıyı kapat
     */
    public function closeConnection() {
        $this->conn = null;
    }
}
?>