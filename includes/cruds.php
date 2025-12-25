<?php
require_once __DIR__ . '/../config/database.php';

/**
 * CRUD Operations Class
 * Temel veritabanı işlemleri için generic class
 */
class CRUD {
    private $conn;
    private $db;
    
    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    /**
     * SELECT - Tüm kayıtları getir
     * @param string $table Tablo adı
     * @param string $conditions WHERE koşulları (opsiyonel)
     * @param array $params Prepared statement parametreleri
     * @param string $orderBy ORDER BY ifadesi (opsiyonel)
     * @return array|false
     */
    public function read($table, $conditions = "", $params = [], $orderBy = "") {
        try {
            $query = "SELECT * FROM " . $table;
            
            if (!empty($conditions)) {
                $query .= " WHERE " . $conditions;
            }
            
            if (!empty($orderBy)) {
                $query .= " ORDER BY " . $orderBy;
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
            
        } catch(PDOException $e) {
            error_log("Read Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * SELECT - Tek kayıt getir
     * @param string $table Tablo adı
     * @param string $conditions WHERE koşulları
     * @param array $params Prepared statement parametreleri
     * @return array|false
     */
    public function readOne($table, $conditions, $params = []) {
        try {
            $query = "SELECT * FROM " . $table . " WHERE " . $conditions . " LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            
            return $stmt->fetch();
            
        } catch(PDOException $e) {
            error_log("ReadOne Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * INSERT - Yeni kayıt ekle
     * @param string $table Tablo adı
     * @param array $data Eklenecek veri (kolon => değer)
     * @return int|false Son eklenen ID veya false
     */
    public function create($table, $data) {
        try {
            $columns = implode(", ", array_keys($data));
            $placeholders = ":" . implode(", :", array_keys($data));
            
            $query = "INSERT INTO " . $table . " (" . $columns . ") VALUES (" . $placeholders . ")";
            $stmt = $this->conn->prepare($query);
            
            // Parametreleri bağla
            foreach($data as $key => $value) {
                $stmt->bindValue(":" . $key, $value);
            }
            
            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            
            return false;
            
        } catch(PDOException $e) {
            error_log("Create Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * UPDATE - Kayıt güncelle
     * @param string $table Tablo adı
     * @param array $data Güncellenecek veri (kolon => değer)
     * @param string $conditions WHERE koşulları
     * @param array $whereParams WHERE parametreleri
     * @return bool
     */
    public function update($table, $data, $conditions, $whereParams = []) {
        try {
            $set = [];
            foreach(array_keys($data) as $key) {
                $set[] = $key . " = :" . $key;
            }
            $setString = implode(", ", $set);
            
            $query = "UPDATE " . $table . " SET " . $setString . " WHERE " . $conditions;
            $stmt = $this->conn->prepare($query);
            
            // Data parametrelerini bağla
            foreach($data as $key => $value) {
                $stmt->bindValue(":" . $key, $value);
            }
            
            // WHERE parametrelerini bağla
            foreach($whereParams as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            return $stmt->execute();
            
        } catch(PDOException $e) {
            error_log("Update Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * DELETE - Kayıt sil
     * @param string $table Tablo adı
     * @param string $conditions WHERE koşulları
     * @param array $params Prepared statement parametreleri
     * @return bool
     */
    public function delete($table, $conditions, $params = []) {
        try {
            $query = "DELETE FROM " . $table . " WHERE " . $conditions;
            $stmt = $this->conn->prepare($query);
            
            foreach($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            return $stmt->execute();
            
        } catch(PDOException $e) {
            error_log("Delete Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * CUSTOM QUERY - Özel sorgu çalıştır
     * @param string $query SQL sorgusu
     * @param array $params Parametreler
     * @return array|bool
     */
    public function customQuery($query, $params = []) {
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            
            // SELECT sorgusuysa sonuçları döndür
            // Başındaki boşlukları temizleyerek kontrol et
            $normalizedQuery = ltrim($query);
            $isSelectLike = stripos($normalizedQuery, 'SELECT') === 0 || stripos($normalizedQuery, 'CALL') === 0;
            if ($isSelectLike || $stmt->columnCount() > 0) {
                $rows = $stmt->fetchAll();
                $stmt->closeCursor();
                return $rows;
            }
            
            // INSERT, UPDATE, DELETE için boolean döndür
            return true;
            
        } catch(PDOException $e) {
            error_log("Custom Query Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * COUNT - Kayıt sayısını getir
     * @param string $table Tablo adı
     * @param string $conditions WHERE koşulları (opsiyonel)
     * @param array $params Parametreler
     * @return int|false
     */
    public function count($table, $conditions = "", $params = []) {
        try {
            $query = "SELECT COUNT(*) as total FROM " . $table;
            
            if (!empty($conditions)) {
                $query .= " WHERE " . $conditions;
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            
            $result = $stmt->fetch();
            return $result['total'];
            
        } catch(PDOException $e) {
            error_log("Count Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Transaction başlat
     */
    public function beginTransaction() {
        return $this->conn->beginTransaction();
    }

    /**
     * Transaction commit et
     */
    public function commit() {
        return $this->conn->commit();
    }

    /**
     * Transaction rollback yap
     */
    public function rollback() {
        return $this->conn->rollBack();
    }

    /**
     * Bağlantıyı kapat
     */
    public function __destruct() {
        $this->db->closeConnection();
    }
}
?>
