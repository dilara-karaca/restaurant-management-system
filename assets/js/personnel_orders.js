(() => {
    const ordersTableBody = document.getElementById('ordersTableBody');
    if (!ordersTableBody) return;

    const apiBase = '/Restaurant-Management-System/api/orders';
    const menuApi = '/Restaurant-Management-System/api/menu/list.php';

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
    const orderTableSelect = document.getElementById('orderTableSelect');
    const updateTableBtn = document.getElementById('updateTableBtn');
    const paymentSection = document.getElementById('paymentSection');
    const paymentMethodSelect = document.getElementById('paymentMethodSelect');
    const completePaymentBtn = document.getElementById('completePaymentBtn');
    const paymentStatusNote = document.getElementById('paymentStatusNote');
    const extraPaymentBar = document.getElementById('extraPaymentBar');
    const extraPaymentAmount = document.getElementById('extraPaymentAmount');
    const extraPaymentMethodSelect = document.getElementById('extraPaymentMethodSelect');
    const extraPaymentBtn = document.getElementById('extraPaymentBtn');
    const paidItemsTitle = document.getElementById('paidItemsTitle');
    const extraItemsWrap = document.getElementById('extraItemsWrap');
    const extraItemsList = document.getElementById('extraItemsList');
    const orderItemsList = document.getElementById('orderItemsList');
    const orderTotalValue = document.getElementById('orderTotalValue');
    const addItemProduct = document.getElementById('addItemProduct');
    const addItemQty = document.getElementById('addItemQty');
    const addItemBtn = document.getElementById('addItemBtn');
    const unassignedOrdersTableBody = document.getElementById('unassignedOrdersTableBody');

    let orders = [];
    let unassignedOrders = [];
    let activeOrderId = null;
    let menuItems = [];
    let tables = [];

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

    const paymentMethodLabels = {
        'Cash': 'Nakit',
        'Credit Card': 'Kredi Kartı',
        'Debit Card': 'Banka Kartı',
        'Mobile Payment': 'Mobil Ödeme'
    };

    const formatPaymentMethod = (method) => {
        return paymentMethodLabels[method] || method || '-';
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

    const getExtraPaymentInfo = (order) => {
        const items = order.items || [];
        const paidAmount = Number(order.paid_amount || 0);
        const totalAmount = Number(order.total_amount || 0);
        const extraAmount = totalAmount - paidAmount;
        const paidDetailMaxId = order.paid_detail_max_id ? Number(order.paid_detail_max_id) : 0;
        if (extraAmount <= 0.01 || !items.length) {
            return { paidItems: items, extraItems: [], extraTotal: 0 };
        }
        if (paidDetailMaxId > 0) {
            const paidItems = items.filter((item) => Number(item.order_detail_id) <= paidDetailMaxId);
            const extraItems = items.filter((item) => Number(item.order_detail_id) > paidDetailMaxId);
            const extraTotal = extraItems.reduce((sum, item) => sum + Number(item.subtotal || 0), 0);
            return { paidItems, extraItems, extraTotal };
        }

        const extraItems = [];
        let remaining = extraAmount;
        for (let i = items.length - 1; i >= 0; i -= 1) {
            const item = items[i];
            const subtotal = Number(item.subtotal || 0);
            if (remaining > 0.01) {
                extraItems.unshift(item);
                remaining -= subtotal;
            }
        }
        const extraIds = new Set(extraItems.map((item) => item.order_detail_id));
        const paidItems = items.filter((item) => !extraIds.has(item.order_detail_id));
        const extraTotal = extraItems.reduce((sum, item) => sum + Number(item.subtotal || 0), 0);
        return { paidItems, extraItems, extraTotal };
    };

    const renderItemsList = (items, target, isFinal, emptyMessage) => {
        if (!target) return;
        if (!items || !items.length) {
            target.innerHTML = `<div class="order-item-row">${emptyMessage}</div>`;
            return;
        }
        const optionsHtml = menuItems.map((item) => `
            <option value="${item.product_id}">${item.product_name}</option>
        `).join('');

        target.innerHTML = items.map((item) => `
            <div class="order-item-row" data-detail-id="${item.order_detail_id}">
                <select class="input order-item-product" ${isFinal ? 'disabled' : ''}>
                    ${optionsHtml}
                </select>
                <input class="input order-item-qty" type="number" min="1" value="${item.quantity}" ${isFinal ? 'disabled' : ''}>
                <span>${formatCurrency(item.unit_price)}</span>
                <span>${formatCurrency(item.subtotal)}</span>
                <div class="order-item-actions">
                    <button class="btn btn--secondary btn--small" data-action="update-item" data-detail-id="${item.order_detail_id}" ${isFinal ? 'disabled' : ''}>Güncelle</button>
                    <button class="btn btn--ghost btn--small" data-action="delete-item" data-detail-id="${item.order_detail_id}" ${isFinal ? 'disabled' : ''}>Sil</button>
                </div>
            </div>
        `).join('');

        items.forEach((item) => {
            const row = target.querySelector(`[data-detail-id="${item.order_detail_id}"]`);
            if (!row) return;
            const select = row.querySelector('.order-item-product');
            if (select) {
                select.value = item.product_id;
            }
        });
    };

    const renderOrderItems = (order) => {
        if (!orderItemsList) return;
        const items = order.items || [];
        if (!items.length) {
            orderItemsList.innerHTML = '<div class="order-item-row">Sipariş kalemi yok.</div>';
            if (extraItemsWrap) extraItemsWrap.hidden = true;
            if (paidItemsTitle) paidItemsTitle.hidden = true;
            return;
        }

        const isFinal = order.status === 'Completed' || order.status === 'Cancelled';
        const isMobilePayment = order.payment_method === 'Mobile Payment';
        const { paidItems, extraItems, extraTotal } = getExtraPaymentInfo(order);
        const hasExtraPayment = isMobilePayment && extraTotal > 0;

        if (hasExtraPayment) {
            renderItemsList(paidItems, orderItemsList, isFinal, 'Ödenen ürün yok.');
            if (paidItemsTitle) paidItemsTitle.hidden = false;
            if (extraItemsWrap) extraItemsWrap.hidden = false;
            renderItemsList(extraItems, extraItemsList, isFinal, 'Yeni eklenen ürün yok.');
            return;
        }

        if (paidItemsTitle) paidItemsTitle.hidden = true;
        if (extraItemsWrap) extraItemsWrap.hidden = true;
        renderItemsList(items, orderItemsList, isFinal, 'Sipariş kalemi yok.');
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
        const hasPayment = !!order.payment_method;
        if (paymentMethodSelect) {
            paymentMethodSelect.value = order.payment_method || '';
        }
        orderTotalValue.textContent = formatCurrency(order.total_amount);
        const { extraTotal } = getExtraPaymentInfo(order);
        const isMobilePayment = order.payment_method === 'Mobile Payment';
        const hasExtraPayment = isMobilePayment && extraTotal > 0;

        if (completePaymentBtn) {
            const isFinal = order.status === 'Completed' || order.status === 'Cancelled';
            completePaymentBtn.disabled = isFinal || hasPayment;
            completePaymentBtn.textContent = (isFinal || hasPayment) ? 'Ödeme Alındı' : 'Ödemeyi Tamamla';
        }

        const isFinal = order.status === 'Completed' || order.status === 'Cancelled';
        if (addItemProduct) addItemProduct.disabled = isFinal;
        if (addItemQty) addItemQty.disabled = isFinal;
        if (addItemBtn) addItemBtn.disabled = isFinal;
        if (orderTableSelect) {
            orderTableSelect.disabled = isFinal;
            orderTableSelect.value = order.table_id || '';
        }
        if (updateTableBtn) {
            updateTableBtn.disabled = isFinal;
        }
        if (paymentMethodSelect) {
            paymentMethodSelect.disabled = isFinal || hasPayment;
        }
        if (paymentStatusNote) {
            paymentStatusNote.textContent = hasPayment
                ? `Ödeme alındı (${formatPaymentMethod(order.payment_method)}).`
                : 'Ödeme bekleniyor.';
            paymentStatusNote.classList.toggle('is-paid', hasPayment);
        }
        if (paymentSection) {
            paymentSection.hidden = hasExtraPayment;
        }
        if (extraPaymentBar) {
            extraPaymentBar.hidden = !hasExtraPayment;
        }
        if (extraPaymentAmount) {
            extraPaymentAmount.textContent = hasExtraPayment ? formatCurrency(extraTotal) : '₺0';
        }
        if (extraPaymentMethodSelect) {
            extraPaymentMethodSelect.disabled = isFinal;
            extraPaymentMethodSelect.value = '';
        }
        if (extraPaymentBtn) {
            extraPaymentBtn.disabled = isFinal;
        }

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

    const loadMenuItems = async () => {
        try {
            const data = await fetchJson(menuApi);
            menuItems = data.data || [];
            if (addItemProduct) {
                if (!menuItems.length) {
                    addItemProduct.innerHTML = '<option value="">Menü bulunamadı</option>';
                } else {
                    addItemProduct.innerHTML = menuItems.map((item) => `
                        <option value="${item.product_id}">${item.product_name}</option>
                    `).join('');
                }
            }
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

    const loadTables = async () => {
        if (!orderTableSelect) return;
        try {
            const data = await fetchJson('/Restaurant-Management-System/api/tables/list.php');
            tables = data.data || [];
            if (!tables.length) {
                orderTableSelect.innerHTML = '<option value="">Masa bulunamadı</option>';
                return;
            }
            const options = tables.map((table) => {
                const location = table.location ? ` - ${table.location}` : '';
                const label = `${getTableLabel(table)} (${table.capacity} kişi)${location}`;
                return `<option value="${table.table_id}">${label}</option>`;
            }).join('');
            orderTableSelect.innerHTML = options;
        } catch (error) {
            showNotice(notice, error.message, 'error');
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

    const completePayment = async () => {
        if (!activeOrderId) return;
        const paymentMethod = paymentMethodSelect ? paymentMethodSelect.value : '';
        if (!paymentMethod) {
            showNotice(notice, 'Lütfen ödeme yöntemi seçin.', 'error');
            return;
        }
        if (!window.confirm('Ödemeyi tamamlamak istediğinizden emin misiniz?')) return;
        try {
            await fetchJson(`${apiBase}/complete_payment.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    order_id: activeOrderId,
                    payment_method: paymentMethod
                })
            });
            await loadOrders();
            await loadUnassignedOrders();
            openModal(activeOrderId);
            showNotice(notice, 'Ödeme başarıyla alındı.');
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

    if (completePaymentBtn) {
        completePaymentBtn.addEventListener('click', completePayment);
    }
    if (extraPaymentBtn) {
        extraPaymentBtn.addEventListener('click', async () => {
            if (!activeOrderId) return;
            const paymentMethod = extraPaymentMethodSelect ? extraPaymentMethodSelect.value : '';
            if (!paymentMethod) {
                showNotice(notice, 'Lütfen ödeme yöntemi seçin.', 'error');
                return;
            }
            if (!window.confirm('Ek ödemeyi tamamlamak istediğinizden emin misiniz?')) return;
            try {
                await fetchJson(`${apiBase}/complete_payment.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        order_id: activeOrderId,
                        payment_method: paymentMethod
                    })
                });
                await loadOrders();
                await loadUnassignedOrders();
                openModal(activeOrderId);
                showNotice(notice, 'Ek ödeme başarıyla alındı.');
            } catch (error) {
                showNotice(notice, error.message, 'error');
            }
        });
    }

    if (updateTableBtn) {
        updateTableBtn.addEventListener('click', async () => {
            if (!activeOrderId) return;
            const tableId = parseInt(orderTableSelect.value, 10);
            if (!tableId) {
                showNotice(notice, 'Geçerli bir masa seçin.', 'error');
                return;
            }
            try {
                await fetchJson(`${apiBase}/update_table.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        order_id: activeOrderId,
                        table_id: tableId
                    })
                });
                await loadOrders();
                await loadUnassignedOrders();
                openModal(activeOrderId);
                showNotice(notice, 'Masa güncellendi.');
            } catch (error) {
                showNotice(notice, error.message, 'error');
            }
        });
    }
    const handleOrderItemsClick = async (event) => {
        const button = event.target.closest('[data-action]');
        if (!button) return;
        const action = button.dataset.action;
        const detailId = parseInt(button.dataset.detailId, 10);
        if (!detailId || !activeOrderId) return;
        const container = event.currentTarget;

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
                showNotice(notice, 'Ürün siparişten kaldırıldı.');
            } catch (error) {
                showNotice(notice, error.message, 'error');
            }
            return;
        }

        if (action === 'update-item') {
            const row = container.querySelector(`[data-detail-id="${detailId}"]`);
            if (!row) return;
            const productSelect = row.querySelector('.order-item-product');
            const qtyInput = row.querySelector('.order-item-qty');
            const quantity = parseInt(qtyInput.value, 10);
            const productId = parseInt(productSelect.value, 10);
            if (!quantity || quantity <= 0 || !productId) {
                showNotice(notice, 'Geçerli bir ürün ve miktar seçin.', 'error');
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
                showNotice(notice, 'Sipariş kalemi güncellendi.');
            } catch (error) {
                showNotice(notice, error.message, 'error');
            }
        }
    };

    if (orderItemsList) {
        orderItemsList.addEventListener('click', handleOrderItemsClick);
    }
    if (extraItemsList) {
        extraItemsList.addEventListener('click', handleOrderItemsClick);
    }

    if (addItemBtn) {
        addItemBtn.addEventListener('click', async () => {
            if (!activeOrderId) return;
            const productId = parseInt(addItemProduct.value, 10);
            const quantity = parseInt(addItemQty.value, 10);
            if (!productId || !quantity || quantity <= 0) {
                showNotice(notice, 'Geçerli bir ürün ve miktar seçin.', 'error');
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
                showNotice(notice, 'Ürün siparişe eklendi.');
            } catch (error) {
                showNotice(notice, error.message, 'error');
            }
        });
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

    loadMenuItems();
    loadTables();
    loadOrders();
    loadUnassignedOrders();
})();
