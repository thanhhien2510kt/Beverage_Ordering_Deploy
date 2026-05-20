document.addEventListener('DOMContentLoaded', () => {
    const usersListEl = document.getElementById('manageUsersList');
    const loadingEl = document.getElementById('manageUsersLoading');
    const emptyEl = document.getElementById('manageUsersEmpty');
    const tableEl = document.getElementById('usersTable');
    const searchInput = document.getElementById('manageUserSearchInput');
    
    let allUsers = [];

    // Load users from API
    async function loadUsers() {
        showLoading();
        try {
            const response = await fetch('../../api/management/users.php');
            const data = await response.json();
            
            if (data.success) {
                allUsers = data.users || [];
                renderUsers(allUsers);
            } else {
                showSnackBar(data.message || 'Không thể tải dữ liệu người dùng.', 'error');
                showEmpty();
            }
        } catch (error) {
            console.error('Error fetching users:', error);
            showSnackBar('Đã xảy ra lỗi khi tải danh sách người dùng.', 'error');
            showEmpty();
        }
    }

    function renderUsers(users) {
        if (!users || users.length === 0) {
            showEmpty();
            return;
        }

        hideLoadingAndEmpty();
        tableEl.style.display = 'table';
        
        usersListEl.innerHTML = users.map(user => {
            const fullName = `${user.ho || ''} ${user.ten || ''}`.trim();
            const statusClass = user.trangthai == 1 ? 'status-active' : 'status-inactive';
            const statusText = user.trangthai == 1 ? 'Hoạt động' : 'Bị khóa';
            
            return `
                <tr>
                    <td><strong>#${user.mauser}</strong></td>
                    <td>${fullName || 'N/A'}</td>
                    <td>
                        <div>${user.username}</div>
                        <div style="font-size: 0.85rem; color: #666;">${user.email || ''}</div>
                    </td>
                    <td>${user.dienthoai || 'N/A'}</td>
                    <td><span class="role-badge">${user.tenrole || 'N/A'}</span></td>
                    <td><span class="status-badge ${statusClass}">${statusText}</span></td>
                    <td style="white-space: nowrap;">
                        <button class="btn btn-sm view-user-btn" data-id="${user.mauser}" style="padding: 6px; font-size: 0.8rem; margin-right: 5px; background: transparent; border: 1px solid var(--border-color); color: var(--text-dark);" title="Xem chi tiết">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                        <button class="btn btn-primary btn-sm edit-user-btn" data-id="${user.mauser}" style="padding: 6px; font-size: 0.8rem; margin-right: 5px;" title="Sửa">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                        </button>
                        <button class="btn btn-danger btn-sm toggle-status-btn" data-id="${user.mauser}" data-status="${user.trangthai}" style="padding: 6px; font-size: 0.8rem;" title="${user.trangthai == 1 ? 'Khóa' : 'Mở khóa'}">
                            ${user.trangthai == 1 ? `
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                            ` : `
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 9.9-1"></path>
                            </svg>
                            `}
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
        
        // Add event listeners for new buttons
        document.querySelectorAll('.view-user-btn').forEach(btn => {
            btn.addEventListener('click', (e) => openViewModal(e.currentTarget.dataset.id));
        });
        document.querySelectorAll('.edit-user-btn').forEach(btn => {
            btn.addEventListener('click', (e) => openEditModal(e.currentTarget.dataset.id));
        });
        document.querySelectorAll('.toggle-status-btn').forEach(btn => {
            btn.addEventListener('click', (e) => toggleUserStatus(e.currentTarget.dataset.id, e.currentTarget.dataset.status));
        });
    }

    function showLoading() {
        loadingEl.style.display = 'block';
        emptyEl.style.display = 'none';
        tableEl.style.display = 'none';
    }

    function showEmpty() {
        loadingEl.style.display = 'none';
        emptyEl.style.display = 'block';
        tableEl.style.display = 'none';
    }

    function hideLoadingAndEmpty() {
        loadingEl.style.display = 'none';
        emptyEl.style.display = 'none';
    }

    // Search functionality
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const searchTerm = e.target.value.toLowerCase().trim();
            
            if (!searchTerm) {
                renderUsers(allUsers);
                return;
            }
            
            const filteredUsers = allUsers.filter(user => {
                const fullName = `${user.ho || ''} ${user.ten || ''}`.toLowerCase();
                const username = (user.username || '').toLowerCase();
                const email = (user.email || '').toLowerCase();
                const phone = (user.dienthoai || '').toLowerCase();
                
                return fullName.includes(searchTerm) || 
                       username.includes(searchTerm) || 
                       email.includes(searchTerm) || 
                       phone.includes(searchTerm);
            });
            
            renderUsers(filteredUsers);
        });
    }

    // Modal elements
    const userModal = document.getElementById('userModal');
    const userModalOverlay = document.getElementById('userModalOverlay');
    const closeUserModalBtn = document.getElementById('closeUserModal');
    const cancelUserBtn = document.getElementById('cancelUserBtn');
    const userForm = document.getElementById('userForm');
    const addUserBtn = document.getElementById('addUserBtn');
    const userModalTitle = document.getElementById('userModalTitle');
    const passwordHelp = document.getElementById('passwordHelp');

    // Open modal to add user
    if (addUserBtn) {
        addUserBtn.addEventListener('click', () => {
            userForm.reset();
            document.getElementById('userId').value = '';
            userModalTitle.textContent = 'Thêm Người dùng';
            
            // Enable all inputs and show save button
            Array.from(userForm.elements).forEach(el => {
                if (el.tagName !== 'BUTTON') {
                    el.readOnly = false;
                    if (el.tagName === 'SELECT') el.disabled = false;
                }
            });
            document.getElementById('userPassword').closest('div').style.display = 'block';
            document.getElementById('saveUserBtn').style.display = 'inline-block';
            document.getElementById('cancelUserBtn').textContent = 'Hủy';

            passwordHelp.style.display = 'none';
            document.getElementById('userPassword').required = true;
            userModal.style.display = 'flex';
        });
    }

    // Open modal to view user details
    window.openViewModal = function(userId) {
        const user = allUsers.find(u => u.mauser == userId);
        if (!user) return;

        populateUserModal(user);
        
        userModalTitle.textContent = 'Chi tiết Người dùng';
        passwordHelp.style.display = 'none';
        
        // Disable all inputs and hide save button
        Array.from(userForm.elements).forEach(el => {
            if (el.tagName !== 'BUTTON') {
                el.readOnly = true;
                if (el.tagName === 'SELECT') el.disabled = true;
            }
        });
        document.getElementById('userPassword').closest('div').style.display = 'none'; // Hide password field
        document.getElementById('saveUserBtn').style.display = 'none';
        document.getElementById('cancelUserBtn').textContent = 'Đóng';

        userModal.style.display = 'flex';
    };

    // Open modal to edit user
    window.openEditModal = function(userId) {
        const user = allUsers.find(u => u.mauser == userId);
        if (!user) return;

        populateUserModal(user);

        userModalTitle.textContent = 'Sửa Người dùng';
        passwordHelp.style.display = 'inline';
        
        // Enable all inputs and show save button
        Array.from(userForm.elements).forEach(el => {
            if (el.tagName !== 'BUTTON') {
                el.readOnly = false;
                if (el.tagName === 'SELECT') el.disabled = false;
            }
        });
        document.getElementById('userPassword').closest('div').style.display = 'block'; // Show password field
        document.getElementById('userPassword').required = false;
        document.getElementById('saveUserBtn').style.display = 'inline-block';
        document.getElementById('cancelUserBtn').textContent = 'Hủy';

        userModal.style.display = 'flex';
    };

    function populateUserModal(user) {
        userForm.reset();
        document.getElementById('userId').value = user.mauser;
        document.getElementById('userHo').value = user.ho || '';
        document.getElementById('userTen').value = user.ten || '';
        document.getElementById('userUsername').value = user.username || '';
        document.getElementById('userEmail').value = user.email || '';
        document.getElementById('userPhone').value = user.dienthoai || '';
        document.getElementById('userAddress').value = user.diachi || '';
        
        // Select matching role
        const roleSelect = document.getElementById('userRole');
        for (let i = 0; i < roleSelect.options.length; i++) {
            if (roleSelect.options[i].text === user.tenrole || roleSelect.options[i].value == user.marole) {
                roleSelect.selectedIndex = i;
                break;
            }
        }
    }

    // Close modal
    function closeModal() {
        userModal.style.display = 'none';
    }

    if (closeUserModalBtn) closeUserModalBtn.addEventListener('click', closeModal);
    if (cancelUserBtn) cancelUserBtn.addEventListener('click', closeModal);
    if (userModalOverlay) userModalOverlay.addEventListener('click', closeModal);

    // Save user (Create/Update)
    if (userForm) {
        userForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(userForm);
            const isEdit = formData.get('id') !== '';
            const endpoint = isEdit ? '../../api/management/update-user.php' : '../../api/management/create-user.php';

            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.success) {
                    showSnackBar(data.message || 'Lưu thành công!', 'success');
                    closeModal();
                    loadUsers();
                } else {
                    showSnackBar(data.message || 'Có lỗi xảy ra', 'error');
                }
            } catch (error) {
                console.error('Error saving user:', error);
                showSnackBar('Đã xảy ra lỗi khi lưu thông tin', 'error');
            }
        });
    }

    // Toggle user status
    window.toggleUserStatus = async function(userId, currentStatus) {
        const newStatus = currentStatus == 1 ? 0 : 1;
        const confirmMsg = newStatus == 1 ? 'Bạn có chắc chắn muốn mở khóa người dùng này?' : 'Bạn có chắc chắn muốn khóa người dùng này?';
        
        if (!confirm(confirmMsg)) return;

        try {
            const formData = new FormData();
            formData.append('id', userId);
            formData.append('status', newStatus);

            const response = await fetch('../../api/management/update-user-status.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (data.success) {
                showSnackBar('Cập nhật trạng thái thành công', 'success');
                loadUsers();
            } else {
                showSnackBar(data.message || 'Có lỗi xảy ra', 'error');
            }
        } catch (error) {
            console.error('Error toggling status:', error);
            showSnackBar('Đã xảy ra lỗi', 'error');
        }
    };

    // Initial load
    loadUsers();
});
