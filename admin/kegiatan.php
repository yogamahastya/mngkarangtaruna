<h2 class="mb-4 text-primary"><i class="fa-solid fa-calendar-alt me-2"></i>Kelola Data Kegiatan</h2>
<div class="row mb-3 gy-2 align-items-center">
    <div class="col-12 col-md-6">
        <form action="" method="GET" class="d-flex w-100">
            <input type="hidden" name="tab" value="kegiatan">
            <div class="input-group">
                <input type="text" class="form-control" placeholder="Cari kegiatan..." name="search" value="<?= htmlspecialchars($searchTerm) ?>">
                <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search"></i></button>
                <?php if (!empty($searchTerm)): ?>
                    <a href="?tab=kegiatan" class="btn btn-outline-secondary" title="Hapus Pencarian"><i class="fas fa-times"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    <div class="col-12 col-md-6 text-md-end">
        <button type="button" class="btn btn-primary w-100 w-md-auto" data-bs-toggle="modal" data-bs-target="#addKegiatanModal">
            <i class="fa-solid fa-plus-circle me-2"></i> Tambah Kegiatan
        </button>
    </div>
</div>
<div class="">
    <table class="table table-hover table-striped d-none d-md-table">
        <tbody>
            <?php if (count($kegiatan) > 0): ?>
            <div class="row">
                <?php foreach ($kegiatan as $row): ?>
                    <div class="col-xl-4 col-sm-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="dropdown float-end">
                                    <a class="text-muted dropdown-toggle font-size-16" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true"><i class="bx bx-dots-horizontal-rounded"></i></a>
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
                                <div class="d-flex align-items-center">
                                    <div class="avatar-md">
                                        <div class="avatar-title bg-soft-primary text-primary display-6 m-0 rounded-circle">
                                            <i class="bx bx-calendar-event"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1 ms-3">
                                        <h5 class="font-size-16 mb-1 text-dark"><?= htmlspecialchars($row['nama_kegiatan']) ?></h5>
                                        <span class="badge badge-soft-success mb-0">Aktif</span>
                                    </div>
                                </div>
                                <div class="mt-3 pt-1">
                                    <p class="text-muted mb-2"><i class="mdi mdi-map-marker-outline font-size-15 align-middle pe-2 text-primary"></i> <?= htmlspecialchars($row['lokasi'] ?? '') ?></p>
                                    <p class="text-muted mb-2"><i class="mdi mdi-text-long font-size-15 align-middle pe-2 text-primary"></i> <?= htmlspecialchars($row['deskripsi'] ?? '') ?></p>               
                                    <p class="text-muted mb-2"><i class="mdi mdi-file-document-outline font-size-15 align-middle pe-2 text-primary"></i> Notulen: <?= nl2br(htmlspecialchars($row['notulen'] ?? '')) ?></p>                                   
                                    <p class="text-muted mb-0"><i class="mdi mdi-calendar-range font-size-15 align-middle pe-2 text-primary"></i> <?= htmlspecialchars($row['tanggal_mulai'] ?? '') ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="col-12 text-center text-muted mt-5">
                <p>Tidak ada data kegiatan.</p>
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
    <nav aria-label="Page navigation example" class="mt-4 d-md-none">
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                    <a class="page-link" href="?tab=keuangan&year=<?= $selectedYear ?>&page=<?= $i ?><?= !empty($searchTerm) ? '&search=' . htmlspecialchars($searchTerm) : '' ?>"><?= $i ?></a>
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
                    <a class="page-link" href="?tab=kegiatan&page=<?= $i ?><?= !empty($searchTerm) ? '&search=' . htmlspecialchars($searchTerm) : '' ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
            </ul>
    </nav>
<?php endif; ?>