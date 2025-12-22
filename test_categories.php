<?php
require_once __DIR__ . '/config/database.php';

$database = new Database();
$db = $database->getConnection();

$query = "SELECT category_id, category_name FROM MenuCategories ORDER BY category_id";
$stmt = $db->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>Veritabanında Kategoriler:</h2>";
echo "<pre>";
print_r($categories);
echo "</pre>";

echo "<h2>Kategoriler (after filtering):</h2>";
$seen = array();
$unique = array();
foreach ($categories as $cat) {
    if (!in_array($cat['category_name'], $seen)) {
        $unique[] = $cat;
        $seen[] = $cat['category_name'];
    }
}
print_r($unique);
?>
