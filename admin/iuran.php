<h2 class="mb-4 text-primary"><i class="fa-solid fa-receipt me-2"></i>Kelola Data Iuran</h2>
<?php
// Pastikan $conn sudah terdefinisi dan terhubung ke database.

// Mengambil tahun yang dipilih dari URL atau menggunakan tahun saat ini sebagai default
$selectedYear = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Mengambil total iuran keseluruhan untuk tahun yang dipilih
$stmt = $conn->prepare("SELECT SUM(jumlah_bayar) FROM iuran WHERE YEAR(tanggal_bayar) = ?");
$stmt->bind_param("s", $selectedYear);
$stmt->execute();
$stmt->bind_result($totalIuran);
$stmt->fetch();
$stmt->close();

// Mengambil data pemasukan iuran bulanan untuk tahun yang dipilih
$pemasukanData = [];
$labels = [];

// Loop dari Januari hingga Desember di tahun yang dipilih
for ($i = 1; $i <= 12; $i++) {
    $date = new DateTime("$selectedYear-$i-01");
    $month = $date->format('m');
    $monthName = $date->format('M Y'); // Contoh: Jan 2024

    // Kueri untuk menghitung total pemasukan iuran per bulan
    $stmt = $conn->prepare("SELECT SUM(jumlah_bayar) FROM iuran WHERE MONTH(tanggal_bayar) = ? AND YEAR(tanggal_bayar) = ?");
    $stmt->bind_param("ss", $month, $selectedYear);
    $stmt->execute();
    $stmt->bind_result($monthlyPemasukan);
    $stmt->fetch();
    $stmt->close();

    $monthlyPemasukan = $monthlyPemasukan ?? 0;

    // HANYA TAMBAHKAN DATA JIKA ADA PEMASUKAN DI BULAN TERSEBUT
    if ($monthlyPemasukan > 0) {
        $pemasukanData[] = $monthlyPemasukan;
        $labels[] = $monthName;
    }
}

// Mengubah array PHP ke JSON untuk digunakan di JavaScript
$pemasukanDataJson = json_encode($pemasukanData);
$labelsJson = json_encode($labels);
?>

<div class="row mb-3 gy-2 align-items-center">
    <div class="col-12 col-md-6">
        <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-calendar-alt"></i></span>
            <select class="form-select" onchange="window.location.href = '?tab=<?= $active_tab ?>&year=' + this.value + '<?= !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : '' ?>'">
                <?php
                $minYearQuery = "SELECT MIN(YEAR(tanggal_bayar)) AS min_year FROM iuran";
                $minYearResult = $conn->query($minYearQuery);
                $minYearRow = $minYearResult->fetch_assoc();
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
            <input type="hidden" name="tab" value="iuran">
            <input type="hidden" name="year" value="<?= $selectedYear ?>">
            <div class="input-group">
                <input type="text" class="form-control" placeholder="Cari iuran..." name="search" value="<?= htmlspecialchars($searchTerm) ?>">
                <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search"></i></button>
                <?php if (!empty($searchTerm)): ?>
                    <a href="?tab=iuran&year=<?= $selectedYear ?>" class="btn btn-outline-secondary" title="Hapus Pencarian"><i class="fas fa-times"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="">
    <div class="col-12 col-md-4">
        <div class="card text-white bg-info">
            <div class="card-body">
                <h5 class="card-title">Total Pemasukan Iuran</h5>
                <p class="card-text fs-4">Rp<?= number_format($totalIuran, 0, ',', '.') ?></p>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0">Progres Pemasukan Iuran Bulanan (Tahun <?= $selectedYear ?>)</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-12">
                <canvas id="pemasukanBarChart" style="max-height: 400px;"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-12 text-md-end">
        <button type="button" class="btn btn-primary w-100 w-md-auto" data-bs-toggle="modal" data-bs-target="#addIuranModal">
            <i class="fa-solid fa-plus-circle me-2"></i> Tambah Pembayaran Iuran
        </button>
    </div>
</div>
<div class="">
    <table class="table table-hover table-striped d-none d-md-table">
        <tbody>
            <div class="row">
                <?php if (count($iuran) > 0): ?>
                    <?php foreach ($iuran as $row): ?>
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
                            // Logika untuk menentukan status berdasarkan DUES_MONTHLY_FEE dari config.php
                            $status = ($row['jumlah_bayar'] >= DUES_MONTHLY_FEE) ? 'Lunas' : 'Belum Lunas';
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
                                            <a class="dropdown-item edit-btn" href="#" data-bs-toggle="modal" data-bs-target="#editIuranModal" data-id="<?= $row['id'] ?>" data-anggota-id="<?= $row['anggota_id'] ?>" data-tanggal="<?= $row['tanggal_bayar'] ?>" data-jumlah="<?= $row['jumlah_bayar'] ?>" data-keterangan="<?= $row['keterangan'] ?>">
                                                <i class="bx bx-edit me-1"></i> Edit
                                            </a>
                                            <form action="" method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="tab" value="iuran">
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
                                                <a href="?tab=iuran&member_id=<?= htmlspecialchars($row['anggota_id']) ?>" class="text-dark">
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
                        <p>Tidak ada data iuran.</p>
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
                    <a class="page-link" href="?tab=iuran&year=<?= $selectedYear ?>&page=<?= $i ?><?= !empty($searchTerm) ? '&search=' . htmlspecialchars($searchTerm) : '' ?>"><?= $i ?></a>
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
                    <a class="page-link" href="?tab=iuran&year=<?= $selectedYear ?>&page=<?= $i ?><?= !empty($searchTerm) ? '&search=' . htmlspecialchars($searchTerm) : '' ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
            </ul>
    </nav>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const pemasukanData = <?= $pemasukanDataJson ?>;
        const labels = <?= $labelsJson ?>;

        const data = {
            labels: labels,
            datasets: [
                {
                    label: 'Total Pemasukan (Rp)',
                    data: pemasukanData,
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }
            ]
        };

        const config = {
            type: 'bar',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Jumlah Pemasukan (Rp)'
                        },
                        ticks: {
                            callback: function(value) {
                                return 'Rp' + value.toLocaleString('id-ID');
                            }
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Bulan'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += 'Rp' + context.raw.toLocaleString('id-ID');
                                return label;
                            }
                        }
                    }
                }
            }
        };

        new Chart(document.getElementById('pemasukanBarChart'), config);
    });
</script>