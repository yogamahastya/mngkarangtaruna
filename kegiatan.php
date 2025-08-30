<h2 class="mb-4 text-primary"><i class="fa-solid fa-calendar-alt me-2"></i>Daftar Kegiatan</h2>
<div class="row mb-3 gy-2 align-items-center">
    <div class="col-12 col-md-6">
        <p class="fs-5 mb-0">Total Kegiatan: <span class="badge bg-primary"><?= $total_kegiatan ?></span></p>
    </div>
    <div class="col-12 col-md-6">
        <form action="" method="GET" class="d-flex w-100 justify-content-end">
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
</div>
<div class="">
    <table class="table table-hover table-striped" id="kegiatanTable">
        <tbody>
            <?php if (count($kegiatan) > 0): ?>
                <div class="row">
                    <?php foreach ($kegiatan as $row): ?>
                        <div class="col-lg-4 col-md-6 col-sm-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-md">
                                            <div class="avatar-title bg-soft-primary text-primary display-6 m-0 rounded-circle">
                                                <i class="bx bx-calendar-event"></i>
                                            </div>
                                        </div>
                                        <div class="flex-1 ms-3">
                                            <h5 class="font-size-16 mb-1"><a href="#" class="text-dark"><?= htmlspecialchars($row['nama_kegiatan']) ?></a></h5>
                                            <span class="badge badge-soft-success mb-0">Aktif</span>
                                        </div>
                                    </div>
                                    <div class="mt-3 pt-1">
                                        <p class="text-muted mb-0"><i class="mdi mdi-map-marker-outline font-size-15 align-middle pe-2 text-primary"></i> <?= htmlspecialchars($row['lokasi']) ?></p>
                                        <p class="text-muted mb-0 mt-2"><i class="mdi mdi-calendar-range font-size-15 align-middle pe-2 text-primary"></i> <?= htmlspecialchars($row['tanggal_mulai']) ?></p>
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
</div>
<nav aria-label="Page navigation example">
    <ul class="pagination justify-content-center">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                <a class="page-link" href="?tab=kegiatan&page=<?= $i ?><?= !empty($searchTerm) ? '&search=' . htmlspecialchars($searchTerm) : '' ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
    </ul>
</nav>