<?php
$users = $users ?? [];
$anggotaList = $anggotaList ?? [];
$searchTerm = $searchTerm ?? '';
$page = $page ?? 1;
$total_pages = $total_pages ?? 1;
$offset = $offset ?? 0;
?>

<style>
/* Toggle Switch Styling */
.toggle-switch {
    position: relative;
    display: inline-block;
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
    background-color: #ccc;
    transition: 0.4s;
    border-radius: 30px;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
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
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

input:checked + .toggle-slider {
    background-color: #0d6efd;
}

input:checked + .toggle-slider:before {
    transform: translateX(30px);
}

.toggle-slider:hover {
    box-shadow: 0 0 10px rgba(13, 110, 253, 0.3);
}

/* Status Indicator */
.status-indicator {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 8px;
    transition: all 0.3s ease;
}

.status-inactive {
    background-color: #6c757d;
}

.status-active {
    background-color: #28a745;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
        box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
    }
    50% {
        opacity: 0.8;
        box-shadow: 0 0 0 6px rgba(40, 167, 69, 0);
    }
}

/* Success Badge Animation */
@keyframes bounceIn {
    0% {
        opacity: 0;
        transform: scale(0.3);
    }
    50% {
        opacity: 1;
        transform: scale(1.05);
    }
    70% {
        transform: scale(0.9);
    }
    100% {
        transform: scale(1);
    }
}

.badge-animate {
    animation: bounceIn 0.5s ease-out;
}

/* Last Update Info */
.last-update-info {
    font-size: 0.85rem;
    color: #6c757d;
    margin-top: 0.5rem;
}

.update-spinner {
    display: inline-block;
    width: 12px;
    height: 12px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid #0d6efd;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-right: 8px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Card Animation on Update */
.user-card-updating {
    animation: cardPulse 0.5s ease-out;
}

@keyframes cardPulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.02);
    }
}
</style>

