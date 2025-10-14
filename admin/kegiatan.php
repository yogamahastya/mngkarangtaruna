<?php
// =================== LOGIKA ===================
if (!function_exists('getKegiatanStatusBadge')) {
    function getKegiatanStatusBadge($status) {
        $statusText = 'Aktif';
        $statusClass = 'badge-soft-success'; 
        if (isset($status)) {
            $statusLower = strtolower($status);
            if ($statusLower == 'selesai' || $statusLower == 'nonaktif') {
                $statusClass = 'bg-secondary text-white';
                $statusText = 'Selesai';
            } elseif ($statusLower == 'tertunda' || $statusLower == 'pending') {
                $statusClass = 'bg-warning text-dark';
                $statusText = 'Tertunda';
            }
        }
        return ['class' => $statusClass, 'text' => $statusText];
    }
}

$currentTab = $_GET['tab'] ?? 'kegiatan';
$action     = $_GET['action'] ?? '';
$searchTerm = $_GET['search'] ?? '';
$page       = $_GET['page'] ?? 1;
$limit      = 6;
$offset     = ($page - 1) * $limit;

$kegiatan = [];
$kegiatanDetail = null;
$total_kegiatan = 0;
$total_pages = 1;

// === QUERY LIST DATA ===
$where = "";
if (!empty($searchTerm)) {
    $searchTermDB = mysqli_real_escape_string($conn, $searchTerm);
    $where = "WHERE nama_kegiatan LIKE '%$searchTermDB%' OR lokasi LIKE '%$searchTermDB%' OR deskripsi LIKE '%$searchTermDB%'";
}

$sqlCount = "SELECT COUNT(*) as total FROM kegiatan $where";
$resCount = mysqli_query($conn, $sqlCount);
if ($resCount && $rowCount = mysqli_fetch_assoc($resCount)) {
    $total_kegiatan = $rowCount['total'];
}

$total_pages = ceil($total_kegiatan / $limit);

$sqlList = "SELECT * FROM kegiatan $where ORDER BY tanggal_mulai DESC LIMIT $limit OFFSET $offset";
$resList = mysqli_query($conn, $sqlList);
if ($resList) {
    while ($row = mysqli_fetch_assoc($resList)) {
        $kegiatan[] = $row;
    }
}

// === QUERY DETAIL ===
if ($action === 'detail' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sqlDetail = "SELECT * FROM kegiatan WHERE id = $id";
    $resDetail = mysqli_query($conn, $sqlDetail);
    if ($resDetail && $rowDetail = mysqli_fetch_assoc($resDetail)) {
        $kegiatanDetail = $rowDetail;
    }
}
?>

