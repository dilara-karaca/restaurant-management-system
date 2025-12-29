<?php
require_once __DIR__ . '/../includes/functions.php';

date_default_timezone_set('Europe/Istanbul');

requirePersonnel();

$bodyClass = "page-admin";
$title = "Personel Siparişleri";
$username = isset($_SESSION['personnel_name']) ? $_SESSION['personnel_name'] : 'Personel';
$roleName = isset($_SESSION['personnel_role']) ? $_SESSION['personnel_role'] : 'Personel';
$extraJs = ['/Restaurant-Management-System/assets/js/personnel_orders.js'];

include __DIR__ . '/../includes/layout/top.php';
?>

<main class="app">
    <div class="admin-container">
        <nav class="admin-nav">
            <div class="nav-header">
                <div class="nav-logo">🍽️ Restoran</div>
                <p class="nav-subtitle">Personel Paneli</p>
            </div>
            <ul class="nav-menu">
                <li><a href="/Restaurant-Management-System/personnel/orders.php" class="nav-link active">Siparişlerim</a></li>
            </ul>
            <div class="nav-footer">
                <div class="user-name">
                    <div><?php echo htmlspecialchars($username); ?></div>
                    <small><?php echo htmlspecialchars($roleName); ?></small>
                </div>
                <a href="/Restaurant-Management-System/personnel/logout.php" class="logout-btn">Çıkış Yap</a>
            </div>
        </nav>

        <div class="admin-content">
            <header class="admin-header">
                <div class="header-top">
                    <div class="header-greeting">
                        <h1>Sipariş Yönetimi</h1>
                        <p class="header-date">Size atanmış siparişleri görüntüleyin ve güncelleyin.</p>
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

            <div class="card orders-card">
                <div class="card-header orders-toolbar">
                    <h3>Atanmamış Siparişler</h3>
                    <div class="orders-toolbar-actions">
                        <span class="order-filter-label">Garson seçip üzerinize alabilirsiniz.</span>
                    </div>
                </div>
                <div id="unassignedNotice" class="orders-notice"></div>
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
                        <tbody id="unassignedOrdersTableBody">
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
            <h3>Sipariş Detayı</h3>
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

        <div class="field order-status-field">
            <label class="field__label" for="orderTableSelect">Masa</label>
            <div class="field__control">
                <select id="orderTableSelect" class="input"></select>
                <button id="updateTableBtn" class="btn btn--secondary btn--small">Masa Güncelle</button>
            </div>
        </div>

        <div class="field">
            <label class="field__label" for="paymentMethodSelect">Ödeme Yöntemi</label>
            <div class="field__control">
                <select id="paymentMethodSelect" class="input">
                    <option value="">Seçiniz</option>
                    <option value="Cash">Nakit</option>
                    <option value="Credit Card">Kredi Kartı</option>
                    <option value="Debit Card">Banka Kartı</option>
                    <option value="Mobile Payment">Mobil Ödeme</option>
                </select>
                <button id="completePaymentBtn" class="btn btn--primary btn--small">Ödemeyi Tamamla</button>
            </div>
            <p id="paymentStatusNote" class="payment-status-note"></p>
        </div>

        <div class="order-items-wrap">
            <div class="order-items-header">
                <span>Ürün</span>
                <span>Adet</span>
                <span>Birim</span>
                <span>Tutar</span>
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
