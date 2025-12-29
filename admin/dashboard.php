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
                        <button id="openReservationBtn" class="btn btn--secondary">Rezervasyon Ekle</button>
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
                            <span class="legend-item"><span class="legend-dot"
                                    style="background: #f59e0b;"></span>Rezervasyon</span>
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
            <p id="paymentStatusNote" class="payment-status-note" style="margin-top: 16px;"></p>
            <button id="completePaymentBtn" class="btn btn--primary btn--block" style="margin-top: 16px;">🗑️ Masayı Boşalt</button>
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
            <div id="reservationDetails" class="panel-info" style="display: none;">
                <div class="info-row">
                    <span class="info-label">Rezervasyon:</span>
                    <span id="reservationCustomer" class="info-value">-</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Saat:</span>
                    <span id="reservationTimeValue" class="info-value">-</span>
                </div>
            </div>
            <div class="orders-list" id="infoOrderItemsList">
                <!-- Sipariş kalemleri buraya dinamik olarak eklenecek -->
            </div>
            <div class="order-total">
                <span>Toplam:</span>
                <span id="infoTotalAmount">₺0.00</span>
            </div>
            <button id="cancelReservationTableBtn" class="btn btn--secondary btn--block" style="margin-top: 16px; display: none;">Rezervasyonu İptal Et</button>
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

<div id="reservationModal" class="modal">
    <div class="modal-content modal-wide">
        <button class="modal-close" id="reservationModalClose">&times;</button>
        <div class="modal-title-row">
            <h3>Rezervasyon Ekle</h3>
        </div>
        <p class="helper-text">Uygun masayı seçin.</p>
        <div id="reservationTablesGrid" class="tables-grid"></div>
    </div>
</div>

