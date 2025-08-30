<h2 class="mb-4 text-primary"><i class="fa-solid fa-receipt me-2"></i>Kelola Data Iuran 17</h2>
<div class="row mb-3 gy-2 align-items-center">
    <div class="col-12 col-md-6">
        <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-calendar-alt"></i></span>
            <select class="form-select" onchange="window.location.href = '?tab=<?= $active_tab ?>&year=' + this.value + '<?= !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : '' ?>'">
                <?php
                // Query untuk mendapatkan tahun minimum dari tabel iuran17
                $minYearQuery = "SELECT MIN(YEAR(tanggal_bayar)) AS min_year FROM iuran17";
                $minYearResult = $conn->query($minYearQuery);
                $minYearRow = $minYearResult->fetch_assoc();
                // Jika tidak ada data, gunakan tahun saat ini
                $minYear = $minYearRow['min_year'] ? $minYearRow['min_year'] : date('Y');
                for ($year = date('Y'); $year >= $minYear; $year--):
                ?>
                    <option value="<?= $year ?>" <?= ($year == $selectedYear) ? 'selected' : '' ?>>
                        <?= $year ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>
    </div>
    <div class="col-12 col-md-6">
        <form action="" method="GET" class="d-flex w-100">
            <input type="hidden" name="tab" value="iuran17">
            <input type="hidden" name="year" value="<?= $selectedYear ?>">
            <div class="input-group">
                <input type="text" class="form-control" placeholder="Cari iuran 17..." name="search" value="<?= htmlspecialchars($searchTerm) ?>">
                <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search"></i></button>
                <?php if (!empty($searchTerm)): ?>
                    <a href="?tab=iuran17&year=<?= $selectedYear ?>" class="btn btn-outline-secondary" title="Hapus Pencarian"><i class="fas fa-times"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="row mb-4 gy-3">
    <div class="col-12 col-md-4">
        <div class="card text-white bg-info">
            <div class="card-body">
                <h5 class="card-title">Total Pemasukan Iuran 17</h5>
                <p class="card-text fs-4">Rp<?= number_format($totalIuran17, 0, ',', '.') ?></p>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-12 text-md-end">
        <button type="button" class="btn btn-primary w-100 w-md-auto" data-bs-toggle="modal" data-bs-target="#addIuran17Modal">
            <i class="fa-solid fa-plus-circle me-2"></i> Tambah Pembayaran Iuran 17
        </button>
    </div>
</div>
<div class="">
    <table class="table table-hover table-striped d-none d-md-table">
        <tbody>
            <div class="row">
                <?php if (count($iuran17) > 0): ?>
                    <?php foreach ($iuran17 as $row): ?>
                        <?php
                            $anggotaName = 'Tidak Ditemukan';
                            // Periksa jika ada anggota_nama dari join (jika search term diterapkan)
                            if (isset($row['anggota_nama'])) {
                                $anggotaName = $row['anggota_nama'];
                            } else {
                                // Jika tidak ada join, cari manual dari anggotaList
                                foreach ($anggotaList as $member) {
                                    if ($member['id'] == $row['anggota_id']) {
                                        $anggotaName = $member['nama_lengkap'];
                                        break;
                                    }
                                }
                            }
                            // Logika untuk menentukan status berdasarkan DUES_MONTHLY_FEE17 dari config.php
                            $status = ($row['jumlah_bayar'] >= DUES_MONTHLY_FEE17) ? 'Lunas' : 'Belum Lunas';
                            $badgeClass = ($status == 'Lunas') ? 'bg-success' : 'bg-danger';
                        ?>
                        <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body">
                                    <div class="dropdown float-end">
                                        <a class="text-muted dropdown-toggle font-size-16" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true">
                                            <i class="bx bx-dots-horizontal-rounded"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a class="dropdown-item edit-btn" href="#" data-bs-toggle="modal" data-bs-target="#editIuran17Modal" data-id="<?= $row['id'] ?>" data-anggota-id="<?= $row['anggota_id'] ?>" data-tanggal="<?= $row['tanggal_bayar'] ?>" data-jumlah="<?= $row['jumlah_bayar'] ?>" data-keterangan="<?= $row['keterangan'] ?>">
                                                <i class="bx bx-edit me-1"></i> Edit
                                            </a>
                                            <form action="" method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="tab" value="iuran17">
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
                                                <i class="bx bxs-wallet"></i>
                                            </div>
                                        </div>
                                        <div class="flex-1 ms-3">
                                            <h5 class="font-size-16 mb-1">
                                                <a href="?tab=iuran17&member_id=<?= htmlspecialchars($row['anggota_id']) ?>" class="text-dark">
                                                    <?= htmlspecialchars($anggotaName) ?>
                                                </a>
                                            </h5>
                                            <span class="badge <?= $badgeClass ?> mb-0"><?= $status ?></span>
                                        </div>
                                    </div>

                                    <div class="mt-3 pt-1">
                                        <p class="text-muted mb-0">
                                            <i class="mdi mdi-calendar font-size-15 align-middle pe-2 text-primary"></i> Tanggal Bayar: <span class="float-end"><?= htmlspecialchars($row['tanggal_bayar']) ?></span>
                                        </p>
                                        <p class="text-muted mb-0 mt-2">
                                            <i class="mdi mdi-currency-usd font-size-15 align-middle pe-2 text-primary"></i> Jumlah: <span class="float-end fw-bold">Rp<?= htmlspecialchars(number_format($row['jumlah_bayar'], 0, ',', '.')) ?></span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center text-muted">
                        <p>Tidak ada data iuran 17.</p>
                    </div>
                <?php endif; ?>
            </div>
        </tbody>
    </table>
    <?php if ($total_pages > 1): ?>
    <nav aria-label="Page navigation example" class="mt-4 d-none d-md-block">
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                    <a class="page-link" href="?tab=iuran17&year=<?= $selectedYear ?>&page=<?= $i ?><?= !empty($searchTerm) ? '&search=' . htmlspecialchars($searchTerm) : '' ?>"><?= $i ?></a>
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
                    <a class="page-link" href="?tab=iuran17&year=<?= $selectedYear ?>&page=<?= $i ?><?= !empty($searchTerm) ? '&search=' . htmlspecialchars($searchTerm) : '' ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
            </ul>
    </nav>
<?php endif; ?>