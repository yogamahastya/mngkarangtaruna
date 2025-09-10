<h2 class="mb-4 text-primary"><i class="fa-solid fa-user-group me-2"></i>Kelola Data Anggota</h2>
<div class="row mb-3 gy-2 align-items-center">
    <div class="col-12 col-md-4">
        <p class="fs-5 mb-0">Total Anggota: <span class="badge bg-primary"><?= $totalAnggota ?></span></p>
    </div>
    <div class="col-12 col-md-4">
        <form action="" method="GET" class="d-flex w-100">
            <input type="hidden" name="tab" value="anggota">
            <div class="input-group">
                <input type="text" class="form-control" placeholder="Cari anggota..." name="search" value="<?= htmlspecialchars($searchTerm) ?>">
                <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search"></i></button>
                <?php if (!empty($searchTerm)): ?>
                    <a href="?tab=anggota" class="btn btn-outline-secondary" title="Hapus Pencarian"><i class="fas fa-times"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    <div class="col-12 col-md-4 text-md-end">
        <button type="button" class="btn btn-primary w-100 w-md-auto" data-bs-toggle="modal" data-bs-target="#addAnggotaModal">
            <i class="fa-solid fa-plus-circle me-2"></i> Tambah Anggota
        </button>
    </div>
</div>
<div class="">
    <table class="table table-hover table-striped d-none d-md-table">
        <tbody>
            <?php if (count($anggota) > 0): ?>
            <div class="row">
                <?php foreach ($anggota as $row): ?>
                    <?php
                        // Tentukan kelas CSS badge berdasarkan nilai jabatan
                        $jabatan = htmlspecialchars($row['jabatan']);
                        $badge_class = 'badge '; // Kelas dasar untuk badge

                        switch (strtolower($jabatan)) {
                            case 'ketua':
                                $badge_class .= 'ketua';
                                break;
                            case 'wakil ketua':
                                $badge_class .= 'wakilketua';
                                break;
                            case 'sekretaris':
                                $badge_class .= 'sekretaris';
                                break;
                            case 'bendahara':
                                $badge_class .= 'bendahara';
                                break;
                            case 'anggota':
                                $badge_class .= 'anggota';
                                break;
                            case 'humas':
                                $badge_class .= 'humas';
                                break;
                            
                        }

                        // Tentukan gambar profil atau ikon default
                        
                    ?>
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="dropdown float-end">
                                    <a class="text-muted dropdown-toggle font-size-16" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true"><i class="bx bx-dots-horizontal-rounded"></i></a>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a class="dropdown-item edit-btn" href="#" data-bs-toggle="modal" data-bs-target="#editAnggotaModal" data-id="<?= $row['id'] ?>" data-nama="<?= htmlspecialchars($row['nama_lengkap']) ?>" data-jabatan="<?= htmlspecialchars($row['jabatan']) ?>" data-sejak="<?= htmlspecialchars($row['bergabung_sejak']) ?>">
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
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <img src="<?= $profile_image ?>" alt="<?= htmlspecialchars($row['nama_lengkap']) ?>" class="avatar-md rounded-circle img-thumbnail" />
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h5 class="font-size-16 mb-1">
                                            <a href="#" class="text-dark"><?= htmlspecialchars($row['nama_lengkap']) ?></a>
                                        </h5>
                                        <span class="<?= $badge_class ?> mb-0"><?= $jabatan ?></span>
                                    </div>
                                </div>
                                <div class="mt-3 pt-1">
                                    <p class="text-muted mb-0"><i class="mdi mdi-calendar font-size-15 align-middle pe-2 text-primary"></i> Bergabung: <?= htmlspecialchars($row['bergabung_sejak']) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="col-12 text-center text-muted mt-5">
                <p>Tidak ada data anggota.</p>
            </div>
        <?php endif; ?>
        </tbody>
    </table>
    <?php if ($total_pages > 1): ?>
    <nav aria-label="Page navigation example" class="mt-4 d-none d-md-block">
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                    <a class="page-link" href="?tab=anggota&page=<?= $i ?><?= !empty($searchTerm) ? '&search=' . htmlspecialchars($searchTerm) : '' ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
            </ul>
    </nav>
    <?php endif; ?>        
</div>
<?php if ($total_pages > 1): ?>
    <nav aria-label="Page navigation example" class="mt-4 d-md-none">
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                    <a class="page-link" href="?tab=anggota&page=<?= $i ?><?= !empty($searchTerm) ? '&search=' . htmlspecialchars($searchTerm) : '' ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
            </ul>
    </nav>
<?php endif; ?>