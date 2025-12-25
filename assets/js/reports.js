(() => {
    const formatCurrency = (value) => {
        const amount = Number(value || 0);
        return `₺${amount.toFixed(2)}`;
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

    const fetchJson = async (url) => {
        const response = await fetch(url);
        const data = await response.json();
        if (!data.success) {
            throw new Error(data.message || 'İşlem başarısız');
        }
        return data.data || [];
    };

    const renderRows = (tbody, rows, renderer, emptyText) => {
        if (!tbody) return;
        if (!rows.length) {
            tbody.innerHTML = `<tr><td colspan="20">${emptyText}</td></tr>`;
            return;
        }
        tbody.innerHTML = rows.map(renderer).join('');
    };

    const filterEls = {
        status: document.getElementById('reportStatus'),
        orderId: document.getElementById('reportOrderId'),
        customerId: document.getElementById('reportCustomerId'),
        personnelId: document.getElementById('reportPersonnelId'),
        categoryId: document.getElementById('reportCategoryId'),
        ingredientId: document.getElementById('reportIngredientId'),
        movementType: document.getElementById('reportMovementType'),
        dateFrom: document.getElementById('reportDateFrom'),
        dateTo: document.getElementById('reportDateTo'),
        limit: document.getElementById('reportLimit')
    };

    const buildParams = () => {
        const params = {};
        if (filterEls.status?.value) params.status = filterEls.status.value;
        if (filterEls.orderId?.value) params.order_id = filterEls.orderId.value;
        if (filterEls.customerId?.value) params.customer_id = filterEls.customerId.value;
        if (filterEls.personnelId?.value) params.personnel_id = filterEls.personnelId.value;
        if (filterEls.categoryId?.value) params.category_id = filterEls.categoryId.value;
        if (filterEls.ingredientId?.value) params.ingredient_id = filterEls.ingredientId.value;
        if (filterEls.movementType?.value) params.movement_type = filterEls.movementType.value;
        if (filterEls.dateFrom?.value) params.date_from = filterEls.dateFrom.value;
        if (filterEls.dateTo?.value) params.date_to = filterEls.dateTo.value;
        if (filterEls.limit?.value) params.limit = filterEls.limit.value;
        return params;
    };

    const withParams = (url, params, allowedKeys) => {
        const query = new URLSearchParams();
        allowedKeys.forEach((key) => {
            if (params[key]) {
                query.set(key, params[key]);
            }
        });
        const qs = query.toString();
        return qs ? `${url}?${qs}` : url;
    };

    const reports = [
        {
            tbodyId: 'ordersOverviewBody',
            url: '/Restaurant-Management-System/api/reports/orders_overview.php',
            params: ['status'],
            empty: 'Sipariş bulunamadı.',
            render: (row) => `
                <tr>
                    <td>${row.order_id}</td>
                    <td>${row.table_number ?? '-'}</td>
                    <td>${row.customer_name ?? '-'}</td>
                    <td>${row.waiter_name ?? '-'}</td>
                    <td>${row.status ?? '-'}</td>
                    <td>${row.item_count ?? 0}</td>
                    <td>${formatCurrency(row.total_amount)}</td>
                    <td>${formatDate(row.order_date)}</td>
                </tr>
            `
        },
        {
            tbodyId: 'orderItemsBody',
            url: '/Restaurant-Management-System/api/reports/order_items.php',
            params: ['order_id'],
            empty: 'Sipariş kalemi bulunamadı.',
            render: (row) => `
                <tr>
                    <td>${row.order_id}</td>
                    <td>${row.category_name ?? '-'}</td>
                    <td>${row.product_name ?? '-'}</td>
                    <td>${row.quantity ?? 0}</td>
                    <td>${formatCurrency(row.unit_price)}</td>
                    <td>${formatCurrency(row.subtotal)}</td>
                </tr>
            `
        },
        {
            tbodyId: 'stockSummaryBody',
            url: '/Restaurant-Management-System/api/reports/stock_summary.php',
            params: [],
            empty: 'Stok verisi bulunamadı.',
            render: (row) => `
                <tr>
                    <td>${row.ingredient_name ?? '-'}</td>
                    <td>${row.unit ?? '-'}</td>
                    <td>${row.quantity ?? 0}</td>
                    <td>${row.minimum_quantity ?? 0}</td>
                    <td>${row.supplier_name ?? '-'}</td>
                </tr>
            `
        },
        {
            tbodyId: 'stockMovementsBody',
            url: '/Restaurant-Management-System/api/reports/stock_movements.php',
            params: ['ingredient_id', 'movement_type', 'date_from', 'date_to', 'limit'],
            empty: 'Stok hareketi bulunamadı.',
            render: (row) => `
                <tr>
                    <td>${row.ingredient_name ?? '-'}</td>
                    <td>${row.movement_type ?? '-'}</td>
                    <td>${row.quantity ?? 0}</td>
                    <td>${row.note ?? '-'}</td>
                    <td>${formatDate(row.created_at)}</td>
                    <td>${row.supplier_name ?? '-'}</td>
                </tr>
            `
        },
        {
            tbodyId: 'menuCompositionBody',
            url: '/Restaurant-Management-System/api/reports/menu_composition.php',
            params: ['category_id'],
            empty: 'Reçete verisi bulunamadı.',
            render: (row) => `
                <tr>
                    <td>${row.category_name ?? '-'}</td>
                    <td>${row.product_name ?? '-'}</td>
                    <td>${row.ingredient_name ?? '-'}</td>
                    <td>${row.quantity_required ?? 0}</td>
                    <td>${row.unit ?? '-'}</td>
                </tr>
            `
        },
        {
            tbodyId: 'customerHistoryBody',
            url: '/Restaurant-Management-System/api/reports/customer_history.php',
            params: ['customer_id'],
            empty: 'Müşteri geçmişi bulunamadı.',
            render: (row) => `
                <tr>
                    <td>${row.customer_name ?? '-'}</td>
                    <td>${row.order_id ?? '-'}</td>
                    <td>${row.product_name ?? '-'}</td>
                    <td>${row.quantity ?? 0}</td>
                    <td>${formatCurrency(row.subtotal)}</td>
                    <td>${formatDate(row.order_date)}</td>
                </tr>
            `
        },
        {
            tbodyId: 'personnelPerformanceBody',
            url: '/Restaurant-Management-System/api/reports/personnel_performance.php',
            params: ['personnel_id'],
            empty: 'Personel performansı bulunamadı.',
            render: (row) => `
                <tr>
                    <td>${row.personnel_name ?? '-'}</td>
                    <td>${row.total_orders ?? 0}</td>
                    <td>${row.total_items ?? 0}</td>
                    <td>${formatCurrency(row.total_sales)}</td>
                </tr>
            `
        }
    ];

    const loadReports = async () => {
        const params = buildParams();
        reports.forEach(async (report) => {
            const tbody = document.getElementById(report.tbodyId);
            if (!tbody) return;
            try {
                const url = withParams(report.url, params, report.params);
                const rows = await fetchJson(url);
                renderRows(tbody, rows, report.render, report.empty);
            } catch (error) {
                renderRows(tbody, [], () => '', error.message || report.empty);
            }
        });
    };

    const applyBtn = document.getElementById('applyReportsFilters');
    const resetBtn = document.getElementById('resetReportsFilters');

    if (applyBtn) {
        applyBtn.addEventListener('click', (event) => {
            event.preventDefault();
            loadReports();
        });
    }

    if (resetBtn) {
        resetBtn.addEventListener('click', (event) => {
            event.preventDefault();
            Object.values(filterEls).forEach((input) => {
                if (!input) return;
                if (input.tagName === 'SELECT') {
                    input.value = '';
                } else {
                    input.value = '';
                }
            });
            loadReports();
        });
    }

    loadReports();
})();
