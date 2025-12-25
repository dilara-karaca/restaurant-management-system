(() => {
    const ordersTableBody = document.getElementById('ordersTableBody');
    if (!ordersTableBody) return;

    const apiBase = '/Restaurant-Management-System/api/orders';

    const notice = document.getElementById('ordersNotice');
    const unassignedNotice = document.getElementById('unassignedNotice');
    const filterSelect = document.getElementById('orderStatusFilter');
    const refreshBtn = document.getElementById('refreshOrdersBtn');
    const modal = document.getElementById('orderManageModal');
    const modalClose = document.getElementById('orderManageClose');

    const orderMetaId = document.getElementById('orderMetaId');
    const orderMetaTable = document.getElementById('orderMetaTable');
    const orderMetaCustomer = document.getElementById('orderMetaCustomer');
    const orderMetaDate = document.getElementById('orderMetaDate');
    const orderStatusSelect = document.getElementById('orderStatusSelect');
    const updateStatusBtn = document.getElementById('updateStatusBtn');
    const paymentMethodSelect = document.getElementById('paymentMethodSelect');
    const orderItemsList = document.getElementById('orderItemsList');
    const orderTotalValue = document.getElementById('orderTotalValue');
    const unassignedOrdersTableBody = document.getElementById('unassignedOrdersTableBody');

    let orders = [];
    let unassignedOrders = [];
    let activeOrderId = null;

    const statusLabels = {
        Pending: 'Beklemede',
        Preparing: 'Hazırlanıyor',
        Served: 'Servis Edildi',
        Completed: 'Tamamlandı',
        Cancelled: 'İptal'
    };

    const statusClass = {
        Pending: 'pending',
        Preparing: 'preparing',
        Served: 'served',
        Completed: 'completed',
        Cancelled: 'cancelled'
    };

    const formatCurrency = (amount) => {
        const value = Number(amount || 0);
        return `₺${value.toFixed(2)}`;
    };

    const formatDate = (value) => {
        if (!value) return '-';
        const date = new Date(value.replace(' ', 'T'));
        if (Number.isNaN(date.getTime())) return value;
        return date.toLocaleString('tr-TR', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });
    };

    const showNotice = (target, message, type = 'success') => {
        if (!target) return;
        target.textContent = message;
        target.classList.remove('error');
        target.classList.add('show');
        if (type === 'error') {
            target.classList.add('error');
        }
        window.clearTimeout(target.dataset.timerId);
        target.dataset.timerId = window.setTimeout(() => {
            target.classList.remove('show');
        }, 3500);
    };

    const fetchJson = async (url, options = {}) => {
        const response = await fetch(url, options);
        const data = await response.json();
        if (!data.success) {
            throw new Error(data.message || 'İşlem başarısız');
        }
        return data;
    };

    const renderOrders = () => {
        const filter = filterSelect ? filterSelect.value : '';
        const filtered = filter ? orders.filter((order) => order.status === filter) : orders;

        if (!filtered.length) {
            ordersTableBody.innerHTML = `
                <tr>
                    <td colspan="8">Sipariş bulunamadı.</td>
                </tr>
            `;
            return;
        }

        ordersTableBody.innerHTML = filtered.map((order) => {
            const itemsSummary = order.items && order.items.length
                ? order.items.map((item) => `${item.product_name} x${item.quantity}`).join(', ')
                : '-';
            const badgeClass = statusClass[order.status] || 'pending';
            const statusLabel = statusLabels[order.status] || order.status;
            return `
                <tr>
                    <td>${order.order_id}</td>
                    <td>${order.table_number}</td>
                    <td>${order.customer_name || '-'}</td>
                    <td>${itemsSummary}</td>
                    <td><span class="status-badge ${badgeClass}">${statusLabel}</span></td>
                    <td>${formatCurrency(order.total_amount)}</td>
                    <td>${formatDate(order.order_date)}</td>
                    <td>
                        <button class="btn btn--secondary btn--small" data-action="manage" data-order-id="${order.order_id}">
                            Görüntüle
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
    };

    const renderUnassignedOrders = () => {
        if (!unassignedOrdersTableBody) return;
        if (!unassignedOrders.length) {
            unassignedOrdersTableBody.innerHTML = `
                <tr>
                    <td colspan="8">Atanmamış sipariş bulunamadı.</td>
                </tr>
            `;
            return;
        }

        unassignedOrdersTableBody.innerHTML = unassignedOrders.map((order) => {
            const itemsSummary = order.items && order.items.length
                ? order.items.map((item) => `${item.product_name} x${item.quantity}`).join(', ')
                : '-';
            const badgeClass = statusClass[order.status] || 'pending';
            const statusLabel = statusLabels[order.status] || order.status;
            return `
                <tr>
                    <td>${order.order_id}</td>
                    <td>${order.table_number}</td>
                    <td>${order.customer_name || '-'}</td>
                    <td>${itemsSummary}</td>
                    <td><span class="status-badge ${badgeClass}">${statusLabel}</span></td>
                    <td>${formatCurrency(order.total_amount)}</td>
                    <td>${formatDate(order.order_date)}</td>
                    <td>
                        <button class="btn btn--primary btn--small" data-action="assign" data-order-id="${order.order_id}">
                            Üstlen
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
    };

    const renderOrderItems = (order) => {
        if (!orderItemsList) return;
        if (!order.items || !order.items.length) {
            orderItemsList.innerHTML = '<div class="order-item-row">Sipariş kalemi yok.</div>';
            return;
        }

        orderItemsList.innerHTML = order.items.map((item) => `
            <div class="order-item-row">
                <span>${item.product_name}</span>
                <span>${item.quantity}</span>
                <span>${formatCurrency(item.unit_price)}</span>
                <span>${formatCurrency(item.subtotal)}</span>
            </div>
        `).join('');
    };

    const openModal = (orderId) => {
        const order = orders.find((item) => item.order_id === orderId);
        if (!order || !modal) return;
        activeOrderId = orderId;

        orderMetaId.textContent = order.order_id;
        orderMetaTable.textContent = order.table_number;
        orderMetaCustomer.textContent = order.customer_name || '-';
        orderMetaDate.textContent = formatDate(order.order_date);
        orderStatusSelect.value = order.status;
        if (paymentMethodSelect) {
            paymentMethodSelect.value = order.payment_method || '';
        }
        orderTotalValue.textContent = formatCurrency(order.total_amount);

        renderOrderItems(order);
        modal.classList.add('active');
    };

    const closeModal = () => {
        if (!modal) return;
        modal.classList.remove('active');
        activeOrderId = null;
    };

    const loadOrders = async () => {
        try {
            const data = await fetchJson(`${apiBase}/list_by_personnel.php`);
            orders = data.data || [];
            renderOrders();
        } catch (error) {
            showNotice(notice, error.message, 'error');
        }
    };

    const loadUnassignedOrders = async () => {
        if (!unassignedOrdersTableBody) return;
        try {
            const data = await fetchJson(`${apiBase}/list_unassigned.php`);
            unassignedOrders = data.data || [];
            renderUnassignedOrders();
        } catch (error) {
            showNotice(unassignedNotice, error.message, 'error');
        }
    };

    const updateOrderStatus = async () => {
        if (!activeOrderId) return;
        const status = orderStatusSelect.value;
        const paymentMethod = paymentMethodSelect ? paymentMethodSelect.value : '';
        try {
            const payload = new URLSearchParams({
                order_id: activeOrderId,
                status
            });
            if (paymentMethod) {
                payload.append('payment_method', paymentMethod);
            }
            await fetchJson(`${apiBase}/update_status_personnel.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: payload
            });
            await loadOrders();
            await loadUnassignedOrders();
            openModal(activeOrderId);
            showNotice(notice, 'Sipariş durumu güncellendi.');
        } catch (error) {
            showNotice(notice, error.message, 'error');
        }
    };

    ordersTableBody.addEventListener('click', (event) => {
        const button = event.target.closest('[data-action="manage"]');
        if (!button) return;
        const orderId = parseInt(button.dataset.orderId, 10);
        openModal(orderId);
    });

    if (unassignedOrdersTableBody) {
        unassignedOrdersTableBody.addEventListener('click', async (event) => {
            const button = event.target.closest('[data-action="assign"]');
            if (!button) return;
            const orderId = parseInt(button.dataset.orderId, 10);
            if (!orderId) return;
            if (!window.confirm('Bu siparişi üzerinize almak istiyor musunuz?')) return;
            try {
                await fetchJson(`${apiBase}/assign_to_personnel.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ order_id: orderId })
                });
                await loadOrders();
                await loadUnassignedOrders();
                showNotice(notice, 'Sipariş üzerinize alındı.');
            } catch (error) {
                showNotice(unassignedNotice, error.message, 'error');
            }
        });
    }

    if (filterSelect) {
        filterSelect.addEventListener('change', renderOrders);
    }

    if (refreshBtn) {
        refreshBtn.addEventListener('click', () => {
            loadOrders();
            loadUnassignedOrders();
        });
    }

    if (updateStatusBtn) {
        updateStatusBtn.addEventListener('click', updateOrderStatus);
    }

    if (modalClose) {
        modalClose.addEventListener('click', closeModal);
    }

    if (modal) {
        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeModal();
            }
        });
    }

    loadOrders();
    loadUnassignedOrders();
})();
