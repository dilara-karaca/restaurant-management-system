<?php
session_start();
date_default_timezone_set('Europe/Istanbul');

// Session kontrolü - Eğer giriş yapılmamışsa login sayfasına yönlendir
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: /Restaurant-Management-System/admin/login.php');
    exit;
}

$extraJs = [];
$bodyClass = "page-admin";
$username = isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : 'Admin';

// Tarih ve saat bilgisi
$dayNames = ['Monday' => 'Pazartesi', 'Tuesday' => 'Salı', 'Wednesday' => 'Çarşamba', 'Thursday' => 'Perşembe', 'Friday' => 'Cuma', 'Saturday' => 'Cumartesi', 'Sunday' => 'Pazar'];
$monthNames = ['January' => 'Ocak', 'February' => 'Şubat', 'March' => 'Mart', 'April' => 'Nisan', 'May' => 'Mayıs', 'June' => 'Haziran', 'July' => 'Temmuz', 'August' => 'Ağustos', 'September' => 'Eylül', 'October' => 'Ekim', 'November' => 'Kasım', 'December' => 'Aralık'];

$day_name = $dayNames[date('l')] ?? date('l');
$date_num = date('d');
$month_name = $monthNames[date('F')] ?? date('F');
$date_str = $date_num . ' ' . $month_name . ' ' . $day_name;

// Saat ve servis türü
$current_hour = (int) date('H');
$current_time = date('H:i');
if ($current_hour >= 6 && $current_hour < 12) {
    $service_type = 'Kahvaltı Servisi';
} elseif ($current_hour >= 12 && $current_hour < 17) {
    $service_type = 'Öğle Servisi';
} else {
    $service_type = 'Akşam Servisi';
}

include __DIR__ . '/../includes/layout/top.php';
?>

