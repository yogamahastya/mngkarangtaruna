<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<div class="card shadow-lg border-0 border-start border-5 border-primary mb-4 rounded-4">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h5 class="mb-0 text-dark d-flex align-items-center">
                    <i class="bi bi-currency-dollar fs-5 me-2 flex-shrink-0 text-success" style="font-size: 1.25rem;"></i>
                    <span>Kelola Data Keuangan</span>
                </h5>                   
            </div>
        </div>
    </div>
</div>
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
$types = "iii"; // default bind types

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

if(!empty($searchTerm)){
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param($types, ...$params);
}

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

<!-- Filter Tahun & Pencarian -->
<div class="card shadow-lg border-0 border-start border-5 border-primary mb-4 rounded-4">
    <div class="card-body py-3">
        <div class="row g-3 align-items-center">
            
            <!-- Filter Tahun -->
            <div class="col-12 col-md-6 col-lg-4">
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

            <!-- Search Transaksi -->
            <div class="col-12 col-md-6 col-lg-8">
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
                <i class="bi bi-cash-stack me-3 flex-shrink-0"></i>
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
                <i class="bi bi-cart-dash me-3 flex-shrink-0"></i>
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
                <i class="bi bi-wallet2 me-3 flex-shrink-0"></i>
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
    <div class="card-header bg-primary text-white fw-bold">Progres Keuangan Bulanan (Tahun <?= $selectedYear ?>)</div>
    <div class="card-body"><canvas id="keuanganBarChart" style="height:350px; width:100%;"></canvas></div>
</div>

<!-- Chart Pie -->
<div class="card mb-4 shadow rounded-4">
    <div class="card-header bg-secondary text-white fw-bold">Ringkasan Pemasukan vs Pengeluaran</div>
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
        <div class="transaction-card shadow bg-white p-3 rounded-4 d-flex flex-column">
            <div class="d-flex align-items-center mb-2">
                <div class="avatar-md me-3 <?= $isPemasukan?'bg-success-gradient text-white':'bg-danger-gradient text-white' ?>">
                    <i class="<?= $icon_class ?>"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-1"><?= htmlspecialchars($isPemasukan?'Pemasukan':'Pengeluaran') ?></h6>
                    <span class="badge <?= $badge_class ?>"><?= htmlspecialchars(ucfirst($row['deskripsi'])) ?></span>
                </div>
                <div class="ms-auto <?= $amount_color ?> amount">Rp<?= number_format($row['jumlah'],0,',','.') ?></div>
            </div>
            <small class="text-muted d-block"><i class="bi bi-calendar-range me-1"></i><?= htmlspecialchars($row['tanggal_transaksi']) ?></small>
            <?php if(!empty($row['dicatat_oleh_nama'])): ?>
                <small class="text-muted d-block"><i class="bi bi-person me-1"></i>Dicatat oleh: <?= htmlspecialchars($row['dicatat_oleh_nama']) ?></small>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; else: ?>
    <div class="col-12 text-center text-muted mt-5"><p>Tidak ada data keuangan untuk tahun ini.</p></div>
<?php endif; ?>
</div>

<!-- Pagination -->
<nav aria-label="Page navigation example">
    <ul class="pagination justify-content-center mt-4">
        <?php for($i=1;$i<=$total_pages;$i++): ?>
            <li class="page-item <?= ($page==$i)?'active':'' ?>"><a class="page-link" href="?tab=keuangan&year=<?= $selectedYear ?>&page=<?= $i ?><?= !empty($searchTerm)?'&search='.htmlspecialchars($searchTerm):'' ?>"><?= $i ?></a></li>
        <?php endfor; ?>
    </ul>
</nav>
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

