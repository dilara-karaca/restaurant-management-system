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
                                <div class="product-cart-controls" 
                                     id="cartControls_<?= $product['product_id'] ?>" 
                                     data-product-id="<?= $product['product_id'] ?>"
                                     data-product-name="<?= htmlspecialchars($product['tr_name'], ENT_QUOTES) ?>"
                                     data-product-price="<?= $product['price'] ?>"
                                     data-product-image="<?= htmlspecialchars($product['image_url'] ?? '', ENT_QUOTES) ?>">
                                    <!-- Bu içerik JavaScript ile dinamik olarak güncellenecek -->
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sipariş Butonu -->
    <button class="order-bubble" id="orderBubble" onclick="toggleOrderPanel()">
        <img src="../assets/images/serving-dishes.png" alt="Siparişler" class="order-bubble__icon">
        <span class="order-bubble__badge" id="orderBadge">0</span>
    </button>

    <!-- Sipariş Paneli -->
    <div class="order-panel" id="orderPanel">
        <div class="order-panel__overlay" onclick="toggleOrderPanel()"></div>
        <div class="order-panel__content">
            <div class="order-panel__header">
                <h2>Siparişlerim</h2>
                <button class="order-panel__close" onclick="toggleOrderPanel()">✕</button>
            </div>
            <div class="order-panel__body">
                <div class="order-panel__empty" id="emptyState">
                    <p>Henüz sipariş eklemediniz</p>
                    <small>Menüden ürün ekleyerek başlayın</small>
                </div>
                <div class="order-panel__items" id="orderItems" style="display: none;">
                    <!-- Sipariş öğeleri buraya eklenecek -->
                </div>
            </div>
            <div class="order-panel__footer" id="orderFooter" style="display: none;">
                <div class="order-panel__total">
                    <span>Toplam:</span>
                    <strong id="orderTotal">0.00 ₺</strong>
                </div>
                <button class="order-panel__checkout" onclick="showCheckoutConfirm()">Siparişi Tamamla</button>
            </div>
        </div>
    </div>

    <!-- Checkout Modalı -->
    <div class="confirm-modal" id="confirmModal">
        <div class="confirm-modal__overlay" onclick="hideCheckoutConfirm()"></div>
        <div class="confirm-modal__content">
            <h3>Siparişi Tamamla</h3>
            
            <!-- Sipariş Özeti -->
            <div class="checkout-summary">
                <div class="checkout-summary__row">
                    <span>Toplam Tutar:</span>
                    <strong id="checkoutTotal">0.00 ₺</strong>
                </div>
            </div>

            <!-- Masa Bilgisi -->
            <div class="info-message">
                <p>📍 Masa numarası otomatik olarak atanacaktır.</p>
            </div>

            <!-- Müşteri İsmi -->
            <div class="form-group">
                <label for="customerName">Adınız Soyadınız *</label>
                <input type="text" id="customerName" class="form-input" placeholder="Adınızı ve soyadınızı giriniz" required>
            </div>

            <!-- Ödeme Yöntemi -->
            <div class="form-group">
                <label for="paymentMethod">Ödeme Şekli *</label>
                <select id="paymentMethod" class="form-input" required>
                    <option value="">Seçiniz</option>
                    <option value="Cash">Nakit</option>
                    <option value="Credit Card">Kredi Kartı</option>
                    <option value="Debit Card">Banka Kartı</option>
                    <option value="Mobile Payment">Mobil Ödeme</option>
                </select>
            </div>

            <!-- Bilgi Mesajı -->
            <div class="info-message" id="paymentInfoMessage">
                <p>💡 Ödeme şeklinizi seçiniz.</p>
            </div>

            <!-- Sipariş Notu -->
            <div class="form-group">
                <label for="orderNote">Sipariş Notu (Opsiyonel)</label>
                <textarea id="orderNote" class="form-textarea" placeholder="Özel bir isteğiniz varsa buraya yazabilirsiniz..." rows="3"></textarea>
            </div>

            <div class="confirm-modal__actions">
                <button class="confirm-modal__btn confirm-modal__btn--cancel" onclick="hideCheckoutConfirm()">Vazgeç</button>
                <button class="confirm-modal__btn confirm-modal__btn--confirm" onclick="confirmCheckout()">Siparişi Onayla</button>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="toast-notification" id="toastNotification">
        <div class="toast__message">Siparişiniz başarıyla alındı!</div>
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

    .product-cart-controls {
        display: flex;
        align-items: center;
        gap: 8px;
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

    .cart-qty-controls {
        display: flex;
        align-items: center;
        gap: 8px;
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.1) 0%, rgba(16, 185, 129, 0.1) 100%);
        border: 1px solid rgba(37, 99, 235, 0.3);
        border-radius: 8px;
        padding: 4px 8px;
    }

    .cart-qty-btn {
        width: 24px;
        height: 24px;
        border-radius: 6px;
        border: 1px solid rgba(37, 99, 235, 0.3);
        background: white;
        color: var(--primary, #2563eb);
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        line-height: 1;
    }

    .cart-qty-btn:hover {
        background: linear-gradient(135deg, #2563eb, #10b981);
        color: white;
        border-color: transparent;
        transform: scale(1.1);
    }

    .cart-qty-display {
        font-size: 14px;
        font-weight: 700;
        min-width: 20px;
        text-align: center;
        color: var(--text, #e2e8f0);
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

    /* Sipariş Baloncuk Butonu */
    .order-bubble {
        position: fixed;
        bottom: 24px;
        right: 24px;
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: linear-gradient(135deg, #2563eb 0%, #10b981 100%);
        border: none;
        box-shadow: 0 8px 24px rgba(37, 99, 235, 0.4);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s cubic-bezier(0.23, 1, 0.320, 1);
        z-index: 999;
        overflow: visible;
    }

    .order-bubble:hover {
        transform: scale(1.1) translateY(-4px);
        box-shadow: 0 12px 32px rgba(37, 99, 235, 0.6);
    }

    .order-bubble:active {
        transform: scale(0.95);
    }

    .order-bubble__icon {
        width: 32px;
        height: 32px;
        object-fit: contain;
        filter: brightness(0) invert(1) drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
    }

    .order-bubble__badge {
        position: absolute;
        top: -4px;
        right: -4px;
        background: #ef4444;
        color: white;
        font-size: 12px;
        font-weight: 700;
        min-width: 24px;
        height: 24px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 6px;
        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.5);
        border: 2px solid white;
    }

    @media (max-width: 768px) {
        .order-bubble {
            bottom: 20px;
            right: 20px;
            width: 56px;
            height: 56px;
        }

        .order-bubble__icon {
            width: 28px;
            height: 28px;
        }

        .order-bubble__badge {
            min-width: 20px;
            height: 20px;
            font-size: 11px;
        }
    }

    /* Sipariş Paneli */
    .order-panel {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1000;
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .order-panel.active {
        opacity: 1;
        pointer-events: auto;
    }

    .order-panel__overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(4px);
    }

    .order-panel__content {
        position: absolute;
        top: 0;
        right: 0;
        width: 400px;
        max-width: 100%;
        height: 100%;
        background: var(--bg);
        box-shadow: -4px 0 24px rgba(0, 0, 0, 0.2);
        display: flex;
        flex-direction: column;
        transform: translateX(100%);
        transition: transform 0.3s cubic-bezier(0.23, 1, 0.320, 1);
    }

    .order-panel.active .order-panel__content {
        transform: translateX(0);
    }

    .order-panel__header {
        padding: 24px;
        border-bottom: 1px solid var(--line);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.1) 0%, rgba(16, 185, 129, 0.1) 100%);
    }

    .order-panel__header h2 {
        margin: 0;
        font-size: 22px;
        background: linear-gradient(135deg, #2563eb, #10b981);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .order-panel__close {
        background: none;
        border: none;
        font-size: 24px;
        color: var(--muted);
        cursor: pointer;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        transition: all 0.2s ease;
    }

    .order-panel__close:hover {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
    }

    .order-panel__body {
        flex: 1;
        overflow-y: auto;
        padding: 24px;
    }

    .order-panel__empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: var(--muted);
        text-align: center;
    }

    .order-panel__empty p {
        margin: 0 0 8px;
        font-size: 18px;
        font-weight: 600;
    }

    .order-panel__empty small {
        font-size: 14px;
        opacity: 0.7;
    }

    .order-panel__items {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .order-panel__footer {
        padding: 24px;
        border-top: 1px solid var(--line);
        background: var(--bg);
    }

    .order-panel__total {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
        font-size: 18px;
    }

    .order-panel__total strong {
        font-size: 24px;
        background: linear-gradient(135deg, #2563eb, #10b981);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .order-panel__checkout {
        width: 100%;
        padding: 16px;
        background: linear-gradient(135deg, #2563eb 0%, #10b981 100%);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 16px rgba(37, 99, 235, 0.3);
    }

    .order-panel__checkout:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 24px rgba(37, 99, 235, 0.4);
    }

    @media (max-width: 768px) {
        .order-panel__content {
            width: 100%;
        }
    }

    /* Sipariş Öğeleri */
    .order-item-wrapper {
        position: relative;
        overflow: hidden;
        border-radius: 12px;
    }

    .order-item-delete {
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        width: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .order-item-delete span {
        color: #ef4444;
        font-size: 16px;
        font-weight: 700;
    }

    .order-item {
        position: relative;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        background: var(--bg);
        border-radius: 12px;
        border: 1px solid var(--line);
        z-index: 2;
        width: 100%;
    }

    .order-item__image {
        width: 60px;
        height: 60px;
        border-radius: 8px;
        overflow: hidden;
        flex-shrink: 0;
    }

    .order-item__image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .order-item__details {
        flex: 1;
        min-width: 0;
    }

    .order-item__details h4 {
        margin: 0 0 4px;
        font-size: 14px;
        font-weight: 600;
        color: var(--text);
    }

    .order-item__price {
        margin: 0;
        font-size: 13px;
        color: var(--muted);
        font-weight: 500;
    }

    .order-item__controls {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }

    .qty-btn {
        width: 28px;
        height: 28px;
        border-radius: 6px;
        border: 1px solid var(--line);
        background: var(--bg);
        color: var(--text);
        font-size: 16px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .qty-btn:hover {
        background: linear-gradient(135deg, #2563eb, #10b981);
        color: white;
        border-color: transparent;
    }

    .qty-display {
        font-size: 14px;
        font-weight: 600;
        min-width: 24px;
        text-align: center;
    }

    .delete-item-btn {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        border: 1px solid rgba(239, 68, 68, 0.3);
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
        font-size: 16px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        margin-left: 4px;
        padding: 0;
    }

    .delete-item-btn:hover {
        background: #ef4444;
        color: white;
        border-color: #ef4444;
        transform: scale(1.1);
    }

    .delete-item-btn:active {
        transform: scale(0.95);
    }

    /* Onay Modalı */
    .confirm-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 2000;
        display: none;
        align-items: center;
        justify-content: center;
    }

    .confirm-modal.active {
        display: flex;
    }

    .confirm-modal__overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(4px);
        animation: fadeIn 0.2s ease;
    }

    .confirm-modal__content {
        position: relative;
        background: var(--bg);
        border-radius: 16px;
        padding: 24px;
        max-width: 500px;
        width: 90%;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        animation: slideUp 0.3s ease;
        z-index: 1;
        max-height: 90vh;
        overflow-y: auto;
    }

    .confirm-modal__content h3 {
        margin: 0 0 12px;
        font-size: 20px;
        color: var(--text);
    }

    .confirm-modal__content p {
        margin: 0 0 24px;
        color: var(--muted);
        font-size: 15px;
        line-height: 1.5;
    }

    .confirm-modal__actions {
        display: flex;
        gap: 12px;
    }

    /* Checkout Form */
    .checkout-summary {
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.1), rgba(16, 185, 129, 0.1));
        border-radius: 10px;
        padding: 16px;
        margin-bottom: 20px;
    }

    .checkout-summary__row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 18px;
    }

    .checkout-summary__row strong {
        font-size: 24px;
        background: linear-gradient(135deg, #2563eb, #10b981);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-size: 14px;
        font-weight: 600;
        color: var(--text);
    }

    .form-input,
    .form-textarea {
        width: 100%;
        padding: 12px;
        border: 1px solid var(--line);
        border-radius: 8px;
        font-size: 15px;
        background: var(--bg);
        color: var(--text);
        transition: all 0.2s ease;
        font-family: inherit;
    }

    .form-input:focus,
    .form-textarea:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .form-textarea {
        resize: vertical;
    }

    .info-message {
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.1), rgba(16, 185, 129, 0.1));
        border: 1px solid rgba(37, 99, 235, 0.3);
        border-radius: 10px;
        padding: 12px 16px;
        margin-bottom: 20px;
    }

    .info-message p {
        margin: 0;
        font-size: 14px;
        color: var(--text);
        line-height: 1.5;
    }

    .confirm-modal__btn {
        flex: 1;
        padding: 12px;
        border-radius: 10px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        border: none;
    }

    .confirm-modal__btn--cancel {
        background: rgba(0, 0, 0, 0.1);
        color: var(--text);
    }

    .confirm-modal__btn--cancel:hover {
        background: rgba(0, 0, 0, 0.15);
    }

    .confirm-modal__btn--confirm {
        background: linear-gradient(135deg, #2563eb 0%, #10b981 100%);
        color: white;
    }

    .confirm-modal__btn--confirm:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideUp {
        from {
            transform: translateY(20px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    /* Toast Notification */
    .toast-notification {
        position: fixed;
        bottom: -200px;
        left: 50%;
        transform: translateX(-50%);
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        padding: 16px 24px;
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(16, 185, 129, 0.5);
        display: flex;
        align-items: center;
        gap: 12px;
        z-index: 3000;
        transition: bottom 0.4s cubic-bezier(0.23, 1, 0.320, 1);
        min-width: 320px;
        max-width: 90%;
    }

    .toast-notification.show {
        bottom: 80px;
    }

    .toast-notification__icon {
        width: 40px;
        height: 40px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        font-weight: bold;
        flex-shrink: 0;
    }

    .toast-notification__content h4 {
        margin: 0 0 4px;
        font-size: 16px;
        font-weight: 700;
    }

    .toast-notification__content p {
        margin: 0;
        font-size: 14px;
        opacity: 0.9;
    }
</style>

<script>
let orderCart = JSON.parse(localStorage.getItem('orderCart')) || [];
const paymentMethodSelect = document.getElementById('paymentMethod');
const paymentInfoMessage = document.getElementById('paymentInfoMessage');

function updatePaymentInfo() {
    if (!paymentInfoMessage) return;
    const method = paymentMethodSelect ? paymentMethodSelect.value : '';
    if (!method) {
        paymentInfoMessage.innerHTML = '<p>💡 Ödeme şeklinizi seçiniz.</p>';
        return;
    }
    if (method === 'Mobile Payment') {
        paymentInfoMessage.innerHTML = '<p>📲 Mobil ödeme için ödeme sayfasına yönlendirileceksiniz.</p>';
        return;
    }
    paymentInfoMessage.innerHTML = '<p>💡 Ödemeniz personel tarafından alınacaktır.</p>';
}

function toggleOrderPanel() {
    const panel = document.getElementById('orderPanel');
    panel.classList.toggle('active');
}

function addToOrder(productId, productName, price, imageUrl) {
    // Ürün zaten sepette var mı?
    const existingItem = orderCart.find(item => item.productId === productId);
    
    if (existingItem) {
        existingItem.quantity++;
    } else {
        orderCart.push({
            productId: productId,
            productName: productName,
            price: price,
            imageUrl: imageUrl,
            quantity: 1
        });
    }
    
    // LocalStorage'a kaydet
    localStorage.setItem('orderCart', JSON.stringify(orderCart));
    
    // UI'yi güncelle
    updateOrderUI();
    updateProductButtons();
}

function updateProductButtons() {
    // Tüm ürün kartlarındaki butonları güncelle
    document.querySelectorAll('.product-cart-controls').forEach(control => {
        const productId = parseInt(control.dataset.productId);
        const productName = control.dataset.productName;
        const productPrice = parseFloat(control.dataset.productPrice);
        const productImage = control.dataset.productImage;
        const cartItem = orderCart.find(item => item.productId === productId);
        
        if (cartItem && cartItem.quantity > 0) {
            // Sepette varsa + - kontrollerini göster
            control.innerHTML = `
                <div class="cart-qty-controls">
                    <button class="cart-qty-btn" onclick="decreaseProductQuantity(${productId})">−</button>
                    <span class="cart-qty-display">${cartItem.quantity}</span>
                    <button class="cart-qty-btn" onclick="increaseProductQuantity(${productId})">+</button>
                </div>
            `;
        } else {
            // Sepette yoksa "Siparişime Ekle" butonunu göster
            control.innerHTML = `
                <button class="add-to-cart-btn" onclick="addToOrder(${productId}, '${productName.replace(/'/g, "\\'")}', ${productPrice}, '${productImage.replace(/'/g, "\\'")}')">
                    Siparişime Ekle
                </button>
            `;
        }
    });
}

function increaseProductQuantity(productId) {
    const control = document.querySelector(`.product-cart-controls[data-product-id="${productId}"]`);
    if (!control) return;
    
    const productName = control.dataset.productName;
    const productPrice = parseFloat(control.dataset.productPrice);
    const productImage = control.dataset.productImage;
    
    const existingItem = orderCart.find(item => item.productId === productId);
    if (existingItem) {
        existingItem.quantity++;
    } else {
        orderCart.push({
            productId: productId,
            productName: productName,
            price: productPrice,
            imageUrl: productImage,
            quantity: 1
        });
    }
    
    localStorage.setItem('orderCart', JSON.stringify(orderCart));
    updateOrderUI();
    updateProductButtons();
}

function decreaseProductQuantity(productId) {
    const existingItem = orderCart.find(item => item.productId === productId);
    if (existingItem) {
        if (existingItem.quantity > 1) {
            existingItem.quantity--;
        } else {
            // Miktar 1 ise sepette kaldır
            orderCart = orderCart.filter(item => item.productId !== productId);
        }
        localStorage.setItem('orderCart', JSON.stringify(orderCart));
        updateOrderUI();
        updateProductButtons();
    }
}

function updateOrderUI() {
    const badge = document.getElementById('orderBadge');
    const emptyState = document.getElementById('emptyState');
    const orderItems = document.getElementById('orderItems');
    const orderFooter = document.getElementById('orderFooter');
    const orderTotal = document.getElementById('orderTotal');
    
    // Toplam ürün sayısı
    const totalItems = orderCart.reduce((sum, item) => sum + item.quantity, 0);
    badge.textContent = totalItems;
    
    if (orderCart.length === 0) {
        emptyState.style.display = 'flex';
        orderItems.style.display = 'none';
        orderFooter.style.display = 'none';
    } else {
        emptyState.style.display = 'none';
        orderItems.style.display = 'flex';
        orderFooter.style.display = 'block';
        
        // Sipariş listesini oluştur
        orderItems.innerHTML = orderCart.map(item => `
            <div class="order-item-wrapper">
                <div class="order-item-delete">
                    <span>Sil</span>
                </div>
                <div class="order-item" data-product-id="${item.productId}">
                    ${item.imageUrl ? `
                    <div class="order-item__image">
                        <img src="${item.imageUrl}" alt="${item.productName}">
                    </div>
                    ` : ''}
                    <div class="order-item__details">
                        <h4>${item.productName}</h4>
                        <p class="order-item__price">${item.price.toFixed(2)} ₺</p>
                    </div>
                    <div class="order-item__controls">
                        <button onclick="event.stopPropagation(); decreaseQuantity(${item.productId})" ontouchend="event.stopPropagation()" class="qty-btn">−</button>
                        <span class="qty-display">${item.quantity}</span>
                        <button onclick="event.stopPropagation(); increaseQuantity(${item.productId})" ontouchend="event.stopPropagation()" class="qty-btn">+</button>
                        <button onclick="event.stopPropagation(); removeFromOrder(${item.productId})" ontouchend="event.stopPropagation()" class="delete-item-btn" title="Ürünü Sil">
                            🗑️
                        </button>
                    </div>
                </div>
            </div>
        `).join('');
        
        // Swipe-to-delete event'lerini ekle
        document.querySelectorAll('.order-item-wrapper').forEach(wrapper => {
            const item = wrapper.querySelector('.order-item');
            let startX = 0;
            let currentX = 0;
            let isDragging = false;
            
            item.addEventListener('touchstart', (e) => {
                startX = e.touches[0].clientX;
                isDragging = true;
                item.style.transition = 'none';
            });
            
            item.addEventListener('touchmove', (e) => {
                if (!isDragging) return;
                currentX = e.touches[0].clientX;
                const diff = currentX - startX;
                if (diff < 0) {
                    item.style.transform = `translateX(${diff}px)`;
                    item.style.opacity = 1 + (diff / 200);
                }
            });
            
            item.addEventListener('touchend', () => {
                if (!isDragging) return;
                isDragging = false;
                const diff = currentX - startX;
                
                if (diff < -80) {
                    // Sil
                    item.style.transition = 'all 0.3s ease';
                    item.style.transform = 'translateX(-100%)';
                    item.style.opacity = '0';
                    setTimeout(() => {
                        const productId = parseInt(item.dataset.productId);
                        removeFromOrder(productId);
                    }, 300);
                } else {
                    // Geri dön
                    item.style.transition = 'all 0.3s ease';
                    item.style.transform = 'translateX(0)';
                    item.style.opacity = '1';
                }
            });
        });
        
        // Toplam tutarı hesapla
        const total = orderCart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        orderTotal.textContent = total.toFixed(2) + ' ₺';
    }
}

function increaseQuantity(productId) {
    const item = orderCart.find(item => item.productId === productId);
    if (item) {
        item.quantity++;
        localStorage.setItem('orderCart', JSON.stringify(orderCart));
        updateOrderUI();
    }
}

function decreaseQuantity(productId) {
    const item = orderCart.find(item => item.productId === productId);
    if (item) {
        if (item.quantity === 1) {
            // Sola kaydırma animasyonu ile sil
            const wrapper = document.querySelector(`.order-item[data-product-id="${productId}"]`);
            if (wrapper) {
                wrapper.style.transition = 'all 0.3s ease';
                wrapper.style.transform = 'translateX(-100%)';
                wrapper.style.opacity = '0';
                setTimeout(() => {
                    removeFromOrder(productId);
                }, 300);
            }
        } else {
            item.quantity--;
            localStorage.setItem('orderCart', JSON.stringify(orderCart));
            updateOrderUI();
        }
    }
}

function removeFromOrder(productId) {
    // Animasyonlu silme
    const itemElement = document.querySelector(`.order-item[data-product-id="${productId}"]`);
    if (itemElement) {
        itemElement.style.transition = 'all 0.3s ease';
        itemElement.style.transform = 'translateX(-100%)';
        itemElement.style.opacity = '0';
        setTimeout(() => {
            orderCart = orderCart.filter(item => item.productId !== productId);
            localStorage.setItem('orderCart', JSON.stringify(orderCart));
            updateOrderUI();
            updateProductButtons();
        }, 300);
    } else {
        orderCart = orderCart.filter(item => item.productId !== productId);
        localStorage.setItem('orderCart', JSON.stringify(orderCart));
        updateOrderUI();
        updateProductButtons();
    }
}

// Sayfa yüklendiğinde UI'yi güncelle
document.addEventListener('DOMContentLoaded', function() {
    updateOrderUI();
    updateProductButtons();
    updatePaymentInfo();
    if (paymentMethodSelect) {
        paymentMethodSelect.addEventListener('change', updatePaymentInfo);
    }
});

function showCheckoutConfirm() {
    // Toplam tutarı hesapla ve göster
    const total = orderCart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    document.getElementById('checkoutTotal').textContent = total.toFixed(2) + ' ₺';
    updatePaymentInfo();
    
    // Modalı aç
    document.getElementById('confirmModal').classList.add('active');
}

function hideCheckoutConfirm() {
    document.getElementById('confirmModal').classList.remove('active');
}

function showToast(message, type = 'success') {
    const toast = document.getElementById('toastNotification');
    const toastMessage = toast.querySelector('.toast__message');
    
    // Mesajı güncelle
    toastMessage.textContent = message;
    
    // Renk temasını ayarla
    if (type === 'error') {
        toast.style.background = 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)';
    } else {
        toast.style.background = 'linear-gradient(135deg, #10b981 0%, #059669 100%)';
    }
    
    toast.classList.add('show');
    
    // 3 saniye sonra toast'u gizle
    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}

async function confirmCheckout() {
    // Form verilerini topla
    const customerName = document.getElementById('customerName').value.trim();
    const orderNote = document.getElementById('orderNote').value;
    const paymentMethod = paymentMethodSelect ? paymentMethodSelect.value : '';
    
    // Validasyon
    if (!customerName || customerName.length < 2) {
        showToast('Lütfen adınızı ve soyadınızı giriniz!', 'error');
        return;
    }

    if (!paymentMethod) {
        showToast('Lütfen ödeme şekli seçiniz!', 'error');
        return;
    }
    
    if (orderCart.length === 0) {
        showToast('Sepetiniz boş!', 'error');
        return;
    }
    
    // İsmi ad ve soyada ayır
    const nameParts = customerName.split(' ');
    const firstName = nameParts[0] || customerName;
    const lastName = nameParts.slice(1).join(' ') || 'Müşteri';
    
    // Sipariş verilerini hazırla
    const orderData = {
        customer_name: customerName,
        first_name: firstName,
        last_name: lastName,
        order_note: orderNote || null,
        items: orderCart.map(item => ({
            productId: item.productId,
            quantity: item.quantity,
            price: item.price,
            specialInstructions: null,
            name: item.productName
        }))
    };
    
    try {
        if (paymentMethod === 'Mobile Payment') {
            const orderTotal = orderCart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            localStorage.setItem('pendingOrderDraft', JSON.stringify({
                ...orderData,
                total: orderTotal,
                payment_method: paymentMethod
            }));

            hideCheckoutConfirm();
            showToast('Ödeme sayfasına yönlendiriliyorsunuz...', 'success');
            setTimeout(() => {
                window.location.href = 'payment.php?method=mobile';
            }, 800);
            return;
        }

        // API'ye sipariş gönder
        const formData = new URLSearchParams();
        formData.append('customer_name', orderData.customer_name);
        formData.append('first_name', orderData.first_name);
        formData.append('last_name', orderData.last_name);
        if (orderData.order_note) {
            formData.append('order_note', orderData.order_note);
        }
        formData.append('items', JSON.stringify(orderData.items));
        if (paymentMethod) {
            formData.append('payment_method', paymentMethod);
        }
        
        console.log('Sipariş gönderiliyor:', orderData);
        
        const response = await fetch('../api/orders/create.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: formData
        });
        
        const result = await response.json();
        console.log('API Yanıtı:', result);
        
        if (result.success) {
            const tableInfo = result.data && result.data.table_number 
                ? ` Masa: ${result.data.table_number}` 
                : '';

            hideCheckoutConfirm();
            showToast('Siparişiniz başarıyla oluşturuldu!' + tableInfo + ' Ödemeniz alınacaktır.', 'success');
            
            // Sepeti temizle
            setTimeout(() => {
                orderCart = [];
                localStorage.removeItem('orderCart');
                updateOrderUI();
                updateProductButtons();
                toggleOrderPanel();
                
                // Formu sıfırla
                document.getElementById('customerName').value = '';
                document.getElementById('orderNote').value = '';
                if (paymentMethodSelect) {
                    paymentMethodSelect.value = '';
                    updatePaymentInfo();
                }
            }, 2000);
        } else {
            console.error('Sipariş hatası:', result.message);
            showToast(result.message || 'Sipariş oluşturulamadı!', 'error');
        }
    } catch (error) {
        console.error('Sipariş hatası:', error);
        showToast('Bir hata oluştu. Lütfen tekrar deneyin.', 'error');
    }
}
</script>

<?php require_once __DIR__ . '/../includes/layout/bottom.php'; ?>
