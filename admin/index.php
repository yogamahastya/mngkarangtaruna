<?php
require_once 'process_data.php';

// Logika tab & tahun aktif
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'anggota';
$selectedYear = isset($_GET['year']) ? $_GET['year'] : date('Y');

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - <?= htmlspecialchars(ORGANIZATION_NAME) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="p-3">

<!-- HEADER -->
<header class="d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <div class="logo-icon">AD</div>
        <div class="header-title">
            <h1>Admin Dashboard - <?= htmlspecialchars(ORGANIZATION_NAME) ?></h1>
            <p class="d-none d-sm-block">Kelola data organisasi Anda dengan mudah</p>
        </div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <div class="admin-badge d-none d-md-flex">
            <i class="fa-solid fa-user-shield"></i>
            <span><strong>Admin Mode</strong></span>
        </div>
        <button class="menu-toggle d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu">
            <i class="fa-solid fa-bars"></i>
        </button>
    </div>
</header>

<!-- NOTIFIKASI UPDATE -->
<?php if ($isUpdateAvailable): ?>
    <div class="alert alert-warning d-flex align-items-center mb-4 rounded-4" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <div>
            Versi terbaru tersedia! (<?= htmlspecialchars($remoteVersion) ?>)  
            <button id="update-button" class="btn btn-primary btn-sm ms-2">Perbarui</button>
        </div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- NOTIFIKASI MESSAGE -->
<?php if (isset($message)): ?>
<div class="alert alert-<?= $success ? 'success' : 'danger' ?> alert-dismissible fade show rounded-4" role="alert">
    <?= htmlspecialchars($message) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<div class="row g-4">
    <!-- SIDEBAR DESKTOP -->
    <aside class="col-lg-3 sidebar-desktop">
        <div class="bg-white p-4 rounded-4 shadow-sm">
            <nav>
                <ul class="nav flex-column gap-2 nav-pills-custom">
                    <li><a class="nav-link <?= ($active_tab == 'anggota') ? 'active' : '' ?>" href="?tab=anggota&year=<?= $selectedYear ?>"><i class="fa-solid fa-users me-2"></i> Anggota</a></li>
                    <li><a class="nav-link <?= ($active_tab == 'kegiatan') ? 'active' : '' ?>" href="?tab=kegiatan&year=<?= $selectedYear ?>"><i class="fa-solid fa-calendar-alt me-2"></i> Kegiatan</a></li>
                    <li><a class="nav-link <?= ($active_tab == 'keuangan') ? 'active' : '' ?>" href="?tab=keuangan&year=<?= $selectedYear ?>"><i class="fa-solid fa-wallet me-2"></i> Keuangan</a></li>
                    <li><a class="nav-link <?= ($active_tab == 'iuran' || $active_tab == 'iuran17') ? 'active' : '' ?>" href="#" data-bs-toggle="modal" data-bs-target="#iuranModal"><i class="fa-solid fa-receipt me-2"></i> Iuran</a></li>
                    <li><a class="nav-link <?= ($active_tab == 'users') ? 'active' : '' ?>" href="?tab=users<?= !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : '' ?>"><i class="fa-solid fa-user-circle me-2"></i> Users & Lokasi</a></li>
                    <div class="logout-section">
                        <a href="../logout.php" class="btn-logout" onclick="return confirm('Apakah Anda yakin ingin keluar?')">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            <span>Keluar</span>
                        </a>
                    </div>
                </ul>
            </nav>
            <div class="mt-4 pt-4 border-top">
                <div class="stat-card text-center">
                    <h3><?= $total_anggota ?></h3>
                    <p>Total Anggota Aktif <?= date('Y') ?></p>
                </div>
            </div>
        </div>
    </aside>

    <!-- KONTEN -->
    <section class="col-lg-9">
        <div class="content-card">
            <?php
            // Define the tab file mapping
            $tab_files = [
                'anggota' => 'anggota.php',
                'kegiatan' => 'kegiatan.php',
                'keuangan' => 'keuangan.php',
                'iuran' => 'iuran.php',
                'iuran17' => 'iuran17.php',
                'users' => 'userlokasi.php'
            ];

            // Determine which file to include
            $include_file = isset($tab_files[$active_tab]) ? $tab_files[$active_tab] : $tab_files['anggota'];

            // Include the corresponding tab file
            if (file_exists($include_file)) {
                include $include_file;
            } else {
                echo "<p>Error: Content file not found.</p>";
            }
            ?>
             
        </div>
        <?php require_once '../footer.php'; ?>
    </section>
    
