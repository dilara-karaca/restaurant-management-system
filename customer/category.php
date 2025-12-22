<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");

require_once __DIR__ . '/../config/database.php';

$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

if ($category_id <= 0) {
    header("Location: menu.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Get category info
$query = "SELECT * FROM MenuCategories WHERE category_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$category_id]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    header("Location: menu.php");
    exit;
}

// Get products in this category
$query = "SELECT product_id, product_name, description, price, image_url FROM MenuProducts WHERE category_id = ? AND is_available = 1 ORDER BY product_id";
$stmt = $db->prepare($query);
$stmt->execute([$category_id]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Turkish category names
$categoryNames = [
    'Appetizers' => 'Başlangıçlar',
    'Soups' => 'Çorbalar',
    'Main Courses' => 'Ana Yemekler',
    'Desserts' => 'Tatlılar',
    'Beverages' => 'İçecekler',
];

// Turkish product translations
$productTranslations = [
    // Appetizers
    'Caesar Salad' => ['Sezar Salatası', 'Taze lahana, parmesan ve croutonlar ile'],
    'Bruschetta' => ['Bruşetta', 'Taze domates ve fesleğenle hazırlanmış'],
    'Chicken Wings' => ['Tavuk Kanat', 'Baharatlı ızgara tavuk kanatlı'],
    'French Fries' => ['Patates Kızartması', 'Çıtır altın patates kızartması'],
    
    // Soups
    'Tomato Soup' => ['Domates Çorbası', 'Fesleğenli kremalı domates çorbası'],
    'Chicken Soup' => ['Tavuk Çorbası', 'Sebzeli ev yapımı tavuk çorbası'],

    
    // Main Courses
    'Grilled Chicken' => ['Izgara Tavuk', 'Taze sebzeler ve patates ile'],
    'Spaghetti Bolognese' => ['Spagetti Bolognese', 'Klasik pasta et sosuyla'],
    'Margherita Pizza' => ['Margarita Pizza', 'Taze mozzarella, domates ve fesleğen ile'],
    'Cheeseburger' => ['Cheese Burger', 'Peynirli burger ve patates kızartması ile'],
    'Beef Steak' => ['Sığır Bifteği', 'Premium et, patates kızartması ile'],
    
    // Desserts
    'Chocolate Cake' => ['Çikolatalı Kek', 'Yumuşak çikolatalı kek'],
    'Tiramisu' => ['Tiramisu', 'İtalyan tatlısı, kahveli'],
    'Cheesecake' => ['Cheesecake', 'Taze peynirli kremalı kek'],
    
    // Beverages
    'Coca Cola' => ['Kola', 'Soğuk gazlı içecek (330ml)'],
    'Fresh Orange Juice' => ['Taze Portakal Suyu', 'Taze sıkılmış portakal suyu'],
    'Iced Tea' => ['Soğuk Çay', 'Soğuk çay'],
    'Turkish Coffee' => ['Türk Kahvesi', 'Geleneksel Türk kahvesi'],
];

$tr_name = $categoryNames[$category['category_name']] ?? $category['category_name'];

// Translate products
foreach ($products as &$product) {
    if (isset($productTranslations[$product['product_name']])) {
        $product['tr_name'] = $productTranslations[$product['product_name']][0];
        $product['tr_desc'] = $productTranslations[$product['product_name']][1];
    } else {
        $product['tr_name'] = $product['product_name'];
        $product['tr_desc'] = $product['description'];
    }
}
unset($product);

$title = $tr_name . ' - Restoran';
$bodyClass = 'page-customer';
require_once __DIR__ . '/../includes/layout/top.php';
?>

<main class="app">
    <div class="customer-container">
        <!-- Header with back button -->
        <header class="category-header">
            <div class="header-nav">
                <a href="menu.php" class="back-btn">
                    <span class="back-icon">←</span>
                    <span class="back-text">Menüye Dön</span>
                </a>
            </div>
            <h1><?= htmlspecialchars($tr_name) ?></h1>
            <p class="category-desc"><?= htmlspecialchars($category['description'] ?? '') ?></p>
        </header>

        <!-- Products Grid -->
        <div class="products-grid">
            <?php if (empty($products)): ?>
                <div class="empty-state">
                    <p>📭 Bu kategoride ürün bulunamadı.</p>
                </div>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <?php if ($product['image_url']): ?>
                            <div class="product-image">
                                <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>">
                            </div>
                        <?php endif; ?>
                        <div class="product-content">
                            <h3 class="product-name"><?= htmlspecialchars($product['tr_name']) ?></h3>
                            <?php if ($product['tr_desc']): ?>
                                <p class="product-desc"><?= htmlspecialchars($product['tr_desc']) ?></p>
                            <?php endif; ?>
                            <div class="product-footer">
                                <span class="product-price"><?= number_format($product['price'], 2) ?> ₺</span>
                                <button class="add-to-cart-btn">Sepete Ekle</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<style>
    .customer-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 24px;
    }

    .category-header {
        margin-bottom: 32px;
    }

    .header-nav {
        margin-bottom: 16px;
    }

    .back-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.15) 0%, rgba(16, 185, 129, 0.15) 100%);
        color: var(--primary, #2563eb);
        text-decoration: none;
        font-weight: 600;
        padding: 8px 12px;
        border-radius: 8px;
        border: 1px solid rgba(37, 99, 235, 0.3);
        transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
        font-size: 13px;
        cursor: pointer;
    }

    .back-btn:hover {
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.25) 0%, rgba(16, 185, 129, 0.25) 100%);
        border-color: var(--primary, #2563eb);
        transform: translateX(-4px);
    }

    .back-icon {
        font-size: 18px;
        transition: transform 0.3s ease;
    }

    .back-btn:hover .back-icon {
        transform: translateX(-2px);
    }

    .category-header h1 {
        margin: 12px 0 8px;
        font-size: 32px;
        font-weight: 700;
        color: var(--text, #e2e8f0);
        background: linear-gradient(135deg, #2563eb, #10b981);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .category-desc {
        margin: 0;
        color: var(--muted, #94a3b8);
        font-size: 16px;
    }

    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 20px;
        margin-bottom: 32px;
    }

    .empty-state {
        grid-column: 1 / -1;
        text-align: center;
        padding: 64px 24px;
        color: var(--muted);
        font-size: 18px;
    }

    .product-card {
        background: linear-gradient(135deg, rgba(255,255,255,0.05) 0%, rgba(255,255,255,0.02) 100%);
        border: 1px solid var(--line, rgba(148, 163, 184, 0.2));
        border-radius: 16px;
        overflow: hidden;
        transition: all 0.35s cubic-bezier(0.23, 1, 0.32, 1);
        display: flex;
        flex-direction: column;
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.12);
        position: relative;
    }

    .product-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 16px 48px rgba(0, 0, 0, 0.2);
        border-color: var(--primary, #2563eb);
    }

    .product-image {
        width: 100%;
        height: 180px;
        overflow: hidden;
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.1) 0%, rgba(16, 185, 129, 0.1) 100%);
    }

    .product-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s cubic-bezier(0.23, 1, 0.32, 1);
    }

    .product-card:hover .product-image img {
        transform: scale(1.08);
    }

    .product-content {
        padding: 16px;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }

    .product-name {
        margin: 0 0 8px;
        font-size: 16px;
        font-weight: 700;
        color: var(--text, #e2e8f0);
    }

    .product-desc {
        margin: 0 0 12px;
        font-size: 13px;
        color: var(--muted, #94a3b8);
        flex-grow: 1;
        line-height: 1.4;
    }

    .product-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: auto;
        gap: 12px;
    }

    .product-price {
        font-size: 18px;
        font-weight: 700;
        color: var(--primary, #2563eb);
        white-space: nowrap;
    }

    .add-to-cart-btn {
        background: linear-gradient(135deg, var(--primary, #2563eb) 0%, #10b981 100%);
        color: white;
        border: none;
        padding: 8px 12px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
        font-size: 12px;
        white-space: nowrap;
    }

    .add-to-cart-btn:hover {
        transform: scale(1.05);
        box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .product-card {
        animation: fadeIn 0.6s ease-out;
    }

    @media (max-width: 768px) {
        .customer-container {
            padding: 16px;
        }

        .category-header h1 {
            font-size: 24px;
        }

        .products-grid {
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 16px;
        }

        .product-image {
            height: 140px;
        }

        .product-name {
            font-size: 14px;
        }

        .product-desc {
            font-size: 12px;
        }

        .product-price {
            font-size: 16px;
        }

        .add-to-cart-btn {
            font-size: 11px;
            padding: 6px 10px;
        }
    }
</style>

<?php require_once __DIR__ . '/../includes/layout/bottom.php'; ?>
