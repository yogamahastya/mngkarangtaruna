<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        
        * {
            font-family: 'Inter', sans-serif;
        }
        
        .bg-success-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .bg-danger-gradient {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        .bg-primary-gradient {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        .bg-info-gradient {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }
        
        .stat-card {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%);
            opacity: 0;
            transition: opacity 0.4s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }
        
        .stat-card:hover::before {
            opacity: 1;
        }
        
        .responsive-amount {
            font-size: 2.2rem;
            font-weight: 700;
            white-space: nowrap;
            letter-spacing: -0.5px;
        }

        @media (max-width: 767.98px) {
            .responsive-amount {
                font-size: 1.6rem;
            }
        }

        @media (max-width: 575.98px) {
            .responsive-amount {
                font-size: 1.3rem;
            }
        }

        .card-modern {
            border: none;
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.3s ease;
            background: #fff;
        }
        
        .card-modern:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }
        
        .card-header-modern {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 1.5rem;
            position: relative;
        }
        
        .card-header-modern::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #fff 0%, rgba(255,255,255,0.3) 100%);
        }
        
        .user-card {
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .user-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, transparent, #667eea, #764ba2);
            transition: left 0.5s ease;
        }
        
        .user-card:hover::before {
            left: 100%;
        }
        
        .user-card:hover {
            border-color: #667eea;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.2);
        }
        
        .avatar-modern {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: white;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        .badge-modern {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }
        
        .badge-admin {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        
        .badge-user {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }
        
        .info-box {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
            padding: 1rem;
            transition: all 0.3s ease;
        }
        
        .info-box:hover {
            background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
            transform: scale(1.02);
        }
        
        .btn-modern {
            border-radius: 50px;
            padding: 0.6rem 1.5rem;
            font-weight: 600;
            border: 2px solid transparent;
            transition: all 0.3s ease;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }
        
        .btn-edit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
        }
        
        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .btn-delete {
            background: transparent;
            color: #f5576c;
            border: 2px solid #f5576c;
        }
        
        .btn-delete:hover {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border-color: transparent;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(245, 87, 108, 0.4);
        }
        
        .search-box {
            border-radius: 50px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
            background: white;
        }
        
        .search-box:focus-within {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }
        
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2rem;
            border-radius: 20px;
            color: white;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }
        
        .toggle-switch {
            position: relative;
            width: 60px;
            height: 30px;
        }
        
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
            transition: 0.4s;
            border-radius: 50px;
        }
        
        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 22px;
            width: 22px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: 0.4s;
            border-radius: 50%;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        
        input:checked + .toggle-slider {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }
        
        input:checked + .toggle-slider:before {
            transform: translateX(30px);
        }
        
        .empty-state {
            padding: 4rem 2rem;
            text-align: center;
        }
        
        .empty-state i {
            font-size: 5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            opacity: 0.3;
        }
        
        .pagination-modern .page-link {
            border: none;
            border-radius: 10px;
            margin: 0 5px;
            color: #667eea;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .pagination-modern .page-link:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        .pagination-modern .page-item.active .page-link {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-card {
            animation: fadeInUp 0.5s ease forwards;
        }
    </style>
</head>

