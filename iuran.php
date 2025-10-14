<?php if ($memberDuesBreakdown): ?>

    <div style="min-height: calc(100vh - 200px);"> 
    
        <a href="?tab=iuran&year=<?= htmlspecialchars($selectedYear) ?>" class="btn btn-outline-primary mb-4 rounded-pill fw-medium">
            <i class="fa-solid fa-arrow-left me-2"></i> Kembali ke Daftar Iuran
        </a>

        <div class="card shadow-lg border-0 border-start border-5 border-primary mb-5 rounded-4">
            <div class="card-body py-4">
                <div class="d-flex align-items-center">
                    
                    <div class="rounded-circle me-4 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center shadow-sm" 
                        style="width: 3.5rem; height: 3.5rem; font-size: 1.5rem; flex-shrink: 0;">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    
                    <div>
                        <h5 class="card-title fw-bold mb-1 text-dark">
                            <?= htmlspecialchars($memberDuesBreakdown['member']['nama_lengkap']) ?>
                        </h5>
                        
                        <p class="card-text text-muted small mb-0">
                            <i class="fa-solid fa-calendar-day me-1 text-primary"></i>
                            Bergabung Sejak: 
                            <span class="fw-semibold text-dark">
                                <?= htmlspecialchars($memberDuesBreakdown['member']['bergabung_sejak']) ?>
                            </span>
                        </p>
                    </div>

                </div>
            </div>
        </div>


        <div class="card shadow-lg border-0 border-start border-5 border-primary mb-4 rounded-4">
            <div class="card-body py-3">
                <form action="" method="GET" class="d-flex align-items-center flex-wrap gap-3">
                    <input type="hidden" name="tab" value="iuran">
                    <input type="hidden" name="member_id" value="<?= htmlspecialchars($attendanceMemberId) ?>">

                  

                    <div class="input-group" style="max-width: 220px;">
                        <span class="input-group-text bg-primary text-white border-0 rounded-start-pill">
                            <i class="fa-solid fa-calendar"></i>
                        </span>
                        <select class="form-select border-0 shadow-sm rounded-end-pill px-3 py-2"
                                id="year-iuran"
                                name="year"
                                onchange="this.form.submit()"
                                style="cursor:pointer;">
                            <?php
                            $currentYear = date('Y');
                            // Asumsi $conn sudah ada dan berfungsi, tambahkan htmlspecialchars untuk output
                            if (isset($conn) && $conn) {
                                $resultYears = @$conn->query("SELECT DISTINCT YEAR(tanggal_bayar) AS year FROM iuran ORDER BY year DESC");
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
                                foreach ($years as $year): ?>
                                    <option value="<?= htmlspecialchars($year) ?>" <?= ($year == $selectedYear) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($year) ?>
                                    </option>
                                <?php endforeach; 
                            } else { 
                                // Opsi fallback
                                ?>
                                <option value="<?= htmlspecialchars($currentYear) ?>" selected>
                                    <?= htmlspecialchars($currentYear) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </form>
            </div>
        </div>


        <div class="row">
            <div class="col-xl-6 col-md-12 mb-4">
                <div class="card shadow-sm h-100 rounded-4">
                    <div class="card-header bg-primary text-white fw-semibold py-3 rounded-top-4">
                        <h6 class="card-title mb-0">
                            <i class="fa-solid fa-calendar-days me-2"></i>Rincian Bulanan (<?= htmlspecialchars($selectedYear) ?>)
                        </h6>
                    </div>
                    <div class="card-body p-2">
                        <ul class="list-group list-group-flush">
                            <?php if (count($memberDuesBreakdown['breakdown']) > 0): ?>
                                <?php foreach ($memberDuesBreakdown['breakdown'] as $item): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-3 py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center me-3"
                                                style="width:40px; height:40px;">
                                                <i class="fa-solid fa-calendar-days text-primary fs-6"></i>
                                            </div>
                                            <div>
                                                <p class="mb-1 fw-semibold text-dark"><?= htmlspecialchars($item['month']) ?></p>
                                                <small class="text-muted"><?= formatRupiah($item['paid']) ?></small>
                                                <?php if (!empty($item['notes'])): ?>
                                                    <small class="text-danger fw-bold ms-2">(<?= htmlspecialchars($item['notes']) ?>)</small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php 
                                            $badgeClass = (strtolower($item['status']) == 'lunas') 
                                                ? 'bg-success fw-semibold py-2 px-3 rounded-pill shadow-sm' 
                                                : 'bg-danger fw-semibold py-2 px-3 rounded-pill shadow-sm';
                                            $statusText = (strtolower($item['status']) == 'lunas') ? 'Lunas' : 'Kurang';
                                        ?>
                                        <span class="badge <?= $badgeClass ?>">
                                            <?= htmlspecialchars($statusText) ?>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="list-group-item text-center text-muted py-5">
                                    <i class="fa-solid fa-box-open fa-3x mb-3 d-block"></i>
                                    <p class="mb-0">Tidak ada data iuran yang tercatat untuk tahun ini.</p>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-xl-6 col-md-12 mb-4">
                <div class="card shadow-sm h-100 rounded-4">
                    <div class="card-header bg-primary text-white fw-semibold py-3 rounded-top-4">
                        <h6 class="card-title mb-0">
                            <i class="fa-solid fa-chart-column me-2"></i>Ringkasan Keuangan
                        </h6>
                    </div>
                    <div class="card-body p-2">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-3 py-3">
                                <span class="d-flex align-items-center">
                                    <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center me-2"
                                        style="width:35px;height:35px;">
                                        <i class="fa-solid fa-circle-check text-success fs-6"></i>
                                    </div>
                                    Total Pembayaran
                                </span>
                                <h6 class="fw-bold text-success mb-0"><?= formatRupiah($memberDuesBreakdown['summary']['total_paid']) ?></h6>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-3 py-3">
                                <span class="d-flex align-items-center">
                                    <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center me-2"
                                        style="width:35px;height:35px;">
                                        <i class="fa-solid fa-sack-dollar text-primary fs-6"></i>
                                    </div>
                                    Total Seharusnya
                                </span>
                                <h6 class="fw-bold text-primary mb-0"><?= formatRupiah($memberDuesBreakdown['summary']['total_expected']) ?></h6>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-3 py-3">
                                <span class="d-flex align-items-center">
                                    <div class="rounded-circle bg-danger bg-opacity-10 d-flex align-items-center justify-content-center me-2"
                                        style="width:35px;height:35px;">
                                        <i class="fa-solid fa-circle-exclamation text-danger fs-6"></i>
                                    </div>
                                    Kekurangan
                                </span>
                                <h6 class="fw-bold text-danger mb-0"><?= formatRupiah($memberDuesBreakdown['summary']['shortfall']) ?></h6>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
    </div> <?php else: ?>
    <div style="min-height: calc(100vh - 200px);"> 
        <head>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
        <style>
            .bg-success-gradient {
                background: linear-gradient(45deg, #28a745, #1d7e35);
            }
            .bg-danger-gradient {
                background: linear-gradient(45deg, #dc3545, #a32734);
            }
            .bg-primary-gradient {
                background: linear-gradient(45deg, #007bff, #0056b3);
            }
            .stat-card {
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }
            .stat-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            }
            .table-responsive-card {
                display: block;
                width: 100%;
                overflow-x: auto;
            }
            .responsive-amount {
                /* Tetapkan ukuran font default yang besar untuk desktop */
                font-size: 2rem; /* Ukuran default yang besar */
                white-space: nowrap; /* Mencegah angka pecah baris */
            }

            /* Untuk layar kecil (misalnya, handphone) */
            @media (max-width: 767.98px) {
                .responsive-amount {
                    font-size: 1.5rem; /* Kurangi ukuran font agar muat */
                }
            }

            /* Atau, jika ingin lebih spesifik untuk layar yang sangat sempit */
            @media (max-width: 575.98px) {
                .responsive-amount {
                    font-size: 1.25rem; /* Kurangi lagi agar muat di layar ekstra kecil */
                }
            }

            .card-header {
                background: linear-gradient(135deg, #0d6efd, #0056b3);
            }
            .fw-medium { font-weight: 500; }
            .fw-semibold { font-weight: 600; }
            .bg-light { background-color: #f8f9fa !important; }
            .btn-outline-primary {
                transition: all 0.3s ease;
            }
            .btn-outline-primary:hover {
                background-color: #0d6efd;
                color: #fff;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            }
            .rounded-pill { border-radius: 50px !important; }
            .text-truncate { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        </style>
    </head>
        <div class="card shadow-lg border-0 border-start border-5 border-primary mb-4 rounded-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h5 class="mb-0 text-dark d-flex align-items-center">
                            <i class="bi bi-people fs-5 me-2 flex-shrink-0 text-warning" style="font-size: 1.25rem;"></i>
                            <span>Daftar Iuran</span>
                        </h5>                   
                    </div>
                </div>
            </div>
        </div>
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

        // Pastikan $totalIuran adalah 0 jika tidak ada data ditemukan
        $totalIuran = $totalIuran ?? 0;

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
        <div class="row mb-4 g-3 d-flex align-items-stretch">
            <div class="col-12 col-sm-4">
                <div class="card text-white shadow-lg rounded-4 bg-success-gradient stat-card h-100">
                    <div class="card-body d-flex align-items-center">
                        <i class="bi bi-cash-stack fs-1 me-3 flex-shrink-0"></i>
                        <div class="flex-grow-1 overflow-hidden">
                            <h6 class="card-title mb-1 text-truncate">Total Pemasukan</h6>
                            <p class="card-text fs-4 fw-bold responsive-amount">
                                Rp<?= number_format($totalIuran, 0, ',', '.') ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div> 
        <div class="card mb-4 shadow rounded-4">
            <div class="card-header bg-primary text-white fw-bold">
                Progres pemasukan Iuran - Bulanan (Tahun <?= htmlspecialchars($selectedYear) ?>)
            </div>
            <div class="card-body">
                <canvas id="pemasukanBarChart" style="height:350px; width:100%;"></canvas>
            </div>
        </div>
        <div class="card shadow-lg border-0 border-start border-5 border-primary mb-4 rounded-4">
    <div class="card-body py-3">
        <div class="row g-3 align-items-center">

            <!-- Filter Tahun -->
            <div class="col-12 col-md-6 col-lg-4">
                <form action="" method="GET" class="mb-0">
                    <input type="hidden" name="tab" value="iuran">

                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        

                        <div class="input-group flex-grow-1" style="min-width: 150px;">
                            <span class="input-group-text bg-primary text-white border-0 rounded-start-pill">
                                <i class="fa-solid fa-calendar"></i>
                            </span>
                            <select class="form-select border-0 shadow-sm rounded-end-pill px-3 py-2"
                                    id="year-iuran"
                                    name="year"
                                    onchange="this.form.submit()"
                                    style="cursor:pointer;">
                                <?php
                                $currentYear = date('Y');
                                if (isset($conn) && $conn) {
                                    $resultYears = @$conn->query("SELECT DISTINCT YEAR(tanggal_bayar) AS year FROM iuran ORDER BY year DESC");
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
                                    foreach ($years as $year): ?>
                                        <option value="<?= htmlspecialchars($year) ?>" <?= ($year == $selectedYear) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($year) ?>
                                        </option>
                                    <?php endforeach;
                                } else {
                                    ?>
                                    <option value="<?= htmlspecialchars($currentYear) ?>" selected>
                                        <?= htmlspecialchars($currentYear) ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Search Anggota -->
            <div class="col-12 col-md-6 col-lg-8">
                <form action="" method="GET" class="mb-0">
                    <input type="hidden" name="tab" value="iuran">
                    <input type="hidden" name="year" value="<?= htmlspecialchars($selectedYear) ?>">

                    <div class="row g-2 align-items-center">
                        <!-- Input Group -->
                        <div class="col">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0 rounded-start-pill">
                                    <i class="fas fa-search text-primary"></i>
                                </span>
                                <input type="text" 
                                       id="searchInputIuran" 
                                       class="form-control border-0 shadow-sm px-3 py-2"
                                       placeholder="Cari anggota..." 
                                       name="search" 
                                       value="<?= htmlspecialchars($searchTerm ?? '') ?>">
                                
                                <!-- Tombol Submit -->
                                <button class="btn btn-primary px-3 px-sm-4 shadow-sm rounded-end-pill" type="submit">
                                    <span class="d-none d-sm-inline">Cari</span>
                                    <i class="fas fa-search d-inline d-sm-none"></i>
                                </button>
                            </div>
                        </div>

                        <?php if (!empty($searchTerm)): ?>
                            <!-- Tombol Hapus Pencarian -->
                            <div class="col-auto">
                                <a href="?tab=iuran&year=<?= htmlspecialchars($selectedYear) ?>" 
                                   class="btn btn-outline-danger rounded-pill d-flex align-items-center justify-content-center p-0"
                                   style="width: 42px; height: 42px;"
                                   title="Hapus Pencarian">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

        <div class="">
            <div class="row">
                <?php if (!empty($iuran)): ?>
                    <?php foreach ($iuran as $row): ?>
                    <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
        <div class="card h-100 shadow-lg border-0 rounded-4 overflow-hidden">
            
            <div class="card-header bg-gradient-primary text-white py-3 d-flex justify-content-between align-items-center">
                <span class="fw-semibold text-truncate">
                    <i class="bx bxs-wallet me-2"></i>
                    <a href="?tab=iuran&member_id=<?= htmlspecialchars($row['anggota_id']) ?>&year=<?= htmlspecialchars($selectedYear) ?>" 
                        class="text-white text-decoration-none">
                        <?= htmlspecialchars($row['nama_lengkap']) ?>
                    </a>
                </span>
                <?php 
                    // LOGIKA TETAP SAMA
                    $monthlyFee = DUES_MONTHLY_FEE;
                    $joinDate = new DateTime($row['bergabung_sejak']);
                    $startOfSelectedYear = new DateTime("{$selectedYear}-01-01");
                    $endOfSelectedYear = new DateTime("{$selectedYear}-12-31");

                    $currentDate = new DateTime();
                    $startDate = max($joinDate, $startOfSelectedYear);
                    $endDate = min($currentDate, $endOfSelectedYear);

                    $interval = $startDate->diff($endDate);
                    $months = ($interval->y * 12) + $interval->m;
                    if ($startDate <= $endDate) { $months += 1; }

                    $totalSeharusnya = $months * $monthlyFee;
                    $totalBayar = $row['total_bayar'] ?? 0;
                    // Asumsi function getPaymentStatus() tersedia
                    $statusData = getPaymentStatus($totalBayar, $totalSeharusnya);
                    $status = $statusData['status'];

                    // Badge warna sesuai status
                    $badgeClass = '';
                    if (strtolower($status) == 'lunas') { 
                        $badgeClass = 'bg-success text-white'; // hijau
                    } elseif (strtolower($status) == 'kurang') { 
                        $badgeClass = 'bg-warning text-dark'; // kuning
                    } elseif (strtolower($status) == 'belum bayar') { 
                        $badgeClass = 'bg-danger text-white'; // merah
                    } else { 
                        $badgeClass = 'bg-secondary text-white';
                    }
                ?>
                <span class="badge <?= $badgeClass ?> px-3 py-2 rounded-pill fw-semibold"><?= htmlspecialchars($status) ?></span>
            </div>

            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3 p-3 rounded bg-light shadow-sm">
                    <div class="d-flex align-items-center">
                        <i class="mdi mdi-currency-usd font-size-18 me-2 text-success"></i>
                        <span class="fw-medium text-dark">Total Dibayar</span>
                    </div>
                    <span class="fw-bold text-success"><?= htmlspecialchars(formatRupiah($totalBayar)) ?></span>
                </div>

                <div class="d-flex justify-content-between align-items-center p-3 rounded bg-light shadow-sm">
                    <div class="d-flex align-items-center">
                        <i class="mdi mdi-alert-circle-outline font-size-18 me-2 text-danger"></i>
                        <span class="fw-medium text-dark">Seharusnya</span>
                    </div>
                    <span class="fw-bold text-danger"><?= htmlspecialchars(formatRupiah($totalSeharusnya)) ?></span>
                </div>
            </div>

            <div class="card-footer bg-light border-0 pt-3">
                <a href="?tab=iuran&member_id=<?= htmlspecialchars($row['anggota_id']) ?>&year=<?= htmlspecialchars($selectedYear) ?>" 
                    class="btn btn-outline-primary btn-sm w-100 d-flex align-items-center justify-content-center gap-1 rounded-pill fw-semibold">
                    <i class="bx bx-receipt"></i>
                    <span>Detail Riwayat</span>
                </a>
            </div>
        </div>
    </div>

                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center text-muted mt-5">
                        <p>Tidak ada data iuran yang ditemukan.</p>
                    </div>
                <?php endif; ?>
            </div>
            <nav aria-label="Page navigation example">
                <ul class="pagination justify-content-center">
                    <?php 
                    // Pastikan $total_pages terdefinisi sebelum perulangan
                    $total_pages = $total_pages ?? 1; 
                    for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= (($page ?? 1) == $i) ? 'active' : '' ?>">
                            <a class="page-link" href="?tab=iuran&page=<?= htmlspecialchars($i) ?><?= !empty($searchTerm) ? '&search=' . htmlspecialchars($searchTerm) : '' ?>&year=<?= htmlspecialchars($selectedYear) ?>"><?= htmlspecialchars($i) ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
        
    </div> <?php endif; ?>
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