<div style="min-height: calc(100vh - 200px);">
    <!-- Page Header -->
    <div class="card shadow-lg border-0 border-start border-5 border-primary mb-4 rounded-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h2 class="mb-2 fw-bold text-dark d-flex align-items-center">
                        <i class="fa-solid fa-user-shield me-3 text-primary" style="font-size: 2rem;"></i>
                        <span>Kelola Data Users</span>
                    </h2>
                    <p class="mb-0 text-muted ms-5 ps-2">
                        <i class="fas fa-info-circle me-2"></i>Manajemen pengguna sistem dan hak akses
                    </p>
                </div>
                <div class="d-flex flex-column align-items-end gap-2">
                    <div class="d-flex align-items-center gap-3">
                        <label class="toggle-switch mb-0">
                            <input type="checkbox" id="autoUpdateCheckbox">
                            <span class="toggle-slider"></span>
                        </label>
                        <div>
                            <span class="status-indicator status-inactive" id="statusIndicator"></span>
                            <span id="autoUpdateText" class="fw-semibold">Auto Update</span>
                            <span id="updateSuccessBadge" class="badge bg-success rounded-pill ms-2 d-none">
                                <i class="fas fa-check me-1"></i> Berhasil
                            </span>
                        </div>
                    </div>
                    <div class="last-update-info">
                        <span id="updateSpinner" class="update-spinner d-none"></span>
                        <i class="fas fa-clock me-1"></i>
                        <span id="lastUpdateTime">Belum pernah diupdate</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Search and Actions Card -->
    <div class="card shadow-lg border-0 border-start border-5 border-primary mb-4 rounded-4">
        <div class="card-body py-3">
            <div class="row g-3 align-items-center">
                
                <!-- Search Bar -->
                <div class="col-12 col-md order-md-1">
                    <form action="" method="GET" class="mb-0">
                        <input type="hidden" name="tab" value="users">
                        
                        <div class="row g-2 align-items-center">
                            <!-- Input Group -->
                            <div class="col">
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0 rounded-start-pill border-end-0">
                                        <i class="fas fa-search text-primary"></i>
                                    </span>
                                    
                                    <input type="text" 
                                        id="search-input" 
                                        name="search" 
                                        value="<?= htmlspecialchars($searchTerm) ?>" 
                                        placeholder="Cari username atau anggota..." 
                                        class="form-control border-0 shadow-sm px-3 py-2">
                                    
                                    <!-- Tombol Submit -->
                                    <button class="btn btn-primary px-3 px-sm-4 shadow-sm rounded-end-pill" type="submit">
                                        <span class="d-none d-sm-inline">Cari</span>
                                        <i class="fas fa-search d-inline d-sm-none"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <?php if (!empty($searchTerm)): ?>
                                <!-- Tombol Hapus Pencarian -->
                                <div class="col-auto">
                                    <a href="?tab=users" 
                                       class="btn btn-outline-danger rounded-pill d-flex align-items-center justify-content-center p-0"
                                       style="width: 42px; height: 42px;"
                                       title="Hapus Pencarian">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Action Buttons -->
                            <div class="col-12 col-sm-auto">
                                <div class="d-flex gap-2 flex-wrap">
                                    <button type="button" 
                                            class="btn btn-primary rounded-pill px-4 py-2 shadow-sm flex-fill" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#addUsersModal">
                                        <i class="fa-solid fa-plus-circle me-2"></i> 
                                        <span class="d-none d-lg-inline">Tambah User</span>
                                        <span class="d-inline d-lg-none">User</span>
                                    </button>
                                    <button type="button" 
                                            class="btn btn-info rounded-pill px-4 py-2 shadow-sm flex-fill text-white" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#addLokasiModal">
                                        <i class="fa-solid fa-map-marker-alt me-2"></i> 
                                        <span class="d-none d-lg-inline">Lokasi</span>
                                        <span class="d-inline d-lg-none">Lokasi</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
    
    <!-- User Cards -->
    <div id="userCardsContainer">
        <?php if (count($users) > 0): ?>
            <div class="row g-4">
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
                    
                    // Badge styling berdasarkan role
                    $role = strtolower($row['role']);
                    $badge_class = 'badge rounded-pill px-3 py-2 ';
                    $badge_icon = '';
                    switch ($role) {
                        case 'admin':
                            $badge_class .= 'bg-danger';
                            $badge_icon = '<i class="fas fa-crown me-1"></i>';
                            break;
                        case 'user':
                            $badge_class .= 'bg-info text-white';
                            $badge_icon = '<i class="fas fa-user me-1"></i>';
                            break;
                        default:
                            $badge_class .= 'bg-secondary';
                            $badge_icon = '<i class="fas fa-user-circle me-1"></i>';
                            break;
                    }
                    
                    // Avatar Inisial
                    $nameParts = explode(' ', $row['username']);
                    $initials = '';
                    if (count($nameParts) >= 1) {
                        $initials .= strtoupper($nameParts[0][0]);
                    }
                    if (count($nameParts) >= 2) {
                        $initials .= strtoupper($nameParts[1][0]);
                    } else if (strlen($nameParts[0]) > 1) {
                        $initials .= strtoupper($nameParts[0][1]);
                    }
                    $avatarColor = sprintf("hsl(%d, 70%%, 50%%)", ($offset + $index) * 30 % 360);
                    ?>
                    <div class="col-lg-4 col-md-6 col-sm-12 user-card">
                        <div class="card shadow-lg h-100 rounded-4 border-0">
                            <div class="card-body">
                                <!-- Dropdown Menu -->
                                <div class="dropdown float-end">
                                    <a class="text-muted dropdown-toggle font-size-16" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true">
                                        <i class="bx bx-dots-horizontal-rounded"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end shadow-lg">
                                        <a class="dropdown-item edit-btn" href="#" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editUsersModal" 
                                            data-id="<?= $row['id'] ?>" 
                                            data-username="<?= $row['username'] ?>" 
                                            data-role="<?= $row['role'] ?>" 
                                            data-anggota-id="<?= $row['anggota_id'] ?>">
                                            <i class="bx bx-edit me-2"></i> Edit
                                        </a>

                                        <form action="" method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="tab" value="users">
                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                            <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Yakin ingin menghapus user ini?')">
                                                <i class="bx bx-trash me-2"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <!-- Avatar dan Info User -->
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0 avatar-lg me-3">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center shadow" 
                                             style="width: 3.5rem; height: 3.5rem; font-size: 1.25rem; flex-shrink: 0; background-color: <?= $avatarColor ?>; color: white; font-weight: 600;">
                                            <?= $initials ?>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1 fw-bold">
                                            <?= htmlspecialchars($row['username']) ?>
                                        </h6>
                                        <span class="<?= $badge_class ?>">
                                            <?= $badge_icon ?>
                                            <?= htmlspecialchars(ucfirst($row['role'])) ?>
                                        </span>
                                    </div>
                                </div>

                                <!-- Info Anggota -->
                                <div class="mt-3 pt-3 border-top">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                                 style="width: 40px; height: 40px; background-color: rgba(13, 110, 253, 0.1);">
                                                <i class="fas fa-user-tag text-primary"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <p class="mb-0 text-muted small fw-semibold">TERKAIT DENGAN</p>
                                            <p class="mb-0 fw-bold text-dark"><?= htmlspecialchars($anggotaName) ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center text-muted py-5">
                <i class="fa-solid fa-users-slash fa-3x mb-3 opacity-50"></i>
                <p class="mb-2 fw-bold">Tidak Ada Data User</p>
                <p class="mb-3">Belum ada user yang terdaftar dalam sistem</p>
                <button class="btn btn-primary rounded-pill px-4 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#addUsersModal">
                    <i class="fa-solid fa-plus-circle me-2"></i> Tambah User Pertama
                </button>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Pagination -->
    <?php if (count($users) > 0 && $total_pages > 1): ?>
    <nav aria-label="Page navigation" class="mt-4">
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                    <a class="page-link" href="?tab=users&page=<?= $i ?><?= !empty($searchTerm) ? '&search=' . htmlspecialchars($searchTerm) : '' ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<script>
(function() {
    let autoUpdateInterval = null;
    const checkbox = document.getElementById('autoUpdateCheckbox');
    const autoUpdateText = document.getElementById('autoUpdateText');
    const updateSuccessBadge = document.getElementById('updateSuccessBadge');
    const statusIndicator = document.getElementById('statusIndicator');
    const lastUpdateTime = document.getElementById('lastUpdateTime');
    const updateSpinner = document.getElementById('updateSpinner');

    // Fungsi untuk update data users
    function updateUsersData() {
        // Show loading spinner
        updateSpinner.classList.remove('d-none');
        
        // Get current page and search term
        const urlParams = new URLSearchParams(window.location.search);
        const currentPage = urlParams.get('page') || 1;
        const searchTerm = urlParams.get('search') || '';
        
        // Build fetch URL
        let fetchUrl = '?tab=users&ajax=1&page=' + currentPage;
        if (searchTerm) {
            fetchUrl += '&search=' + encodeURIComponent(searchTerm);
        }
        
        // Fetch updated data
        fetch(fetchUrl)
            .then(response => response.text())
            .then(html => {
                // Parse HTML response
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newContent = doc.querySelector('#userCardsContainer');
                
                if (newContent) {
                    // Add animation class to cards
                    const userCards = document.querySelectorAll('.user-card');
                    userCards.forEach(card => {
                        card.classList.add('user-card-updating');
                    });
                    
                    // Update content after animation
                    setTimeout(() => {
                        const container = document.getElementById('userCardsContainer');
                        container.innerHTML = newContent.innerHTML;
                    }, 250);
                }
                
                // Show success badge
                updateSuccessBadge.classList.remove('d-none');
                updateSuccessBadge.classList.add('badge-animate');
                
                // Update last update time
                const now = new Date();
                const timeString = now.getHours().toString().padStart(2, '0') + ':' + 
                                 now.getMinutes().toString().padStart(2, '0') + ':' + 
                                 now.getSeconds().toString().padStart(2, '0');
                lastUpdateTime.textContent = 'Terakhir update: ' + timeString;
                
                // Hide spinner
                updateSpinner.classList.add('d-none');
                
                // Hide success badge after 2 seconds
                setTimeout(() => {
                    updateSuccessBadge.classList.add('d-none');
                    updateSuccessBadge.classList.remove('badge-animate');
                }, 2000);
                
                console.log('Users data updated at:', timeString);
            })
            .catch(error => {
                console.error('Error updating users data:', error);
                updateSpinner.classList.add('d-none');
                
                // Show error notification (optional)
                const errorBadge = document.createElement('span');
                errorBadge.className = 'badge bg-danger rounded-pill ms-2';
                errorBadge.innerHTML = '<i class="fas fa-times me-1"></i> Error';
                autoUpdateText.parentNode.appendChild(errorBadge);
                
                setTimeout(() => {
                    errorBadge.remove();
                }, 3000);
            });
    }

    // Toggle auto update
    checkbox.addEventListener('change', function() {
        if (this.checked) {
            // Enable auto update
            autoUpdateText.textContent = 'Auto Update (ON)';
            autoUpdateText.classList.add('text-success', 'fw-bold');
            statusIndicator.classList.remove('status-inactive');
            statusIndicator.classList.add('status-active');
            
            // Update immediately
            updateUsersData();
            
            // Set interval - update setiap 10 detik
            autoUpdateInterval = setInterval(updateUsersData, 10000);
            
            console.log('Auto update enabled - refreshing every 10 seconds');
        } else {
            // Disable auto update
            autoUpdateText.textContent = 'Auto Update (OFF)';
            autoUpdateText.classList.remove('text-success', 'fw-bold');
            statusIndicator.classList.remove('status-active');
            statusIndicator.classList.add('status-inactive');
            
            // Clear interval
            if (autoUpdateInterval) {
                clearInterval(autoUpdateInterval);
                autoUpdateInterval = null;
            }
            
            console.log('Auto update disabled');
        }
    });

    // Manual update dengan keyboard shortcut (tekan 'U')
    document.addEventListener('keydown', function(e) {
        if ((e.key === 'u' || e.key === 'U') && !e.ctrlKey && !e.altKey) {
            // Pastikan tidak sedang mengetik di input field
            if (document.activeElement.tagName !== 'INPUT' && 
                document.activeElement.tagName !== 'TEXTAREA') {
                updateUsersData();
                console.log('Manual update triggered');
            }
        }
    });
})();
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkbox = document.getElementById('autoUpdateCheckbox');
        const autoUpdateText = document.getElementById('autoUpdateText');
        const updateSuccessBadge = document.getElementById('updateSuccessBadge');

        let timeoutId;
        let isUpdating = false;
        let currentStatus = false;

        function showStatusFeedback(message, type, duration = 1500) {
            if (timeoutId) clearTimeout(timeoutId);
            autoUpdateText.classList.add('d-none');
            updateSuccessBadge.innerHTML = `<i class="fas fa-${type === 'success' ? 'check' : 'times'} me-1"></i> ${message}`;
            updateSuccessBadge.classList.remove('d-none', 'bg-success', 'bg-danger');
            updateSuccessBadge.classList.add(`bg-${type}`);

            timeoutId = setTimeout(() => {
                updateSuccessBadge.classList.add('d-none');
                autoUpdateText.classList.remove('d-none');
            }, duration);
        }

        // ✅ Kirim status ke server
        function updateServerStatus(status) {
            if (isUpdating) return Promise.reject('Update in progress');
            isUpdating = true;

            return fetch('../application/update_settings.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ auto_update: status })
            })
            .then(res => {
                if (!res.ok) throw new Error('Network error');
                return res.json();
            })
            .then(data => {
                console.log('Server response:', data);
                if (data.status === 'success') {
                    currentStatus = status;
                    checkbox.checked = status;
                    showStatusFeedback('Berhasil', 'success');
                } else {
                    throw new Error(data.message || 'Update failed');
                }
            })
            .catch(err => {
                console.error('Error:', err);
                checkbox.checked = currentStatus;
                showStatusFeedback('Gagal', 'danger');
            })
            .finally(() => {
                isUpdating = false;
            });
        }

        // ✅ Ambil status awal
        fetch('../application/auto_update_status.json?' + new Date().getTime(), {
            cache: 'no-store'
        })
        .then(res => {
            if (!res.ok) throw new Error('Gagal mengambil status');
            return res.json();
        })
        .then(data => {
            console.log('Initial status:', data);
            const isActive = data.auto_update === true || data.auto_update === 1 ||
                            data.auto_update === "1" || data.auto_update === "true";
            currentStatus = isActive;
            checkbox.checked = isActive;
            autoUpdateText.classList.remove('d-none');
            updateSuccessBadge.classList.add('d-none');
        })
        .catch(err => {
            console.error('Gagal memuat status awal:', err);
            currentStatus = false;
            checkbox.checked = false;
            autoUpdateText.classList.remove('d-none');
            updateSuccessBadge.classList.add('d-none');
        });

        checkbox.addEventListener('change', function(e) {
            if (isUpdating) {
                e.preventDefault();
                checkbox.checked = currentStatus;
                return;
            }
            updateServerStatus(this.checked);
        });

        checkbox.addEventListener('click', function(e) {
            if (isUpdating) {
                e.preventDefault();
                return false;
            }
        });
    });
</script>