<main class="app">
    <div class="admin-container">
        <!-- Navbar -->
        <nav class="admin-nav">
            <div class="nav-header">
                <div class="nav-logo">🍽️ Restoran</div>
                <p class="nav-subtitle">Yönetim Paneli</p>
            </div>
            <ul class="nav-menu">
                <li><a href="#" class="nav-link active">📊 Dashboard</a></li>
                <li><a href="/Restaurant-Management-System/admin/menu.php" class="nav-link">📋 Menü</a></li>
                <li><a href="#" class="nav-link">📦 Siparişler</a></li>
                <li><a href="#" class="nav-link">🍽️ Masalar</a></li>
                <li><a href="#" class="nav-link">📦 Stok</a></li>
                <li><a href="#" class="nav-link">👥 Kullanıcılar</a></li>
                <li><a href="#" class="nav-link">⚙️ Ayarlar</a></li>
            </ul>
            <div class="nav-footer">
                <span class="user-name"><?php echo htmlspecialchars($username); ?></span>
                <a href="logout.php" class="logout-btn">Çıkış Yap</a>
            </div>
        </nav>

        <!-- Content -->
        <div class="admin-content">
            <header class="admin-header">
                <div class="header-top">
                    <div class="header-greeting">
                        <h1>İyi Akşamlar, <?php echo htmlspecialchars($username); ?></h1>
                        <p class="header-date"><?php echo $date_str; ?></p>
                        <p class="header-time"><?php echo $current_time; ?> • <?php echo $service_type; ?></p>
                    </div>
                    <div class="header-actions">
                        <button class="icon-btn">🔔</button>
                        <a href="logout.php" class="logout-link">Çıkış</a>
                    </div>
                </div>
            </header>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <h3>Günlük Kazanç</h3>
                    </div>
                    <p class="stat-value">₺2.450,50</p>
                    <p class="stat-change positive">↑ 15% dün'e göre</p>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <h3>Aktif Siparişler</h3>
                    </div>
                    <p class="stat-value">12</p>
                    <div class="stat-status">
                        <p class="status-line"><span class="status-dot preparing"></span>7 Hazırlanıyor</p>
                        <p class="status-line"><span class="status-dot ready"></span>5 Teslime Hazır</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <h3>Dolu Masalar</h3>
                    </div>
                    <p class="stat-value">12 / 25</p>
                    <p class="stat-progress">
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: 40%"></div>
                    </div>
                    </p>
                </div>

                <div class="stat-card staff-card" id="staffCard">
                    <div class="stat-header">
                        <h3>Aktif Personel</h3>
                    </div>
                    <p class="stat-value" id="activeStaffCount">9</p>
                    <p class="stat-change neutral">Hazır</p>
                </div>
            </div>

            <!-- Charts and Content Grid -->
            <div class="content-grid">
                <!-- Floor Plan -->
                <div class="card floor-plan-card">
                    <div class="card-header">
                        <h3>Kat Planı</h3>
                        <div class="legend">
                            <span class="legend-item"><span class="legend-dot"
                                    style="background: #10b981;"></span>Boş</span>
                            <span class="legend-item"><span class="legend-dot"
                                    style="background: #ef4444;"></span>Dolu</span>
                            <span class="legend-item"><span class="legend-dot"
                                    style="background: #f59e0b;"></span>Rezerve</span>
                        </div>
                    </div>

                    <!-- Ana Bölüm -->
                    <div class="floor-section">
                        <h4 class="section-title">Ana Salon</h4>
                        <div class="floor-container">
                            <div class="floor-grid main-section">
                                <div class="table">M1</div>
                                <div class="table occupied">M2</div>
                                <div class="table reserved">M3</div>
                                <div class="table occupied">M4</div>
                                <div class="table">M5</div>
                                <div class="table reserved">M6</div>
                                <div class="table">M7</div>
                                <div class="table occupied">M8</div>
                                <div class="table">M9</div>
                                <div class="table occupied">M10</div>
                                <div class="table">M11</div>
                                <div class="table reserved">M12</div>
                                <div class="table">M13</div>
                                <div class="table occupied">M14</div>
                                <div class="table">M15</div>
                            </div>
                            <div class="door-divider">GİRİŞ</div>
                        </div>
                    </div>

                    <!-- Divider -->
                    <div class="floor-divider">Bahçe</div>

                    <!-- Bahçe Bölümü -->
                    <div class="floor-section">
                        <div class="floor-grid garden-section">
                            <div class="table occupied">B1</div>
                            <div class="table reserved">B2</div>
                            <div class="table occupied">B3</div>
                            <div class="table">B4</div>
                            <div class="table occupied">B5</div>
                            <div class="table">B6</div>
                            <div class="table occupied">B7</div>
                            <div class="table">B8</div>
                            <div class="table occupied">B9</div>
                            <div class="table">B10</div>
                        </div>
                    </div>
                </div>

                <!-- Siparişler -->
                <div class="card stock-card">
                    <div class="card-header">
                        <h3>Gelen Siparişler</h3>
                    </div>
                    <div class="stock-list" id="ordersList">
                        <div class="order-request-item">
                            <div class="order-details">
                                <span class="order-table">M1</span>
                            </div>
                            <span class="order-request">Tavuk Şiş x2, Pilav, Salata</span>
                        </div>
                        <div class="order-request-item">
                            <div class="order-details">
                                <span class="order-table">M5</span>
                            </div>
                            <span class="order-request">Beyti Kebap, Garnitür</span>
                        </div>
                        <div class="order-request-item">
                            <div class="order-details">
                                <span class="order-table">M7</span>
                            </div>
                            <span class="order-request">Lahmacun x4, Ayran x2</span>
                        </div>
                        <div class="order-request-item">
                            <div class="order-details">
                                <span class="order-table">M9</span>
                            </div>
                            <span class="order-request">Urfa Kebap x1, Çay x2</span>
                        </div>
                        <div class="order-request-item">
                            <div class="order-details">
                                <span class="order-table">M11</span>
                            </div>
                            <span class="order-request">Kelle Paça Çorbası, Tost</span>
                        </div>
                        <div class="order-request-item">
                            <div class="order-details">
                                <span class="order-table">M13</span>
                            </div>
                            <span class="order-request">Manti, Böbrek, Limonata</span>
                        </div>
                        <div class="order-request-item">
                            <div class="order-details">
                                <span class="order-table">M15</span>
                            </div>
                            <span class="order-request">Döner Kebap, Meyve Suyu</span>
                        </div>
                        <div class="order-request-item">
                            <div class="order-details">
                                <span class="order-table">B4</span>
                            </div>
                            <span class="order-request">İçli Köfte, Ayran</span>
                        </div>
                        <div class="order-request-item">
                            <div class="order-details">
                                <span class="order-table">B6</span>
                            </div>
                            <span class="order-request">Cigkofte, Limonata</span>
                        </div>
                        <div class="order-request-item">
                            <div class="order-details">
                                <span class="order-table">B8</span>
                            </div>
                            <span class="order-request">Hamsi Tava, Ayran</span>
                        </div>
                        <div class="order-request-item">
                            <div class="order-details">
                                <span class="order-table">B10</span>
                            </div>
                            <span class="order-request">Karides Güveç, Çay</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <button class="btn btn--primary btn--large">+ Yeni Sipariş</button>
                <button class="btn btn--secondary btn--large">📅 Rezervasyon Ekle</button>
                <button class="btn btn--ghost btn--large">⚠️ Sorun Bildir</button>
            </div>
        </div>
    </div>
