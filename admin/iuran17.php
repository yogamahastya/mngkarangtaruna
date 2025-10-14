<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>

<div style="min-height: calc(100vh - 200px);">
    <div class="card shadow-lg border-0 border-start border-5 border-primary mb-4 rounded-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h5 class="mb-0 text-dark d-flex align-items-center">
                        <i class="fa-solid fa-hand-holding-dollar me-2 text-primary" style="font-size: 1.25rem;"></i>
                        <span>Kelola Data Iuran 17 Agustus</span>
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
        <div class="row g-3 align-items-center">
            
            <!-- Filter Tahun -->
            <div class="col-12 col-md-4 col-lg-3">
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

            <!-- Form Pencarian & Tombol Tambah -->
            <div class="col-12 col-md-8 col-lg-9">
                <form action="" method="GET" class="mb-0">
                    <input type="hidden" name="tab" value="iuran17">
                    <input type="hidden" name="year" value="<?= htmlspecialchars($selectedYear) ?>">

                    <div class="row g-2 align-items-center">
                        <!-- Input Group -->
                        <div class="col">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0 rounded-start-pill">
                                    <i class="fas fa-search text-primary"></i>
                                </span>
                                <input type="text" 
                                       class="form-control border-0 shadow-sm px-3 py-2"
                                       placeholder="Cari iuran 17..."
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
                                <a href="?tab=iuran17&year=<?= htmlspecialchars($selectedYear) ?>"
                                   class="btn btn-outline-danger rounded-pill d-flex align-items-center justify-content-center p-0"
                                   style="width: 42px; height: 42px;"
                                   title="Hapus Pencarian">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                        <?php endif; ?>

                        <!-- Tombol Tambah Pembayaran -->
                        <div class="col-12 col-sm-auto">
                            <button type="button" 
                                    class="btn btn-primary rounded-pill px-4 py-2 shadow-sm w-100 w-sm-auto" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#addIuran17Modal">
                                <i class="fa-solid fa-plus-circle me-2"></i> 
                                <span class="d-none d-lg-inline">Tambah Pembayaran</span>
                                <span class="d-inline d-lg-none">Tambah</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

        </div>
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
    <nav aria-label="Page navigation example">
        <ul class="pagination justify-content-center">
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