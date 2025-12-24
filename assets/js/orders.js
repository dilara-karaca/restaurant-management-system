(() => {
    const ordersTableBody = document.getElementById('ordersTableBody');
    if (!ordersTableBody) return;

    const apiBase = '/Restaurant-Management-System/api/orders';
    const menuApi = '/Restaurant-Management-System/api/menu/list.php';

    const notice = document.getElementById('ordersNotice');
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
    const orderItemsList = document.getElementById('orderItemsList');
    const orderTotalValue = document.getElementById('orderTotalValue');
    const addItemProduct = document.getElementById('addItemProduct');
    const addItemQty = document.getElementById('addItemQty');
    const addItemBtn = document.getElementById('addItemBtn');
    const deleteOrderBtn = document.getElementById('deleteOrderBtn');

    let orders = [];
    let menuItems = [];
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

    const showNotice = (message, type = 'success') => {
        if (!notice) return;
        notice.textContent = message;
        notice.classList.remove('error');
        notice.classList.add('show');
        if (type === 'error') {
            notice.classList.add('error');
        }
        window.clearTimeout(notice.dataset.timerId);
        notice.dataset.timerId = window.setTimeout(() => {
            notice.classList.remove('show');
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
                            Yönet
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
    };

    const populateMenuOptions = () => {
        if (!addItemProduct) return;
        if (!menuItems.length) {
            addItemProduct.innerHTML = '<option value="">Menü bulunamadı</option>';
            return;
        }
        addItemProduct.innerHTML = menuItems.map((item) => `
            <option value="${item.product_id}">${item.product_name}</option>
        `).join('');
    };

    const renderOrderItems = (order) => {
        if (!orderItemsList) return;
        if (!order.items || !order.items.length) {
            orderItemsList.innerHTML = '<div class="order-item-row">Sipariş kalemi yok.</div>';
            return;
        }

        const optionsHtml = menuItems.map((item) => `
            <option value="${item.product_id}">${item.product_name}</option>
        `).join('');

        orderItemsList.innerHTML = order.items.map((item) => `
            <div class="order-item-row" data-detail-id="${item.order_detail_id}">
                <select class="input order-item-product">
                    ${optionsHtml}
                </select>
                <input class="input order-item-qty" type="number" min="1" value="${item.quantity}">
                <span>${formatCurrency(item.unit_price)}</span>
                <div class="order-item-actions">
                    <button class="btn btn--secondary btn--small" data-action="update-item" data-detail-id="${item.order_detail_id}">Güncelle</button>
                    <button class="btn btn--ghost btn--small" data-action="delete-item" data-detail-id="${item.order_detail_id}">Sil</button>
                </div>
            </div>
        `).join('');

        order.items.forEach((item) => {
            const row = orderItemsList.querySelector(`[data-detail-id="${item.order_detail_id}"]`);
            if (!row) return;
            const select = row.querySelector('.order-item-product');
            if (select) {
                select.value = item.product_id;
            }
        });
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
            const data = await fetchJson(`${apiBase}/list.php`);
            orders = data.data || [];
            renderOrders();
        } catch (error) {
            showNotice(error.message, 'error');
        }
    };

    const loadMenuItems = async () => {
        try {
            const data = await fetchJson(menuApi);
            menuItems = data.data || [];
            populateMenuOptions();
        } catch (error) {
            showNotice(error.message, 'error');
        }
    };

    ordersTableBody.addEventListener('click', (event) => {
        const button = event.target.closest('[data-action="manage"]');
        if (!button) return;
        const orderId = parseInt(button.dataset.orderId, 10);
        openModal(orderId);
    });

    orderItemsList.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-action]');
        if (!button) return;

        const action = button.dataset.action;
        const detailId = parseInt(button.dataset.detailId, 10);
        if (!detailId || !activeOrderId) return;

        if (action === 'delete-item') {
            if (!window.confirm('Bu ürünü siparişten kaldırmak istiyor musunuz?')) return;
            try {
                await fetchJson(`${apiBase}/delete_item.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ order_detail_id: detailId })
                });
                await loadOrders();
                openModal(activeOrderId);
                showNotice('Ürün siparişten kaldırıldı.');
            } catch (error) {
                showNotice(error.message, 'error');
            }
            return;
        }

        if (action === 'update-item') {
            const row = orderItemsList.querySelector(`[data-detail-id="${detailId}"]`);
            if (!row) return;
            const productSelect = row.querySelector('.order-item-product');
            const qtyInput = row.querySelector('.order-item-qty');
            const quantity = parseInt(qtyInput.value, 10);
            const productId = parseInt(productSelect.value, 10);
            if (!quantity || quantity <= 0 || !productId) {
                showNotice('Geçerli bir ürün ve miktar seçin.', 'error');
                return;
            }
            try {
                await fetchJson(`${apiBase}/update_item.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        order_detail_id: detailId,
                        quantity,
                        product_id: productId
                    })
                });
                await loadOrders();
                openModal(activeOrderId);
                showNotice('Sipariş kalemi güncellendi.');
            } catch (error) {
                showNotice(error.message, 'error');
            }
        }
    });

    updateStatusBtn.addEventListener('click', async () => {
        if (!activeOrderId) return;
        const status = orderStatusSelect.value;
        try {
            await fetchJson(`${apiBase}/update_status.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    order_id: activeOrderId,
                    status
                })
            });
            await loadOrders();
            openModal(activeOrderId);
            showNotice('Sipariş durumu güncellendi.');
        } catch (error) {
            showNotice(error.message, 'error');
        }
    });

    addItemBtn.addEventListener('click', async () => {
        if (!activeOrderId) return;
        const productId = parseInt(addItemProduct.value, 10);
        const quantity = parseInt(addItemQty.value, 10);
        if (!productId || !quantity || quantity <= 0) {
            showNotice('Geçerli bir ürün ve miktar seçin.', 'error');
            return;
        }
        try {
            await fetchJson(`${apiBase}/add_item.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    order_id: activeOrderId,
                    product_id: productId,
                    quantity
                })
            });
            await loadOrders();
            openModal(activeOrderId);
            showNotice('Ürün siparişe eklendi.');
        } catch (error) {
            showNotice(error.message, 'error');
        }
    });

    deleteOrderBtn.addEventListener('click', async () => {
        if (!activeOrderId) return;
        if (!window.confirm('Siparişi tamamen silmek istiyor musunuz?')) return;
        try {
            await fetchJson(`${apiBase}/delete.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ order_id: activeOrderId })
            });
            closeModal();
            await loadOrders();
            showNotice('Sipariş silindi.');
        } catch (error) {
            showNotice(error.message, 'error');
        }
    });

    modalClose.addEventListener('click', closeModal);
    modal.addEventListener('click', (event) => {
        if (event.target === modal) closeModal();
    });

    if (filterSelect) {
        filterSelect.addEventListener('change', renderOrders);
    }

    if (refreshBtn) {
        refreshBtn.addEventListener('click', loadOrders);
    }

    loadMenuItems();
    loadOrders();
})();
