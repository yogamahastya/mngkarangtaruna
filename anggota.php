<h2 class="mb-4 text-primary"><i class="fa-solid fa-user-group me-2"></i>Data Anggota</h2>
<div class="row mb-3 align-items-center gy-2">
    <div class="col-12 col-md-4">
        <p class="fs-5 mb-0">Total Anggota: <span class="badge bg-primary"><?= $total_anggota ?></span></p>
    </div>
    <div class="col-12 col-md-8 text-md-end">
        <form action="" method="GET" class="d-flex justify-content-start justify-content-md-end">
            <input type="hidden" name="tab" value="anggota">
            <div class="input-group search-input-desktop">
                <input type="text" class="form-control" placeholder="Cari anggota..." name="search" value="<?= htmlspecialchars($searchTerm) ?>">
                <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search"></i></button>
                <?php if (!empty($searchTerm)): ?>
                    <a href="?tab=anggota" class="btn btn-outline-secondary" title="Hapus Pencarian"><i class="fas fa-times"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>
<style>
    /* CSS Kustom untuk mengontrol lebar di desktop */
    @media (min-width: 768px) {
        .search-input-desktop {
            max-width: 300px;
        }
    }
</style>
<div class="">
    <table class="table table-hover table-striped" id="anggotaTable">
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
                    case 'humas': 
                        $badge_class .= 'humas';
                        break;
                    default:
                        $badge_class .= 'anggota'; // Kelas default jika jabatan tidak cocok
                        break;
                }
            ?>
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div><img src="<?= $profile_image ?>" alt="<?= htmlspecialchars($row['nama_lengkap']) ?>" class="avatar-md rounded-circle img-thumbnail" /></div>
                            <div class="flex-1 ms-3">
                                <h5 class="font-size-16 mb-1"><a href="#" class="text-dark"><?= htmlspecialchars($row['nama_lengkap']) ?></a></h5>
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
        <div class="text-center text-muted">Tidak ada data anggota.</div>
    <?php endif; ?>
    </table>
</div>
<nav aria-label="Page navigation example">
    <ul class="pagination justify-content-center">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                <a class="page-link" href="?tab=anggota&page=<?= $i ?><?= !empty($searchTerm) ? '&search=' . htmlspecialchars($searchTerm) : '' ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
    </ul>
</nav>