<?php
require_once __DIR__ . '/../includes/functions.php';

date_default_timezone_set('Europe/Istanbul');
requireAdmin();

$bodyClass = "page-admin";
$title = "Raporlar";
$username = isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : 'Admin';
$extraJs = ['/Restaurant-Management-System/assets/js/reports.js'];

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
                <li><a href="/Restaurant-Management-System/admin/reports.php" class="nav-link active">Raporlar</a></li>
                <li><a href="/Restaurant-Management-System/admin/stock.php" class="nav-link">Stok</a></li>
                <li><a href="/Restaurant-Management-System/admin/users.php" class="nav-link">Kullanıcılar</a></li>
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
                        <h1>Raporlar</h1>
                        <p class="header-date">İşletme performansını özetleyen raporlar.</p>
                    </div>
                </div>
            </header>

            <div class="card orders-card">
                <div class="card-header">
                    <h3>Rapor Filtreleri</h3>
                </div>
                <div class="form" style="padding: 16px;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px;">
                        <label class="field">
                            <span class="field__label">Sipariş Durumu</span>
                            <div class="field__control">
                                <select id="reportStatus" class="input">
                                    <option value="">Tümü</option>
                                    <option value="Pending">Beklemede</option>
                                    <option value="Preparing">Hazırlanıyor</option>
                                    <option value="Served">Servis Edildi</option>
                                    <option value="Completed">Tamamlandı</option>
                                    <option value="Cancelled">İptal</option>
                                </select>
                            </div>
                        </label>
                        <label class="field">
                            <span class="field__label">Sipariş ID</span>
                            <div class="field__control">
                                <input id="reportOrderId" class="input" type="number" min="1" placeholder="Örn: 12">
                            </div>
                        </label>
                        <label class="field">
                            <span class="field__label">Müşteri ID</span>
                            <div class="field__control">
                                <input id="reportCustomerId" class="input" type="number" min="1" placeholder="Örn: 3">
                            </div>
                        </label>
                        <label class="field">
                            <span class="field__label">Personel ID</span>
                            <div class="field__control">
                                <input id="reportPersonnelId" class="input" type="number" min="1" placeholder="Örn: 2">
                            </div>
                        </label>
                        <label class="field">
                            <span class="field__label">Kategori ID</span>
                            <div class="field__control">
                                <input id="reportCategoryId" class="input" type="number" min="1" placeholder="Örn: 1">
                            </div>
                        </label>
                        <label class="field">
                            <span class="field__label">Malzeme ID</span>
                            <div class="field__control">
                                <input id="reportIngredientId" class="input" type="number" min="1" placeholder="Örn: 5">
                            </div>
                        </label>
                        <label class="field">
                            <span class="field__label">Hareket Tipi</span>
                            <div class="field__control">
                                <select id="reportMovementType" class="input">
                                    <option value="">Tümü</option>
                                    <option value="IN">IN</option>
                                    <option value="OUT">OUT</option>
                                    <option value="USED">USED</option>
                                </select>
                            </div>
                        </label>
                        <label class="field">
                            <span class="field__label">Başlangıç</span>
                            <div class="field__control">
                                <input id="reportDateFrom" class="input" type="date">
                            </div>
                        </label>
                        <label class="field">
                            <span class="field__label">Bitiş</span>
                            <div class="field__control">
                                <input id="reportDateTo" class="input" type="date">
                            </div>
                        </label>
                        <label class="field">
                            <span class="field__label">Limit</span>
                            <div class="field__control">
                                <input id="reportLimit" class="input" type="number" min="1" max="200" placeholder="20">
                            </div>
                        </label>
                    </div>
                    <div style="display: flex; gap: 10px; margin-top: 12px;">
                        <button id="applyReportsFilters" class="btn btn--primary btn--small">Filtrele</button>
                        <button id="resetReportsFilters" class="btn btn--secondary btn--small" type="button">Sıfırla</button>
                    </div>
                </div>
            </div>

            <div class="card orders-card">
                <div class="card-header">
                    <h3>Sipariş Genel Raporu</h3>
                </div>
                <div class="orders-table">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Masa</th>
                                <th>Müşteri</th>
                                <th>Garson</th>
                                <th>Durum</th>
                                <th>Kalem</th>
                                <th>Tutar</th>
                                <th>Tarih</th>
                            </tr>
                        </thead>
                        <tbody id="ordersOverviewBody">
                            <tr><td colspan="8">Yükleniyor...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card orders-card">
                <div class="card-header">
                    <h3>Sipariş Kalem Raporu</h3>
                </div>
                <div class="orders-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Sipariş</th>
                                <th>Kategori</th>
                                <th>Ürün</th>
                                <th>Adet</th>
                                <th>Birim</th>
                                <th>Tutar</th>
                            </tr>
                        </thead>
                        <tbody id="orderItemsBody">
                            <tr><td colspan="6">Yükleniyor...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card orders-card">
                <div class="card-header">
                    <h3>Stok Özeti</h3>
                </div>
                <div class="orders-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Malzeme</th>
                                <th>Birim</th>
                                <th>Miktar</th>
                                <th>Minimum</th>
                                <th>Tedarikçi</th>
                            </tr>
                        </thead>
                        <tbody id="stockSummaryBody">
                            <tr><td colspan="5">Yükleniyor...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card orders-card">
                <div class="card-header">
                    <h3>Stok Hareketleri</h3>
                </div>
                <div class="orders-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Malzeme</th>
                                <th>Tip</th>
                                <th>Miktar</th>
                                <th>Not</th>
                                <th>Tarih</th>
                                <th>Tedarikçi</th>
                            </tr>
                        </thead>
                        <tbody id="stockMovementsBody">
                            <tr><td colspan="6">Yükleniyor...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card orders-card">
                <div class="card-header">
                    <h3>Menü Reçete Raporu</h3>
                </div>
                <div class="orders-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Kategori</th>
                                <th>Ürün</th>
                                <th>Malzeme</th>
                                <th>Miktar</th>
                                <th>Birim</th>
                            </tr>
                        </thead>
                        <tbody id="menuCompositionBody">
                            <tr><td colspan="5">Yükleniyor...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card orders-card">
                <div class="card-header">
                    <h3>Müşteri Geçmişi</h3>
                </div>
                <div class="orders-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Müşteri</th>
                                <th>Sipariş</th>
                                <th>Ürün</th>
                                <th>Adet</th>
                                <th>Tutar</th>
                                <th>Tarih</th>
                            </tr>
                        </thead>
                        <tbody id="customerHistoryBody">
                            <tr><td colspan="6">Yükleniyor...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card orders-card">
                <div class="card-header">
                    <h3>Personel Performans</h3>
                </div>
                <div class="orders-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Personel</th>
                                <th>Sipariş</th>
                                <th>Ürün</th>
                                <th>Toplam Satış</th>
                            </tr>
                        </thead>
                        <tbody id="personnelPerformanceBody">
                            <tr><td colspan="4">Yükleniyor...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
include __DIR__ . '/../includes/layout/bottom.php';
?>
