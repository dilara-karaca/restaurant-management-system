<?php
// admin/menu.php
// Menü ve kategori yönetimi ana ekranı (kategori ve ürün kartları)

require_once __DIR__ . '/../includes/layout/top.php';
require_once __DIR__ . '/../includes/layout/admin_nav.php';
?>
<div class="container mt-4">
    <h1>Menü Yönetimi</h1>
    <button class="add-btn" onclick="openCategoryModal()">+ Kategori Ekle</button>
    <div id="menu-categories" class="row"></div>
    <div id="menu-products" class="row" style="display:none;"></div>

    <!-- Ürün Modalı -->
    <div id="product-modal" class="modal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeProductModal()">&times;</button>
            <h3 id="product-modal-title">Ürün Ekle</h3>
            <form id="product-form">
                <input type="hidden" id="product_id" name="product_id">
                <input type="hidden" id="product_category_id" name="category_id">
                <div class="field">
                    <label>Ürün Adı</label>
                    <input type="text" id="product_name" name="product_name" required>
                </div>
                <div class="field">
                    <label>Açıklama</label>
                    <input type="text" id="product_description" name="description">
                </div>
                <div class="field">
                    <label>Fiyat (₺)</label>
                    <input type="number" id="product_price" name="price" min="0" step="0.01" required>
                </div>
                <div class="field">
                    <label>Aktif mi?</label>
                    <select id="product_is_available" name="is_available">
                        <option value="1">Evet</option>
                        <option value="0">Hayır</option>
                    </select>
                </div>
                <div class="field">
                    <label>Resim URL</label>
                    <input type="text" id="product_image_url" name="image_url">
                </div>
                <!-- Ürün için fotoğraf alanı kaldırıldı, sadece kategori modalında olacak -->
                <div class="field">
                    <label>Fotoğraf</label>
                    <input type="file" id="category_image" name="image">
                </div>
                <div class="field">
                    <label>Fotoğraf</label>
                    <input type="file" id="category_image" name="image">
                </div>
                <button type="submit" class="btn-crud">Kaydet</button>
            </form>
        </div>
    </div>

    <!-- Kategori Modalı -->
    <div id="category-modal" class="modal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeCategoryModal()">&times;</button>
            <h3 id="category-modal-title">Kategori Ekle</h3>
            <form id="category-form">
                <input type="hidden" id="category_id" name="category_id">
                <div class="field">
                    <label>Kategori Adı</label>
                    <input type="text" id="category_name" name="category_name" required>
                </div>
                <div class="field">
                    <label>Açıklama</label>
                    <input type="text" id="description" name="description">
                </div>
                <div class="field">
                    <label>Sıra</label>
                    <input type="number" id="display_order" name="display_order" min="0" value="0">
                </div>
                <div class="field">
                    <label>Resim</label>
                    <input type="file" id="category_image" name="image" accept="image/*">
                </div>
                <button type="submit" class="btn-crud">Kaydet</button>
            </form>
        </div>
    </div>
</div>
<link rel="stylesheet" href="/Restaurant-Management-System/assets/css/style.css">
<script src="/Restaurant-Management-System/assets/js/menu.js"></script>
<?php
require_once __DIR__ . '/../includes/layout/bottom.php';
?>