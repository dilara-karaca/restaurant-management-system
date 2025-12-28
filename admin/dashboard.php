<?php
require_once __DIR__ . '/../includes/functions.php';
date_default_timezone_set('Europe/Istanbul');

requireAdmin();

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
                <li><a href="#" class="nav-link active">Dashboard</a></li>
                <li><a href="/Restaurant-Management-System/admin/menu.php" class="nav-link">Menü</a></li>
                <li><a href="/Restaurant-Management-System/admin/orders.php" class="nav-link">Siparişler</a></li>
                <li><a href="/Restaurant-Management-System/admin/reports.php" class="nav-link">Raporlar</a></li>
                <li><a href="/Restaurant-Management-System/admin/stock.php" class="nav-link">Stok</a></li>
                <li><a href="/Restaurant-Management-System/admin/users.php" class="nav-link">Kullanıcılar</a></li>
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
                        <h3>Aktif Siparişler</h3>
                    </div>
                    <p class="stat-value" id="activeOrdersCount">0</p>
                    <div class="stat-status">
                        <p class="status-line"><span class="status-dot pending"></span><span id="pendingOrdersCount">0</span> Beklemede</p>
                        <p class="status-line"><span class="status-dot preparing"></span><span id="preparingOrdersCount">0</span> Hazırlanıyor</p>
                        <p class="status-line"><span class="status-dot ready"></span><span id="servedOrdersCount">0</span> Servis Edildi</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <h3>Dolu Masalar</h3>
                    </div>
                    <p class="stat-value" id="occupiedTablesValue">0 / 0</p>
                    <p class="stat-progress">
                    <div class="progress-bar">
                        <div class="progress-fill" id="occupiedTablesProgress" style="width: 0%"></div>
                    </div>
                    </p>
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
                        </div>
                    </div>

                    <!-- Ana Bölüm -->
                    <div class="floor-section">
                        <h4 class="section-title">Ana Salon</h4>
                        <div class="floor-container">
                            <div class="floor-grid main-section" id="mainSectionTables">
                                <!-- Masalar buraya dinamik olarak yüklenecek -->
                                <div style="grid-column: 1 / -1; text-align: center; padding: 20px; color: var(--muted);">
                                    Yükleniyor...
                                </div>
                            </div>
                            <div class="door-divider">GİRİŞ</div>
                        </div>
                    </div>

                    <!-- Divider -->
                    <div class="floor-divider">Bahçe</div>

                    <!-- Bahçe Bölümü -->
                    <div class="floor-section">
                        <div class="floor-grid garden-section" id="gardenSectionTables">
                            <!-- Masalar buraya dinamik olarak yüklenecek -->
                        </div>
                    </div>
                </div>

                <!-- Siparişler -->
                <div class="card stock-card">
                    <div class="card-header">
                        <h3>Gelen Siparişler</h3>
                    </div>
                    <div class="stock-list" id="ordersList">
                        <div style="padding: 20px; text-align: center; color: var(--muted);">Yükleniyor...</div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
        </div>
    </div>
</main>