</div>

<!-- OFFCANVAS MENU MOBILE -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarMenu">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title fw-bold"><i class="fa-solid fa-bars me-2"></i>Menu Admin</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <ul class="nav flex-column gap-2 nav-pills-custom mb-4">
            <li><a class="nav-link <?= ($active_tab == 'anggota') ? 'active' : '' ?>" href="?tab=anggota&year=<?= $selectedYear ?>"><i class="fa-solid fa-users me-2"></i> Anggota</a></li>
            <li><a class="nav-link <?= ($active_tab == 'kegiatan') ? 'active' : '' ?>" href="?tab=kegiatan&year=<?= $selectedYear ?>"><i class="fa-solid fa-calendar-alt me-2"></i> Kegiatan</a></li>
            <li><a class="nav-link <?= ($active_tab == 'keuangan') ? 'active' : '' ?>" href="?tab=keuangan&year=<?= $selectedYear ?>"><i class="fa-solid fa-wallet me-2"></i> Keuangan</a></li>
            <li><a class="nav-link <?= ($active_tab == 'iuran' || $active_tab == 'iuran17') ? 'active' : '' ?>" href="#" data-bs-toggle="modal" data-bs-target="#iuranModal"><i class="fa-solid fa-receipt me-2"></i> Iuran</a></li>
            <li><a class="nav-link <?= ($active_tab == 'users') ? 'active' : '' ?>" href="?tab=users<?= !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : '' ?>"><i class="fa-solid fa-user-circle me-2"></i> Users & Lokasi</a></li>
            <div class="logout-section">
                <a href="../logout.php" class="btn-logout" onclick="return confirm('Apakah Anda yakin ingin keluar?')">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Keluar</span>
                </a>
            </div>
        </ul>
        <div class="stat-card text-center">
            <h3><?= $total_anggota ?></h3>
            <p>Total Anggota Aktif <?= date('Y') ?></p>
        </div>
        
    </div>
</div>

