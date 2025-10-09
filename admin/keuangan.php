<?php
// --- Data dasar & koneksi ---
$selectedYear = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$searchTerm = $_GET['search'] ?? '';

// Total Pemasukan
$stmtPemasukan = $conn->prepare("SELECT SUM(jumlah) FROM keuangan WHERE YEAR(tanggal_transaksi) = ? AND jenis_transaksi = 'pemasukan'");
$stmtPemasukan->bind_param("i", $selectedYear);
$stmtPemasukan->execute();
$stmtPemasukan->bind_result($totalPemasukan);
$stmtPemasukan->fetch();
$stmtPemasukan->close();

// Total Pengeluaran
$stmtPengeluaran = $conn->prepare("SELECT SUM(jumlah) FROM keuangan WHERE YEAR(tanggal_transaksi) = ? AND jenis_transaksi = 'pengeluaran'");
$stmtPengeluaran->bind_param("i", $selectedYear);
$stmtPengeluaran->execute();
$stmtPengeluaran->bind_result($totalPengeluaran);
$stmtPengeluaran->fetch();
$stmtPengeluaran->close();

$totalPemasukan = $totalPemasukan ?? 0;
$totalPengeluaran = $totalPengeluaran ?? 0;
$saldo = $totalPemasukan - $totalPengeluaran;

// --- Data bulanan chart ---
$pemasukanData = [];
$pengeluaranData = [];
$labels = [];

for($i=1;$i<=12;$i++){
    $date = new DateTime("$selectedYear-$i-01");
    $month = $date->format('m');
    $monthName = $date->format('M Y');

    $stmtPemasukanBulan = $conn->prepare("SELECT SUM(jumlah) FROM keuangan WHERE MONTH(tanggal_transaksi)=? AND YEAR(tanggal_transaksi)=? AND jenis_transaksi='pemasukan'");
    $stmtPemasukanBulan->bind_param("ii", $month, $selectedYear);
    $stmtPemasukanBulan->execute();
    $stmtPemasukanBulan->bind_result($monthlyPemasukan);
    $stmtPemasukanBulan->fetch();
    $stmtPemasukanBulan->close();
    $monthlyPemasukan = $monthlyPemasukan ?? 0;

    $stmtPengeluaranBulan = $conn->prepare("SELECT SUM(jumlah) FROM keuangan WHERE MONTH(tanggal_transaksi)=? AND YEAR(tanggal_transaksi)=? AND jenis_transaksi='pengeluaran'");
    $stmtPengeluaranBulan->bind_param("ii", $month, $selectedYear);
    $stmtPengeluaranBulan->execute();
    $stmtPengeluaranBulan->bind_result($monthlyPengeluaran);
    $stmtPengeluaranBulan->fetch();
    $stmtPengeluaranBulan->close();
    $monthlyPengeluaran = $monthlyPengeluaran ?? 0;

    if($monthlyPemasukan>0 || $monthlyPengeluaran>0){
        $pemasukanData[] = $monthlyPemasukan;
        $pengeluaranData[] = $monthlyPengeluaran;
        $labels[] = $monthName;
    }
}

$pemasukanDataJson = json_encode($pemasukanData);
$pengeluaranDataJson = json_encode($pengeluaranData);
$labelsJson = json_encode($labels);

// --- Ambil data transaksi beserta nama pencatat ---
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 9;
$offset = ($page-1)*$limit;

$whereSearch = "";
$params = [$selectedYear, $limit, $offset];
$types = "iii";

if(!empty($searchTerm)){
    $whereSearch = " AND k.deskripsi LIKE ? ";
    $searchLike = "%$searchTerm%";
    $types = "isii";
    $params = [$selectedYear, $searchLike, $limit, $offset];
}

$sqlKeuangan = "SELECT k.*, a.nama_lengkap AS dicatat_oleh_nama 
                FROM keuangan k 
                LEFT JOIN anggota a ON k.dicatat_oleh_id = a.id 
                WHERE YEAR(k.tanggal_transaksi)=? $whereSearch 
                ORDER BY k.tanggal_transaksi DESC 
                LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sqlKeuangan);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$keuangan = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Hitung total page
