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
            const fullName = `${user.Ho || ''} ${user.Ten || ''}`.trim();
            const statusClass = user.TrangThai == 1 ? 'status-active' : 'status-inactive';
            const statusText = user.TrangThai == 1 ? 'Hoạt động' : 'Bị khóa';
            
            return `
                <tr>
                    <td><strong>#${user.MaUser}</strong></td>
                    <td>${fullName || 'N/A'}</td>
                    <td>
                        <div>${user.Username}</div>
                        <div style="font-size: 0.85rem; color: #666;">${user.Email || ''}</div>
                    </td>
                    <td>${user.DienThoai || 'N/A'}</td>
                    <td><span class="role-badge">${user.TenRole || 'N/A'}</span></td>
                    <td><span class="status-badge ${statusClass}">${statusText}</span></td>
                </tr>
            `;
        }).join('');
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
                const fullName = `${user.Ho || ''} ${user.Ten || ''}`.toLowerCase();
                const username = (user.Username || '').toLowerCase();
                const email = (user.Email || '').toLowerCase();
                const phone = (user.DienThoai || '').toLowerCase();
                
                return fullName.includes(searchTerm) || 
                       username.includes(searchTerm) || 
                       email.includes(searchTerm) || 
                       phone.includes(searchTerm);
            });
            
            renderUsers(filteredUsers);
        });
    }

    // Initial load
    loadUsers();
});
