<h2 class="mb-4 text-primary"><i class="fa-solid fa-wallet me-2"></i>Laporan Keuangan</h2>
<?php
// Pastikan $conn sudah terdefinisi dan terhubung ke database.
// Asumsi $active_tab dan $searchTerm sudah didefinisikan di bagian lain skrip Anda

// Mengambil tahun yang dipilih dari URL atau menggunakan tahun saat ini sebagai default
$selectedYear = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Mengambil total pemasukan dan pengeluaran untuk tahun yang dipilih
$stmtPemasukan = $conn->prepare("SELECT SUM(jumlah) FROM keuangan WHERE YEAR(tanggal_transaksi) = ? AND jenis_transaksi = 'pemasukan'");
$stmtPemasukan->bind_param("s", $selectedYear);
$stmtPemasukan->execute();
$stmtPemasukan->bind_result($totalPemasukan);
$stmtPemasukan->fetch();
$stmtPemasukan->close();

$stmtPengeluaran = $conn->prepare("SELECT SUM(jumlah) FROM keuangan WHERE YEAR(tanggal_transaksi) = ? AND jenis_transaksi = 'pengeluaran'");
$stmtPengeluaran->bind_param("s", $selectedYear);
$stmtPengeluaran->execute();
$stmtPengeluaran->bind_result($totalPengeluaran);
$stmtPengeluaran->fetch();
$stmtPengeluaran->close();

// Menggunakan operator null coalescing (??) untuk menangani nilai NULL dari query
$totalPemasukan = $totalPemasukan ?? 0;
$totalPengeluaran = $totalPengeluaran ?? 0;

$saldo = $totalPemasukan - $totalPengeluaran;

// Mengambil data bulanan untuk grafik
$pemasukanData = [];
$pengeluaranData = [];
$labels = [];

for ($i = 1; $i <= 12; $i++) {
    $date = new DateTime("$selectedYear-$i-01");
    $month = $date->format('m');
    $monthName = $date->format('M Y');

    // Kueri pemasukan bulanan
    $stmtPemasukanBulan = $conn->prepare("SELECT SUM(jumlah) FROM keuangan WHERE MONTH(tanggal_transaksi) = ? AND YEAR(tanggal_transaksi) = ? AND jenis_transaksi = 'pemasukan'");
    $stmtPemasukanBulan->bind_param("ss", $month, $selectedYear);
    $stmtPemasukanBulan->execute();
    $stmtPemasukanBulan->bind_result($monthlyPemasukan);
    $stmtPemasukanBulan->fetch();
    $stmtPemasukanBulan->close();
    $monthlyPemasukan = $monthlyPemasukan ?? 0;

    // Kueri pengeluaran bulanan
    $stmtPengeluaranBulan = $conn->prepare("SELECT SUM(jumlah) FROM keuangan WHERE MONTH(tanggal_transaksi) = ? AND YEAR(tanggal_transaksi) = ? AND jenis_transaksi = 'pengeluaran'");
    $stmtPengeluaranBulan->bind_param("ss", $month, $selectedYear);
    $stmtPengeluaranBulan->execute();
    $stmtPengeluaranBulan->bind_result($monthlyPengeluaran);
    $stmtPengeluaranBulan->fetch();
    $stmtPengeluaranBulan->close();
    $monthlyPengeluaran = $monthlyPengeluaran ?? 0;

    // HANYA TAMBAHKAN DATA JIKA ADA TRANSAKSI (PEMASUKAN ATAU PENGELUARAN) DI BULAN TERSEBUT
    if ($monthlyPemasukan > 0 || $monthlyPengeluaran > 0) {
        $pemasukanData[] = $monthlyPemasukan;
        $pengeluaranData[] = $monthlyPengeluaran;
        $labels[] = $monthName;
    }
}

$pemasukanDataJson = json_encode($pemasukanData);
$pengeluaranDataJson = json_encode($pengeluaranData);
$labelsJson = json_encode($labels);
?>
<div class="row mb-3 gy-2 align-items-center">
    <div class="col-12 col-md-6">
        <form action="" method="GET" class="d-flex align-items-center w-100">
            <input type="hidden" name="tab" value="keuangan">
            <label for="year-keuangan" class="form-label mb-0 me-2 fw-bold">Pilih Tahun:</label>
            <select class="form-select w-auto" id="year-keuangan" name="year" onchange="this.form.submit()">
                <?php
                $currentYear = date('Y');
                $resultYears = $conn->query("SELECT DISTINCT YEAR(tanggal_transaksi) AS year FROM keuangan ORDER BY year DESC");
                $years = [];
                if ($resultYears) {
                    while ($row = $resultYears->fetch_assoc()) {
                        $years[] = $row['year'];
                    }
                }
                if (!in_array($currentYear, $years)) {
                    $years[] = $currentYear;
                    rsort($years);
                }
                foreach ($years as $year):
                ?>
                    <option value="<?= $year ?>" <?= ($year == $selectedYear) ? 'selected' : '' ?>><?= $year ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
    <div class="col-12 col-md-6">
        <form action="" method="GET" class="d-flex w-100 justify-content-end">
            <input type="hidden" name="tab" value="keuangan">
            <input type="hidden" name="year" value="<?= $selectedYear ?>">
            <div class="input-group">
                <input type="text" id="searchInputKeuangan" class="form-control" placeholder="Cari deskripsi..." name="search" value="<?= htmlspecialchars($searchTerm) ?>">
                <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search"></i></button>
                <?php if (!empty($searchTerm)): ?>
                    <a href="?tab=keuangan&year=<?= $selectedYear ?>" class="btn btn-outline-secondary" title="Hapus Pencarian"><i class="fas fa-times"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>
