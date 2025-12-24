(() => {
    const categoryList = document.getElementById('categoryList');
    if (!categoryList) return;

    const apiCategories = '/Restaurant-Management-System/api/categories';
    const apiMenu = '/Restaurant-Management-System/api/menu';
    const apiIngredients = '/Restaurant-Management-System/api/ingredients/list.php';

    const notice = document.getElementById('menuNotice');
    const addCategoryBtn = document.getElementById('addCategoryBtn');
    const addProductBtn = document.getElementById('addProductBtn');
    const productTableBody = document.getElementById('productTableBody');
    const productCategoryFilter = document.getElementById('productCategoryFilter');
    const productSearch = document.getElementById('productSearch');

    const categoryModal = document.getElementById('categoryModal');
    const categoryModalClose = document.getElementById('categoryModalClose');
    const categoryModalTitle = document.getElementById('categoryModalTitle');
    const categoryForm = document.getElementById('categoryForm');
    const categoryIdInput = document.getElementById('categoryId');
    const categoryNameInput = document.getElementById('categoryName');
    const categoryDescriptionInput = document.getElementById('categoryDescription');
    const categoryOrderInput = document.getElementById('categoryOrder');

    const productModal = document.getElementById('productModal');
    const productModalClose = document.getElementById('productModalClose');
    const productModalTitle = document.getElementById('productModalTitle');
    const productForm = document.getElementById('productForm');
    const productIdInput = document.getElementById('productId');
    const productNameInput = document.getElementById('productName');
    const productCategoryInput = document.getElementById('productCategory');
    const productPriceInput = document.getElementById('productPrice');
    const productStatusInput = document.getElementById('productStatus');
    const productDescriptionInput = document.getElementById('productDescription');
    const productImageUrlInput = document.getElementById('productImageUrl');
    const ingredientList = document.getElementById('ingredientList');
    const ingredientEmpty = document.getElementById('ingredientEmpty');

    let categories = [];
    let products = [];
    let ingredients = [];

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

    const formatCurrency = (value) => {
        const num = Number(value || 0);
        return `₺${num.toFixed(2)}`;
    };

    const openModal = (modal) => {
        if (modal) modal.classList.add('active');
    };

    const closeModal = (modal) => {
        if (modal) modal.classList.remove('active');
    };

    const resetCategoryForm = () => {
        categoryIdInput.value = '';
        categoryNameInput.value = '';
        categoryDescriptionInput.value = '';
        categoryOrderInput.value = '0';
        categoryModalTitle.textContent = 'Kategori Ekle';
    };

    const resetProductForm = () => {
        productIdInput.value = '';
        productNameInput.value = '';
        productPriceInput.value = '';
        productStatusInput.value = '1';
        productDescriptionInput.value = '';
        productImageUrlInput.value = '';
        ingredientList.innerHTML = '';
        ingredientEmpty.style.display = 'block';
        productModalTitle.textContent = 'Ürün Ekle';
    };

    const getCategoryName = (categoryId) => {
        const category = categories.find((item) => Number(item.category_id) === Number(categoryId));
        return category ? category.category_name : '-';
    };

    const renderCategoryOptions = () => {
        if (!productCategoryInput || !productCategoryFilter) return;
        if (!categories.length) {
            productCategoryInput.innerHTML = '<option value="">Kategori yok</option>';
            productCategoryFilter.innerHTML = '<option value="">Kategori yok</option>';
            return;
        }
        productCategoryInput.innerHTML = categories.map((item) => (
            `<option value="${item.category_id}">${item.category_name}</option>`
        )).join('');

        const filterOptions = categories.map((item) => (
            `<option value="${item.category_id}">${item.category_name}</option>`
        )).join('');
        productCategoryFilter.innerHTML = `<option value="">Tüm Kategoriler</option>${filterOptions}`;
    };

    const renderCategories = () => {
        if (!categories.length) {
            categoryList.innerHTML = '<div class="menu-category-item">Kategori bulunamadı.</div>';
            return;
        }

        const productCounts = products.reduce((acc, item) => {
            const key = Number(item.category_id);
            acc[key] = (acc[key] || 0) + 1;
            return acc;
        }, {});

        categoryList.innerHTML = categories.map((item) => {
            const count = productCounts[item.category_id] || 0;
            return `
                <div class="menu-category-item" data-category-id="${item.category_id}">
                    <div>
                        <strong>${item.category_name}</strong>
                        <span>${item.description || 'Açıklama yok'}</span>
                    </div>
                    <div class="menu-category-meta">
                        <span>${count} ürün</span>
                        <div class="menu-actions">
                            <button class="btn btn--secondary btn--small" data-action="edit">Düzenle</button>
                            <button class="btn btn--ghost btn--small" data-action="delete">Sil</button>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    };

    const renderProducts = () => {
        if (!productTableBody) return;
        let filtered = [...products];
        const categoryFilter = productCategoryFilter ? productCategoryFilter.value : '';
        const query = productSearch ? productSearch.value.trim().toLowerCase() : '';

        if (categoryFilter) {
            filtered = filtered.filter((item) => String(item.category_id) === String(categoryFilter));
        }

        if (query) {
            filtered = filtered.filter((item) => item.product_name.toLowerCase().includes(query));
        }

        if (!filtered.length) {
            productTableBody.innerHTML = '<tr><td colspan="6">Ürün bulunamadı.</td></tr>';
            return;
        }

        productTableBody.innerHTML = filtered.map((item) => {
            const statusLabel = Number(item.is_available) === 1 ? 'Aktif' : 'Pasif';
            const statusClass = Number(item.is_available) === 1 ? 'ok' : 'cancelled';
            return `
                <tr data-product-id="${item.product_id}">
                    <td>
                        <strong>${item.product_name}</strong>
                        <span class="menu-product-desc">${item.description || '-'}</span>
                    </td>
                    <td>${getCategoryName(item.category_id)}</td>
                    <td>${formatCurrency(item.price)}</td>
                    <td><span class="status-badge ${statusClass}">${statusLabel}</span></td>
                    <td><span class="menu-recipe-chip">${item.ingredients_count || 0} malzeme</span></td>
                    <td>
                        <div class="menu-actions">
                            <button class="btn btn--secondary btn--small" data-action="edit">Düzenle</button>
                            <button class="btn btn--ghost btn--small" data-action="delete">Sil</button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    };

    const renderIngredientMatrix = (selected = {}) => {
        if (!ingredientList) return;
        ingredientList.innerHTML = '';
        if (!ingredients.length) {
            ingredientEmpty.style.display = 'block';
            return;
        }
        ingredientEmpty.style.display = 'none';
        ingredients.forEach((item) => {
            const row = document.createElement('div');
            row.className = 'ingredient-row';

            const label = document.createElement('label');
            label.className = 'ingredient-check';
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.value = item.ingredient_id;
            checkbox.checked = Boolean(selected[item.ingredient_id]);
            const nameSpan = document.createElement('span');
            nameSpan.textContent = item.ingredient_name;
            label.appendChild(checkbox);
            label.appendChild(nameSpan);

            const qtyInput = document.createElement('input');
            qtyInput.className = 'input ingredient-qty';
            qtyInput.type = 'number';
            qtyInput.min = '0.001';
            qtyInput.step = '0.001';
            qtyInput.value = selected[item.ingredient_id] || '';
            qtyInput.disabled = !checkbox.checked;

            const unitSpan = document.createElement('span');
            unitSpan.className = 'ingredient-unit';
            unitSpan.textContent = item.unit;

            row.classList.toggle('is-active', checkbox.checked);

            checkbox.addEventListener('change', () => {
                qtyInput.disabled = !checkbox.checked;
                row.classList.toggle('is-active', checkbox.checked);
                if (!checkbox.checked) {
                    qtyInput.value = '';
                }
            });

            row.appendChild(label);
            row.appendChild(qtyInput);
            row.appendChild(unitSpan);
            ingredientList.appendChild(row);
        });
    };

    const collectIngredients = () => {
        return Array.from(document.querySelectorAll('.ingredient-row')).map((row) => {
            const checkbox = row.querySelector('input[type="checkbox"]');
            const qty = row.querySelector('.ingredient-qty');
            if (!checkbox || !checkbox.checked) return null;
            return {
                ingredient_id: checkbox.value,
                quantity_required: qty ? qty.value : ''
            };
        }).filter((item) => item && Number(item.quantity_required) > 0);
    };

    const loadCategories = async () => {
        const data = await fetchJson(`${apiCategories}/list.php`);
        categories = data.data || [];
    };

    const loadIngredients = async () => {
        const data = await fetchJson(apiIngredients);
        ingredients = data.data || [];
    };

    const loadProducts = async () => {
        const data = await fetchJson(`${apiMenu}/list.php`);
        products = data.data || [];
    };

    const loadProductsWithIngredients = async () => {
        await loadProducts();
        const ingredientCounts = await Promise.all(products.map(async (item) => {
            try {
                const response = await fetchJson(`${apiMenu}/ingredients.php?product_id=${item.product_id}`);
                return { id: item.product_id, count: response.data.length };
            } catch (error) {
                return { id: item.product_id, count: 0 };
            }
        }));

        const countMap = ingredientCounts.reduce((acc, entry) => {
            acc[entry.id] = entry.count;
            return acc;
        }, {});

        products = products.map((item) => ({
            ...item,
            ingredients_count: countMap[item.product_id] || 0
        }));
    };

    const refreshAll = async () => {
        try {
            await Promise.all([loadCategories(), loadIngredients(), loadProductsWithIngredients()]);
            renderCategoryOptions();
            renderCategories();
            renderProducts();
        } catch (error) {
            showNotice(error.message, 'error');
        }
    };

    const openCategoryModal = (category = null) => {
        resetCategoryForm();
        if (category) {
            categoryIdInput.value = category.category_id;
            categoryNameInput.value = category.category_name;
            categoryDescriptionInput.value = category.description || '';
            categoryOrderInput.value = category.display_order || 0;
            categoryModalTitle.textContent = 'Kategori Düzenle';
        }
        openModal(categoryModal);
    };

    const openProductModal = async (product = null) => {
        resetProductForm();
        if (!categories.length) {
            showNotice('Önce kategori ekleyin', 'error');
            return;
        }
        if (product) {
            productIdInput.value = product.product_id;
            productNameInput.value = product.product_name;
            productCategoryInput.value = product.category_id;
            productPriceInput.value = product.price;
            productStatusInput.value = product.is_available;
            productDescriptionInput.value = product.description || '';
            productImageUrlInput.value = product.image_url || '';
            productModalTitle.textContent = 'Ürün Düzenle';

            try {
                const response = await fetchJson(`${apiMenu}/ingredients.php?product_id=${product.product_id}`);
                const selected = response.data.reduce((acc, item) => {
                    acc[item.ingredient_id] = item.quantity_required;
                    return acc;
                }, {});
                renderIngredientMatrix(selected);
            } catch (error) {
                showNotice(error.message, 'error');
            }
        } else if (productCategoryFilter && productCategoryFilter.value) {
            productCategoryInput.value = productCategoryFilter.value;
        }
        if (!product) {
            renderIngredientMatrix();
        }
        openModal(productModal);
    };

    addCategoryBtn.addEventListener('click', () => openCategoryModal());
    addProductBtn.addEventListener('click', () => openProductModal());

    categoryModalClose.addEventListener('click', () => closeModal(categoryModal));
    productModalClose.addEventListener('click', () => closeModal(productModal));

    categoryModal.addEventListener('click', (event) => {
        if (event.target === categoryModal) closeModal(categoryModal);
    });

    productModal.addEventListener('click', (event) => {
        if (event.target === productModal) closeModal(productModal);
    });

    categoryForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        const payload = new URLSearchParams({
            category_id: categoryIdInput.value,
            category_name: categoryNameInput.value.trim(),
            description: categoryDescriptionInput.value.trim(),
            display_order: categoryOrderInput.value
        });

        const endpoint = categoryIdInput.value ? 'update.php' : 'create.php';

        try {
            await fetchJson(`${apiCategories}/${endpoint}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: payload.toString()
            });
            closeModal(categoryModal);
            showNotice(categoryIdInput.value ? 'Kategori güncellendi' : 'Kategori eklendi');
            await refreshAll();
        } catch (error) {
            showNotice(error.message, 'error');
        }
    });

    productForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        const payload = new URLSearchParams({
            product_id: productIdInput.value,
            category_id: productCategoryInput.value,
            product_name: productNameInput.value.trim(),
            description: productDescriptionInput.value.trim(),
            price: productPriceInput.value,
            is_available: productStatusInput.value,
            image_url: productImageUrlInput.value.trim()
        });

        const endpoint = productIdInput.value ? 'update.php' : 'create.php';

        try {
            const response = await fetchJson(`${apiMenu}/${endpoint}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: payload.toString()
            });

            const productId = productIdInput.value || (response.data ? response.data.product_id : null);
            const ingredientsPayload = new URLSearchParams({
                product_id: productId,
                ingredients: JSON.stringify(collectIngredients())
            });

            await fetchJson(`${apiMenu}/update_ingredients.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: ingredientsPayload.toString()
            });

            closeModal(productModal);
            showNotice(productIdInput.value ? 'Ürün güncellendi' : 'Ürün eklendi');
            await refreshAll();
        } catch (error) {
            showNotice(error.message, 'error');
        }
    });

    categoryList.addEventListener('click', async (event) => {
        const actionBtn = event.target.closest('[data-action]');
        if (!actionBtn) return;
        const item = event.target.closest('[data-category-id]');
        if (!item) return;
        const categoryId = item.dataset.categoryId;
        const category = categories.find((entry) => String(entry.category_id) === String(categoryId));
        if (!category) return;

        if (actionBtn.dataset.action === 'edit') {
            openCategoryModal(category);
            return;
        }

        if (actionBtn.dataset.action === 'delete') {
            const count = products.filter((entry) => String(entry.category_id) === String(categoryId)).length;
            const message = count > 0
                ? `Bu kategoride ${count} ürün var. Silmek istiyor musunuz?`
                : 'Bu kategori silinsin mi?';
            if (!window.confirm(message)) return;
            try {
                await fetchJson(`${apiCategories}/delete.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({ category_id: categoryId }).toString()
                });
                showNotice('Kategori silindi');
                await refreshAll();
            } catch (error) {
                showNotice(error.message, 'error');
            }
        }
    });

    productTableBody.addEventListener('click', async (event) => {
        const actionBtn = event.target.closest('[data-action]');
        if (!actionBtn) return;
        const row = event.target.closest('[data-product-id]');
        if (!row) return;
        const productId = row.dataset.productId;
        const product = products.find((entry) => String(entry.product_id) === String(productId));
        if (!product) return;

        if (actionBtn.dataset.action === 'edit') {
            openProductModal(product);
            return;
        }

        if (actionBtn.dataset.action === 'delete') {
            if (!window.confirm('Bu ürün silinsin mi?')) return;
            try {
                await fetchJson(`${apiMenu}/delete.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({ product_id: productId }).toString()
                });
                showNotice('Ürün silindi');
                await refreshAll();
            } catch (error) {
                showNotice(error.message, 'error');
            }
        }
    });

    productCategoryFilter.addEventListener('change', renderProducts);
    productSearch.addEventListener('input', renderProducts);

    refreshAll();
})();
