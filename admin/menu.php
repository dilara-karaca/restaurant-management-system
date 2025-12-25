<?php
require_once __DIR__ . '/../includes/functions.php';
date_default_timezone_set('Europe/Istanbul');

requireAdmin();

$bodyClass = "page-admin";
$title = "Menü Yönetimi";
$username = isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : 'Admin';
$extraJs = ['/Restaurant-Management-System/assets/js/menu.js'];

include __DIR__ . '/../includes/layout/top.php';
?>

<main class="app">
    <div class="admin-container">
        <nav class="admin-nav">
            <div class="nav-header">
                <div class="nav-logo">🍽️ Restoran</div>
                <p class="nav-subtitle">Yönetim Paneli</p>
            </div>
            <ul class="nav-menu">
                <li><a href="/Restaurant-Management-System/admin/dashboard.php" class="nav-link">Dashboard</a></li>
                <li><a href="/Restaurant-Management-System/admin/menu.php" class="nav-link active">Menü</a></li>
                <li><a href="/Restaurant-Management-System/admin/orders.php" class="nav-link">Siparişler</a></li>
                <li><a href="#" class="nav-link">Masalar</a></li>
                <li><a href="/Restaurant-Management-System/admin/stock.php" class="nav-link">Stok</a></li>
                <li><a href="#" class="nav-link">Kullanıcılar</a></li>
                <li><a href="#" class="nav-link">Ayarlar</a></li>
            </ul>
            <div class="nav-footer">
                <span class="user-name"><?php echo htmlspecialchars($username); ?></span>
                <a href="logout.php" class="logout-btn">Çıkış Yap</a>
            </div>
        </nav>

        <div class="admin-content">
            <header class="admin-header">
                <div class="header-top">
                    <div class="header-greeting">
                        <h1>Menü Yönetimi</h1>
                        <p class="header-date">Kategori, ürün ve ürün reçetelerini yönetin.</p>
                    </div>
                    <div class="header-actions">
                        <button id="addCategoryBtn" class="btn btn--secondary">Kategori Ekle</button>
                        <button id="addProductBtn" class="btn btn--primary">Ürün Ekle</button>
                    </div>
                </div>
            </header>

            <div id="menuNotice" class="orders-notice"></div>

            <div class="menu-grid">
                <div class="card menu-categories">
                    <div class="card-header">
                        <div>
                            <h3>Kategoriler</h3>
                            <p class="card-subtitle">Kategori listesi ve düzenleme.</p>
                        </div>
                    </div>
                    <div id="categoryList" class="menu-category-list">
                        <div class="menu-category-item">Yükleniyor...</div>
                    </div>
                </div>

                <div class="card menu-products">
                    <div class="card-header menu-products-header">
                        <div>
                            <h3>Ürünler</h3>
                            <p class="card-subtitle">Ürün detayları ve reçeteler.</p>
                        </div>
                        <div class="menu-filters">
                            <select id="productCategoryFilter" class="input">
                                <option value="">Tüm Kategoriler</option>
                            </select>
                            <input id="productSearch" class="input" type="text" placeholder="Ürün ara...">
                        </div>
                    </div>
                    <div class="orders-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Ürün</th>
                                    <th>Kategori</th>
                                    <th>Fiyat</th>
                                    <th>Durum</th>
                                    <th>Reçete</th>
                                    <th>İşlem</th>
                                </tr>
                            </thead>
                            <tbody id="productTableBody">
                                <tr>
                                    <td colspan="6">Yükleniyor...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<div id="categoryModal" class="modal">
    <div class="modal-content">
        <button class="modal-close" id="categoryModalClose">&times;</button>
        <h3 id="categoryModalTitle">Kategori Ekle</h3>
        <form id="categoryForm" class="stock-form">
            <input type="hidden" id="categoryId">
            <div class="field">
                <label class="field__label" for="categoryName">Kategori Adı</label>
                <div class="field__control">
                    <input id="categoryName" class="input" type="text" required>
                </div>
            </div>
            <div class="field">
                <label class="field__label" for="categoryDescription">Açıklama</label>
                <div class="field__control">
                    <input id="categoryDescription" class="input" type="text">
                </div>
            </div>
            <div class="field">
                <label class="field__label" for="categoryOrder">Sıra</label>
                <div class="field__control">
                    <input id="categoryOrder" class="input" type="number" min="0" value="0">
                </div>
            </div>
            <button class="btn btn--primary" type="submit">Kaydet</button>
        </form>
    </div>
</div>

<div id="productModal" class="modal">
    <div class="modal-content modal-wide">
        <button class="modal-close" id="productModalClose">&times;</button>
        <h3 id="productModalTitle">Ürün Ekle</h3>
        <form id="productForm" class="product-form">
            <input type="hidden" id="productId">
            <div class="product-form-grid">
                <div class="field">
                    <label class="field__label" for="productName">Ürün Adı</label>
                    <div class="field__control">
                        <input id="productName" class="input" type="text" required>
                    </div>
                </div>
                <div class="field">
                    <label class="field__label" for="productCategory">Kategori</label>
                    <div class="field__control">
                        <select id="productCategory" class="input" required></select>
                    </div>
                </div>
                <div class="field">
                    <label class="field__label" for="productPrice">Fiyat (₺)</label>
                    <div class="field__control">
                        <input id="productPrice" class="input" type="number" min="0" step="0.01" required>
                    </div>
                </div>
                <div class="field">
                    <label class="field__label" for="productStatus">Aktif</label>
                    <div class="field__control">
                        <select id="productStatus" class="input">
                            <option value="1">Evet</option>
                            <option value="0">Hayır</option>
                        </select>
                    </div>
                </div>
                <div class="field product-desc">
                    <label class="field__label" for="productDescription">Açıklama</label>
                    <div class="field__control">
                        <input id="productDescription" class="input" type="text">
                    </div>
                </div>
                <div class="field product-image">
                    <label class="field__label" for="productImageUrl">Görsel URL</label>
                    <div class="field__control">
                        <input id="productImageUrl" class="input" type="text">
                    </div>
                </div>
            </div>

            <div class="product-ingredients">
                <div class="product-ingredients-header">
                    <h4>Ürün Reçetesi</h4>
                </div>
                <p class="ingredient-empty" id="ingredientEmpty">Malzeme seçin ve miktar girin.</p>
                <div id="ingredientList" class="ingredient-list"></div>
            </div>

            <button class="btn btn--primary" type="submit">Kaydet</button>
        </form>
    </div>
</div>

<?php
include __DIR__ . '/../includes/layout/bottom.php';
?>