</main>

<!-- Modal Panel -->
<div id="tableModal" class="modal">
    <div class="modal-content">
        <button class="modal-close">&times;</button>

        <!-- Rezervasyon Panel (Sarı masalar için) -->
        <div id="reservationPanel" class="modal-panel">
            <h3>Rezervasyon Bilgisi</h3>
            <div class="panel-info">
                <div class="info-row">
                    <span class="info-label">Masa No:</span>
                    <span id="reservTableNo" class="info-value">-</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Müşteri Adı:</span>
                    <span id="reservCustomerName" class="info-value">-</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Rezervasyon Saati:</span>
                    <span id="reservTime" class="info-value">-</span>
                </div>
            </div>
        </div>

        <!-- Sipariş Panel (Yeşil masalar için) -->
        <div id="orderPanel" class="modal-panel">
            <h3>Sipariş Detayı</h3>
            <div class="panel-info">
                <div class="info-row">
                    <span class="info-label">Masa No:</span>
                    <span id="orderTableNo" class="info-value">-</span>
                </div>
            </div>
            <div class="orders-list">
                <div class="order-item">
                    <span class="order-name">Adana Kebap</span>
                    <span class="order-qty">x1</span>
                    <span class="order-price">₺145</span>
                </div>
                <div class="order-item">
                    <span class="order-name">Ayran</span>
                    <span class="order-qty">x2</span>
                    <span class="order-price">₺30</span>
                </div>
            </div>
            <div class="order-total">
                <span>Toplam:</span>
                <span>₺175</span>
            </div>
            <button class="btn btn--primary btn--block" style="margin-top: 16px;">💳 Ödemeyi Al</button>
        </div>

        <!-- Personel Panel -->
        <div id="staffPanel" class="modal-panel">
            <h3>Çalışan Listesi</h3>
            <div class="staff-list">
                <div class="staff-item">
                    <span class="staff-role">Garson Şefi</span>
                    <span class="staff-name working">Mehmet Özdemir</span>
                </div>
                <div class="staff-item">
                    <span class="staff-role">Garson</span>
                    <span class="staff-name working">Ahmet Yılmaz</span>
                </div>
                <div class="staff-item">
                    <span class="staff-role">Garson</span>
                    <span class="staff-name working">Fatma Kaya</span>
                </div>
                <div class="staff-item">
                    <span class="staff-role">Garson</span>
                    <span class="staff-name working">Ali Demir</span>
                </div>
                <div class="staff-item">
                    <span class="staff-role">Garson</span>
                    <span class="staff-name working">Zeynep Çelik</span>
                </div>
                <div class="staff-item">
                    <span class="staff-role">Garson</span>
                    <span class="staff-name not-working">Serkan Şahin</span>
                </div>
                <div class="staff-item">
                    <span class="staff-role">Garson</span>
                    <span class="staff-name working">Ayşe Kara</span>
                </div>
                <div class="staff-item">
                    <span class="staff-role">Garson</span>
                    <span class="staff-name working">Emre Yıldız</span>
                </div>
                <div class="staff-item">
                    <span class="staff-role">Garson</span>
                    <span class="staff-name working">Leyla Arslan</span>
                </div>
                <div class="staff-item">
                    <span class="staff-role">Garson</span>
                    <span class="staff-name not-working">Berkay Kılıç</span>
                </div>
                <div class="staff-item">
                    <span class="staff-role">Garson</span>
                    <span class="staff-name working">Nilay Güzel</span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include __DIR__ . '/../includes/layout/bottom.php';
?>

