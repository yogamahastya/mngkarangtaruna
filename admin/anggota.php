<?php
$anggota = $anggota ?? []; // Fallback jika $anggota belum didefinisikan
$paginated_anggota = $paginated_anggota ?? $anggota; 
$searchTerm = $searchTerm ?? '';
$page = $page ?? 1;
$total_pages = $total_pages ?? 1;
$offset = $offset ?? 0;
$totalAnggota = $totalAnggota ?? count($anggota);
?>

<!-- Card Pencarian dengan Tombol Tambah -->
<div class="card shadow-lg border-0 border-start border-5 border-primary mb-4 rounded-4">
    <div class="card-body py-3">
        <!-- Desktop Layout -->
        <div class="d-none d-md-flex align-items-center justify-content-between">
            <!-- Form Pencarian -->
            <form action="" method="GET" class="flex-grow-1 me-3">
                <input type="hidden" name="tab" value="anggota">
                
                <div class="input-group">
                    <span class="input-group-text bg-light border-0 rounded-start-pill border-end-0">
                        <i class="fas fa-search text-primary"></i>
                    </span>
                    
                    <input type="text" 
                        id="search-input" 
                        name="search" 
                        value="<?= htmlspecialchars($searchTerm) ?>" 
                        placeholder="Cari anggota..." 
                        class="form-control border-0 shadow-sm px-3 py-2">
                    
                    <button class="btn btn-primary px-4 shadow-sm rounded-end-pill" type="submit">
                        Cari
                    </button>
                </div>
            </form>
            
            <!-- Tombol Tambah Anggota -->
            <button type="button" class="btn btn-primary rounded-pill px-4 py-2 shadow-sm flex-shrink-0" data-bs-toggle="modal" data-bs-target="#addAnggotaModal">
                <i class="fa-solid fa-plus-circle me-2"></i> Tambah Anggota
            </button>
        </div>

        <!-- Mobile Layout -->
        <div class="d-md-none">
            <!-- Tombol Tambah Anggota di atas -->
            <div class="mb-3">
                <button type="button" class="btn btn-primary rounded-pill w-100 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#addAnggotaModal">
                    <i class="fa-solid fa-plus-circle me-2"></i> Tambah Anggota
                </button>
            </div>
            
            <!-- Form Pencarian di bawah -->
            <form action="" method="GET">
                <input type="hidden" name="tab" value="anggota">
                
                <div class="input-group">
                    <span class="input-group-text bg-light border-0 rounded-start-pill border-end-0">
                        <i class="fas fa-search text-primary"></i>
                    </span>
                    
                    <input type="text" 
                        id="search-input-mobile" 
                        name="search" 
                        value="<?= htmlspecialchars($searchTerm) ?>" 
                        placeholder="Cari anggota..." 
                        class="form-control border-0 shadow-sm px-3 py-2">
                    
                    <button class="btn btn-primary shadow-sm rounded-end-pill" type="submit" style="width: 50px;">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
        
        <?php if (!empty($searchTerm)): ?>
            <!-- Tombol Hapus Pencarian -->
            <div class="text-center mt-3">
                <a href="?tab=anggota" 
                   class="btn btn-outline-danger rounded-pill px-3 py-2"
                   title="Hapus Pencarian">
                    <i class="fas fa-times me-2"></i> Hapus Pencarian
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
<!-- Daftar Anggota -->
<div class="">
    <?php if (count($paginated_anggota) > 0): ?>
        <div class="row g-4">
            <?php foreach ($paginated_anggota as $index => $row): ?>
                <?php
                    // Logika badge class
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
                            <!-- Dropdown Menu -->
                            <div class="dropdown float-end">
                                <a class="text-muted dropdown-toggle font-size-16" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true">
                                    <i class="bx bx-dots-horizontal-rounded"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item edit-btn" href="#" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editAnggotaModal" 
                                        data-id="<?= $row['id'] ?>" 
                                        data-nama="<?= htmlspecialchars($row['nama_lengkap']) ?>" 
                                        data-jabatan="<?= htmlspecialchars($row['jabatan']) ?>" 
                                        data-sejak="<?= htmlspecialchars($row['bergabung_sejak']) ?>"
                                        data-nohp="<?= htmlspecialchars($row['no_hp'] ?? '') ?>">
                                        <i class="bx bx-edit me-1"></i> Edit
                                    </a>

                                    <form action="" method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="tab" value="anggota">
                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                        <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                            <i class="bx bx-trash me-1"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Avatar dan Info Anggota -->
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0 avatar-lg me-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                         style="width: 3.5rem; height: 3.5rem; font-size: 1.25rem; flex-shrink: 0; background-color: <?= $avatarColor ?>; color: white; font-weight: 600;">
                                        <?= $initials ?>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">
                                        <a href="#" class="text-dark text-decoration-none" style="font-size: 1rem;">
                                            <?= htmlspecialchars($row['nama_lengkap']) ?>
                                        </a>
                                    </h6>
                                    <span class="<?= $badge_class ?> mb-0"><?= $jabatan ?></span>
                                </div>
                            </div>

                            <!-- Info Tambahan -->
                            <div class="mt-3 pt-1 border-top">
                                <p class="text-muted small mb-2">
                                    <i class="fas fa-calendar-alt align-middle me-2 text-primary"></i> 
                                    Bergabung: <?= htmlspecialchars($row['bergabung_sejak']) ?>
                                </p>
                                <p class="text-muted small mb-0">
                                    <i class="fas fa-phone align-middle me-2 text-primary"></i> 
                                    Nomor HP: <?= htmlspecialchars($row['no_hp'] ?? 'Belum ada') ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center text-muted py-5">
            <i class="fas fa-users fa-3x mb-3 opacity-50"></i>
            <p class="mb-0">Tidak ada data anggota.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
    <nav aria-label="Page navigation example" class="mt-4">
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                    <a class="page-link" href="?tab=anggota&page=<?= $i ?><?= !empty($searchTerm) ? '&search=' . htmlspecialchars($searchTerm) : '' ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
<?php endif; ?>