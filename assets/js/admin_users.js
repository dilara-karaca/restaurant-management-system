(() => {
    const tableBody = document.getElementById('personnelTableBody');
    if (!tableBody) return;

    const apiBase = '/Restaurant-Management-System/api/users';
    const notice = document.getElementById('usersNotice');
    const refreshBtn = document.getElementById('refreshUsersBtn');
    const createForm = document.getElementById('personnelCreateForm');
    const firstNameInput = document.getElementById('personnelFirstName');
    const lastNameInput = document.getElementById('personnelLastName');
    const usernameInput = document.getElementById('personnelUsername');
    const emailInput = document.getElementById('personnelEmail');
    const passwordInput = document.getElementById('personnelPassword');
    const roleSelect = document.getElementById('personnelRole');
    const salaryInput = document.getElementById('personnelSalary');
    const hireDateInput = document.getElementById('personnelHireDate');

    let personnel = [];
    let roles = [];

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

    const roleOptionsHtml = (currentRoleId) => roles.map((role) => `
        <option value="${role.role_id}" ${Number(role.role_id) === Number(currentRoleId) ? 'selected' : ''}>
            ${role.role_name}
        </option>
    `).join('');

    const renderRoleSelect = () => {
        if (!roleSelect) return;
        if (!roles.length) {
            roleSelect.innerHTML = '<option value="">Rol bulunamadı</option>';
            return;
        }
        roleSelect.innerHTML = roles.map((role) => `
            <option value="${role.role_id}">${role.role_name}</option>
        `).join('');
    };

    const renderTable = () => {
        if (!personnel.length) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7">Personel bulunamadı.</td>
                </tr>
            `;
            return;
        }

        tableBody.innerHTML = personnel.map((item) => `
            <tr>
                <td>${item.personnel_id}</td>
                <td>${item.first_name} ${item.last_name}</td>
                <td>
                    <select class="input role-select" data-user-id="${item.user_id}">
                        ${roleOptionsHtml(item.role_id)}
                    </select>
                </td>
                <td>${item.username}</td>
                <td><code>${item.password_hash}</code></td>
                <td>${item.position || '-'}</td>
                <td>
                    <button class="btn btn--primary btn--small" data-action="save-role" data-user-id="${item.user_id}">
                        Kaydet
                    </button>
                    <button class="btn btn--ghost btn--small" data-action="delete-user" data-user-id="${item.user_id}">
                        Sil
                    </button>
                </td>
            </tr>
        `).join('');
    };

    const loadPersonnel = async () => {
        try {
            const data = await fetchJson(`${apiBase}/list_personnel.php`);
            personnel = data.data.personnel || [];
            roles = data.data.roles || [];
            renderTable();
            renderRoleSelect();
        } catch (error) {
            showNotice(error.message, 'error');
        }
    };

    tableBody.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-action]');
        if (!button) return;
        const action = button.dataset.action;
        const userId = parseInt(button.dataset.userId, 10);
        if (!userId) return;

        if (action === 'save-role') {
            const select = tableBody.querySelector(`.role-select[data-user-id="${userId}"]`);
            if (!select) return;
            const roleId = parseInt(select.value, 10);
            if (!roleId) {
                showNotice('Geçerli bir rol seçin.', 'error');
                return;
            }
            try {
                await fetchJson(`${apiBase}/update_personnel_role.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        user_id: userId,
                        role_id: roleId
                    })
                });
                await loadPersonnel();
                showNotice('Personel rolü güncellendi.');
            } catch (error) {
                showNotice(error.message, 'error');
            }
            return;
        }

        if (action === 'delete-user') {
            if (!window.confirm('Bu personeli silmek istediğinizden emin misiniz?')) return;
            try {
                await fetchJson(`${apiBase}/delete_personnel.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ user_id: userId })
                });
                await loadPersonnel();
                showNotice('Personel silindi.');
            } catch (error) {
                showNotice(error.message, 'error');
            }
        }
    });

    if (refreshBtn) {
        refreshBtn.addEventListener('click', loadPersonnel);
    }

    if (createForm) {
        createForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            const payload = new URLSearchParams({
                first_name: firstNameInput.value.trim(),
                last_name: lastNameInput.value.trim(),
                username: usernameInput.value.trim(),
                email: emailInput.value.trim(),
                password: passwordInput.value,
                role_id: roleSelect.value,
                hire_date: hireDateInput.value
            });
            if (salaryInput && salaryInput.value) {
                payload.append('salary', salaryInput.value);
            }
            try {
                await fetchJson(`${apiBase}/create_personnel.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: payload
                });
                createForm.reset();
                await loadPersonnel();
                showNotice('Personel eklendi.');
            } catch (error) {
                showNotice(error.message, 'error');
            }
        });
    }

    loadPersonnel();
})();