<script>
    // Tablo veri depolama
    const tableData = {
        'M1': { type: 'order', order: 'Tavuk Şiş x2, Pilav, Salata' },
        'M5': { type: 'order', order: 'Beyti Kebap, Garnitür' },
        'M7': { type: 'order', order: 'Lahmacun x4, Ayran x2' },
        'M9': { type: 'order', order: 'Urfa Kebap x1, Çay x2' },
        'M11': { type: 'order', order: 'Kelle Paça Çorbası, Tost' },
        'M13': { type: 'order', order: 'Manti, Böbrek, Limonata' },
        'M15': { type: 'order', order: 'Döner Kebap, Meyve Suyu' },
        'B4': { type: 'order', order: 'İçli Köfte, Ayran' },
        'B6': { type: 'order', order: 'Cigkofte, Limonata' },
        'B8': { type: 'order', order: 'Hamsi Tava, Ayran' },
        'B10': { type: 'order', order: 'Karides Güveç, Çay' },
        'M3': { type: 'reserved', customerName: 'Ahmet Yılmaz', reservTime: '19:30' },
        'M6': { type: 'reserved', customerName: 'Fatma Kaya', reservTime: '20:00' },
        'M12': { type: 'reserved', customerName: 'Ali Demir', reservTime: '21:15' },
        'B2': { type: 'reserved', customerName: 'Zeynep Çelik', reservTime: '19:45' }
    };

    // Modal elemanları
    const tableModal = document.getElementById('tableModal');
    const modalClose = document.querySelector('.modal-close');
    const reservationPanel = document.getElementById('reservationPanel');
    const orderPanel = document.getElementById('orderPanel');
    const staffPanel = document.getElementById('staffPanel');
    const staffCard = document.getElementById('staffCard');
    const activeStaffCount = document.getElementById('activeStaffCount');

    // Aktif garson sayısını güncelle
    function updateStaffCount() {
        const workingStaff = document.querySelectorAll('.staff-name.working').length;
        activeStaffCount.textContent = workingStaff;
    }

    // Personel kartına tıklama
    staffCard.addEventListener('click', function (e) {
        e.stopPropagation();
        tableModal.classList.add('active');

        reservationPanel.style.display = 'none';
        orderPanel.style.display = 'none';
        staffPanel.style.display = 'block';
    });

    // Garson isimlerine tıklama olayı - durumu değiştir
    document.querySelectorAll('.staff-name').forEach(staffName => {
        staffName.addEventListener('click', function (e) {
            e.stopPropagation();
            if (this.classList.contains('working')) {
                this.classList.remove('working');
                this.classList.add('not-working');
            } else {
                this.classList.remove('not-working');
                this.classList.add('working');
            }
            updateStaffCount();
        });
    });

    // Masalara tıklama olayı - sadece yeşil ve sarı masalara
    document.querySelectorAll('.table:not(.occupied)').forEach(table => {
        table.addEventListener('click', function (e) {
            e.stopPropagation();
            const tableNo = this.textContent.trim();
            const isReserved = this.classList.contains('reserved');

            // Modal'ı göster
            tableModal.classList.add('active');

            if (isReserved) {
                // Rezervasyon panelini göster
                reservationPanel.style.display = 'block';
                orderPanel.style.display = 'none';
                staffPanel.style.display = 'none';

                const data = tableData[tableNo] || {};
                document.getElementById('reservTableNo').textContent = tableNo;
                document.getElementById('reservCustomerName').textContent = data.customerName || '-';
                document.getElementById('reservTime').textContent = data.reservTime || '-';
            } else {
                // Sipariş panelini göster (yeşil masalar için)
                reservationPanel.style.display = 'none';
                orderPanel.style.display = 'block';
                staffPanel.style.display = 'none';

                document.getElementById('orderTableNo').textContent = tableNo;
                const data = tableData[tableNo] || {};
                const orderContent = document.querySelector('.order-item');
                if (orderContent && data.order) {
                    orderContent.innerHTML = `
                    <span class="order-name">${data.order}</span>
                    <span class="order-price">Göster</span>
                `;
                }
            }
        });
    });

    // Modal kapatma
    modalClose.addEventListener('click', () => {
        tableModal.classList.remove('active');
    });

    // Modal dışında tıklanırsa kapat
    tableModal.addEventListener('click', (e) => {
        if (e.target === tableModal) {
            tableModal.classList.remove('active');
        }
    });
</script>