<div style="min-height: calc(100vh - 200px);">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2 class="mb-2 fw-bold">
                    <i class="fa-solid fa-user-circle me-3"></i>Kelola Data Users
                </h2>
                <p class="mb-0 opacity-75">Manajemen pengguna sistem dan hak akses</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <label class="toggle-switch mb-0">
                    <input type="checkbox" id="autoUpdateCheckbox">
                    <span class="toggle-slider"></span>
                </label>
                <div>
                    <span id="autoUpdateText" class="fw-semibold">Auto Update</span>
                    <span id="updateSuccessBadge" class="badge bg-success rounded-pill ms-2 d-none">
                        <i class="fas fa-check me-1"></i> Berhasil
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Search and Actions -->
    <div class="card card-modern shadow-lg mb-4">
        <div class="card-body p-4">
            <div class="row gy-3 align-items-center">
                
                <!-- Search Bar -->
                <div class="col-12 col-lg-6">
                    <form action="" method="GET">
                        <input type="hidden" name="tab" value="users">
                        <div class="input-group search-box">
                            <span class="input-group-text bg-transparent border-0 ps-4">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" 
                                   class="form-control border-0 ps-0" 
                                   placeholder="Cari username atau anggota..." 
                                   name="search" 
                                   value="<?= htmlspecialchars($searchTerm ?? '') ?>">
                            <button class="btn btn-edit px-4" type="submit">
                                Cari
                            </button>
                            <?php if (!empty($searchTerm)): ?>
                                <a href="?tab=users" class="btn btn-delete px-3">
                                    <i class="fas fa-times"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
    
                <!-- Action Buttons -->
                <div class="col-12 col-lg-6">
                    <div class="d-flex gap-2 justify-content-lg-end flex-wrap">
                        <button type="button" class="btn btn-modern btn-edit" data-bs-toggle="modal" data-bs-target="#addUsersModal">
                            <i class="fa-solid fa-plus-circle me-2"></i> Tambah User
                        </button>
                        <button type="button" class="btn btn-modern btn-edit" data-bs-toggle="modal" data-bs-target="#addLokasiModal">
                            <i class="fa-solid fa-map-marker-alt me-2"></i> Lokasi Absensi
                        </button>
                    </div>
                </div>
    
            </div>
        </div>
    </div>
    
    <!-- User Cards -->
    <div class="row g-4">
        <?php if (count($users) > 0): ?>
            <?php foreach ($users as $index => $row): ?>
                <?php
                $anggotaName = 'Tidak Terkait';
                if ($row['anggota_id'] !== NULL) {
                    foreach ($anggotaList as $member) {
                        if ($member['id'] == $row['anggota_id']) {
                            $anggotaName = $member['nama_lengkap'];
                            break;
                        }
                    }
                }
                
                $badgeClass = ($row['role'] == 'admin') ? 'badge-admin' : 'badge-user';
                ?>
                <div class="col-lg-4 col-md-6 col-sm-12 animate-card" style="animation-delay: <?= $index * 0.1 ?>s">
                    <div class="card user-card h-100 card-modern">
                        <div class="card-body p-4">
                            <!-- User Header -->
                            <div class="d-flex align-items-start justify-content-between mb-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar-modern">
                                        <i class="bx bx-user"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-1 fw-bold text-dark">
                                            <?= htmlspecialchars($row['username']) ?>
                                        </h5>
                                        <span class="badge badge-modern <?= $badgeClass ?>">
                                            <?= htmlspecialchars(ucfirst($row['role'])) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- User Info -->
                            <div class="info-box">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="mdi mdi-account-card-details text-primary" style="font-size: 1.3rem;"></i>
                                        <span class="text-muted small fw-semibold">ANGGOTA</span>
                                    </div>
                                    <span class="fw-bold text-dark small"><?= htmlspecialchars($anggotaName) ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Action Footer -->
                        <div class="card-footer bg-transparent border-0 p-4 pt-0">
                            <div class="d-flex gap-2">
                                <button class="btn btn-modern btn-edit flex-fill edit-btn"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editUsersModal" 
                                        data-id="<?= $row['id'] ?>" 
                                        data-username="<?= $row['username'] ?>" 
                                        data-role="<?= $row['role'] ?>" 
                                        data-anggota-id="<?= $row['anggota_id'] ?>">
                                    <i class="bx bx-edit"></i> Edit
                                </button>
                                <form action="" method="POST" class="flex-fill">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="tab" value="users">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <button type="submit" 
                                            class="btn btn-modern btn-delete w-100" 
                                            onclick="return confirm('Yakin ingin menghapus user ini?')">
                                        <i class="bx bx-trash"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="empty-state">
                    <i class="fa-solid fa-users-slash d-block mb-4"></i>
                    <h4 class="text-muted fw-bold mb-2">Tidak Ada Data User</h4>
                    <p class="text-muted">Belum ada user yang terdaftar dalam sistem</p>
                    <button class="btn btn-modern btn-edit mt-3" data-bs-toggle="modal" data-bs-target="#addUsersModal">
                        <i class="fa-solid fa-plus-circle me-2"></i> Tambah User Pertama
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Pagination -->
    <?php if (count($users) > 0): ?>
    <nav aria-label="Page navigation" class="mt-5">
        <ul class="pagination pagination-modern justify-content-center flex-wrap">
            <?php 
            $total_pages = $total_pages ?? 1;
            $current_page = $page ?? 1;
            
            $show_pages = [];
            
            if ($total_pages <= 7) {
                for ($i = 1; $i <= $total_pages; $i++) {
                    $show_pages[] = $i;
                }
            } else {
                $show_pages[] = 1;
                
                if ($current_page > 3) {
                    $show_pages[] = '...';
                }
                
                for ($i = max(2, $current_page - 1); $i <= min($total_pages - 1, $current_page + 1); $i++) {
                    $show_pages[] = $i;
                }
                
                if ($current_page < $total_pages - 2) {
                    $show_pages[] = '...';
                }
                
                if ($total_pages > 1) {
                    $show_pages[] = $total_pages;
                }
            }
            
            foreach ($show_pages as $page_num): 
                if ($page_num === '...'): ?>
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                <?php else: ?>
                    <li class="page-item <?= ($current_page == $page_num) ? 'active' : '' ?>">
                        <a class="page-link" href="?tab=users&page=<?= htmlspecialchars($page_num) ?><?= !empty($searchTerm) ? '&search=' . htmlspecialchars($searchTerm) : '' ?>">
                            <?= htmlspecialchars($page_num) ?>
                        </a>
                    </li>
                <?php endif;
            endforeach; ?>
        </ul>
    </nav>
    <?php endif; ?>
    
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkbox = document.getElementById('autoUpdateCheckbox');
    const autoUpdateText = document.getElementById('autoUpdateText');
    const updateSuccessBadge = document.getElementById('updateSuccessBadge');

    let timeoutId;
    let isUpdating = false;
    let currentStatus = false; // Track current status

    // Fungsi untuk menampilkan badge feedback
    function showStatusFeedback(message, type, duration = 1500) {
        if (timeoutId) {
            clearTimeout(timeoutId);
        }

        autoUpdateText.classList.add('d-none');
        updateSuccessBadge.innerHTML = `<i class="fas fa-${type === 'success' ? 'check' : 'times'} me-1"></i> ${message}`;
        updateSuccessBadge.classList.remove('d-none', 'bg-success', 'bg-danger');
        updateSuccessBadge.classList.add(`bg-${type}`);

        timeoutId = setTimeout(() => {
            updateSuccessBadge.classList.add('d-none');
            autoUpdateText.classList.remove('d-none');
        }, duration);
    }

    // Fungsi untuk memperbarui status di server
    function updateServerStatus(status) {
        if (isUpdating) {
            console.log('Update already in progress...');
            return Promise.reject('Already updating');
        }

        isUpdating = true;
        
        return fetch('../application/update_settings.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ auto_update: status }),
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('Server response:', data);
            if (data.status === 'success') {
                currentStatus = status;
                checkbox.checked = status;
                showStatusFeedback('Berhasil', 'success');
                return true;
            } else {
                throw new Error(data.message || 'Update failed');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Kembalikan ke status sebelumnya
            checkbox.checked = currentStatus;
            showStatusFeedback('Gagal', 'danger');
            
            if (timeoutId) {
                clearTimeout(timeoutId);
            }
            setTimeout(() => {
                updateSuccessBadge.classList.add('d-none');
                autoUpdateText.classList.remove('d-none');
            }, 2000);
            
            return false;
        })
        .finally(() => {
            isUpdating = false;
        });
    }

    // Ambil status dari server saat halaman dimuat
    fetch('../application/auto_update_status.json?' + new Date().getTime()) // Cache busting
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to fetch status');
            }
            return response.json();
        })
        .then(data => {
            console.log('Initial status from server:', data);
            // Parsing yang lebih robust
            const isAutoUpdateActive = data.auto_update === true || 
                                       data.auto_update === 1 || 
                                       data.auto_update === "1" ||
                                       data.auto_update === "true";
            
            currentStatus = isAutoUpdateActive;
            checkbox.checked = isAutoUpdateActive;
            
            autoUpdateText.classList.remove('d-none');
            updateSuccessBadge.classList.add('d-none');
            
            console.log('Auto update set to:', isAutoUpdateActive);
        })
        .catch(error => {
            console.error('Gagal memuat pengaturan awal:', error);
            currentStatus = false;
            checkbox.checked = false;
            autoUpdateText.classList.remove('d-none');
            updateSuccessBadge.classList.add('d-none');
        });

    // Event listener untuk checkbox
    checkbox.addEventListener('change', function(e) {
        if (isUpdating) {
            e.preventDefault();
            checkbox.checked = currentStatus;
            return;
        }
        
        const newStatus = this.checked;
        console.log('Checkbox changed to:', newStatus);
        
        // Update ke server
        updateServerStatus(newStatus);
    });

    // Prevent double-click issues
    checkbox.addEventListener('click', function(e) {
        if (isUpdating) {
            e.preventDefault();
            return false;
        }
    });
});
</script>