<!-- MODAL IURAN -->
<div class="modal fade" id="iuranModal" tabindex="-1" aria-labelledby="iuranModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pt-4 px-4 pb-0">
                <h4 class="modal-title fw-bolder text-dark" id="iuranModalLabel">Pilih Jenis Iuran Anda</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted mb-4">Silakan pilih kategori iuran yang ingin Anda kelola atau lihat.</p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <a href="?tab=iuran" class="card card-hover h-100 border-0 shadow-sm rounded-4 p-3 text-decoration-none">
                            <div class="card-body">
                                <div class="icon-circle bg-primary-subtle text-primary mb-3">
                                    <i class="fa-solid fa-coins fa-2x"></i>
                                </div>
                                <h6 class="fw-bold mb-1">Iuran Kas</h6>
                                <p class="small text-muted mb-0">Kelola iuran rutin kas bulanan.</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="?tab=iuran17" class="card card-hover h-100 border-0 shadow-sm rounded-4 p-3 text-decoration-none">
                            <div class="card-body">
                                <div class="icon-circle bg-danger-subtle text-danger mb-3">
                                    <i class="fa-solid fa-star fa-2x"></i>
                                </div>
                                <h6 class="fw-bold mb-1">Iuran 17-an</h6>
                                <p class="small text-muted mb-0">Lihat kontribusi acara 17 Agustus.</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addAnggotaModal" tabindex="-1" aria-labelledby="addAnggotaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAnggotaModalLabel">Tambah Anggota</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="tab" value="anggota">
                    <div class="mb-3">
                        <label for="add-nama-lengkap" class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" id="add-nama-lengkap" name="data[nama_lengkap]" required>
                    </div>
                    <div class="mb-3">
                        <label for="add-jabatan" class="form-label">Jabatan</label>
                        <select class="form-control" id="add-jabatan" name="data[jabatan]" required>
                            <option value="">-- Pilih Jabatan --</option>
                            <?php foreach (JABATAN_OPTIONS as $jabatan): ?>
                                <option value="<?= htmlspecialchars($jabatan) ?>">
                                    <?= htmlspecialchars($jabatan) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="add-no-hp" class="form-label">No hp</label>
                        <input type="text" class="form-control" id="add-no-hp" name="data[no_hp]" required>
                    </div>
                    <div class="mb-3">
                        <label for="add-bergabung-sejak" class="form-label">Bergabung Sejak</label>
                        <input type="date" class="form-control" id="add-bergabung-sejak" name="data[bergabung_sejak]" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editAnggotaModal" tabindex="-1" aria-labelledby="editAnggotaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAnggotaModalLabel">Edit Anggota</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="tab" value="anggota">
                    <input type="hidden" name="id" id="edit-anggota-id">

                    <div class="mb-3">
                        <label for="edit-nama-lengkap" class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" id="edit-nama-lengkap" name="data[nama_lengkap]" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit-jabatan" class="form-label">Jabatan</label>
                        <select class="form-control" id="edit-jabatan" name="data[jabatan]" required>
                            <option value="">-- Pilih Jabatan --</option>
                            <?php foreach (JABATAN_OPTIONS as $jabatan): ?>
                                <option value="<?= htmlspecialchars($jabatan) ?>"><?= htmlspecialchars($jabatan) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="edit-nohp" class="form-label">No HP</label>
                        <input type="text" class="form-control" id="edit-nohp" name="data[no_hp]">
                    </div>

                    <div class="mb-3">
                        <label for="edit-bergabung-sejak" class="form-label">Bergabung Sejak</label>
                        <input type="date" class="form-control" id="edit-bergabung-sejak" name="data[bergabung_sejak]" required>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addKegiatanModal" tabindex="-1" aria-labelledby="addKegiatanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addKegiatanModalLabel">Tambah Kegiatan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="tab" value="kegiatan">
                    <div class="mb-3">
                        <label for="add-nama-kegiatan" class="form-label">Nama Kegiatan</label>
                        <input type="text" class="form-control" id="add-nama-kegiatan" name="data[nama_kegiatan]" required>
                    </div>
                    <div class="mb-3">
                        <label for="add-lokasi" class="form-label">Lokasi</label>
                        <input type="text" class="form-control" id="add-lokasi" name="data[lokasi]">
                    </div>
                    <div class="mb-3">
                        <label for="add-deskripsi" class="form-label">Deskripsi</label>
                        <input type="text" class="form-control" id="add-deskripsi" name="data[deskripsi]" required>
                    </div>
                    <div class="mb-3">
                        <label for="add-notulen" class="form-label">Notulen</label>
                        <textarea class="form-control" id="add-notulen" name="data[notulen]" rows="3"></textarea>
                    </div>                  
                    <div class="mb-3">
                        <label for="add-tanggal-mulai" class="form-label">Tanggal Mulai</label>
                        <input type="date" class="form-control" id="add-tanggal-mulai" name="data[tanggal_mulai]" value="<?= date('Y-m-d'); ?>" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editKegiatanModal" tabindex="-1" aria-labelledby="editKegiatanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editKegiatanModalLabel">Edit Kegiatan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="tab" value="kegiatan">
                    <input type="hidden" name="id" id="edit-kegiatan-id">

                    <div class="mb-3">
                        <label for="edit-nama-kegiatan" class="form-label">Nama Kegiatan</label>
                        <input type="text" class="form-control" id="edit-nama-kegiatan" name="data[nama_kegiatan]" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit-lokasi" class="form-label">Lokasi</label>
                        <input type="text" class="form-control" id="edit-lokasi" name="data[lokasi]">
                    </div>

                    <div class="mb-3">
                        <label for="edit-deskripsi" class="form-label">Deskripsi</label>
                        <input type="text" class="form-control" id="edit-deskripsi" name="data[deskripsi]" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit-notulen" class="form-label">Notulen</label>
                        <textarea class="form-control" id="edit-notulen" name="data[notulen]" rows="3"></textarea>
                    </div> 

                    <div class="mb-3">
                        <label for="edit-tanggal-mulai" class="form-label">Tanggal Mulai</label>
                        <input type="date" class="form-control" id="edit-tanggal-mulai" name="data[tanggal_mulai]" required>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addKeuanganModal" tabindex="-1" aria-labelledby="addKeuanganModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addKeuanganModalLabel">Tambah Transaksi Keuangan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="tab" value="keuangan">
                    <div class="mb-3">
                        <label for="add-jenis-transaksi" class="form-label">Jenis Transaksi</label>
                        <select class="form-select" id="add-jenis-transaksi" name="data[jenis_transaksi]" required>
                            <option value="pemasukan">Pemasukan</option>
                            <option value="pengeluaran">Pengeluaran</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="add-jumlah-keuangan" class="form-label">Jumlah</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" id="add-jumlah-keuangan" name="data[jumlah]" required min="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="add-deskripsi-keuangan" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="add-deskripsi-keuangan" name="data[deskripsi]" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="add-tanggal-transaksi" class="form-label">Tanggal Transaksi</label>
                        <input type="date" class="form-control" id="add-tanggal-transaksi" name="data[tanggal_transaksi]" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editKeuanganModal" tabindex="-1" aria-labelledby="editKeuanganModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editKeuanganModalLabel">Edit Transaksi Keuangan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="tab" value="keuangan">
                    <input type="hidden" name="id" id="edit-keuangan-id">
                    <div class="mb-3">
                        <label for="edit-jenis-transaksi" class="form-label">Jenis Transaksi</label>
                        <select class="form-select" id="edit-jenis-transaksi" name="data[jenis_transaksi]" required>
                            <option value="pemasukan">Pemasukan</option>
                            <option value="pengeluaran">Pengeluaran</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit-jumlah-keuangan" class="form-label">Jumlah</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" id="edit-jumlah-keuangan" name="data[jumlah]" required min="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit-deskripsi-keuangan" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="edit-deskripsi-keuangan" name="data[deskripsi]" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit-tanggal-transaksi" class="form-label">Tanggal Transaksi</label>
                        <input type="date" class="form-control" id="edit-tanggal-transaksi" name="data[tanggal_transaksi]" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addIuranModal" tabindex="-1" aria-labelledby="addIuranModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addIuranModalLabel">Tambah Pembayaran Iuran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST" id="addIuranForm">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="tab" value="iuran">

                    <div class="mb-3">
                        <label for="search-anggota-iuran" class="form-label">Nama Anggota</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="search-anggota-iuran" placeholder="Cari anggota...">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <div id="search-results-iuran" class="list-group mt-2" style="max-height: 200px; overflow-y: auto;">
                            </div>
                        <input type="hidden" name="data[anggota_id]" id="add-anggota-id-iuran" required>
                    </div>
                    <div class="mb-3">
                        <label for="add-tanggal-bayar" class="form-label">Tanggal Bayar</label>
                        <input type="date" class="form-control" id="add-tanggal-bayar" name="data[tanggal_bayar]" required>
                    </div>
                    <div class="mb-3">
                        <label for="add-jumlah-iuran" class="form-label">Jumlah Bayar</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" id="add-jumlah-iuran" name="data[jumlah_bayar]" required min="0" placeholder="Isi jika kurang <?= number_format(DUES_MONTHLY_FEE, 0, ',', '.') ?>">
                            <button class="btn btn-outline-secondary" type="button" id="autoFillBtn">Isi Otomatis</button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="add-keterangan" class="form-label">Keterangan</label>
                        <textarea class="form-control" id="add-keterangan" name="data[keterangan]" rows="2" placeholder="Tidak peralu di isi"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editIuranModal" tabindex="-1" aria-labelledby="editIuranModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editIuranModalLabel">Edit Pembayaran Iuran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="tab" value="iuran">
                    <input type="hidden" name="id" id="edit-iuran-id">
                    <div class="mb-3">
                        <label for="edit-anggota-id" class="form-label">Nama Anggota</label>
                        <select class="form-select" id="edit-anggota-id" name="data[anggota_id]" required>
                            <option value="">Pilih Anggota</option>
                            <?php foreach ($anggotaList as $anggota): ?>
                                <option value="<?= $anggota['id'] ?>"><?= htmlspecialchars($anggota['nama_lengkap']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit-tanggal-bayar" class="form-label">Tanggal Bayar</label>
                        <input type="date" class="form-control" id="edit-tanggal-bayar" name="data[tanggal_bayar]" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-jumlah-iuran" class="form-label">Jumlah Bayar</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" id="edit-jumlah-iuran" name="data[jumlah_bayar]" required min="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit-keterangan" class="form-label">Keterangan</label>
                        <textarea class="form-control" id="edit-keterangan" name="data[keterangan]" rows="2"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addIuran17Modal" tabindex="-1" aria-labelledby="addIuran17ModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addIuran17ModalLabel">Tambah Pembayaran Iuran 17</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST" id="addIuran17Form">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="tab" value="iuran17">

                    <div class="mb-3">
                        <label for="search-anggota-iuran17" class="form-label">Nama Anggota</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="search-anggota-iuran17" placeholder="Cari anggota...">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <div id="search-results-iuran17" class="list-group mt-2" style="max-height: 200px; overflow-y: auto;">
                        </div>
                        <input type="hidden" name="data[anggota_id]" id="add-anggota-id-iuran17" required>
                    </div>
                    <div class="mb-3">
                        <label for="add-tanggal-bayar17" class="form-label">Tanggal Bayar</label>
                        <input type="date" class="form-control" id="add-tanggal-bayar17" name="data[tanggal_bayar]" required>
                    </div>
                    <div class="mb-3">
                        <label for="add-jumlah-iuran17" class="form-label">Jumlah Bayar</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" id="add-jumlah-iuran17" name="data[jumlah_bayar]" required min="0" placeholder="Isi jika kurang dari <?= number_format(DUES_MONTHLY_FEE17, 0, ',', '.') ?>">
                            <button class="btn btn-outline-secondary" type="button" id="autoFillBtn17">Isi Otomatis</button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="add-keterangan17" class="form-label">Keterangan</label>
                        <textarea class="form-control" id="add-keterangan17" name="data[keterangan]" rows="2" placeholder="Isi jika perlu"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editIuran17Modal" tabindex="-1" aria-labelledby="editIuran17ModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editIuran17ModalLabel">Edit Pembayaran Iuran 17</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="tab" value="iuran17">
                    <input type="hidden" name="id" id="edit-iuran17-id">
                    <div class="mb-3">
                        <label for="edit-anggota-id17" class="form-label">Nama Anggota</label>
                        <select class="form-select" id="edit-anggota-id17" name="data[anggota_id]" required>
                            <option value="">Pilih Anggota</option>
                            <?php foreach ($anggotaList as $anggota): ?>
                                <option value="<?= $anggota['id'] ?>"><?= htmlspecialchars($anggota['nama_lengkap']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit-tanggal-bayar17" class="form-label">Tanggal Bayar</label>
                        <input type="date" class="form-control" id="edit-tanggal-bayar17" name="data[tanggal_bayar]" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-jumlah-iuran17" class="form-label">Jumlah Bayar</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" id="edit-jumlah-iuran17" name="data[jumlah_bayar]" required min="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit-keterangan17" class="form-label">Keterangan</label>
                        <textarea class="form-control" id="edit-keterangan17" name="data[keterangan]" rows="2"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addUsersModal" tabindex="-1" aria-labelledby="addUsersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUsersModalLabel">Tambah User Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="tab" value="users">
                    <div class="mb-3">
                        <label for="add-username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="add-username" name="data[username]" required>
                    </div>
                    <div class="mb-3">
                        <label for="add-password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="add-password" name="data[password]" required>
                    </div>
                    <div class="mb-3">
                        <label for="add-role" class="form-label">Role</label>
                        <select class="form-select" id="add-role" name="data[role]" required>                          
                            <option value="sekretaris">Sekretaris</option>
                            <option value="bendahara">Bendahara</option>
                            <option value="admin">Admin</option>
                            <option value="superadmin">Superadmin</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="search-anggota-user" class="form-label">Anggota Terkait<span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="search-anggota-user" placeholder="Cari anggota sesuai dengan jabatan...">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <div id="search-results-user" class="list-group mt-2" style="max-height: 200px; overflow-y: auto;">
                        </div>
                        <input type="hidden" name="data[anggota_id]" id="add-anggota-id-user" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editUsersModal" tabindex="-1" aria-labelledby="editUsersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUsersModalLabel">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="tab" value="users">
                    <input type="hidden" name="id" id="edit-users-id">
                    <div class="mb-3">
                        <label for="edit-username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="edit-username" name="data[username]" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-password" class="form-label">Password (kosongkan jika tidak diubah)</label>
                        <input type="password" class="form-control" id="edit-password" name="data[password]">
                    </div>
                    <div class="mb-3">
                        <label for="edit-role" class="form-label">Role</label>
                        <select class="form-select" id="edit-role" name="data[role]" required>
                            <option value="sekretaris">Sekretaris</option>
                            <option value="bendahara">Bendahara</option>
                            <option value="admin">Admin</option>
                            <option value="superadmin">Superadmin</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit-search-anggota-user" class="form-label">Anggota Terkait <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="edit-search-anggota-user" placeholder="Cari anggota sesuai dengan jabatan...">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <div id="edit-search-results-user" class="list-group mt-2" style="max-height: 200px; overflow-y: auto;">
                        </div>
                        <input type="hidden" name="data[anggota_id]" id="edit-anggota-id-user" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addLokasiModal" tabindex="-1" aria-labelledby="addLokasiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addLokasiModalLabel"><i class="fa-solid fa-map-marker-alt me-2"></i>Atur Lokasi Absensi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_location">
                    
                    <div class="mb-3">
                        <button type="button" class="btn btn-info" id="detect-device-location-btn">
                            <i class="fas fa-crosshairs me-2"></i>Gunakan Lokasi Perangkat Sekarang
                        </button>
                    </div>

                    <div id="map-container" style="height: 250px; width: 100%; margin-bottom: 15px;">
                        <iframe id="gmaps-iframe" width="100%" height="100%" frameborder="0" style="border:0"
                            src="" allowfullscreen>
                        </iframe>
                    </div>

                    <div class="mb-3">
                        <label for="lokasi_latitude" class="form-label">Latitude</label>
                        <input type="text" class="form-control" id="lokasi_latitude" name="lokasi_latitude" required value="<?= htmlspecialchars($current_latitude) ?>" readonly>
                        <div class="form-text">Latitude akan otomatis terisi setelah deteksi lokasi perangkat.</div>
                    </div>
                    <div class="mb-3">
                        <label for="lokasi_longitude" class="form-label">Longitude</label>
                        <input type="text" class="form-control" id="lokasi_longitude" name="lokasi_longitude" required value="<?= htmlspecialchars($current_longitude) ?>" readonly>
                        <div class="form-text">Longitude akan otomatis terisi setelah deteksi lokasi perangkat.</div>
                    </div>
                    <div class="mb-3">
                        <label for="jarak_toleransi" class="form-label">Jarak Toleransi (meter)</label>
                        <input type="number" class="form-control" id="jarak_toleransi" name="jarak_toleransi" required value="<?= htmlspecialchars($current_tolerance) ?>">
                        <div class="form-text">Jarak maksimal dari lokasi yang diizinkan untuk absensi.</div>
                    </div>
                    <div class="mb-3">
                        <label for="durasi_absensi" class="form-label">Durasi Absensi (menit)</label>
                        <input type="number" class="form-control" id="lokasi_durasi" name="lokasi_durasi" required value="<?= htmlspecialchars($current_duration) ?>">
                        <div class="form-text">Contoh: 60 menit = 1 jam.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Lokasi</button>
                </div>
            </form>
        </div>
    </div>
</div>  

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/admin.js"></script>
<script src="../assets/js/maps.js"></script> 
<script>
    document.addEventListener('DOMContentLoaded', function() {
    // Ambil daftar anggota dari PHP dan konversi ke JavaScript
    const anggotaList = <?= json_encode($anggotaList) ?>;

    const searchInput = document.getElementById('search-anggota-iuran');
    const searchResultsDiv = document.getElementById('search-results-iuran');
    const anggotaIdInput = document.getElementById('add-anggota-id-iuran');
    const form = document.getElementById('addIuranForm');

    // Fungsi untuk menampilkan hasil pencarian
    const displayResults = (results) => {
        searchResultsDiv.innerHTML = ''; // Hapus hasil sebelumnya
        if (results.length > 0) {
            results.forEach(anggota => {
                const resultItem = document.createElement('a');
                resultItem.href = '#';
                resultItem.classList.add('list-group-item', 'list-group-item-action');
                resultItem.textContent = anggota.nama_lengkap;
                resultItem.setAttribute('data-id', anggota.id);
                searchResultsDiv.appendChild(resultItem);
            });
            searchResultsDiv.style.display = 'block';
        } else {
            searchResultsDiv.style.display = 'none';
        }
    };

    // Event listener saat pengguna mengetik
    searchInput.addEventListener('keyup', function() {
        const query = this.value.toLowerCase();
        if (query.length > 0) {
            const filteredResults = anggotaList.filter(anggota =>
                anggota.nama_lengkap.toLowerCase().includes(query)
            );
            displayResults(filteredResults);
        } else {
            searchResultsDiv.innerHTML = '';
            searchResultsDiv.style.display = 'none';
        }
    });

    // Event listener saat hasil pencarian diklik
    searchResultsDiv.addEventListener('click', function(e) {
        if (e.target.tagName === 'A') {
            e.preventDefault();
            const selectedId = e.target.getAttribute('data-id');
            const selectedNama = e.target.textContent;

            // Isi input pencarian dan input tersembunyi
            anggotaIdInput.value = selectedId;
            searchInput.value = selectedNama;

            // Sembunyikan hasil pencarian
            searchResultsDiv.innerHTML = '';
            searchResultsDiv.style.display = 'none';
        }
    });
    
    // Sembunyikan hasil pencarian jika klik di luar area input
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResultsDiv.contains(e.target)) {
            searchResultsDiv.style.display = 'none';
        }
    });
});

