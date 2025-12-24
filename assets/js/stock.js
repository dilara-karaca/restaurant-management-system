(() => {
    const tableBody = document.getElementById('stockTableBody');
    if (!tableBody) return;

    const apiBase = '/Restaurant-Management-System/api/stocks';
    const notice = document.getElementById('stockNotice');
    const refreshBtn = document.getElementById('refreshStocksBtn');
    const form = document.getElementById('stockMovementForm');
    const ingredientSelect = document.getElementById('movementIngredient');
    const typeSelect = document.getElementById('movementType');
    const qtyInput = document.getElementById('movementQty');
    const noteInput = document.getElementById('movementNote');
    const movementsList = document.getElementById('stockMovements');
    const filterForm = document.getElementById('stockFilterForm');
    const filterIngredient = document.getElementById('movementFilterIngredient');
    const filterType = document.getElementById('movementFilterType');
    const filterFrom = document.getElementById('movementFilterFrom');
    const filterTo = document.getElementById('movementFilterTo');
    const filterLimit = document.getElementById('movementFilterLimit');
    const filterReset = document.getElementById('stockFilterReset');
    const movementModal = document.getElementById('stockMovementModal');
    const movementModalClose = document.getElementById('stockMovementModalClose');
    const movementEditForm = document.getElementById('stockMovementEditForm');
    const editMovementIngredient = document.getElementById('editMovementIngredient');
    const editMovementType = document.getElementById('editMovementType');
    const editMovementQty = document.getElementById('editMovementQty');
    const editMovementNote = document.getElementById('editMovementNote');

    let stocks = [];
    let movements = [];
    let editingMovementId = null;

    const formatNumber = (value) => {
        const num = Number(value || 0);
        return num.toFixed(2);
    };

    const formatCurrency = (value) => {
        const num = Number(value || 0);
        return `₺${num.toFixed(2)}`;
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

    const getStatus = (item) => {
        const qty = Number(item.quantity || 0);
        const min = Number(item.minimum_quantity || 0);
        if (qty <= min) {
            return { label: 'Kritik', className: 'critical' };
        }
        if (min > 0 && qty <= min * 1.5) {
            return { label: 'Düşük', className: 'low' };
        }
        return { label: 'Normal', className: 'ok' };
    };

    const renderStocks = () => {
        if (!stocks.length) {
            tableBody.innerHTML = '<tr><td colspan="8">Stok bulunamadı.</td></tr>';
            return;
        }

        tableBody.innerHTML = stocks.map((item) => {
            const status = getStatus(item);
            const supplier = item.supplier_name || '-';
            return `
                <tr>
                    <td>${item.ingredient_name}</td>
                    <td>${supplier}</td>
                    <td>${formatNumber(item.quantity)}</td>
                    <td>${formatNumber(item.minimum_quantity)}</td>
                    <td>${item.unit}</td>
                    <td>${formatCurrency(item.unit_price)}</td>
                    <td>${formatDate(item.last_updated)}</td>
                    <td><span class="status-badge ${status.className}">${status.label}</span></td>
                </tr>
            `;
        }).join('');
    };

    const renderIngredientOptions = () => {
        if (!ingredientSelect) return;
        if (!stocks.length) {
            ingredientSelect.innerHTML = '<option value="">Stok bulunamadı</option>';
            return;
        }
        ingredientSelect.innerHTML = stocks.map((item) => (
            `<option value="${item.ingredient_id}">${item.ingredient_name} (${formatNumber(item.quantity)} ${item.unit})</option>`
        )).join('');
    };

    const renderFilterOptions = () => {
        if (!filterIngredient) return;
        if (!stocks.length) {
            filterIngredient.innerHTML = '<option value="">Stok bulunamadı</option>';
            return;
        }
        const options = stocks.map((item) => (
            `<option value="${item.ingredient_id}">${item.ingredient_name}</option>`
        )).join('');
        filterIngredient.innerHTML = `<option value="">Tümü</option>${options}`;
    };

    const renderMovementRow = (move) => {
        const labels = {
            IN: 'Gelen',
            OUT: 'Giden',
            USED: 'Kullanılan'
        };
        const label = labels[move.movement_type] || move.movement_type;
        const note = move.note ? move.note : '-';
        return `
            <tr data-movement-id="${move.movement_id}">
                <td>${move.ingredient_name}</td>
                <td><span class="stock-movement-type ${move.movement_type.toLowerCase()}">${label}</span></td>
                <td>${formatNumber(move.quantity)} ${move.unit}</td>
                <td>${note}</td>
                <td>${formatDate(move.created_at)}</td>
                <td class="stock-movement-actions">
                    <button class="btn btn--secondary btn--small" data-action="edit">Düzenle</button>
                    <button class="btn btn--ghost btn--small" data-action="delete">Sil</button>
                </td>
            </tr>
        `;
    };

    const renderMovements = (data) => {
        if (!movementsList) return;
        if (!data.length) {
            movementsList.innerHTML = '<tr><td colspan="6">Hareket kaydı bulunamadı.</td></tr>';
            return;
        }
        movementsList.innerHTML = data.map((move) => renderMovementRow(move)).join('');
    };

    const loadStocks = async () => {
        try {
            const data = await fetchJson(`${apiBase}/list.php`);
            stocks = data.data || [];
            renderStocks();
            renderIngredientOptions();
            renderFilterOptions();
        } catch (error) {
            showNotice(error.message, 'error');
        }
    };

    const buildMovementsQuery = () => {
        const params = new URLSearchParams();
        if (filterIngredient && filterIngredient.value) {
            params.append('ingredient_id', filterIngredient.value);
        }
        if (filterType && filterType.value) {
            params.append('movement_type', filterType.value);
        }
        if (filterFrom && filterFrom.value) {
            params.append('date_from', filterFrom.value);
        }
        if (filterTo && filterTo.value) {
            params.append('date_to', filterTo.value);
        }
        if (filterLimit && filterLimit.value) {
            params.append('limit', filterLimit.value);
        }
        const query = params.toString();
        return query ? `?${query}` : '';
    };

    const loadMovements = async () => {
        try {
            const data = await fetchJson(`${apiBase}/movements.php${buildMovementsQuery()}`);
            movements = data.data || [];
            renderMovements(movements);
        } catch (error) {
            movements = [];
            renderMovements(movements);
        }
    };

    if (form) {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (!ingredientSelect || !typeSelect || !qtyInput) return;

            const payload = new URLSearchParams({
                ingredient_id: ingredientSelect.value,
                movement_type: typeSelect.value,
                quantity: qtyInput.value,
                note: noteInput ? noteInput.value.trim() : ''
            });

            try {
                await fetchJson(`${apiBase}/adjust.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: payload.toString()
                });
                qtyInput.value = '';
                if (noteInput) noteInput.value = '';
                showNotice('Stok hareketi eklendi');
                await loadStocks();
                await loadMovements();
            } catch (error) {
                showNotice(error.message, 'error');
            }
        });
    }

    if (refreshBtn) {
        refreshBtn.addEventListener('click', () => {
            loadStocks();
            loadMovements();
        });
    }

    if (filterForm) {
        filterForm.addEventListener('submit', (event) => {
            event.preventDefault();
            loadMovements();
        });
    }

    if (filterReset) {
        filterReset.addEventListener('click', () => {
            if (filterIngredient) filterIngredient.value = '';
            if (filterType) filterType.value = '';
            if (filterFrom) filterFrom.value = '';
            if (filterTo) filterTo.value = '';
            if (filterLimit) filterLimit.value = '12';
            loadMovements();
        });
    }

    const openMovementModal = (movementId) => {
        if (!movementModal) return;
        const movement = movements.find((item) => String(item.movement_id) === String(movementId));
        if (!movement) return;
        editingMovementId = movementId;
        if (editMovementIngredient) editMovementIngredient.value = movement.ingredient_name;
        if (editMovementType) editMovementType.value = movement.movement_type;
        if (editMovementQty) editMovementQty.value = movement.quantity;
        if (editMovementNote) editMovementNote.value = movement.note || '';
        movementModal.classList.add('active');
    };

    const closeMovementModal = () => {
        if (!movementModal) return;
        movementModal.classList.remove('active');
        editingMovementId = null;
    };

    if (movementModalClose) {
        movementModalClose.addEventListener('click', closeMovementModal);
    }

    if (movementModal) {
        movementModal.addEventListener('click', (event) => {
            if (event.target === movementModal) closeMovementModal();
        });
    }

    if (movementEditForm) {
        movementEditForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (!editingMovementId) return;
            const payload = new URLSearchParams({
                movement_id: editingMovementId,
                movement_type: editMovementType ? editMovementType.value : '',
                quantity: editMovementQty ? editMovementQty.value : '',
                note: editMovementNote ? editMovementNote.value.trim() : ''
            });

            try {
                await fetchJson(`${apiBase}/update.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: payload.toString()
                });
                showNotice('Stok hareketi güncellendi');
                closeMovementModal();
                await loadStocks();
                await loadMovements();
            } catch (error) {
                showNotice(error.message, 'error');
            }
        });
    }

    if (movementsList) {
        movementsList.addEventListener('click', async (event) => {
            const actionBtn = event.target.closest('[data-action]');
            if (!actionBtn) return;
            const row = event.target.closest('[data-movement-id]');
            if (!row) return;

            const movementId = row.dataset.movementId;
            if (!movementId) return;

            if (actionBtn.dataset.action === 'edit') {
                openMovementModal(movementId);
                return;
            }

            if (actionBtn.dataset.action === 'delete') {
                if (!window.confirm('Bu stok hareketi silinsin mi?')) return;
                try {
                    await fetchJson(`${apiBase}/delete.php`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({ movement_id: movementId }).toString()
                    });
                    showNotice('Stok hareketi silindi');
                    await loadStocks();
                    await loadMovements();
                } catch (error) {
                    showNotice(error.message, 'error');
                }
            }
        });
    }

    loadStocks();
    loadMovements();
})();
