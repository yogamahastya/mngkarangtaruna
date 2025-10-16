<?php
$anggota = $anggota ?? []; // Fallback jika $anggota belum didefinisikan
$paginated_anggota = $paginated_anggota ?? $anggota; 
$searchTerm = $searchTerm ?? '';
$page = $page ?? 1;
$total_pages = $total_pages ?? 1;
$offset = $offset ?? 0;
// --- Akhir Bagian Logika PHP ---
?>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<div class="card shadow-lg border-0 border-start border-5 border-primary mb-4 rounded-4">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h5 class="mb-0 text-dark d-flex align-items-center">
                    <i class="bi bi-people fs-5 me-2 flex-shrink-0 text-warning" style="font-size: 1.25rem;"></i>
                    <span>Daftar Anggota</span>
                </h5>                   
            </div>
        </div>
    </div>
</div>
<div class="card shadow-lg border-0 border-start border-5 border-primary mb-4 rounded-4">
    <div class="card-body py-3">
        <!-- Form Pencarian Anggota -->
        <form action="" method="GET" class="mb-0">
            <input type="hidden" name="tab" value="anggota">
            
            <div class="row g-2 align-items-center">
                <!-- Search Input Group -->
                <div class="col">
                    <div class="input-group">
                        <!-- Ikon Prefix -->
                        <span class="input-group-text bg-light border-0 rounded-start-pill border-end-0">
                            <i class="fas fa-search text-primary"></i>
                        </span>
                        
                        <!-- Input Field -->
                        <input type="text" 
                            id="search-input" 
                            name="search" 
                            value="<?= htmlspecialchars($searchTerm) ?>" 
                            placeholder="Cari anggota..." 
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
                        <a href="?tab=anggota" 
                           class="btn btn-outline-danger rounded-pill d-flex align-items-center justify-content-center p-0"
                           style="width: 42px; height: 42px;"
                           title="Hapus Pencarian">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>


<div class="">
    <?php if (count($paginated_anggota) > 0): ?>
        <div class="row g-4">
            <?php foreach ($paginated_anggota as $index => $row): ?>
                <?php
                    // Logika badge class tetap dipertahankan dari skrip Anda, namun saya tambahkan kelas Bootstrap standard
                    $jabatan = htmlspecialchars($row['jabatan']);
                    $badge_class = 'badge rounded-pill ';
                    switch (strtolower($jabatan)) {
                        case 'ketua':
                            $badge_class .= 'bg-primary';
                            break;
                        case 'wakil ketua': 
                            $badge_class .= 'bg-info';
                            break;
                        case 'sekretaris': 
                            $badge_class .= 'bg-success';
                            break;
                        case 'bendahara': 
                            $badge_class .= 'bg-warning text-dark';
                            break;
                        case 'humas': 
                            $badge_class .= 'bg-secondary';
                            break;
                        case 'anggota': 
                            $badge_class .= 'bg-secondary';
                            break;
                        default:
                            $badge_class .= 'bg-light text-secondary';
                            break;
                    }

                    // Logika untuk Avatar Inisial
                    $nameParts = explode(' ', $row['nama_lengkap']);
                    $initials = '';
                    if (count($nameParts) >= 1) {
                        $initials .= strtoupper($nameParts[0][0]);
                    }
                    if (count($nameParts) >= 2) {
                        $initials .= strtoupper($nameParts[1][0]);
                    }
                    $avatarColor = sprintf("hsl(%d, 70%%, 50%%)", ($offset + $index) * 30 % 360);
                ?>
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="card shadow-lg h-100 rounded-4 border-0">
                        <div class="card-body">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0 avatar-lg me-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 3.5rem; height: 3.5rem; font-size: 1.25rem; font-weight: ; flex-shrink: 0; background-color: <?= $avatarColor ?>; color: white; font-weight: 600;">
                                        <?= $initials ?>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">
                                        <a href="#" class="text-dark fw- text-decoration-none" style="font-size: 1rem;"><?= htmlspecialchars($row['nama_lengkap']) ?></a>
                                    </h6>
                                    <span class="<?= $badge_class ?> mb-0"><?= $jabatan ?></span>
                                </div>
                            </div>
                            <div class="mt-3 pt-1 border-top">
                                <p class="text-muted small mb-0">
                                    <i class="fas fa-calendar-alt align-middle me-2 text-primary"></i>Bergabung: <?= formatTanggalIndo($row['bergabung_sejak']) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center text-muted">Tidak ada data anggota.</div>
    <?php endif; ?>
</div>

<nav aria-label="Page navigation example">
    <ul class="pagination justify-content-center mt-4">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                <a class="page-link" href="?tab=anggota&page=<?= $i ?><?= !empty($searchTerm) ? '&search=' . htmlspecialchars($searchTerm) : '' ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
    </ul>
</nav>