<div class="row mb-4">
    <div class="col-12 col-sm-4">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5 class="card-title">Total Pemasukan</h5>
                <p class="card-text fs-4">Rp<?= number_format($totalPemasukan, 0, ',', '.') ?></p>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-4">
        <div class="card text-white bg-danger">
            <div class="card-body">
                <h5 class="card-title">Total Pengeluaran</h5>
                <p class="card-text fs-4">Rp<?= number_format($totalPengeluaran, 0, ',', '.') ?></p>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-4">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5 class="card-title">Sisa Saldo</h5>
                <p class="card-text fs-4">Rp<?= number_format($saldo, 0, ',', '.') ?></p>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Progres Keuangan Bulanan (Tahun <?= $selectedYear ?>)</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-12">
                <canvas id="keuanganBarChart" style="max-height: 400px;"></canvas>
            </div>
        </div>
    </div>
</div>
<div class="">
    <table class="table table-hover table-striped" id="keuanganTable">
    <tbody>
            <?php if (count($keuangan) > 0): ?>
            <div class="row">
                <?php foreach ($keuangan as $row): ?>
                    <?php 
                        $isPemasukan = ($row['jenis_transaksi'] == 'pemasukan');
                        $badge_class = $isPemasukan ? 'badge-soft-success' : 'badge-soft-danger';
                        $icon_class = $isPemasukan ? 'mdi mdi-arrow-down-bold' : 'mdi mdi-arrow-up-bold';
                        $title_text = $isPemasukan ? 'Pemasukan' : 'Pengeluaran';
                        $amount_color = $isPemasukan ? 'text-success' : 'text-danger';
                    ?>
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-md flex-shrink-0">
                                        <div class="avatar-title bg-soft-primary text-primary display-6 m-0 rounded-circle">
                                            <i class="<?= $icon_class ?>"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1 ms-3">
                                        <h5 class="font-size-16 mb-1 text-dark"><?= htmlspecialchars($title_text) ?></h5>
                                        <div class="d-block mt-1">
                                            <span class="badge <?= $badge_class ?>" style="word-break: break-word; white-space: normal;">
                                                <?= htmlspecialchars(ucfirst($row['deskripsi'])) ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ms-auto">
                                        </div>
                                </div>
                                <div class="mt-3 pt-1">
                                    <h4 class="<?= $amount_color ?> mb-0"><?= htmlspecialchars(formatRupiah($row['jumlah'])) ?></h4>
                                    <p class="text-muted mb-0 mt-2"><i class="mdi mdi-calendar-range font-size-15 align-middle pe-2 text-primary"></i> <?= htmlspecialchars($row['tanggal_transaksi']) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="col-12 text-center text-muted mt-5">
                <p>Tidak ada data keuangan untuk tahun ini.</p>
            </div>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<nav aria-label="Page navigation example">
    <ul class="pagination justify-content-center">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                <a class="page-link" href="?tab=keuangan&year=<?= $selectedYear ?>&page=<?= $i ?><?= !empty($searchTerm) ? '&search=' . htmlspecialchars($searchTerm) : '' ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
    </ul>
</nav>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const pemasukanData = <?= $pemasukanDataJson ?>;
        const pengeluaranData = <?= $pengeluaranDataJson ?>;
        const labels = <?= $labelsJson ?>;

        const data = {
            labels: labels,
            datasets: [
                {
                    label: 'Pemasukan',
                    data: pemasukanData,
                    backgroundColor: 'rgba(40, 167, 69, 0.7)',
                    borderColor: 'rgba(40, 167, 69, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Pengeluaran',
                    data: pengeluaranData,
                    backgroundColor: 'rgba(220, 53, 69, 0.7)',
                    borderColor: 'rgba(220, 53, 69, 1)',
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
                    x: {
                        title: {
                            display: true,
                            text: 'Bulan'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Jumlah (Rp)'
                        },
                        ticks: {
                            callback: function(value) {
                                return 'Rp' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
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

        new Chart(document.getElementById('keuanganBarChart'), config);
    });
</script>