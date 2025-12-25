<?php
require_once __DIR__ . '/../includes/functions.php';
date_default_timezone_set('Europe/Istanbul');

requireAdmin();

$bodyClass = "page-admin";
$title = "Siparişler";
$username = isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : 'Admin';
$extraJs = ['/Restaurant-Management-System/assets/js/orders.js'];

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
                <li><a href="/Restaurant-Management-System/admin/menu.php" class="nav-link">Menü</a></li>
                <li><a href="/Restaurant-Management-System/admin/orders.php" class="nav-link active">Siparişler</a></li>
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
                        <h1>Sipariş Yönetimi</h1>
                        <p class="header-date">Mevcut siparişleri yönetin, durumlarını güncelleyin.</p>
                    </div>
                    <div class="header-actions">
                        <button id="refreshOrdersBtn" class="btn btn--secondary">Yenile</button>
                    </div>
                </div>
            </header>

            <div class="card orders-card">
                <div class="card-header orders-toolbar">
                    <h3>Mevcut Siparişler</h3>
                    <div class="orders-toolbar-actions">
                        <label for="orderStatusFilter" class="order-filter-label">Durum</label>
                        <select id="orderStatusFilter" class="order-filter-select">
                            <option value="">Tümü</option>
                            <option value="Pending">Beklemede</option>
                            <option value="Preparing">Hazırlanıyor</option>
                            <option value="Served">Servis Edildi</option>
                            <option value="Completed">Tamamlandı</option>
                            <option value="Cancelled">İptal</option>
                        </select>
                    </div>
                </div>
                <div id="ordersNotice" class="orders-notice"></div>
                <div class="orders-table">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Masa</th>
                                <th>Müşteri</th>
                                <th>Ürünler</th>
                                <th>Durum</th>
                                <th>Tutar</th>
                                <th>Tarih</th>
                                <th>İşlem</th>
                            </tr>
                        </thead>
                        <tbody id="ordersTableBody">
                            <tr>
                                <td colspan="8">Yükleniyor...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<div id="orderManageModal" class="modal">
    <div class="modal-content modal-wide">
        <button class="modal-close" id="orderManageClose">&times;</button>
        <div class="modal-title-row">
            <h3>Sipariş Yönetimi</h3>
            <button id="deleteOrderBtn" class="btn btn--ghost btn--small">Siparişi Sil</button>
        </div>
        <div class="panel-info order-meta">
            <div class="info-row">
                <span class="info-label">Sipariş No:</span>
                <span id="orderMetaId" class="info-value">-</span>
            </div>
            <div class="info-row">
                <span class="info-label">Masa:</span>
                <span id="orderMetaTable" class="info-value">-</span>
            </div>
            <div class="info-row">
                <span class="info-label">Müşteri:</span>
                <span id="orderMetaCustomer" class="info-value">-</span>
            </div>
            <div class="info-row">
                <span class="info-label">Tarih:</span>
                <span id="orderMetaDate" class="info-value">-</span>
            </div>
        </div>

        <div class="field order-status-field">
            <label class="field__label" for="orderStatusSelect">Sipariş Durumu</label>
            <div class="field__control">
                <select id="orderStatusSelect" class="input">
                    <option value="Pending">Beklemede</option>
                    <option value="Preparing">Hazırlanıyor</option>
                    <option value="Served">Servis Edildi</option>
                    <option value="Completed">Tamamlandı</option>
                    <option value="Cancelled">İptal</option>
                </select>
                <button id="updateStatusBtn" class="btn btn--primary btn--small">Güncelle</button>
            </div>
        </div>

        <div class="order-items-wrap">
            <div class="order-items-header">
                <span>Ürün</span>
                <span>Adet</span>
                <span>Birim</span>
                <span>İşlem</span>
            </div>
            <div id="orderItemsList" class="order-items-list"></div>
        </div>

        <div class="order-total-row">
            <span>Toplam</span>
            <span id="orderTotalValue">₺0</span>
        </div>

        <div class="order-add-row">
            <div class="field">
                <label class="field__label" for="addItemProduct">Ürün Ekle</label>
                <div class="field__control">
                    <select id="addItemProduct" class="input"></select>
                </div>
            </div>
            <div class="field">
                <label class="field__label" for="addItemQty">Adet</label>
                <div class="field__control">
                    <input id="addItemQty" class="input" type="number" min="1" value="1">
                </div>
            </div>
            <button id="addItemBtn" class="btn btn--primary btn--small">Ürün Ekle</button>
        </div>
    </div>
</div>

<?php
include __DIR__ . '/../includes/layout/bottom.php';
?>