// --- Fungsionalitas untuk iuran17 ---
    document.addEventListener('DOMContentLoaded', function() {
        // Ambil daftar anggota dari PHP dan konversi ke JavaScript
        const anggotaList = <?= json_encode($anggotaList) ?>;

        const searchInput17 = document.getElementById('search-anggota-iuran17');
        const searchResultsDiv17 = document.getElementById('search-results-iuran17');
        const anggotaIdInput17 = document.getElementById('add-anggota-id-iuran17');
        const form17 = document.getElementById('addIuran17Form');

        // Fungsi untuk menampilkan hasil pencarian
        const displayResults17 = (results) => {
            searchResultsDiv17.innerHTML = ''; // Hapus hasil sebelumnya
            if (results.length > 0) {
                results.forEach(anggota => {
                    const resultItem = document.createElement('a');
                    resultItem.href = '#';
                    resultItem.classList.add('list-group-item', 'list-group-item-action');
                    resultItem.textContent = anggota.nama_lengkap;
                    resultItem.setAttribute('data-id', anggota.id);
                    searchResultsDiv17.appendChild(resultItem);
                });
                searchResultsDiv17.style.display = 'block';
            } else {
                searchResultsDiv17.style.display = 'none';
            }
        };
        
        // Event listener saat pengguna mengetik
        searchInput17.addEventListener('keyup', function() {
            const query = this.value.toLowerCase();
            if (query.length > 0) {
                const filteredResults = anggotaList.filter(anggota =>
                    anggota.nama_lengkap.toLowerCase().includes(query)
                );
                displayResults17(filteredResults);
            } else {
                searchResultsDiv17.innerHTML = '';
                searchResultsDiv17.style.display = 'none';
            }
        });

        // Event listener saat hasil pencarian diklik
        searchResultsDiv17.addEventListener('click', function(e) {
            if (e.target.tagName === 'A') {
                e.preventDefault();
                const selectedId = e.target.getAttribute('data-id');
                const selectedNama = e.target.textContent;

                // Isi input pencarian dan input tersembunyi
                anggotaIdInput17.value = selectedId;
                searchInput17.value = selectedNama;

                // Sembunyikan hasil pencarian
                searchResultsDiv17.innerHTML = '';
                searchResultsDiv17.style.display = 'none';
            }
        });
        
        // Sembunyikan hasil pencarian jika klik di luar area input
        document.addEventListener('click', function(e) {
            if (!searchInput17.contains(e.target) && !searchResultsDiv17.contains(e.target)) {
                searchResultsDiv17.style.display = 'none';
            }
        });
    });
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Pastikan data anggota tersedia (misal dari PHP)
    const anggotaList = [
        <?php foreach ($anggotaList as $anggota): ?>
            { id: <?= $anggota['id'] ?>, nama: '<?= htmlspecialchars($anggota['nama_lengkap']) ?>' },
        <?php endforeach; ?>
    ];

    // --- Skrip untuk Modal TAMBAH User (dari perbaikan sebelumnya) ---
    const searchInputAdd = document.getElementById('search-anggota-user');
    const searchResultsAdd = document.getElementById('search-results-user');
    const selectedAnggotaIdAdd = document.getElementById('add-anggota-id-user');

    if (searchInputAdd) {
        searchInputAdd.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            searchResultsAdd.innerHTML = '';
            if (query.length > 0) {
                const filteredAnggota = anggotaList.filter(anggota =>
                    anggota.nama.toLowerCase().includes(query)
                );
                filteredAnggota.forEach(anggota => {
                    const item = document.createElement('a');
                    item.href = '#';
                    item.className = 'list-group-item list-group-item-action';
                    item.textContent = anggota.nama;
                    item.setAttribute('data-id', anggota.id);
                    item.setAttribute('data-nama', anggota.nama);
                    item.addEventListener('click', function(e) {
                        e.preventDefault();
                        searchInputAdd.value = this.getAttribute('data-nama');
                        selectedAnggotaIdAdd.value = this.getAttribute('data-id');
                        searchResultsAdd.innerHTML = '';
                    });
                    searchResultsAdd.appendChild(item);
                });
            }
        });
    }   
});
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const DUES_MONTHLY_FEE = <?php echo DUES_MONTHLY_FEE; ?>;
        
        const autoFillBtn = document.getElementById('autoFillBtn');
        const jumlahIuranInput = document.getElementById('add-jumlah-iuran');

        // Pastikan kedua elemen ditemukan sebelum menambahkan event listener
        if (autoFillBtn && jumlahIuranInput) {
            autoFillBtn.addEventListener('click', function() {
                jumlahIuranInput.value = DUES_MONTHLY_FEE;
            });
        } else {
            console.error("Elemen tombol atau input tidak ditemukan.");
        }
    });
    document.addEventListener('DOMContentLoaded', function() {
        const DUES_MONTHLY_FEE17 = <?php echo DUES_MONTHLY_FEE17; ?>;

        const autoFillBtn17 = document.getElementById('autoFillBtn17');
        const jumlahIuran17Input = document.getElementById('add-jumlah-iuran17');

        // Pastikan kedua elemen ditemukan sebelum menambahkan event listener
        if (autoFillBtn17 && jumlahIuran17Input) {
            autoFillBtn17.addEventListener('click', function() {
                jumlahIuran17Input.value = DUES_MONTHLY_FEE17;
            });
        } else {
            console.error("Elemen tombol atau input iuran17 tidak ditemukan.");
        }
    });
</script> 
<script> // Script deteksi scroll
let lastScrollTop = 0;
window.addEventListener("scroll", function() {
  let st = window.pageYOffset || document.documentElement.scrollTop;
  const nav = document.querySelector(".nav-pills-custom");

  if (st > lastScrollTop) {
    // scroll ke bawah → sembunyikan
    nav.classList.add("hide");
  } else {
    // scroll ke atas → tampilkan
    nav.classList.remove("hide");
  }
  lastScrollTop = st <= 0 ? 0 : st; // biar gak negatif
}, false);
</script>
</body>
</html>