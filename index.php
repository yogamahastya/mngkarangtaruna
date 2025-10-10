<?php
// === HANDLE AJAX REQUEST UNTUK UPDATE ONLINE COUNT ===
if (isset($_GET['ajax_update_online'])) {
    header('Content-Type: application/json');
    
    if (!session_id()) {
        session_start();
    }
    
    $file = "online_users.txt";
    $lockFile = "online_users.lock";
    $user_identifier = session_id() . '_' . md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);
    
    $fp = fopen($lockFile, 'w');
    if (flock($fp, LOCK_EX)) {
        $online_users = [];
        if (file_exists($file)) {
            $data = file_get_contents($file);
            $online_users = json_decode($data, true);
            if (!is_array($online_users)) {
                $online_users = [];
            }
        }
        
        $current_time = time();
        foreach ($online_users as $identifier => $last_time) {
            if ($current_time - $last_time > 30) {
                unset($online_users[$identifier]);
            }
        }
        
        $online_users[$user_identifier] = $current_time;
        file_put_contents($file, json_encode($online_users));
        flock($fp, LOCK_UN);
    }
    fclose($fp);
    
    echo json_encode([
        'count' => count($online_users),
        'status' => 'success'
    ]);
    exit;
}

require_once 'process_data.php';

// Logika tab & tahun aktif
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'anggota';
$selectedYear = isset($_GET['year']) ? $_GET['year'] : date('Y');

// === LOGIKA USER ONLINE ===
if (!session_id()) {
    session_start();
}

$file = "online_users.txt";
$lockFile = "online_users.lock";

$user_identifier = session_id() . '_' . md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);

$fp = fopen($lockFile, 'w');
if (flock($fp, LOCK_EX)) {
    $online_users = [];
    if (file_exists($file)) {
        $data = file_get_contents($file);
        $online_users = json_decode($data, true);
        if (!is_array($online_users)) {
            $online_users = [];
        }
    }

    $current_time = time();
    
    foreach ($online_users as $identifier => $last_time) {
        if ($current_time - $last_time > 30) {
            unset($online_users[$identifier]);
        }
    }

    $online_users[$user_identifier] = $current_time;
    file_put_contents($file, json_encode($online_users));
    
    flock($fp, LOCK_UN);
}
fclose($fp);

$online_count = count($online_users);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= ORGANIZATION_NAME ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="p-3">

<!-- HEADER -->
<header class="d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
        <div class="logo-icon">KT</div>
        <div class="header-title">
            <h1><?= ORGANIZATION_NAME ?></h1>
            <p class="d-none d-sm-block">Satu visi, satu aksi, untuk kemajuan bersama</p>
        </div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <div class="online-status">
            <div class="online-icon">
                <i class="fa-solid fa-users"></i>
            </div>
            <span class="online-count" id="onlineCount"><?= $online_count ?></span>
        </div>
        <button class="menu-toggle d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu">
            <i class="fa-solid fa-bars"></i>
        </button>
    </div>
</header>

<div class="row g-4">
    <!-- SIDEBAR DESKTOP -->
    <aside class="col-lg-3 sidebar-desktop">
        <div class="bg-white p-4 rounded-4 shadow-sm">
            <nav>
                <ul class="nav flex-column gap-2 nav-pills-custom">
                    <li><a class="nav-link <?= ($active_tab == 'anggota') ? 'active' : '' ?>" href="?tab=anggota&year=<?= $selectedYear ?>"><i class="fa-solid fa-users me-2"></i> Anggota</a></li>
                    <li><a class="nav-link <?= ($active_tab == 'absensi') ? 'active' : '' ?>" href="?tab=absensi"><i class="fa-solid fa-calendar-check me-2"></i> Absensi</a></li>
                    <li><a class="nav-link <?= ($active_tab == 'kegiatan') ? 'active' : '' ?>" href="?tab=kegiatan&year=<?= $selectedYear ?>"><i class="fa-solid fa-calendar-alt me-2"></i> Kegiatan</a></li>
                    <li><a class="nav-link <?= ($active_tab == 'keuangan') ? 'active' : '' ?>" href="?tab=keuangan&year=<?= $selectedYear ?>"><i class="fa-solid fa-wallet me-2"></i> Keuangan</a></li>
                    <li><a class="nav-link <?= ($active_tab == 'iuran' || $active_tab == 'iuran17') ? 'active' : '' ?>" href="#" data-bs-toggle="modal" data-bs-target="#iuranModal"><i class="fa-solid fa-receipt me-2"></i> Iuran</a></li>
                </ul>
            </nav>
            <div class="mt-4 pt-4 border-top">
                <div class="stat-card text-center">
                    <h3 id="totalAnggotaDesktop"><?= $total_anggota ?></h3>
                    <p>Total Anggota Aktif <?= date('Y') ?></p>
                </div>
            </div>
        </div>
    </aside>

    <!-- KONTEN -->
    <section class="col-lg-9">
        <div class="content-card">
            <?php
            switch ($active_tab) {
                case 'anggota': include 'anggota.php'; break;
                case 'absensi': include 'absensi.php'; break;
                case 'kegiatan': include 'kegiatan.php'; break;
                case 'keuangan': include 'keuangan.php'; break;
                case 'iuran': include 'iuran.php'; break;
                case 'iuran17': include 'iuran17.php'; break;
            }
            ?>
             
        </div>
        <?php require_once 'footer.php'; ?>
    </section>
    