<div id="reservationFormModal" class="modal">
    <div class="modal-content">
        <button class="modal-close" id="reservationFormClose">&times;</button>
        <div class="modal-title-row">
            <h3>Rezervasyon Bilgisi</h3>
        </div>
        <div class="panel-info">
            <div class="info-row">
                <span class="info-label">Masa:</span>
                <span id="reservationTableLabel" class="info-value">-</span>
            </div>
        </div>
        <div class="field">
            <label class="field__label" for="reservationFirstName">Ad</label>
            <div class="field__control">
                <input id="reservationFirstName" class="input" type="text" placeholder="Ad">
            </div>
        </div>
        <div class="field">
            <label class="field__label" for="reservationLastName">Soyad</label>
            <div class="field__control">
                <input id="reservationLastName" class="input" type="text" placeholder="Soyad">
            </div>
        </div>
        <div class="field">
            <label class="field__label" for="reservationTime">Saat</label>
            <div class="field__control">
                <input id="reservationTime" class="input" type="time">
            </div>
        </div>
        <div class="modal-title-row" style="justify-content: flex-end; margin-top: 12px;">
            <button id="cancelReservationBtn" class="btn btn--secondary">Vazgeç</button>
            <button id="saveReservationBtn" class="btn btn--primary">Rezervasyonu Kaydet</button>
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
    let currentTableId = null;

    // Modal elemanları
    const tableModal = document.getElementById('tableModal');
    const modalClose = document.querySelector('#tableModal .modal-close');
    const orderPanel = document.getElementById('orderPanel');
    const orderInfoPanel = document.getElementById('orderInfoPanel');
    const completePaymentBtn = document.getElementById('completePaymentBtn');
    const paymentMethodSelect = document.getElementById('paymentMethodSelect');
    const paymentStatusNote = document.getElementById('paymentStatusNote');
    const openReservationBtn = document.getElementById('openReservationBtn');
    const reservationModal = document.getElementById('reservationModal');
    const reservationModalClose = document.getElementById('reservationModalClose');
    const reservationTablesGrid = document.getElementById('reservationTablesGrid');
    const reservationFormModal = document.getElementById('reservationFormModal');
    const reservationFormClose = document.getElementById('reservationFormClose');
    const reservationTableLabel = document.getElementById('reservationTableLabel');
    const reservationFirstName = document.getElementById('reservationFirstName');
    const reservationLastName = document.getElementById('reservationLastName');
    const reservationTime = document.getElementById('reservationTime');
    const saveReservationBtn = document.getElementById('saveReservationBtn');
    const cancelReservationBtn = document.getElementById('cancelReservationBtn');
    const reservationDetails = document.getElementById('reservationDetails');
    const reservationCustomer = document.getElementById('reservationCustomer');
    const reservationTimeValue = document.getElementById('reservationTimeValue');
    const cancelReservationTableBtn = document.getElementById('cancelReservationTableBtn');

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

    const paymentMethodLabels = {
        'Cash': 'Nakit',
        'Credit Card': 'Kredi Kartı',
        'Debit Card': 'Banka Kartı',
        'Mobile Payment': 'Mobil Ödeme'
    };

    const formatPaymentMethod = (method) => {
        return paymentMethodLabels[method] || method || '-';
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

    function renderReservationTables() {
        if (!reservationTablesGrid) return;
        const availableTables = Object.values(tablesData).filter(table => table.status === 'Available');
        if (!availableTables.length) {
            reservationTablesGrid.innerHTML = '<div style="padding: 16px; color: var(--muted);">Uygun masa bulunamadı.</div>';
            return;
        }

        reservationTablesGrid.innerHTML = availableTables.map(table => {
            const label = getTableLabel(table);
            return `
                <div class="table-selection-item" data-table-id="${table.table_id}" data-table-label="${label}">
                    <div class="table-item-box empty">
                        <div class="table-item-no">${label}</div>
                        <div class="table-item-label">Müsait</div>
                    </div>
                </div>
            `;
        }).join('');
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
            } else if (table.status === 'Reserved') {
                div.classList.add('reserved');
            }
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
        currentTableId = table.table_id;
        const hasPayment = !!(table.payment_method);
        
        if (completePaymentBtn) {
            completePaymentBtn.disabled = false;
            completePaymentBtn.textContent = '🗑️ Masayı Boşalt';
        }
        if (paymentStatusNote) {
            if (hasPayment) {
                paymentStatusNote.textContent = `Ödeme alındı (${formatPaymentMethod(table.payment_method)}).`;
                paymentStatusNote.classList.add('is-paid');
            } else {
                paymentStatusNote.textContent = 'Ödeme bekleniyor.';
                paymentStatusNote.classList.remove('is-paid');
            }
        }
    }


    // Sipariş bilgi panelini göster (Yeşil masalar)
    function showOrderInfoPanel(table) {
        if (!orderInfoPanel) return;
        
        if (orderPanel) orderPanel.style.display = 'none';
        orderInfoPanel.style.display = 'block';

        const isReserved = table.status === 'Reserved';
        document.getElementById('infoTableNo').textContent = getTableLabel(table);
        document.getElementById('infoCustomerName').textContent = table.customer_name || (isReserved ? table.reservation_name || '-' : '-');
        const infoStatus = table.order_status || (isReserved ? 'Rezervasyon' : '-');
        document.getElementById('infoOrderStatus').textContent = infoStatus;
        
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
            itemsList.innerHTML = isReserved
                ? '<div style="padding: 20px; text-align: center; color: var(--muted);">Rezervasyonlu masa</div>'
                : '<div style="padding: 20px; text-align: center; color: var(--muted);">Bu masada aktif sipariş yok</div>';
        }
        
        document.getElementById('infoTotalAmount').textContent = `₺${parseFloat(table.total_amount || 0).toFixed(2)}`;

        if (reservationDetails && reservationCustomer && reservationTimeValue && cancelReservationTableBtn) {
            if (isReserved) {
                reservationCustomer.textContent = table.reservation_name || '-';
                reservationTimeValue.textContent = table.reservation_time || '-';
                reservationDetails.style.display = 'block';
                cancelReservationTableBtn.style.display = 'block';
                cancelReservationTableBtn.dataset.reservationId = table.reservation_id || '';
                cancelReservationTableBtn.dataset.tableId = table.table_id || '';
            } else {
                reservationDetails.style.display = 'none';
                cancelReservationTableBtn.style.display = 'none';
                cancelReservationTableBtn.dataset.reservationId = '';
                cancelReservationTableBtn.dataset.tableId = '';
            }
        }
    }

    function openReservationModal() {
        if (!reservationModal) return;
        renderReservationTables();
        reservationModal.classList.add('active');
    }

    function closeReservationModal() {
        if (!reservationModal) return;
        reservationModal.classList.remove('active');
    }

    function openReservationForm(tableId, tableLabel) {
        if (!reservationFormModal) return;
        reservationFormModal.dataset.tableId = tableId;
        if (reservationTableLabel) reservationTableLabel.textContent = tableLabel;
        if (reservationFirstName) reservationFirstName.value = '';
        if (reservationLastName) reservationLastName.value = '';
        if (reservationTime) reservationTime.value = '';
        reservationFormModal.classList.add('active');
    }

    function closeReservationForm() {
        if (!reservationFormModal) return;
        reservationFormModal.classList.remove('active');
        reservationFormModal.dataset.tableId = '';
    }

    async function saveReservation() {
        const tableId = reservationFormModal ? parseInt(reservationFormModal.dataset.tableId || '', 10) : 0;
        const firstName = reservationFirstName ? reservationFirstName.value.trim() : '';
        const lastName = reservationLastName ? reservationLastName.value.trim() : '';
        const reservedAt = reservationTime ? reservationTime.value : '';

        if (!tableId) {
            alert('Masa seçimi bulunamadı.');
            return;
        }
        if (!firstName || !lastName) {
            alert('Lütfen ad ve soyad giriniz.');
            return;
        }
        if (!reservedAt) {
            alert('Lütfen saat seçiniz.');
            return;
        }

        try {
            const formData = new URLSearchParams();
            formData.append('table_id', tableId);
            formData.append('first_name', firstName);
            formData.append('last_name', lastName);
            formData.append('reserved_at', reservedAt);

            await fetchJson(`${apiBase}/reservations/create.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData
            });

            closeReservationForm();
            closeReservationModal();
            loadTables();
            alert('Rezervasyon kaydedildi.');
        } catch (error) {
            alert('Hata: ' + error.message);
        }
    }

    async function cancelReservation() {
        const reservationId = cancelReservationTableBtn ? parseInt(cancelReservationTableBtn.dataset.reservationId || '', 10) : 0;
        const tableId = cancelReservationTableBtn ? parseInt(cancelReservationTableBtn.dataset.tableId || '', 10) : 0;
        if (!reservationId && !tableId) {
            alert('Rezervasyon bulunamadı.');
            return;
        }
        if (!confirm('Rezervasyonu iptal etmek istiyor musunuz?')) {
            return;
        }
        try {
            const formData = new URLSearchParams();
            if (reservationId) formData.append('reservation_id', reservationId);
            if (tableId) formData.append('table_id', tableId);

            await fetchJson(`${apiBase}/reservations/cancel.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData
            });

            tableModal.classList.remove('active');
            loadTables();
            alert('Rezervasyon iptal edildi.');
        } catch (error) {
            alert('Hata: ' + error.message);
        }
    }

    // Masayı boşalt
    if (completePaymentBtn) {
        completePaymentBtn.addEventListener('click', async function() {
            if (!currentTableId) {
                alert('Masa bulunamadı');
                return;
            }
            
            if (!confirm('Masayı boşaltmak istediğinizden emin misiniz?')) {
                return;
            }
            
            try {
                const formData = new URLSearchParams();
                formData.append('table_id', currentTableId);
                
                await fetchJson(`${apiBase}/tables/clear.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: formData
                });
                
                alert('Masa başarıyla boşaltıldı!');
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

    if (openReservationBtn) {
        openReservationBtn.addEventListener('click', openReservationModal);
    }

    if (reservationModalClose) {
        reservationModalClose.addEventListener('click', closeReservationModal);
    }

    if (reservationModal) {
        reservationModal.addEventListener('click', (e) => {
            if (e.target === reservationModal) {
                closeReservationModal();
            }
        });
    }

    if (reservationTablesGrid) {
        reservationTablesGrid.addEventListener('click', (event) => {
            const target = event.target.closest('.table-selection-item');
            if (!target) return;
            const tableId = target.dataset.tableId;
            const tableLabel = target.dataset.tableLabel;
            openReservationForm(tableId, tableLabel);
        });
    }

    if (reservationFormClose) {
        reservationFormClose.addEventListener('click', closeReservationForm);
    }

    if (cancelReservationBtn) {
        cancelReservationBtn.addEventListener('click', closeReservationForm);
    }

    if (reservationFormModal) {
        reservationFormModal.addEventListener('click', (event) => {
            if (event.target === reservationFormModal) {
                closeReservationForm();
            }
        });
    }

    if (saveReservationBtn) {
        saveReservationBtn.addEventListener('click', saveReservation);
    }

    if (cancelReservationTableBtn) {
        cancelReservationTableBtn.addEventListener('click', cancelReservation);
    }
</script>
