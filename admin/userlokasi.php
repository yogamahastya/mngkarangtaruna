<h2 class="mb-4 text-primary"><i class="fa-solid fa-user-circle me-2"></i>Kelola Data Users</h2>
<div class="row mb-3 gy-2 align-items-center">
    <div class="col-12 col-md-6">
        <form action="" method="GET" class="d-flex w-100">
            <input type="hidden" name="tab" value="users">
            <div class="input-group">
                <input type="text" class="form-control" placeholder="Cari user..." name="search" value="<?= htmlspecialchars($searchTerm) ?>">
                <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search"></i></button>
                <?php if (!empty($searchTerm)): ?>
                    <a href="?tab=users" class="btn btn-outline-secondary" title="Hapus Pencarian"><i class="fas fa-times"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    <div class="col-12 col-md-6 text-md-end d-flex flex-column flex-md-row justify-content-md-end">
        <div class="form-check form-switch d-flex align-items-center me-md-4 mb-2 mb-md-0 justify-content-center justify-content-md-start">
            <input class="form-check-input" type="checkbox" id="autoUpdateCheckbox">
            <label class="form-check-label ms-2" for="autoUpdateCheckbox">
                Auto Update
                <span id="update-badge" class="badge rounded-pill bg-success ms-2 d-none">
                    Berhasil
                </span>
            </label>
        </div>

        <button type="button" class="btn btn-primary w-100 w-md-auto mb-2 mb-md-0 me-md-2" data-bs-toggle="modal" data-bs-target="#addUsersModal">
            <i class="fa-solid fa-plus-circle me-2"></i> Tambah User
        </button>
        <button type="button" class="btn btn-primary w-100 w-md-auto" data-bs-toggle="modal" data-bs-target="#addLokasiModal">
            <i class="fa-solid fa-map-marker-alt me-2"></i> Atur Lokasi Absensi
        </button>
    </div>
</div>
<div class="">
    <table class="table table-hover table-striped d-none d-md-table">
        <tbody>
            <div class="row">
                <?php if (count($users) > 0): ?>
                    <?php foreach ($users as $row): ?>
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
                        ?>
                        <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-md">
                                            <div class="avatar-title bg-soft-primary text-primary display-6 m-0 rounded-circle">
                                                <i class="bx bx-user"></i>
                                            </div>
                                        </div>
                                        <div class="flex-1 ms-3">
                                            <h5 class="font-size-16 mb-1"><a href="#" class="text-dark"><?= htmlspecialchars($row['username']) ?></a></h5>
                                            <span class="badge bg-info mb-0"><?= htmlspecialchars(ucfirst($row['role'])) ?></span>
                                        </div>
                                        <div class="ms-auto">
                                            <div class="dropdown">
                                                <a class="text-muted dropdown-toggle font-size-16" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true">
                                                    <i class="bx bx-dots-horizontal-rounded"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a class="dropdown-item edit-btn" href="#" data-bs-toggle="modal" data-bs-target="#editUsersModal" data-id="<?= $row['id'] ?>" data-username="<?= $row['username'] ?>" data-role="<?= $row['role'] ?>" data-anggota-id="<?= $row['anggota_id'] ?>">
                                                        <i class="bx bx-edit me-1"></i> Edit
                                                    </a>
                                                    <form action="" method="POST" class="d-inline">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="tab" value="users">
                                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                        <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                                            <i class="bx bx-trash me-1"></i> Hapus
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3 pt-1">
                                        <p class="text-muted mb-0 mt-2"><i class="mdi mdi-account-card-details font-size-15 align-middle pe-2 text-primary"></i> Anggota Terkait: <span class="float-end"><?= htmlspecialchars($anggotaName) ?></span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center text-muted">
                        <p>Tidak ada data user.</p>
                    </div>
                <?php endif; ?>
            </div>
        </tbody>
    </table>
</div>
<script> 
document.addEventListener('DOMContentLoaded', function() {
    const checkbox = document.getElementById('autoUpdateCheckbox');
    const updateBadge = document.getElementById('update-badge');

    // Fungsi untuk menampilkan badge
    function showBadge(message, type) {
        updateBadge.textContent = message;
        updateBadge.classList.remove('d-none', 'bg-success', 'bg-danger');
        updateBadge.classList.add(`bg-${type}`);
        
        // Sembunyikan badge setelah 3 detik
        setTimeout(() => {
            updateBadge.classList.add('d-none');
        }, 3000);
    }

    // Fungsi untuk mengirim status ke server
    function updateServerStatus(status) {
        // Path disesuaikan: "../application/update_settings.php"
        fetch('../application/update_settings.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ auto_update: status }),
        })
        .then(response => response.json())
        .then(data => {
            console.log('Server response:', data);
            if (data.status === 'success') {
                // Tampilkan badge sukses
                showBadge('Berhasil', 'success');
            } else {
                // Tampilkan badge gagal
                showBadge('Gagal', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Tampilkan badge error
            showBadge('Error', 'danger');
        });
    }

    // Ambil status dari server saat halaman dimuat
    fetch('../application/auto_update_status.json')
        .then(response => response.json())
        .then(data => {
            if (data && data.auto_update) {
                checkbox.checked = true;
            }
        })
        .catch(error => {
            console.error('Gagal memuat pengaturan awal:', error);
            checkbox.checked = false;
        });

    // Mendengarkan perubahan pada checkbox
    checkbox.addEventListener('change', function() {
        if (this.checked) {
            updateServerStatus(true);
        } else {
            updateServerStatus(false);
        }
    });
});
</script>