<!-- Modal Panel -->
<div id="tableModal" class="modal">
    <div class="modal-content">
        <button class="modal-close">&times;</button>


        <!-- Sipariş Panel (Kırmızı masalar için - Ödeme) -->
        <div id="orderPanel" class="modal-panel">
            <h3>Sipariş Detayı</h3>
            <div class="panel-info">
                <div class="info-row">
                    <span class="info-label">Masa No:</span>
                    <span id="orderTableNo" class="info-value">-</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Müşteri:</span>
                    <span id="orderCustomerName" class="info-value">-</span>
                </div>
            </div>
            <div class="orders-list" id="orderItemsList">
                <!-- Sipariş kalemleri buraya dinamik olarak eklenecek -->
            </div>
            <div class="order-total">
                <span>Toplam:</span>
                <span id="orderTotalAmount">₺0.00</span>
            </div>
            <div class="form-group" style="margin-top: 16px;">
                <label>Ödeme Yöntemi *</label>
                <select id="paymentMethodSelect" class="form-input">
                    <option value="">Seçiniz</option>
                    <option value="Cash">Nakit</option>
                    <option value="Credit Card">Kredi Kartı</option>
                    <option value="Debit Card">Banka Kartı</option>
                    <option value="Mobile Payment">Mobil Ödeme</option>
                </select>
            </div>
            <button id="completePaymentBtn" class="btn btn--primary btn--block" style="margin-top: 16px;">💳 Ödemeyi Al</button>
        </div>

        <!-- Sipariş Bilgi Panel (Yeşil/Sarı masalar için) -->
        <div id="orderInfoPanel" class="modal-panel">
            <h3>Sipariş Bilgisi</h3>
            <div class="panel-info">
                <div class="info-row">
                    <span class="info-label">Masa No:</span>
                    <span id="infoTableNo" class="info-value">-</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Müşteri:</span>
                    <span id="infoCustomerName" class="info-value">-</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Durum:</span>
                    <span id="infoOrderStatus" class="info-value">-</span>
                </div>
            </div>
            <div class="orders-list" id="infoOrderItemsList">
                <!-- Sipariş kalemleri buraya dinamik olarak eklenecek -->
            </div>
            <div class="order-total">
                <span>Toplam:</span>
                <span id="infoTotalAmount">₺0.00</span>
            </div>
        </div>

        <!-- Sipariş Oluştur Panel (Masa Seçimi) -->
        <div id="orderCreatePanel" class="modal-panel">
            <h3>Sipariş Ekle - Masa Seçin</h3>
            <div class="tables-selection">
                <div id="tableSelectionContainer" class="tables-grid">
                    <!-- Masalar buraya dinamik olarak yüklenecek -->
                </div>
            </div>
        </div>

        <!-- Menü Seçimi Panel -->
        <div id="menuPanel" class="modal-panel">
            <div class="menu-header">
                <button id="backToTables" class="btn btn--secondary btn--small">←</button>
                <h3 id="selectedTableDisplay">Masa Seçin</h3>
            </div>
            <div class="menu-list">
                <h4>Menü (Yakında eklenecek)</h4>
                <p style="color: var(--muted); font-size: 14px;">Menü öğeleri burada listelenecektir</p>
            </div>
        </div>

    </div>
</div>

<?php
include __DIR__ . '/../includes/layout/bottom.php';
?>

