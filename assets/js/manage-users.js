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
                    <td>
                        <button class="btn btn-primary btn-sm edit-user-btn" data-id="${user.mauser}" style="padding: 5px 10px; font-size: 0.8rem; margin-right: 5px;">Sửa</button>
                        <button class="btn btn-danger btn-sm toggle-status-btn" data-id="${user.mauser}" data-status="${user.trangthai}" style="padding: 5px 10px; font-size: 0.8rem;">
                            ${user.trangthai == 1 ? 'Khóa' : 'Mở khóa'}
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
        
        // Add event listeners for new buttons
        document.querySelectorAll('.edit-user-btn').forEach(btn => {
            btn.addEventListener('click', (e) => openEditModal(e.target.dataset.id));
        });
        document.querySelectorAll('.toggle-status-btn').forEach(btn => {
            btn.addEventListener('click', (e) => toggleUserStatus(e.target.dataset.id, e.target.dataset.status));
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
            passwordHelp.style.display = 'none';
            document.getElementById('userPassword').required = true;
            userModal.style.display = 'flex';
        });
    }

    // Open modal to edit user
    window.openEditModal = function(userId) {
        const user = allUsers.find(u => u.mauser == userId);
        if (!user) return;

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

        userModalTitle.textContent = 'Sửa Người dùng';
        passwordHelp.style.display = 'inline';
        document.getElementById('userPassword').required = false;
        userModal.style.display = 'flex';
    };

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
