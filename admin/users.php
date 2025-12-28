<?php
require_once __DIR__ . '/../includes/functions.php';
date_default_timezone_set('Europe/Istanbul');

requireAdmin();

$bodyClass = "page-admin";
$title = "Kullanıcı Yönetimi";
$username = isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : 'Admin';
$extraJs = ['/Restaurant-Management-System/assets/js/admin_users.js'];

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
                <li><a href="/Restaurant-Management-System/admin/reports.php" class="nav-link">Raporlar</a></li>
                <li><a href="/Restaurant-Management-System/admin/stock.php" class="nav-link">Stok</a></li>
                <li><a href="/Restaurant-Management-System/admin/users.php" class="nav-link active">Kullanıcılar</a></li>
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
                        <h1>Kullanıcı Yönetimi</h1>
                        <p class="header-date">Personel hesaplarını görüntüleyin ve rol güncelleyin.</p>
                    </div>
                    <div class="header-actions">
                        <button id="refreshUsersBtn" class="btn btn--secondary">Yenile</button>
                    </div>
                </div>
            </header>

            <div id="usersNotice" class="orders-notice"></div>

            <div class="card orders-card">
                <div class="card-header orders-toolbar">
                    <h3>Yeni Personel</h3>
                    <p class="card-subtitle">Yeni personel hesabı oluşturun.</p>
                </div>
                <div class="card-body">
                    <form id="personnelCreateForm" class="form">
                        <div class="grid-form">
                            <label class="field">
                                <span class="field__label">Ad</span>
                                <div class="field__control">
                                    <input id="personnelFirstName" class="input" type="text" required>
                                </div>
                            </label>
                            <label class="field">
                                <span class="field__label">Soyad</span>
                                <div class="field__control">
                                    <input id="personnelLastName" class="input" type="text" required>
                                </div>
                            </label>
                            <label class="field">
                                <span class="field__label">Kullanıcı Adı</span>
                                <div class="field__control">
                                    <input id="personnelUsername" class="input" type="text" required>
                                </div>
                            </label>
                            <label class="field">
                                <span class="field__label">E-posta</span>
                                <div class="field__control">
                                    <input id="personnelEmail" class="input" type="email" required>
                                </div>
                            </label>
                            <label class="field">
                                <span class="field__label">Şifre</span>
                                <div class="field__control">
                                    <input id="personnelPassword" class="input" type="password" required>
                                </div>
                            </label>
                            <label class="field">
                                <span class="field__label">Rol</span>
                                <div class="field__control">
                                    <select id="personnelRole" class="input" required></select>
                                </div>
                            </label>
                            <label class="field">
                                <span class="field__label">Maaş</span>
                                <div class="field__control">
                                    <input id="personnelSalary" class="input" type="number" min="0" step="0.01" placeholder="Opsiyonel">
                                </div>
                            </label>
                            <label class="field">
                                <span class="field__label">İşe Giriş Tarihi</span>
                                <div class="field__control">
                                    <input id="personnelHireDate" class="input" type="date" required>
                                </div>
                            </label>
                        </div>
                        <div class="form-actions">
                            <button class="btn btn--primary" type="submit">Personel Ekle</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card orders-card">
                <div class="card-header orders-toolbar">
                    <h3>Personel Kullanıcıları</h3>
                    <p class="card-subtitle">Kullanıcı adı, şifre (hash) ve isim bilgileri listelenir.</p>
                </div>
                <div class="orders-table">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Ad Soyad</th>
                                <th>Rol</th>
                                <th>Kullanıcı Adı</th>
                                <th>Şifre (Hash)</th>
                                <th>Pozisyon</th>
                                <th>İşlem</th>
                            </tr>
                        </thead>
                        <tbody id="personnelTableBody">
                            <tr>
                                <td colspan="7">Yükleniyor...</td>
                            </tr>
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