<script>
    // API Base URL
    const apiBase = '../api';
    let tablesData = {};
    let currentOrderId = null;

    // Modal elemanları
    const tableModal = document.getElementById('tableModal');
    const modalClose = document.querySelector('.modal-close');
    const orderPanel = document.getElementById('orderPanel');
    const orderInfoPanel = document.getElementById('orderInfoPanel');
    const completePaymentBtn = document.getElementById('completePaymentBtn');
    const paymentMethodSelect = document.getElementById('paymentMethodSelect');

    // Fetch helper
    async function fetchJson(url, options = {}) {
        const response = await fetch(url, options);
        const data = await response.json();
        if (!data.success) {
            throw new Error(data.message || 'Bir hata oluştu');
        }
        return data;
    }

    const isGardenLocation = (location) => {
        if (!location) return false;
        const normalized = location.toLowerCase();
        return normalized.includes('bahçe') || normalized.includes('bahce') || normalized.includes('garden');
    };

    const getDisplayNumber = (table) => {
        if (!table) return '';
        if (isGardenLocation(table.location) && Number(table.table_number) > 15) {
            return Number(table.table_number) - 15;
        }
        return table.table_number;
    };

    const getTableLabel = (table) => {
        const prefix = isGardenLocation(table.location) ? 'B' : 'M';
        return `${prefix}${getDisplayNumber(table)}`;
    };

    // Masaları yükle
    async function loadTables() {
        try {
            const data = await fetchJson(`${apiBase}/tables/list.php`);
            tablesData = {};
            
            // Masaları table_number ve location'a göre indexle
            data.data.forEach(table => {
                // Location'a göre prefix belirle
                const prefix = isGardenLocation(table.location) ? 'B' : 'M';
                table.display_number = getDisplayNumber(table);
                const tableKey = `${prefix}${table.display_number}`;
                tablesData[tableKey] = table;
            });
            
            renderTables();
            updateOrdersList();
            updateStats();
        } catch (error) {
            console.error('Masalar yüklenemedi:', error);
        }
    }

    // Masaları render et
    function renderTables() {
        const mainSection = document.getElementById('mainSectionTables');
        const gardenSection = document.getElementById('gardenSectionTables');
        
        if (!mainSection || !gardenSection) return;
        
        mainSection.innerHTML = '';
        gardenSection.innerHTML = '';
        
        // Ana salon masaları (M1-M15)
        for (let i = 1; i <= 15; i++) {
            const tableKey = `M${i}`;
            const table = tablesData[tableKey];
            const tableElement = createTableElement(tableKey, table);
            mainSection.appendChild(tableElement);
        }
        
        // Bahçe masaları (B1-B10)
        for (let i = 1; i <= 10; i++) {
            const tableKey = `B${i}`;
            const table = tablesData[tableKey];
            const tableElement = createTableElement(tableKey, table);
            gardenSection.appendChild(tableElement);
        }
        
        // Event listener'ları ekle
        attachTableListeners();
    }

    // Masa elementi oluştur
    function createTableElement(tableKey, table) {
        const div = document.createElement('div');
        div.className = 'table';
        div.dataset.tableKey = tableKey;
        div.dataset.tableId = table ? table.table_id : '';
        div.textContent = tableKey;
        
        if (table) {
            if (table.status === 'Occupied') {
                div.classList.add('occupied');
            }
            // Reserved durumu artık kullanılmıyor
        }
        
        return div;
    }

    // Masa event listener'larını ekle
    function attachTableListeners() {
        document.querySelectorAll('.table').forEach(table => {
            // Eski listener'ı kaldır
            table.replaceWith(table.cloneNode(true));
        });
        
        document.querySelectorAll('.table').forEach(table => {
            table.addEventListener('click', function(e) {
                e.stopPropagation();
                const tableKey = this.dataset.tableKey;
                const table = tablesData[tableKey];
                
                if (!table) return;
                
                tableModal.classList.add('active');
                
                if (table.status === 'Occupied') {
                    // Kırmızı masa - Ödeme paneli
                    showPaymentPanel(table);
                } else {
                    // Yeşil masa - Sipariş bilgi paneli (eğer sipariş varsa)
                    showOrderInfoPanel(table);
                }
            });
        });
    }

    // Ödeme panelini göster (Kırmızı masalar)
    function showPaymentPanel(table) {
        if (orderInfoPanel) orderInfoPanel.style.display = 'none';
        if (orderPanel) orderPanel.style.display = 'block';
        
        document.getElementById('orderTableNo').textContent = getTableLabel(table);
        document.getElementById('orderCustomerName').textContent = table.customer_name || '-';
        
        const itemsList = document.getElementById('orderItemsList');
        itemsList.innerHTML = '';
        
        if (table.items && table.items.length > 0) {
            table.items.forEach(item => {
                const itemDiv = document.createElement('div');
                itemDiv.className = 'order-item';
                itemDiv.innerHTML = `
                    <span class="order-name">${item.product_name}</span>
                    <span class="order-qty">x${item.quantity}</span>
                    <span class="order-price">₺${parseFloat(item.subtotal).toFixed(2)}</span>
                `;
                itemsList.appendChild(itemDiv);
            });
        } else {
            itemsList.innerHTML = '<div style="padding: 20px; text-align: center; color: var(--muted);">Sipariş kalemi bulunamadı</div>';
        }
        
        document.getElementById('orderTotalAmount').textContent = `₺${parseFloat(table.total_amount || 0).toFixed(2)}`;
        currentOrderId = table.order_id;
        if (paymentMethodSelect) paymentMethodSelect.value = '';
    }


    // Sipariş bilgi panelini göster (Yeşil masalar)
    function showOrderInfoPanel(table) {
        if (!orderInfoPanel) return;
        
        if (orderPanel) orderPanel.style.display = 'none';
        orderInfoPanel.style.display = 'block';

        document.getElementById('infoTableNo').textContent = getTableLabel(table);
        document.getElementById('infoCustomerName').textContent = table.customer_name || '-';
        document.getElementById('infoOrderStatus').textContent = table.order_status || '-';
        
        const itemsList = document.getElementById('infoOrderItemsList');
        itemsList.innerHTML = '';
        
        if (table.items && table.items.length > 0) {
            table.items.forEach(item => {
                const itemDiv = document.createElement('div');
                itemDiv.className = 'order-item';
                itemDiv.innerHTML = `
                    <span class="order-name">${item.product_name}</span>
                    <span class="order-qty">x${item.quantity}</span>
                    <span class="order-price">₺${parseFloat(item.subtotal).toFixed(2)}</span>
                `;
                itemsList.appendChild(itemDiv);
            });
        } else {
            itemsList.innerHTML = '<div style="padding: 20px; text-align: center; color: var(--muted);">Bu masada aktif sipariş yok</div>';
        }
        
        document.getElementById('infoTotalAmount').textContent = `₺${parseFloat(table.total_amount || 0).toFixed(2)}`;
    }

    // Ödeme tamamla
    if (completePaymentBtn) {
        completePaymentBtn.addEventListener('click', async function() {
            if (!currentOrderId) {
                alert('Sipariş bulunamadı');
                return;
            }
            
            const paymentMethod = paymentMethodSelect ? paymentMethodSelect.value : '';
            if (!paymentMethod) {
                alert('Lütfen ödeme yöntemi seçiniz');
                return;
            }
            
            if (!confirm('Ödemeyi tamamlamak istediğinizden emin misiniz?')) {
                return;
            }
            
            try {
                const formData = new URLSearchParams();
                formData.append('order_id', currentOrderId);
                formData.append('payment_method', paymentMethod);
                
                await fetchJson(`${apiBase}/orders/complete_payment.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: formData
                });
                
                alert('Ödeme başarıyla alındı!');
                tableModal.classList.remove('active');
                loadTables(); // Masaları yeniden yükle
            } catch (error) {
                alert('Hata: ' + error.message);
            }
        });
    }

    // Sipariş listesini güncelle
    function updateOrdersList() {
        const ordersList = document.getElementById('ordersList');
        if (!ordersList) return;
        
        ordersList.innerHTML = '';
        
        Object.values(tablesData).forEach(table => {
            if (table.status === 'Occupied' && table.items && table.items.length > 0) {
                const orderDiv = document.createElement('div');
                orderDiv.className = 'order-request-item';
                const itemsText = table.items.map(item => `${item.product_name} x${item.quantity}`).join(', ');
                
                orderDiv.innerHTML = `
                    <div class="order-details">
                        <span class="order-table">${getTableLabel(table)}</span>
                    </div>
                    <span class="order-request">${itemsText}</span>
                `;
                ordersList.appendChild(orderDiv);
            }
        });
        
        if (ordersList.children.length === 0) {
            ordersList.innerHTML = '<div style="padding: 20px; text-align: center; color: var(--muted);">Aktif sipariş yok</div>';
        }
    }

    // İstatistikleri güncelle
    function updateStats() {
        const occupiedCount = Object.values(tablesData).filter(t => t.status === 'Occupied').length;
        const totalTables = Object.keys(tablesData).length;
        const occupiedValue = document.getElementById('occupiedTablesValue');
        const progressFill = document.getElementById('occupiedTablesProgress');
        if (occupiedValue) {
            occupiedValue.textContent = `${occupiedCount} / ${totalTables}`;
        }
        if (progressFill && totalTables > 0) {
            progressFill.style.width = `${(occupiedCount / totalTables) * 100}%`;
        } else if (progressFill) {
            progressFill.style.width = '0%';
        }

        const activeOrders = Object.values(tablesData).filter(
            t => t.order_id && ['Pending', 'Preparing', 'Served'].includes(t.order_status)
        );
        const pendingCount = activeOrders.filter(t => t.order_status === 'Pending').length;
        const preparingCount = activeOrders.filter(t => t.order_status === 'Preparing').length;
        const servedCount = activeOrders.filter(t => t.order_status === 'Served').length;

        const activeOrdersCount = document.getElementById('activeOrdersCount');
        const pendingOrdersCount = document.getElementById('pendingOrdersCount');
        const preparingOrdersCount = document.getElementById('preparingOrdersCount');
        const servedOrdersCount = document.getElementById('servedOrdersCount');

        if (activeOrdersCount) {
            activeOrdersCount.textContent = String(activeOrders.length);
        }
        if (pendingOrdersCount) {
            pendingOrdersCount.textContent = String(pendingCount);
        }
        if (preparingOrdersCount) {
            preparingOrdersCount.textContent = String(preparingCount);
        }
        if (servedOrdersCount) {
            servedOrdersCount.textContent = String(servedCount);
        }
    }


    // Sayfa yüklendiğinde masaları yükle
    document.addEventListener('DOMContentLoaded', function() {
        loadTables();
        // Her 10 saniyede bir masaları güncelle
        setInterval(loadTables, 10000);
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
