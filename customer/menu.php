<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");

require_once __DIR__ . '/../config/database.php';

$database = new Database();
$db = $database->getConnection();

$query = "SELECT category_id, category_name, display_order FROM MenuCategories ORDER BY display_order ASC, category_id ASC";
$stmt = $db->prepare($query);
$stmt->execute();
$allCats = $stmt->fetchAll(PDO::FETCH_ASSOC);

$data = [
    'Appetizers' => ['Başlangıçlar', '../assets/images/categories/appetizers.jpeg', 1, '#FF6B6B'],
    'Soups' => ['Çorbalar', '../assets/images/categories/soups.webp', 2, '#4ECDC4'],
    'Main Courses' => ['Ana Yemekler', '../assets/images/categories/main-courses.webp', 3, '#45B7D1'],
    'Desserts' => ['Tatlılar', '../assets/images/categories/desserts.webp', 4, '#FFA07A'],
    'Beverages' => ['İçecekler', '../assets/images/categories/beverages.webp', 5, '#98D8C8'],
    'Başlangıçlar' => ['Başlangıçlar', '../assets/images/categories/appetizers.jpeg', 1, '#FF6B6B'],
    'Çorbalar' => ['Çorbalar', '../assets/images/categories/soups.webp', 2, '#4ECDC4'],
    'Ana Yemekler' => ['Ana Yemekler', '../assets/images/categories/main-courses.webp', 3, '#45B7D1'],
    'Tatlılar' => ['Tatlılar', '../assets/images/categories/desserts.webp', 4, '#FFA07A'],
    'İçecekler' => ['İçecekler', '../assets/images/categories/beverages.webp', 5, '#98D8C8'],
];

$categories = [];
foreach ($allCats as $cat) {
    if (isset($data[$cat['category_name']])) {
        $info = $data[$cat['category_name']];
        $categories[] = [
            'category_id' => $cat['category_id'],
            'category_name' => $cat['category_name'],
            'tr_name' => $info[0],
            'icon' => $info[1],
            'order' => $cat['display_order'] ?? $info[2],
            'color' => $info[3],
        ];
    } else {
        $categories[] = [
            'category_id' => $cat['category_id'],
            'category_name' => $cat['category_name'],
            'tr_name' => $cat['category_name'],
            'icon' => '../assets/images/serving-dishes.png',
            'order' => $cat['display_order'] ?? 999,
            'color' => '#94a3b8',
        ];
    }
}

usort($categories, function($a, $b) {
    return $a['order'] - $b['order'];
});

$title = 'Menü - Restoran';
$bodyClass = 'page-customer';
require_once __DIR__ . '/../includes/layout/top.php';
?>