</div>

<!-- OFFCANVAS MENU MOBILE -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarMenu">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title fw-bold"><i class="fa-solid fa-bars me-2"></i>Menu</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <ul class="nav flex-column gap-2 nav-pills-custom mb-4">
            <li><a class="nav-link <?= ($active_tab == 'anggota') ? 'active' : '' ?>" href="?tab=anggota&year=<?= $selectedYear ?>"><i class="fa-solid fa-users me-2"></i> Anggota</a></li>
            <li><a class="nav-link <?= ($active_tab == 'absensi') ? 'active' : '' ?>" href="?tab=absensi"><i class="fa-solid fa-calendar-check me-2"></i> Absensi</a></li>
            <li><a class="nav-link <?= ($active_tab == 'kegiatan') ? 'active' : '' ?>" href="?tab=kegiatan&year=<?= $selectedYear ?>"><i class="fa-solid fa-calendar-alt me-2"></i> Kegiatan</a></li>
            <li><a class="nav-link <?= ($active_tab == 'keuangan') ? 'active' : '' ?>" href="?tab=keuangan&year=<?= $selectedYear ?>"><i class="fa-solid fa-wallet me-2"></i> Keuangan</a></li>
            <li><a class="nav-link <?= ($active_tab == 'iuran' || $active_tab == 'iuran17') ? 'active' : '' ?>" href="#" data-bs-toggle="modal" data-bs-target="#iuranModal"><i class="fa-solid fa-receipt me-2"></i> Iuran</a></li>
        </ul>
        <div class="stat-card text-center">
            <h3 id="totalAnggotaMobile"><?= $total_anggota ?></h3>
            <p>Total Anggota Aktif <?= date('Y') ?></p>
        </div>
    </div>
</div>

<!-- MODAL IURAN -->
<div class="modal fade" id="iuranModal" tabindex="-1" aria-labelledby="iuranModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pt-4 px-4 pb-0">
                <h4 class="modal-title fw-bolder text-dark" id="iuranModalLabel">Pilih Jenis Iuran Anda</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted mb-4">Silakan pilih kategori iuran yang ingin Anda kelola atau lihat.</p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <a href="?tab=iuran" class="card border-0 shadow-sm rounded-4 p-3 text-decoration-none">
                            <div class="icon-box text-primary mb-3"><i class="fa-solid fa-coins fa-2x"></i></div>
                            <h6 class="fw-bold mb-1">Iuran Kas</h6>
                            <p class="small text-muted mb-0">Kelola iuran rutin kas bulanan.</p>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="?tab=iuran17" class="card border-0 shadow-sm rounded-4 p-3 text-decoration-none">
                            <div class="icon-box text-danger mb-3"><i class="fa-solid fa-star fa-2x"></i></div>
                            <h6 class="fw-bold mb-1">Iuran 17-an</h6>
                            <p class="small text-muted mb-0">Lihat kontribusi acara 17 Agustus.</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Auto-refresh online count tanpa reload halaman -->
<script>
function updateOnlineCount() {
    fetch('?ajax_update_online=1')
        .then(response => response.json())
        .then(data => {
            // Update counter di header
            document.getElementById('onlineCount').textContent = data.count;
        })
        .catch(error => console.log('Error updating online count:', error));
}

// Update setiap 15 detik
setInterval(updateOnlineCount, 15000);

// Update saat halaman kembali aktif
document.addEventListener('visibilitychange', function() {
    if (!document.hidden) {
        updateOnlineCount();
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>