$countQuery = "SELECT COUNT(*) FROM keuangan k WHERE YEAR(k.tanggal_transaksi)=? $whereSearch";
$stmtCount = $conn->prepare($countQuery);
if(!empty($searchTerm)){
    $stmtCount->bind_param("is", $selectedYear, $searchLike);
} else {
    $stmtCount->bind_param("i", $selectedYear);
}
$stmtCount->execute();
$stmtCount->bind_result($totalRows);
$stmtCount->fetch();
$stmtCount->close();
$total_pages = ceil($totalRows/$limit);
?>

<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* Gradient Cards */
        .bg-success-gradient { background: linear-gradient(135deg, #28a745, #1c7c33); }
        .bg-danger-gradient { background: linear-gradient(135deg, #dc3545, #a22734); }
        .bg-primary-gradient { background: linear-gradient(135deg, #007bff, #0056b3); }

        /* Card Hover */
        .stat-card, .transaction-card { transition: transform 0.3s ease, box-shadow 0.3s ease; border-radius: 12px; }
        .stat-card:hover, .transaction-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.15); }

        /* Responsive Amount */
        .responsive-amount { font-size: 2rem; white-space: nowrap; }
        @media (max-width: 767.98px) { .responsive-amount { font-size: 1.5rem; } }
        @media (max-width: 575.98px) { .responsive-amount { font-size: 1.25rem; } }

        /* Avatar */
        .avatar-md { width: 50px; height: 50px; display:flex; align-items:center; justify-content:center; border-radius:50%; font-size:1.25rem; }

        /* Badges */
        .badge-soft-success { background-color: rgba(40,167,69,0.2); color: #28a745; font-weight:500; }
        .badge-soft-danger { background-color: rgba(220,53,69,0.2); color: #dc3545; font-weight:500; }

        /* Charts */
        .card-header { font-weight:600; }

        /* Pagination */
        .pagination .page-item.active .page-link { background-color:#007bff; border-color:#007bff; }
    </style>
</head>

<!-- Filter Tahun & Pencarian dengan Tombol Tambah -->
<div class="card shadow-lg border-0 border-start border-5 border-primary mb-4 rounded-4">
    <div class="card-body py-3">
        <div class="row g-3 align-items-center">
            
            <!-- Filter Tahun -->
            <div class="col-12 col-md-4 col-lg-3 order-1">
                <div class="input-group">
                    <span class="input-group-text bg-primary text-white border-0 rounded-start-pill">
                        <i class="fa-solid fa-calendar-alt"></i>
                    </span>
                    <select class="form-select border-0 shadow-sm rounded-end-pill px-3 py-2"
                            onchange="window.location.href='?tab=keuangan&year='+this.value+'<?= !empty($searchTerm) ? '&search='.urlencode($searchTerm) : '' ?>'">
                        <?php
                        $minYearQuery = "SELECT MIN(YEAR(tanggal_transaksi)) AS min_year FROM keuangan";
                        $minYearResult = $conn->query($minYearQuery);
                        $minYearRow = $minYearResult->fetch_assoc();
                        $minYear = $minYearRow['min_year'] ?? date('Y');
                        for ($year = date('Y'); $year >= $minYear; $year--): ?>
                            <option value="<?= $year ?>" <?= $year == $selectedYear ? 'selected' : '' ?>>
                                <?= $year ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>

            <!-- Tombol Tambah Transaksi -->
            <div class="col-12 col-md-auto order-3 order-md-2">
                <button type="button" 
                        class="btn btn-primary rounded-pill px-4 py-2 shadow-sm w-100 w-md-auto" 
                        data-bs-toggle="modal" 
                        data-bs-target="#addKeuanganModal">
                    <i class="fa-solid fa-plus-circle me-2"></i> 
                    <span class="d-none d-md-inline">Tambah</span>
                    <span class="d-inline d-md-none">Tambah Transaksi</span>
                </button>
            </div>

            <!-- Form Pencarian -->
            <div class="col-12 col-md order-2 order-md-3">
                <form action="" method="GET" class="mb-0">
                    <input type="hidden" name="tab" value="keuangan">
                    <input type="hidden" name="year" value="<?= $selectedYear ?>">

                    <div class="row g-2 align-items-center">
                        <!-- Input Group -->
                        <div class="col">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0 rounded-start-pill">
                                    <i class="fas fa-search text-primary"></i>
                                </span>
                                <input type="text" 
                                       class="form-control border-0 shadow-sm px-3 py-2" 
                                       placeholder="Cari transaksi..." 
                                       name="search" 
                                       value="<?= htmlspecialchars($searchTerm) ?>">
                                
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
                                <a href="?tab=keuangan&year=<?= $selectedYear ?>" 
                                   class="btn btn-outline-danger rounded-pill d-flex align-items-center justify-content-center p-0"
                                   style="width: 42px; height: 42px;"
                                   title="Hapus Pencarian">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                        <div class="col-12 col-sm-auto">
                            <button type="button" 
                                    class="btn btn-primary rounded-pill px-4 py-2 shadow-sm w-100 w-sm-auto" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#addIuranModal">
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

<!-- Statistik -->
<div class="row mb-4 g-3 d-flex align-items-stretch">
    <div class="col-12 col-sm-4">
        <div class="card text-white shadow-lg stat-card h-100 bg-success-gradient">
            <div class="card-body d-flex align-items-center">
                <i class="bi bi-cash-stack me-3 flex-shrink-0" style="font-size: 2.5rem;"></i>
                <div class="flex-grow-1 overflow-hidden">
                    <h6 class="card-title mb-1 text-truncate">Total Pemasukan</h6>
                    <p class="card-text fs-4 fw-bold responsive-amount">Rp<?= number_format($totalPemasukan,0,',','.') ?></p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-4">
        <div class="card text-white shadow-lg stat-card h-100 bg-danger-gradient">
            <div class="card-body d-flex align-items-center">
                <i class="bi bi-cart-dash me-3 flex-shrink-0" style="font-size: 2.5rem;"></i>
                <div class="flex-grow-1 overflow-hidden">
                    <h6 class="card-title mb-1 text-truncate">Total Pengeluaran</h6>
                    <p class="card-text fs-4 fw-bold responsive-amount">Rp<?= number_format($totalPengeluaran,0,',','.') ?></p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-4">
        <div class="card text-white shadow-lg stat-card h-100 bg-primary-gradient">
            <div class="card-body d-flex align-items-center">
                <i class="bi bi-wallet2 me-3 flex-shrink-0" style="font-size: 2.5rem;"></i>
                <div class="flex-grow-1 overflow-hidden">
                    <h6 class="card-title mb-1 text-truncate">Sisa Saldo</h6>
                    <p class="card-text fs-4 fw-bold responsive-amount">Rp<?= number_format($saldo,0,',','.') ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart Bulanan -->
<div class="card mb-4 shadow rounded-4">
    <div class="card-header bg-primary text-white fw-bold rounded-top-4 py-3">
        <i class="fas fa-chart-bar me-2"></i>Progres Keuangan Bulanan (Tahun <?= $selectedYear ?>)
    </div>
    <div class="card-body"><canvas id="keuanganBarChart" style="height:350px; width:100%;"></canvas></div>
</div>

<!-- Chart Pie -->
<div class="card mb-4 shadow rounded-4">
    <div class="card-header bg-secondary text-white fw-bold rounded-top-4 py-3">
        <i class="fas fa-chart-pie me-2"></i>Ringkasan Pemasukan vs Pengeluaran
    </div>
    <div class="card-body text-center"><canvas id="ringkasanPie" style="max-height:280px;"></canvas></div>
</div>

<!-- Daftar Transaksi -->
<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">
<?php if(count($keuangan)>0): foreach($keuangan as $row):
    $isPemasukan = ($row['jenis_transaksi']=='pemasukan');
    $badge_class = $isPemasukan?'badge-soft-success':'badge-soft-danger';
    $icon_class = $isPemasukan?'bi bi-cash-stack':'bi bi-cart-dash';
    $amount_color = $isPemasukan?'text-success':'text-danger';
?>
    <div class="col">
        <div class="transaction-card shadow bg-white p-3 rounded-4 d-flex flex-column h-100">
            <div class="d-flex align-items-start mb-2">
                <div class="avatar-md me-3 <?= $isPemasukan?'bg-success-gradient text-white':'bg-danger-gradient text-white' ?>">
                    <i class="<?= $icon_class ?>"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-1"><?= htmlspecialchars($isPemasukan?'Pemasukan':'Pengeluaran') ?></h6>
                    <span class="badge <?= $badge_class ?>"><?= htmlspecialchars(ucfirst($row['deskripsi'])) ?></span>
                </div>
                
                <!-- Dropdown Menu -->
                <div class="dropdown ms-2">
                    <a class="text-muted" href="#" role="button" data-bs-toggle="dropdown" style="text-decoration: none; cursor: pointer;">
                        <i class="fas fa-ellipsis-v"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item edit-btn" href="#" 
                           data-bs-toggle="modal" 
                           data-bs-target="#editKeuanganModal" 
                           data-id="<?= $row['id'] ?>" 
                           data-jenis="<?= $row['jenis_transaksi'] ?>" 
                           data-jumlah="<?= $row['jumlah'] ?>" 
                           data-deskripsi="<?= $row['deskripsi'] ?>" 
                           data-tanggal="<?= $row['tanggal_transaksi'] ?>">
                            <i class="bx bx-edit me-1"></i> Edit
                        </a>
                        <form action="" method="POST" class="d-inline">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="tab" value="keuangan">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                <i class="bx bx-trash me-1"></i> Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="<?= $amount_color ?> amount fw-bold fs-5 mb-2">
                Rp<?= number_format($row['jumlah'],0,',','.') ?>
            </div>
            
            <small class="text-muted d-block"><i class="bi bi-calendar-range me-1"></i><?= htmlspecialchars($row['tanggal_transaksi']) ?></small>
            <?php if(!empty($row['dicatat_oleh_nama'])): ?>
                <small class="text-muted d-block"><i class="bi bi-person me-1"></i>Dicatat oleh: <?= htmlspecialchars($row['dicatat_oleh_nama']) ?></small>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; else: ?>
    <div class="col-12 text-center text-muted mt-5">
        <i class="fas fa-receipt fa-3x mb-3 opacity-50"></i>
        <p>Tidak ada data keuangan untuk tahun ini.</p>
    </div>
<?php endif; ?>
</div>

<!-- Pagination -->
<?php if($total_pages > 1): ?>
<nav aria-label="Page navigation example">
    <ul class="pagination justify-content-center mt-4">
        <?php for($i=1;$i<=$total_pages;$i++): ?>
            <li class="page-item <?= ($page==$i)?'active':'' ?>">
                <a class="page-link" href="?tab=keuangan&year=<?= $selectedYear ?>&page=<?= $i ?><?= !empty($searchTerm)?'&search='.htmlspecialchars($searchTerm):'' ?>">
                    <?= $i ?>
                </a>
            </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>

<!-- Chart JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded",()=>{

    // Pie Chart
    new Chart(document.getElementById('ringkasanPie'),{
        type:'doughnut',
        data:{
            labels:['Pemasukan','Pengeluaran'],
            datasets:[{ data:[<?= $totalPemasukan ?>,<?= $totalPengeluaran ?>], backgroundColor:['#28a745','#dc3545'] }]
        },
        options:{
            plugins:{ tooltip:{ callbacks:{ label: ctx=>ctx.label+": Rp"+ctx.raw.toLocaleString("id-ID") } } },
            responsive:true
        }
    });

    // Bar Chart with Gradient
    const ctx = document.getElementById('keuanganBarChart').getContext('2d');
    const gradientGreen = ctx.createLinearGradient(0,0,0,400);
    gradientGreen.addColorStop(0,'rgba(40,167,69,0.8)');
    gradientGreen.addColorStop(1,'rgba(40,167,69,0.2)');
    const gradientRed = ctx.createLinearGradient(0,0,0,400);
    gradientRed.addColorStop(0,'rgba(220,53,69,0.8)');
    gradientRed.addColorStop(1,'rgba(220,53,69,0.2)');

    new Chart(ctx,{
        type:'bar',
        data:{
            labels: <?= $labelsJson ?>,
            datasets:[
                { label:'Pemasukan', data: <?= $pemasukanDataJson ?>, backgroundColor: gradientGreen, borderColor:'#28a745', borderWidth:1 },
                { label:'Pengeluaran', data: <?= $pengeluaranDataJson ?>, backgroundColor: gradientRed, borderColor:'#dc3545', borderWidth:1 }
            ]
        },
        options:{
            responsive:true,
            maintainAspectRatio:false,
            scales:{
                x:{ title:{ display:true, text:'Bulan' } },
                y:{ beginAtZero:true, title:{ display:true, text:'Jumlah (Rp)' }, ticks:{ callback: v=>'Rp'+v.toLocaleString('id-ID') } }
            },
            plugins:{
                legend:{ display:true, position:'top' },
                tooltip:{ callbacks:{ label: function(ctx){ return ctx.dataset.label + ': Rp'+ctx.raw.toLocaleString('id-ID'); } } }
            }
        }
    });
});
</script>