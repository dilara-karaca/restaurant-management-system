document.addEventListener('DOMContentLoaded', function () {
    loadCategories();
    // Modal form submit
    document.getElementById('category-form').onsubmit = handleCategoryFormSubmit;
});

function loadCategories() {
    fetch('/Restaurant-Management-System/api/categories/list.php')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                renderCategories(data.data);
            } else {
                document.getElementById('menu-categories').innerHTML = '<p>Kategori yüklenemedi.</p>';
            }
        });
}

function renderCategories(categories) {
    const container = document.getElementById('menu-categories');
    container.innerHTML = '';
    categories.forEach(cat => {
        const card = document.createElement('div');
        card.className = 'category-card';
        card.innerHTML = `
            <div class="category-title">${cat.category_name}</div>
            <div class="category-card__footer">
                <button class="btn-crud edit" onclick="event.stopPropagation(); openCategoryModal(${cat.category_id}, '${cat.category_name.replace(/'/g, "&#39;")}', '${(cat.description || '').replace(/'/g, "&#39;")}', ${cat.display_order})">Düzenle</button>
                <button class="btn-crud delete" onclick="event.stopPropagation(); deleteCategory(${cat.category_id})">Sil</button>
            </div>
        `;
        card.onclick = () => loadProducts(cat.category_id, cat.category_name);
        container.appendChild(card);
    });
}
// Kategori Modalı Aç/Kapat
function openCategoryModal(id = '', name = '', desc = '', order = 0) {
    document.getElementById('category-modal').classList.add('active');
    document.getElementById('category_id').value = id;
    document.getElementById('category_name').value = name;
    document.getElementById('description').value = desc;
    document.getElementById('display_order').value = order;
    document.getElementById('category-modal-title').innerText = id ? 'Kategori Düzenle' : 'Kategori Ekle';
}

function closeCategoryModal() {
    document.getElementById('category-modal').classList.remove('active');
    document.getElementById('category-form').reset();
}

// Kategori Ekle/Güncelle
function handleCategoryFormSubmit(e) {
    e.preventDefault();
    const id = document.getElementById('category_id').value;
    const name = document.getElementById('category_name').value;
    const desc = document.getElementById('description').value;
    const order = document.getElementById('display_order').value;
    const imageInput = document.getElementById('category_image');
    const formData = new FormData();
    formData.append('category_name', name);
    formData.append('description', desc);
    formData.append('display_order', order);
    if (imageInput && imageInput.files.length > 0) {
        formData.append('image', imageInput.files[0]);
    }
    if (id) formData.append('category_id', id);
    fetch(`/Restaurant-Management-System/api/categories/${id ? 'update' : 'create'}.php`, {
        method: 'POST',
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                closeCategoryModal();
                loadCategories();
            } else {
                alert(data.message || 'İşlem başarısız!');
            }
        });
}

// Kategori Sil
function deleteCategory(id) {
    if (!confirm('Bu kategoriyi silmek istediğinize emin misiniz?')) return;
    const formData = new FormData();
    formData.append('category_id', id);
    fetch('/Restaurant-Management-System/api/categories/delete.php', {
        method: 'POST',
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                loadCategories();
            } else {
                alert(data.message || 'Silme işlemi başarısız!');
            }
        });
}

function loadProducts(categoryId, categoryName) {
    fetch(`/Restaurant-Management-System/api/menu/list.php?category_id=${categoryId}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                renderProducts(data.data, categoryName, categoryId);
            } else {
                document.getElementById('menu-products').innerHTML = '<p>Ürünler yüklenemedi.</p>';
            }
        });
    document.getElementById('menu-categories').style.display = 'none';
    document.getElementById('menu-products').style.display = 'block';
}

function renderProducts(products, categoryName, categoryId) {
    const container = document.getElementById('menu-products');
    container.innerHTML = `<button onclick="backToCategories()">&larr; Kategorilere Dön</button><h2>${categoryName}</h2>`;
    // Ürün ekle butonu
    const addBtn = document.createElement('button');
    addBtn.className = 'add-btn';
    addBtn.innerText = '+ Ürün Ekle';
    addBtn.onclick = () => openProductModal('', categoryId);
    container.appendChild(addBtn);
    const grid = document.createElement('div');
    grid.className = 'products-grid';
    products.forEach(prod => {
        const card = document.createElement('div');
        card.className = 'product-card';
        card.innerHTML = `
            <div class="product-title">${prod.product_name}</div>
            <div class="product-price">${prod.price} ₺</div>
            <div class="category-card__footer">
                <button class="btn-crud edit" onclick="event.stopPropagation(); openProductModal(${prod.product_id}, ${prod.category_id}, '${prod.product_name.replace(/'/g, "&#39;")}', '${(prod.description || '').replace(/'/g, "&#39;")}', ${prod.price}, ${prod.is_available}, '${(prod.image_url || '').replace(/'/g, "&#39;")}')">Düzenle</button>
                <button class="btn-crud delete" onclick="event.stopPropagation(); deleteProduct(${prod.product_id}, ${prod.category_id})">Sil</button>
            </div>
        `;
        grid.appendChild(card);
    });
    container.appendChild(grid);
}
// Ürün Modalı Aç/Kapat
function openProductModal(id = '', categoryId = '', name = '', desc = '', price = '', isAvailable = 1, imageUrl = '') {
    document.getElementById('product-modal').classList.add('active');
    document.getElementById('product_id').value = id;
    document.getElementById('product_category_id').value = categoryId;
    document.getElementById('product_name').value = name;
    document.getElementById('product_description').value = desc;
    document.getElementById('product_price').value = price;
    document.getElementById('product_is_available').value = isAvailable;
    document.getElementById('product_image_url').value = imageUrl;
    document.getElementById('product-modal-title').innerText = id ? 'Ürün Düzenle' : 'Ürün Ekle';
    document.getElementById('product-form').onsubmit = handleProductFormSubmit;
}

function closeProductModal() {
    document.getElementById('product-modal').classList.remove('active');
    document.getElementById('product-form').reset();
}

// Ürün Ekle/Güncelle
function handleProductFormSubmit(e) {
    e.preventDefault();
    const id = document.getElementById('product_id').value;
    const categoryId = document.getElementById('product_category_id').value;
    const name = document.getElementById('product_name').value;
    const desc = document.getElementById('product_description').value;
    const price = document.getElementById('product_price').value;
    const isAvailable = document.getElementById('product_is_available').value;
    const imageUrl = document.getElementById('product_image_url').value;
    const formData = new FormData();
    formData.append('category_id', categoryId);
    formData.append('product_name', name);
    formData.append('description', desc);
    formData.append('price', price);
    formData.append('is_available', isAvailable);
    formData.append('image_url', imageUrl);
    if (id) formData.append('product_id', id);
    fetch(`/Restaurant-Management-System/api/menu/${id ? 'update' : 'create'}.php`, {
        method: 'POST',
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                closeProductModal();
                loadProducts(categoryId, '');
            } else {
                alert(data.message || 'İşlem başarısız!');
            }
        });
}

// Ürün Sil
function deleteProduct(id, categoryId) {
    if (!confirm('Bu ürünü silmek istediğinize emin misiniz?')) return;
    const formData = new FormData();
    formData.append('product_id', id);
    fetch('/Restaurant-Management-System/api/menu/delete.php', {
        method: 'POST',
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                loadProducts(categoryId, '');
            } else {
                alert(data.message || 'Silme işlemi başarısız!');
            }
        });
}

function backToCategories() {
    document.getElementById('menu-products').style.display = 'none';
    document.getElementById('menu-categories').style.display = 'grid';
}
