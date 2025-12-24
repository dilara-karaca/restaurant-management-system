<?php
session_start();
date_default_timezone_set('Europe/Istanbul');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: /Restaurant-Management-System/admin/login.php');
    exit;
}

$bodyClass = "page-admin";
$title = "Stok Yönetimi";
$username = isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : 'Admin';
$extraJs = ['/Restaurant-Management-System/assets/js/stock.js'];

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
                <li><a href="/Restaurant-Management-System/admin/orders.php" class="nav-link">Siparişler</a></li>
                <li><a href="#" class="nav-link">Masalar</a></li>
                <li><a href="/Restaurant-Management-System/admin/stock.php" class="nav-link active">Stok</a></li>
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
                        <h1>Stok Yönetimi</h1>
                        <p class="header-date">Gelen, giden ve kullanılan stok hareketlerini yönetin.</p>
                    </div>
                    <div class="header-actions">
                        <button id="refreshStocksBtn" class="btn btn--secondary">Yenile</button>
                    </div>
                </div>
            </header>

            <div class="stock-grid">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h3>Stok Listesi</h3>
                            <p class="card-subtitle">Güncel stok miktarları ve minimum eşikler.</p>
                        </div>
                    </div>
                    <div id="stockNotice" class="orders-notice"></div>
                    <div class="orders-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Ürün</th>
                                    <th>Tedarikçi</th>
                                    <th>Stok</th>
                                    <th>Minimum</th>
                                    <th>Birim</th>
                                    <th>Birim Fiyat</th>
                                    <th>Son Güncelleme</th>
                                    <th>Durum</th>
                                </tr>
                            </thead>
                            <tbody id="stockTableBody">
                                <tr>
                                    <td colspan="8">Yükleniyor...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="stock-side">
                    <div class="card">
                        <div class="card-header">
                            <div>
                                <h3>Stok Hareketi Ekle</h3>
                                <p class="card-subtitle">Gelen, giden veya kullanılan stok ekleyin.</p>
                            </div>
                        </div>
                        <form id="stockMovementForm" class="stock-form">
                            <div class="field">
                                <label class="field__label" for="movementIngredient">Malzeme</label>
                                <div class="field__control">
                                    <select id="movementIngredient" class="input" required></select>
                                </div>
                            </div>
                            <div class="field">
                                <label class="field__label" for="movementType">Hareket Türü</label>
                                <div class="field__control">
                                    <select id="movementType" class="input" required>
                                        <option value="IN">Gelen Stok</option>
                                        <option value="OUT">Giden Stok</option>
                                        <option value="USED">Kullanılan</option>
                                    </select>
                                </div>
                            </div>
                            <div class="field">
                                <label class="field__label" for="movementQty">Miktar</label>
                                <div class="field__control">
                                    <input id="movementQty" class="input" type="number" min="0.01" step="0.01" required>
                                </div>
                            </div>
                            <div class="field">
                                <label class="field__label" for="movementNote">Not</label>
                                <div class="field__control">
                                    <input id="movementNote" class="input" type="text" placeholder="Örn: haftalık alış">
                                </div>
                            </div>
                            <button class="btn btn--primary" type="submit">Hareket Ekle</button>
                        </form>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <div>
                                <h3>Son Hareketler</h3>
                                <p class="card-subtitle">Son stok değişiklikleri.</p>
                            </div>
                        </div>
                        <form id="stockFilterForm" class="stock-filter">
                            <div class="field">
                                <label class="field__label" for="movementFilterIngredient">Malzeme</label>
                                <div class="field__control">
                                    <select id="movementFilterIngredient" class="input"></select>
                                </div>
                            </div>
                            <div class="field">
                                <label class="field__label" for="movementFilterType">Tür</label>
                                <div class="field__control">
                                    <select id="movementFilterType" class="input">
                                        <option value="">Tümü</option>
                                        <option value="IN">Gelen</option>
                                        <option value="OUT">Giden</option>
                                        <option value="USED">Kullanılan</option>
                                    </select>
                                </div>
                            </div>
                            <div class="field">
                                <label class="field__label" for="movementFilterFrom">Başlangıç</label>
                                <div class="field__control">
                                    <input id="movementFilterFrom" class="input" type="date">
                                </div>
                            </div>
                            <div class="field">
                                <label class="field__label" for="movementFilterTo">Bitiş</label>
                                <div class="field__control">
                                    <input id="movementFilterTo" class="input" type="date">
                                </div>
                            </div>
                            <div class="field">
                                <label class="field__label" for="movementFilterLimit">Limit</label>
                                <div class="field__control">
                                    <select id="movementFilterLimit" class="input">
                                        <option value="12">12</option>
                                        <option value="24">24</option>
                                        <option value="50">50</option>
                                    </select>
                                </div>
                            </div>
                            <div class="stock-filter-actions">
                                <button type="submit" class="btn btn--secondary btn--small">Filtrele</button>
                                <button type="button" id="stockFilterReset" class="btn btn--ghost btn--small">Sıfırla</button>
                            </div>
                        </form>
                        <div class="orders-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Malzeme</th>
                                        <th>Tür</th>
                                        <th>Miktar</th>
                                        <th>Not</th>
                                        <th>Tarih</th>
                                        <th>İşlem</th>
                                    </tr>
                                </thead>
                                <tbody id="stockMovements">
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
    </div>
</main>

<div id="stockMovementModal" class="modal">
    <div class="modal-content">
        <button class="modal-close" id="stockMovementModalClose">&times;</button>
        <h3>Stok Hareketi Düzenle</h3>
        <form id="stockMovementEditForm" class="stock-form">
            <div class="field">
                <label class="field__label" for="editMovementIngredient">Malzeme</label>
                <div class="field__control">
                    <input id="editMovementIngredient" class="input" type="text" disabled>
                </div>
            </div>
            <div class="field">
                <label class="field__label" for="editMovementType">Hareket Türü</label>
                <div class="field__control">
                    <select id="editMovementType" class="input" required>
                        <option value="IN">Gelen</option>
                        <option value="OUT">Giden</option>
                        <option value="USED">Kullanılan</option>
                    </select>
                </div>
            </div>
            <div class="field">
                <label class="field__label" for="editMovementQty">Miktar</label>
                <div class="field__control">
                    <input id="editMovementQty" class="input" type="number" min="0.01" step="0.01" required>
                </div>
            </div>
            <div class="field">
                <label class="field__label" for="editMovementNote">Not</label>
                <div class="field__control">
                    <input id="editMovementNote" class="input" type="text">
                </div>
            </div>
            <button class="btn btn--primary" type="submit">Kaydet</button>
        </form>
    </div>
</div>

<?php
include __DIR__ . '/../includes/layout/bottom.php';
?>
