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
            font-size: 2rem;
            white-space: nowrap;
        }

        @media (max-width: 767.98px) {
            .responsive-amount {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 575.98px) {
            .responsive-amount {
                font-size: 1.25rem;
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

<div style="min-height: calc(100vh - 200px);">
    <h2 class="mb-4 text-primary"><i class="fa-solid fa-receipt me-2"></i>Kelola Data Iuran 17</h2>
    
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
    
    // Pastikan $totalIuran17 adalah 0 jika tidak ada data ditemukan
    $totalIuran17 = $totalIuran17 ?? 0;
    
    // Mengambil data pemasukan iuran bulanan untuk tahun yang dipilih
    $pemasukanData = [];
    $labels = [];
    
    // Loop dari Januari hingga Desember di tahun yang dipilih
    for ($i = 1; $i <= 12; $i++) {
        $date = new DateTime("$selectedYear-$i-01");
        $month = $date->format('m');
        $monthName = $date->format('M Y');
    
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
    
    <!-- Statistics Card -->
    <div class="row mb-4 g-3 d-flex align-items-stretch">
        <div class="col-12 col-sm-4">
            <div class="card text-white shadow-lg rounded-4 bg-success-gradient stat-card h-100">
                <div class="card-body d-flex align-items-center">
                    <i class="bi bi-cash-stack fs-1 me-3 flex-shrink-0"></i>
                    <div class="flex-grow-1 overflow-hidden">
                        <h6 class="card-title mb-1 text-truncate">Total Pemasukan</h6>
                        <p class="card-text fs-4 fw-bold responsive-amount">
                            Rp<?= number_format($totalIuran17, 0, ',', '.') ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Chart Card -->
    <div class="card mb-4 shadow rounded-4">
        <div class="card-header bg-primary text-white fw-bold">
            Progres pemasukan Iuran 17 - Bulanan (Tahun <?= htmlspecialchars($selectedYear) ?>)
        </div>
        <div class="card-body">
            <canvas id="pemasukan17BarChart" style="height:350px; width:100%;"></canvas>
        </div>
    </div>
    
    <!-- Filter Tahun, Pencarian, dan Tombol Tambah Iuran 17 -->
<div class="card shadow-lg border-0 border-start border-5 border-primary mb-4 rounded-4">
    <div class="card-body py-3">

        <!-- Desktop Layout -->
        <div class="d-none d-md-block">
            <div class="row align-items-center">
                
                <!-- Filter Tahun -->
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-primary text-white border-0 rounded-start-pill">
                            <i class="fa-solid fa-calendar-alt"></i>
                        </span>
                        <select class="form-select border-0 shadow-sm rounded-end-pill px-3 py-2"
                                onchange="window.location.href='?tab=iuran17&year='+this.value+'<?= !empty($searchTerm) ? '&search='.urlencode($searchTerm) : '' ?>'">
                            <?php
                            $currentYear = date('Y');
                            if (isset($conn) && $conn) {
                                $resultYears = @$conn->query("SELECT DISTINCT YEAR(tanggal_bayar) AS year FROM iuran17 ORDER BY year DESC");
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
                            } else { ?>
                                <option value="<?= htmlspecialchars($currentYear) ?>" selected><?= htmlspecialchars($currentYear) ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <!-- Search Iuran 17 -->
                <div class="col-md-6">
                    <form action="" method="GET" class="d-flex w-100">
                        <input type="hidden" name="tab" value="iuran17">
                        <input type="hidden" name="year" value="<?= htmlspecialchars($selectedYear) ?>">

                        <div class="input-group">
                            <span class="input-group-text bg-light border-0 rounded-start-pill">
                                <i class="fas fa-search text-primary"></i>
                            </span>
                            <input type="text" class="form-control border-0 shadow-sm px-3 py-2 rounded-end"
                                   placeholder="Cari iuran 17..."
                                   name="search"
                                   value="<?= htmlspecialchars($searchTerm ?? '') ?>">
                        </div>
                    </form>
                </div>

                <!-- Tombol Tambah -->
                <div class="col-md-3 text-end">
                    <button type="button" class="btn btn-primary rounded-pill px-4 py-2 shadow-sm w-100" data-bs-toggle="modal" data-bs-target="#addIuran17Modal">
                        <i class="fa-solid fa-plus-circle me-2"></i> Tambah Pembayaran
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Layout -->
        <div class="d-md-none">

            <!-- Filter Tahun -->
            <div class="mb-3">
                <div class="input-group">
                    <span class="input-group-text bg-primary text-white border-0 rounded-start-pill">
                        <i class="fa-solid fa-calendar-alt"></i>
                    </span>
                    <select class="form-select border-0 shadow-sm rounded-end-pill px-3 py-2"
                            onchange="window.location.href='?tab=iuran17&year='+this.value+'<?= !empty($searchTerm) ? '&search='.urlencode($searchTerm) : '' ?>'">
                        <?php foreach ($years as $year): ?>
                            <option value="<?= htmlspecialchars($year) ?>" <?= ($year == $selectedYear) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($year) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Tombol Tambah -->
            <div class="mb-3">
                <button type="button" class="btn btn-primary rounded-pill w-100 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#addIuran17Modal">
                    <i class="fa-solid fa-plus-circle me-2"></i> Tambah Pembayaran
                </button>
            </div>

            <!-- Search -->
            <form action="" method="GET">
                <input type="hidden" name="tab" value="iuran17">
                <input type="hidden" name="year" value="<?= htmlspecialchars($selectedYear) ?>">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0 rounded-start-pill">
                        <i class="fas fa-search text-primary"></i>
                    </span>
                    <input type="text" class="form-control border-0 shadow-sm px-3 py-2"
                           placeholder="Cari iuran 17..."
                           name="search"
                           value="<?= htmlspecialchars($searchTerm ?? '') ?>">
                    <button class="btn btn-primary shadow-sm rounded-end-pill" type="submit" style="width: 50px;">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>

        <?php if (!empty($searchTerm)): ?>
            <div class="text-center mt-3">
                <a href="?tab=iuran17&year=<?= htmlspecialchars($selectedYear) ?>"
                   class="btn btn-outline-danger rounded-pill px-3 py-2">
                    <i class="fas fa-times me-2"></i> Hapus Pencarian
                </a>
            </div>
        <?php endif; ?>

    </div>
</div>

    
    <!-- Data Cards -->
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
                    <div class="card h-100 shadow-lg border-0 rounded-4 overflow-hidden">
                        
                        <div class="card-header bg-gradient-primary text-white py-3 d-flex justify-content-between align-items-center">
                            <span class="fw-semibold text-truncate">
                                <i class="bx bxs-wallet me-2"></i>
                                <a href="?tab=iuran17&member_id=<?= htmlspecialchars($row['anggota_id']) ?>&year=<?= htmlspecialchars($selectedYear) ?>" 
                                    class="text-white text-decoration-none">
                                    <?= htmlspecialchars($anggotaName) ?>
                                </a>
                            </span>
                            <span class="badge <?= $badgeClass ?> px-3 py-2 rounded-pill fw-semibold"><?= htmlspecialchars($status) ?></span>
                        </div>
    
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3 p-3 rounded bg-light shadow-sm">
                                <div class="d-flex align-items-center">
                                    <i class="mdi mdi-calendar font-size-18 me-2 text-primary"></i>
                                    <span class="fw-medium text-dark">Tanggal Bayar</span>
                                </div>
                                <span class="fw-bold text-dark"><?= htmlspecialchars($row['tanggal_bayar']) ?></span>
                            </div>
    
                            <div class="d-flex justify-content-between align-items-center p-3 rounded bg-light shadow-sm">
                                <div class="d-flex align-items-center">
                                    <i class="mdi mdi-currency-usd font-size-18 me-2 text-success"></i>
                                    <span class="fw-medium text-dark">Jumlah</span>
                                </div>
                                <span class="fw-bold text-success">Rp<?= htmlspecialchars(number_format($row['jumlah_bayar'], 0, ',', '.')) ?></span>
                            </div>
                        </div>
    
                        <div class="card-footer bg-light border-0 pt-3 d-flex gap-2">
                            <a href="#" 
                                class="btn btn-outline-primary btn-sm flex-fill rounded-pill fw-semibold edit-btn"
                                data-bs-toggle="modal" 
                                data-bs-target="#editIuran17Modal" 
                                data-id="<?= $row['id'] ?>" 
                                data-anggota-id="<?= $row['anggota_id'] ?>" 
                                data-tanggal="<?= $row['tanggal_bayar'] ?>" 
                                data-jumlah="<?= $row['jumlah_bayar'] ?>" 
                                data-keterangan="<?= $row['keterangan'] ?>">
                                <i class="bx bx-edit"></i> Edit
                            </a>
                            <form action="" method="POST" class="flex-fill">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="tab" value="iuran17">
                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                <button type="submit" class="btn btn-outline-danger btn-sm w-100 rounded-pill fw-semibold" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                    <i class="bx bx-trash"></i> Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center text-muted mt-5">
                <i class="fa-solid fa-box-open fa-3x mb-3 d-block"></i>
                <p>Tidak ada data iuran 17 yang ditemukan.</p>
            </div>
        <?php endif; ?>
    </div>
    
     <!-- Pagination -->
    <nav aria-label="Page navigation example" class="mt-4 mb-4">
        <ul class="pagination justify-content-center flex-wrap gap-1">
            <?php 
            // Pastikan $total_pages terdefinisi sebelum perulangan
            $total_pages = $total_pages ?? 1;
            $current_page = $page ?? 1;
            
            // Logika Smart Pagination
            $show_pages = [];
            
            if ($total_pages <= 7) {
                // Jika total halaman <= 7, tampilkan semua
                for ($i = 1; $i <= $total_pages; $i++) {
                    $show_pages[] = $i;
                }
            } else {
                // Tampilkan: 1 ... current-1 current current+1 ... last
                $show_pages[] = 1; // Selalu tampilkan halaman pertama
                
                if ($current_page > 3) {
                    $show_pages[] = '...';
                }
                
                // Tampilkan range di sekitar current page
                for ($i = max(2, $current_page - 1); $i <= min($total_pages - 1, $current_page + 1); $i++) {
                    $show_pages[] = $i;
                }
                
                if ($current_page < $total_pages - 2) {
                    $show_pages[] = '...';
                }
                
                if ($total_pages > 1) {
                    $show_pages[] = $total_pages; // Selalu tampilkan halaman terakhir
                }
            }
            
            foreach ($show_pages as $page_num): 
                if ($page_num === '...'): ?>
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                <?php else: ?>
                    <li class="page-item <?= ($current_page == $page_num) ? 'active' : '' ?>">
                        <a class="page-link" href="?tab=iuran17&page=<?= htmlspecialchars($page_num) ?><?= !empty($searchTerm) ? '&search=' . htmlspecialchars($searchTerm) : '' ?>&year=<?= htmlspecialchars($selectedYear) ?>"><?= htmlspecialchars($page_num) ?></a>
                    </li>
                <?php endif;
            endforeach; ?>
        </ul>
    </nav>
    
</div>
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