<style>
    body { background-color: #f0f2f5; }
    .card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: none;
        border-radius: 1rem;
        height: 100%;
    }
    .card:hover {
        transform: translateY(-6px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.15) !important;
    }
    .shadow-lg-custom { box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important; }

    .bg-gradient-primary {
        background: linear-gradient(135deg, #0d6efd 0%, #3a8ef6 100%);
    }
    .card-header {
        border: none;
    }
    .card-footer {
        background-color: #f8f9fa !important;
    }

    .text-truncate-multiline-3 {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .badge-soft-success { 
        color: var(--bs-success-text); 
        background-color: var(--bs-success-bg-subtle); 
    }
    .notulen-box {
        background-color: #f7f9fc; 
        border-left: 5px solid var(--bs-info);
        border-radius: 0.5rem;
    }
</style>

<div class="">

<?php if ($action === 'detail' && isset($_GET['id'])): ?>

    <?php if (!empty($kegiatanDetail)):
        $badge = getKegiatanStatusBadge($kegiatanDetail['status'] ?? 'Aktif');
    ?>
    <!-- Tampilan DETAIL -->
    <a href="?tab=kegiatan" class="btn btn-outline-primary mb-4 rounded-pill fw-medium shadow-sm">
        <i class="fa-solid fa-arrow-left me-2"></i> Kembali ke Daftar
    </a>

    <div class="row g-4">
        <div class="col-lg-5 col-md-12">
            <div class="card shadow-lg-custom h-100 rounded-4 border-start-5 border-primary">
                <div class="card-header bg-primary text-white border-0 pt-3 pb-3 rounded-top-4">
                    <h5 class="card-title fw-semibold mb-0"><i class="fa-solid fa-info-circle me-2"></i>Ringkasan</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom border-dashed">
                        <span class="text-muted fw-medium fs-6">Status</span>
                        <span class="badge <?= $badge['class'] ?> fs-6 fw-semibold p-2 rounded-pill shadow-sm"><?= $badge['text'] ?></span>
                    </div>
                    <div class="detail-info">
                        <p><i class="fa-solid fa-tag fa-fw me-3 text-primary"></i><span class="fw-medium">Nama:</span> <?= htmlspecialchars($kegiatanDetail['nama_kegiatan'] ?? 'N/A') ?></p>
                        <p><i class="fa-solid fa-calendar-day fa-fw me-3 text-primary"></i><span class="fw-medium">Tanggal:</span> <?= htmlspecialchars($kegiatanDetail['tanggal_mulai'] ?? 'N/A') ?></p>
                        <p><i class="fa-solid fa-map-marker-alt fa-fw me-3 text-primary"></i><span class="fw-medium">Lokasi:</span> <?= htmlspecialchars($kegiatanDetail['lokasi'] ?? 'N/A') ?></p>
                        <p><i class="fa-solid fa-user-edit fa-fw me-3 text-primary"></i><span class="fw-medium">Dibuat Oleh:</span> <?= htmlspecialchars($kegiatanDetail['dibuat_oleh'] ?? 'N/A') ?></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-7 col-md-12">
            <div class="card shadow-lg-custom h-100 rounded-4 border-start-5 border-info">
                <div class="card-header bg-info text-white border-0 pt-3 pb-3 rounded-top-4">
                    <h5 class="card-title fw-semibold mb-0"><i class="fa-solid fa-book-open me-2"></i>Deskripsi & Notulen</h5>
                </div>
                <div class="card-body">
                    <h6 class="fw-semibold text-dark mb-2 border-bottom pb-1"><i class="fa-solid fa-circle-info me-2 text-info"></i> Deskripsi</h6>
                    <div class="alert notulen-box p-3 mb-4 text-dark border-info">
                        <pre style="white-space: pre-wrap;"><?= htmlspecialchars($kegiatanDetail['deskripsi'] ?? '-') ?></pre>
                    </div>
                    <h6 class="fw-semibold text-dark mb-2 border-bottom pb-1"><i class="fa-solid fa-file-alt me-2 text-info"></i> Notulen</h6>
                    <div class="alert notulen-box p-3 mb-0 text-dark border-info">
                        <pre style="white-space: pre-wrap;"><?= nl2br(htmlspecialchars($kegiatanDetail['notulen'] ?? '-')) ?></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <br>
    <?php else: ?>
        <div class="alert alert-danger text-center rounded-4 shadow-lg-custom py-5">
            <h4 class="alert-heading display-5"><i class="fa-solid fa-triangle-exclamation me-3"></i>Data Tidak Ditemukan!</h4>
            <a href="?tab=kegiatan" class="btn btn-danger mt-3 px-4 py-2 fw-semibold rounded-pill"><i class="fa-solid fa-arrow-left me-2"></i>Kembali</a>
        </div>
    <?php endif; ?>

<?php else: ?>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
    <!-- Tampilan LIST -->
<div class="card shadow-lg border-0 border-start border-5 border-primary mb-4 rounded-4">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h5 class="mb-0 text-dark d-flex align-items-center">
                    <i class="bi bi-calendar-event fs-5 me-2 flex-shrink-0 text-info" style="font-size: 1.25rem;"></i>
                    <span>Kelola Data Kegiatan</span>
                </h5>                   
            </div>
        </div>
    </div>
</div>
    <!-- Card Pencarian dengan Tombol Tambah -->
   <div class="card shadow-lg border-0 border-start border-5 border-primary mb-4 rounded-4">
    <div class="card-body py-3">
        <div class="row g-3 align-items-center">

            <!-- Form Pencarian -->
            <div class="col-12 col-md order-md-1">
                <form action="" method="GET" class="mb-0">
                    <input type="hidden" name="tab" value="kegiatan">
                    
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
                                    placeholder="Cari kegiatan..." 
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
                                <a href="?tab=kegiatan" 
                                   class="btn btn-outline-danger rounded-pill d-flex align-items-center justify-content-center p-0"
                                   style="width: 42px; height: 42px;"
                                   title="Hapus Pencarian">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                        <!-- Tombol Tambah Transaksi -->
                        <div class="col-12 col-sm-auto">
                            <button type="button" 
                                    class="btn btn-primary rounded-pill px-4 py-2 shadow-sm w-100 w-sm-auto" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#addKegiatanModal">
                                <i class="fa-solid fa-plus-circle me-2"></i> 
                                <span class="d-none d-lg-inline">Tambah Kegiatan</span>
                                <span class="d-inline d-lg-none">Tambah</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

    <!-- Daftar Kegiatan -->
    <div class="mt-4">
        <?php if (count($kegiatan) > 0): ?>
            <div class="row g-4">
                <?php foreach ($kegiatan as $row): ?>
                    <?php $badge_info = getKegiatanStatusBadge($row['status'] ?? 'Aktif'); ?>
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="card h-100 shadow-lg-custom border-0 rounded-4 overflow-hidden">
                            <!-- Header -->
                            <div class="card-header bg-gradient-primary text-white py-3 d-flex justify-content-between align-items-center">
                                <span class="fw-semibold text-truncate me-2">
                                    <i class="fa-solid fa-calendar-check me-2"></i>
                                    <?= htmlspecialchars($row['nama_kegiatan'] ?? 'Kegiatan Tanpa Nama') ?>
                                </span>
                                
                                <!-- Dropdown Menu di Header -->
                                <div class="dropdown">
                                    <a class="text-white dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" style="text-decoration: none; cursor: pointer;">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a class="dropdown-item edit-btn" href="#"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editKegiatanModal"
                                            data-id="<?= $row['id'] ?>"
                                            data-nama="<?= htmlspecialchars($row['nama_kegiatan']) ?>"
                                            data-deskripsi="<?= htmlspecialchars($row['deskripsi']) ?>"
                                            data-lokasi="<?= htmlspecialchars($row['lokasi']) ?>"
                                            data-notulen="<?= htmlspecialchars($row['notulen']) ?>"
                                            data-tanggal="<?= date('Y-m-d', strtotime($row['tanggal_mulai'])) ?>">
                                            <i class="bx bx-edit me-1"></i> Edit
                                        </a>
                                        <form action="" method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="tab" value="kegiatan">
                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                            <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                                <i class="bx bx-trash me-1"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Body -->
                            <div class="card-body small text-muted">
                                <p class="mb-3">
                                    <i class="fa-solid fa-map-marker-alt me-2 text-danger"></i>
                                    <?= htmlspecialchars($row['lokasi'] ?? '-') ?>
                                </p>
                                <p class="mb-3 text-truncate-multiline-3">
                                    <i class="fa-solid fa-circle-info me-2 text-info"></i>
                                    <?= htmlspecialchars($row['deskripsi'] ?? '-') ?>
                                </p>
                                <p class="mb-2 fw-semibold text-dark">
                                    <i class="fa-solid fa-calendar-day me-2 text-primary"></i>
                                    <?= htmlspecialchars($row['tanggal_mulai'] ?? '-') ?>
                                </p>
                                <div class="mt-3">
                                    <span class="badge <?= $badge_info['class'] ?> px-3 py-2 fw-semibold rounded-pill">
                                        <?= $badge_info['text'] ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="card-footer bg-light border-0">
                                <a href="?tab=kegiatan&action=detail&id=<?= htmlspecialchars($row['id'] ?? '') ?>"
                                   class="btn btn-outline-primary w-100 rounded-pill fw-medium">
                                    <i class="fa-solid fa-eye me-1"></i> Lihat Rincian
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center alert alert-info py-5 rounded-4 shadow-lg-custom mt-4">
                <i class="fas fa-box-open fa-3x mb-3 text-info"></i>
                <h4 class="fw-semibold">Tidak Ada Kegiatan</h4>
                <?php if (!empty($searchTerm)): ?>
                    <a href="?tab=kegiatan" class="btn btn-outline-info mt-3 rounded-pill"><i class="fas fa-sync-alt me-2"></i>Tampilkan Semua</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation example" class="mt-5">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= ($page == $i) ? 'active shadow-sm' : '' ?>">
                        <a class="page-link <?= ($page == $i) ? 'fw-semibold' : '' ?>" href="?tab=kegiatan&page=<?= $i ?><?= !empty($searchTerm) ? '&search=' . htmlspecialchars($searchTerm) : '' ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>

<?php endif; ?>

</div>