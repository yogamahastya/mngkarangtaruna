<?php if ($memberDuesBreakdown): ?>
    <a href="?tab=iuran17&year=<?= $selectedYear ?>" class="btn btn-outline-primary mb-4">
        <i class="fa-solid fa-arrow-left me-2"></i> Kembali ke Daftar iuran17
    </a>
    <h2 class="mb-4 text-primary"><i class="fa-solid fa-user me-2"></i>Rekapitulasi iuran17 Anggota</h2>
    <div class="card detail-card mb-4">
        <div class="card-body">
            <h4 class="card-title fw-bold"><i class="fa-solid fa-user-circle me-2"></i><?= htmlspecialchars($memberDuesBreakdown['member']['nama_lengkap']) ?></h4>
            <p class="card-text text-muted">
                <i class="fa-solid fa-calendar me-2"></i>Bergabung Sejak: <?= htmlspecialchars($memberDuesBreakdown['member']['bergabung_sejak']) ?>
            </p>
        </div>
    </div>
    <div class="row mb-3 gy-2 align-items-center">
        <div class="col-12">
            <form action="" method="GET" class="d-flex align-items-center w-100">
                <input type="hidden" name="tab" value="iuran17">
                <input type="hidden" name="member_id" value="<?= htmlspecialchars($attendanceMemberId) ?>">
                <label for="year-iuran17" class="form-label mb-0 me-2 fw-bold">Pilih Tahun:</label>
                <select class="form-select w-auto" id="year-iuran17" name="year" onchange="this.form.submit()">
                    <?php
                    $currentYear = date('Y');
                    $resultYears = $conn->query("SELECT DISTINCT YEAR(tanggal_bayar) AS year FROM iuran17 ORDER BY year DESC");
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
    </div>
    <div class="row">
        <div class="col-xl-6 col-md-12 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><i class="bx bx-list-check me-2"></i>Rincian Bulanan (Tahun <?= $selectedYear ?>)</h5>
                    <ul class="list-group list-group-flush">
                        <?php if (count($memberDuesBreakdown['breakdown']) > 0): ?>
                            <?php foreach ($memberDuesBreakdown['breakdown'] as $item): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm">
                                            <div class="avatar-title bg-soft-primary text-primary m-0 rounded-circle">
                                                <i class="mdi mdi-calendar-month"></i>
                                            </div>
                                        </div>
                                        <div class="ms-3">
                                            <h6 class="mb-0"><?= htmlspecialchars($item['month']) ?></h6>
                                            <small class="text-muted"><?= formatRupiah($item['paid']) ?></small>
                                            <?php if (!empty($item['notes'])): ?>
                                                <small class="text-danger fw-bold ms-2">(<?= htmlspecialchars($item['notes']) ?>)</small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php 
                                        $badgeClass = (strtolower($item['status']) == 'lunas') ? 'badge-soft-success' : 'badge-soft-danger';
                                        $statusText = (strtolower($item['status']) == 'lunas') ? 'Lunas' : 'Kurang';
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= $statusText ?></span>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="list-group-item text-center text-muted">Tidak ada data iuran17 yang tercatat untuk tahun ini.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-xl-6 col-md-12 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><i class="bx bx-chart me-2"></i>Ringkasan Keuangan</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="d-flex align-items-center"><i class="mdi mdi-check-circle-outline font-size-18 me-2 text-success"></i> Total Pembayaran</span>
                            <h6 class="fw-bold text-success mb-0"><?= formatRupiah($memberDuesBreakdown['summary']['total_paid']) ?></h6>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="d-flex align-items-center"><i class="mdi mdi-currency-usd font-size-18 me-2 text-primary"></i> Total Seharusnya</span>
                            <h6 class="fw-bold text-primary mb-0"><?= formatRupiah($memberDuesBreakdown['summary']['total_expected']) ?></h6>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="d-flex align-items-center"><i class="mdi mdi-alert-circle-outline font-size-18 me-2 text-danger"></i> Kekurangan</span>
                            <h6 class="fw-bold text-danger mb-0"><?= formatRupiah($memberDuesBreakdown['summary']['shortfall']) ?></h6>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <h2 class="mb-4 text-primary"><i class="fa-solid fa-receipt me-2"></i>Rekapitulasi iuran17</h2>
    <?php
    // Pastikan $conn sudah terdefinisi dan terhubung ke database.

    // Mengambil tahun yang dipilih dari URL atau menggunakan tahun saat ini sebagai default
    $selectedYear = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

    // Mengambil total iuran keseluruhan untuk tahun yang dipilih
    $stmt = $conn->prepare("SELECT SUM(jumlah_bayar) FROM iuran17 WHERE YEAR(tanggal_bayar) = ?");
    $stmt->bind_param("s", $selectedYear);
    $stmt->execute();
    $stmt->bind_result($totalIuran17);
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
        $stmt = $conn->prepare("SELECT SUM(jumlah_bayar) FROM iuran17 WHERE MONTH(tanggal_bayar) = ? AND YEAR(tanggal_bayar) = ?");
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

    <div class="">
        <div class="col-12 col-md-4">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title">Total Pemasukan Iuran 17</h5>
                    <p class="card-text fs-4">Rp<?= number_format($totalIuran17, 0, ',', '.') ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">Progres Pemasukan Iuran 17 Bulanan (Tahun <?= $selectedYear ?>)</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <canvas id="pemasukan17BarChart" style="max-height: 400px;"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-3 gy-2 align-items-center">
        <div class="col-12 col-md-6">
            <form action="" method="GET" class="d-flex align-items-center w-100">
                <input type="hidden" name="tab" value="iuran17">
                <label for="year-iuran17" class="form-label mb-0 me-2 fw-bold">Pilih Tahun:</label>
                <select class="form-select w-auto" id="year-iuran17" name="year" onchange="this.form.submit()">
                    <?php
                    $currentYear = date('Y');
                    $resultYears = $conn->query("SELECT DISTINCT YEAR(tanggal_bayar) AS year FROM iuran17 ORDER BY year DESC");
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
                <input type="hidden" name="tab" value="iuran17">
                <input type="hidden" name="year" value="<?= $selectedYear ?>">
                <div class="input-group">
                    <input type="text" id="searchInputiuran17" class="form-control" placeholder="Cari anggota..." name="search" value="<?= htmlspecialchars($searchTerm) ?>">
                    <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search"></i></button>
                    <?php if (!empty($searchTerm)): ?>
                        <a href="?tab=iuran17&year=<?= $selectedYear ?>" class="btn btn-outline-secondary" title="Hapus Pencarian"><i class="fas fa-times"></i></a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    <div class="">
        <div class="row">
            <?php if (!empty($iuran17)): ?>
                <?php foreach ($iuran17 as $row): ?>
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-md">
                                        <div class="avatar-title bg-soft-primary text-primary display-6 m-0 rounded-circle">
                                            <i class="bx bxs-wallet"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1 ms-3">
                                        <h5 class="font-size-16 mb-1"><a href="?tab=iuran17&member_id=<?= htmlspecialchars($row['anggota_id']) ?>&year=<?= $selectedYear ?>" class="text-dark"><?= htmlspecialchars($row['nama_lengkap']) ?></a></h5>
                                        <?php 
                                            // Logika perhitungan status
                                            $monthlyFee = DUES_MONTHLY_FEE17;
                                            $joinDate = new DateTime($row['bergabung_sejak']);
                                            $startOfSelectedYear = new DateTime("{$selectedYear}-01-01");
                                            $endOfSelectedYear = new DateTime("{$selectedYear}-12-31");
                                            
                                            $currentDate = new DateTime();
                                            $startDate = max($joinDate, $startOfSelectedYear);
                                            $endDate = min($currentDate, $endOfSelectedYear);
                                            
                                            $interval = $startDate->diff($endDate);
                                            $months = ($interval->y * 12) + $interval->m;
                                            if ($startDate <= $endDate) {
                                                $months += 1;
                                            }
                                            $totalSeharusnya = $months * $monthlyFee;
                                            $totalBayar = $row['total_bayar'] ?? 0;
                                            $statusData = getPaymentStatus($totalBayar, $totalSeharusnya);
                                            $status = $statusData['status'];
                                            
                                            $badgeClass = '';
                                            if (strtolower($status) == 'lunas') {
                                                $badgeClass = 'badge-soft-success';
                                            } elseif (strtolower($status) == 'kurang') {
                                                $badgeClass = 'badge-soft-warning';
                                            } elseif (strtolower($status) == 'belum bayar') {
                                                $badgeClass = 'badge-soft-danger';
                                            } else {
                                                $badgeClass = 'badge-soft-secondary';
                                            }
                                        ?>
                                        <span class="badge <?= $badgeClass ?> mb-0"><?= $status ?></span>
                                    </div>
                                </div>
                                <div class="mt-3 pt-1">
                                    <p class="text-muted mb-0"><i class="mdi mdi-currency-usd font-size-15 align-middle pe-2 text-primary"></i> Total Dibayar: <?= htmlspecialchars(formatRupiah($totalBayar)) ?></p>
                                    <p class="text-muted mb-0 mt-2"><i class="mdi mdi-alert-circle-outline font-size-15 align-middle pe-2 text-primary"></i> Seharusnya: <?= htmlspecialchars(formatRupiah($totalSeharusnya)) ?></p>
                                </div>
                                <div class="d-flex gap-2 pt-4">
                                    <a href="?tab=iuran17&member_id=<?= htmlspecialchars($row['anggota_id']) ?>&year=<?= $selectedYear ?>" class="btn btn-soft-primary btn-sm w-100">
                                        <i class="bx bx-receipt me-1"></i> Detail Riwayat
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center text-muted mt-5">
                    <p>Tidak ada data iuran17 yang ditemukan.</p>
                </div>
            <?php endif; ?>
        </div>
        <nav aria-label="Page navigation example">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                        <a class="page-link" href="?tab=iuran17&page=<?= $i ?><?= !empty($searchTerm) ? '&search=' . htmlspecialchars($searchTerm) : '' ?>&year=<?= $selectedYear ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
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

        new Chart(document.getElementById('pemasukan17BarChart'), config);
    });
</script>