<main class="app">
    <div class="customer-container">
        <!-- Header -->
        <header class="customer-header">
            <div class="header-top" style="padding: 16px 20px !important; text-align: center; display: flex; justify-content: center; align-items: center;">
                <div class="header-greeting" style="text-align: center;">
                    <h1>Restoran Menüsü</h1>
                </div>
            </div>
        </header>

        <!-- Kategoriler Grid -->
        <div class="menu-grid">
            <?php if (empty($categories)): ?>
                <div class="empty-state">
                    <p>📭 Kategori bulunamadı.</p>
                </div>
            <?php else: ?>
                <?php foreach ($categories as $cat): ?>
                    <a href="category.php?category_id=<?= (int)$cat['category_id'] ?>" class="category-card" style="--card-color: <?= $cat['color'] ?>;">
                        <div class="category-card__icon"><img src="<?= $cat['icon'] ?>" alt="<?= htmlspecialchars($cat['tr_name']) ?>" class="category-card__img"></div>
                        <h3 class="category-card__title"><?= htmlspecialchars($cat['tr_name']) ?></h3>
                        <p class="category-card__desc">Ürünleri görüntüle →</p>
                    </a>
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

            <!-- Bilgi Mesajı -->
            <div class="info-message">
                <p>💡 Ödeme işlemi personel tarafından alınacaktır.</p>
                <p>📍 Masa numarası otomatik olarak atanacaktır.</p>
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

    .customer-header {
        margin-bottom: 24px;
    }

    .header-top {
        padding: 20px;
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.15) 0%, rgba(16, 185, 129, 0.15) 100%);
        border-radius: 24px;
        border: 1px solid rgba(148, 163, 184, 0.2);
        box-shadow: 0 8px 32px rgba(37, 99, 235, 0.08);
        max-width: 600px;
        margin: 0 auto;
    }

    .header-greeting h1 {
        margin: 0 0 12px;
        font-size: 22px;
        font-weight: 700;
        text-align: center;
        background: linear-gradient(135deg, #2563eb, #10b981);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .header-subtitle {
        margin: 0;
        font-size: 18px;
        color: var(--muted);
        font-weight: 500;
    }

    .menu-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
        margin-bottom: 32px;
        max-width: 500px;
        margin-left: auto;
        margin-right: auto;
    }

    .empty-state {
        grid-column: 1 / -1;
        text-align: center;
        padding: 64px 24px;
        color: var(--muted);
        font-size: 20px;
    }

    .category-card {
        background: linear-gradient(135deg, rgba(255,255,255,0.05) 0%, rgba(255,255,255,0.02) 100%);
        border: 1px solid var(--line);
        border-radius: 16px;
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.12);
        padding: 16px 12px;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        transition: all 0.35s cubic-bezier(0.23, 1, 0.320, 1);
        cursor: pointer;
        text-decoration: none;
        position: relative;
        overflow: hidden;
    }

    .category-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, var(--card-color, #2563eb) 0%, transparent 70%);
        opacity: 0;
        transition: opacity 0.35s ease;
        pointer-events: none;
    }

    .category-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 16px 48px rgba(0, 0, 0, 0.2);
        border-color: var(--card-color, #2563eb);
    }

    .category-card:hover::before {
        opacity: 0.1;
    }

    .category-card__icon {
        width: 80px;
        height: 80px;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        overflow: hidden;
        background: rgba(0, 0, 0, 0.1);
    }

    .category-card__img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .category-card:hover .category-card__img {
        transform: scale(1.1);
    }

    .category-card__title {
        font-size: 16px;
        font-weight: 700;
        margin: 0 0 4px;
        color: var(--text);
        position: relative;
        z-index: 1;
    }

    .category-card__desc {
        font-size: 12px;
        color: var(--muted);
        margin: 0;
        position: relative;
        z-index: 1;
        font-weight: 500;
        letter-spacing: 0.3px;
    }

    @keyframes bounce {
        0%, 100% { transform: translateY(0) scale(1); }
        50% { transform: translateY(-8px) scale(1.1); }
    }

    @media (max-width: 768px) {
        .customer-container {
            padding: 16px;
        }

        .header-top {
            padding: 32px 20px;
        }

        .header-greeting h1 {
            font-size: 28px;
        }

        .header-subtitle {
            font-size: 16px;
        }

        .menu-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        .category-card {
            padding: 16px 12px;
        }

        .category-card__icon {
            font-size: 40px;
        }

        .category-card__title {
            font-size: 16px;
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

function toggleOrderPanel() {
    const panel = document.getElementById('orderPanel');
    panel.classList.toggle('active');
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
        }, 300);
    } else {
        orderCart = orderCart.filter(item => item.productId !== productId);
        localStorage.setItem('orderCart', JSON.stringify(orderCart));
        updateOrderUI();
    }
}

// Sayfa yüklendiğinde UI'yi güncelle
document.addEventListener('DOMContentLoaded', function() {
    updateOrderUI();
});

function showCheckoutConfirm() {
    // Toplam tutarı hesapla ve göster
    const total = orderCart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    document.getElementById('checkoutTotal').textContent = total.toFixed(2) + ' ₺';
    
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
    
    // Validasyon
    if (!customerName || customerName.length < 2) {
        showToast('Lütfen adınızı ve soyadınızı giriniz!', 'error');
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
            specialInstructions: null
        }))
    };
    
    try {
        // API'ye sipariş gönder
        const formData = new URLSearchParams();
        formData.append('customer_name', orderData.customer_name);
        formData.append('first_name', orderData.first_name);
        formData.append('last_name', orderData.last_name);
        if (orderData.order_note) {
            formData.append('order_note', orderData.order_note);
        }
        formData.append('items', JSON.stringify(orderData.items));
        
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
            hideCheckoutConfirm();
            const tableInfo = result.data && result.data.table_number 
                ? ` Masa: ${result.data.table_number}` 
                : '';
            showToast('Siparişiniz başarıyla oluşturuldu!' + tableInfo + ' Ödeme personel tarafından alınacaktır.', 'success');
            
            // Sepeti temizle
            setTimeout(() => {
                orderCart = [];
                localStorage.removeItem('orderCart');
                updateOrderUI();
                toggleOrderPanel();
                
                // Formu sıfırla
                document.getElementById('customerName').value = '';
                document.getElementById('orderNote').value = '';
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
