<?php
$anggota = $anggota ?? []; // Fallback jika $anggota belum didefinisikan
$paginated_anggota = $paginated_anggota ?? $anggota; 
$searchTerm = $searchTerm ?? '';
$page = $page ?? 1;
$total_pages = $total_pages ?? 1;
$offset = $offset ?? 0;
// --- Akhir Bagian Logika PHP ---
?>
<div class="card shadow-lg border-0 border-start border-5 border-primary mb-4 rounded-4">
    <div class="card-body py-3">
    <!-- Menggunakan d-flex untuk menata form pencarian dan tombol clear (jika ada) -->
        <div class="d-flex align-items-center">

            <!-- Form Pencarian Anggota -->
            <form action="" method="GET" class="w-100 me-2">
                <input type="hidden" name="tab" value="anggota">
                
                <div class="input-group">
                    <!-- Ikon Prefix (mengadopsi style card contoh) -->
                    <span class="input-group-text bg-light border-0 rounded-start-pill border-end-0">
                        <i class="fas fa-search text-primary"></i>
                    </span>
                    
                    <!-- Input Field (mengadopsi style card contoh: border-0 shadow-sm) -->
                    <input type="text" 
                        id="search-input" 
                        name="search" 
                        value="<?= htmlspecialchars($searchTerm) ?>" 
                        placeholder="Cari anggota..." 
                        class="form-control border-0 shadow-sm px-3 py-2">
                    
                    <!-- Tombol Submit (mengadopsi style pill dan shadow) -->
                    <button class="btn btn-primary px-4 shadow-sm rounded-end-pill" type="submit">
                        Cari
                    </button>
                </div>
            </form>
            
            <?php 
            // Tambahkan tombol untuk menghapus pencarian jika $searchTerm tidak kosong.
            // Ini mengadopsi pola UX dari contoh desain.
            if (!empty($searchTerm)): ?>
                <!-- Tombol Hapus Pencarian -->
                <a href="?tab=anggota" 
                class="btn btn-outline-danger rounded-pill flex-shrink-0 d-flex align-items-center justify-content-center p-0"
                style="width: 42px; height: 42px;"
                title="Hapus Pencarian">
                <i class="fas fa-times"></i>
                </a>
            <?php endif; ?>
        </div>
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
                                    <i class="fas fa-calendar-alt align-middle me-2 text-primary"></i> Bergabung: <?= htmlspecialchars($row['bergabung_sejak']) ?>
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
