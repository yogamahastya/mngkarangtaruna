<?php
// Memuat semua logika dan variabel yang diperlukan
require_once 'process_data.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= ORGANIZATION_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.0/css/boxicons.min.css" integrity="sha512-pVCM5+SN2+qwj36KonHToF2p1oIvoU3bsqxphdOIWMYmgr4ZqD3t5DjKvvetKhXGc/ZG5REYTT6ltKfExEei/Q==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/MaterialDesign-Webfont/5.3.45/css/materialdesignicons.css" integrity="sha256-NAxhqDvtY0l4xn+YVa6WjAcmd94NNfttjNsDmNatFVc=" crossorigin="anonymous" referrerpolicy="no-referrer" />  
    <link rel="stylesheet" href="assets/css/styleindex.css"> 
    </head>
<body>

<div class="container py-5">
    <header class="hero-section text-center py-5 bg-light">
        <h1 class="display-4 mb-3">
            <i class="fa-solid fa-people-group me-3"></i><?= ORGANIZATION_NAME ?>
        </h1>
        <p class="fs-5 mb-4">Satu visi, satu aksi, untuk kemajuan bersama.</p>
        
        <div class="d-inline-flex align-items-center px-3 py-2 bg-primary text-white rounded shadow-sm">
            <i class="fa-solid fa-user-clock me-2"></i>
            <span class="me-2">online:</span>
            <span class="badge bg-warning text-dark" style="font-size: 0.8rem;">
                <?php
                    $file = "online_users.txt";

                    $online_users = file_exists($file) ? json_decode(file_get_contents($file), true) : [];

                    $current_time = time();
                    foreach ($online_users as $session => $last_time) {
                        if ($current_time - $last_time > 10) {
                            unset($online_users[$session]);
                        }
                    }

                    if(!session_id()) session_start();
                    $session_id = session_id();
                    $online_users[$session_id] = $current_time;

                    file_put_contents($file, json_encode($online_users));

                    echo count($online_users);
                ?>
            </span>
        </div>
    </header>
    <!--ADS-->
<script type="text/javascript" src="//www.highperformanceformat.com/11101be1f7cc04a774b7e08ac9bad0ad/invoke.js"></script>
    <script async="async" data-cfasync="false" src="//pl27652864.revenuecpmgate.com/1ca6f331b08ea102af96aa9eaf912d7e/invoke.js"></script>
    <div id="container-1ca6f331b08ea102af96aa9eaf912d7e"></div>
    <div class="mb-5">
        <ul class="nav nav-pills nav-justified nav-pills-custom" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link <?= ($active_tab == 'anggota') ? 'active' : '' ?>" href="?tab=anggota&year=<?= $selectedYear ?>">
                    <i class="fa-solid fa-users icon"></i> <span>Anggota</span>
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link <?= ($active_tab == 'absensi') ? 'active' : '' ?>" href="?tab=absensi">
                    <i class="fa-solid fa-user-check"></i> <span>Absensi</span>
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link <?= ($active_tab == 'kegiatan') ? 'active' : '' ?>" href="?tab=kegiatan&year=<?= $selectedYear ?>">
                    <i class="fa-solid fa-calendar-alt"></i> <span>Kegiatan</span>
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link <?= ($active_tab == 'keuangan') ? 'active' : '' ?>" href="?tab=keuangan&year=<?= $selectedYear ?>">
                    <i class="fa-solid fa-wallet"></i> <span>Keuangan</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($active_tab == 'iuran' || $active_tab == 'iuran17') ? 'active' : '' ?>" 
                    href="#" data-bs-toggle="modal" data-bs-target="#iuranModal">
                    <i class="fa-solid fa-receipt"></i> <span>Iuran</span>
                </a>
            </li>
        </ul>

        <div class="modal fade" id="iuranModal" tabindex="-1" aria-labelledby="iuranModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-md">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-header border-bottom-0 pt-4 px-4 pb-0">
                        <h4 class="modal-title fw-bolder text-dark" id="iuranModalLabel">Pilih Jenis Iuran Anda</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <p class="text-muted mb-4">Silakan pilih kategori iuran yang ingin Anda kelola atau lihat.</p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <a href="?tab=iuran" 
                                class="card card-hover h-100 text-decoration-none border-0 shadow-sm rounded-3 p-3 d-flex flex-column justify-content-between">
                                    <div class="card-body">
                                        <div class="icon-circle bg-primary-subtle text-primary mb-3">
                                            <i class="fa-solid fa-coins fa-2x"></i>
                                        </div>
                                        <h5 class="card-title text-dark fw-bold mb-1">Iuran Kas</h5>
                                        <p class="card-text text-muted small">Kelola iuran rutin kas bulanan.</p>
                                    </div>
                                    <div class="card-footer bg-transparent border-top-0 pt-0 text-end">
                                        <small class="text-primary fw-bold">Pilih <i class="fa-solid fa-arrow-right ms-1"></i></small>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="?tab=iuran17" 
                                class="card card-hover h-100 text-decoration-none border-0 shadow-sm rounded-3 p-3 d-flex flex-column justify-content-between">
                                    <div class="card-body">
                                        <div class="icon-circle bg-danger-subtle text-danger mb-3">
                                            <i class="fa-solid fa-star fa-2x"></i>
                                        </div>
                                        <h5 class="card-title text-dark fw-bold mb-1">Iuran Kemerdekaan 17-an</h5>
                                        <p class="card-text text-muted small">Lihat detail dan kontribusi untuk acara 17 Agustus.</p>
                                    </div>
                                    <div class="card-footer bg-transparent border-top-0 pt-0 text-end">
                                        <small class="text-danger fw-bold">Pilih <i class="fa-solid fa-arrow-right ms-1"></i></small>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content-card">
        <?php
        switch ($active_tab) {
            case 'anggota':
                include 'anggota.php';
                break;
            case 'absensi':
                include 'absensi.php';
                break;
            case 'kegiatan':
                include 'kegiatan.php';
                break;
            case 'keuangan':
                include 'keuangan.php';
                break;
            case 'iuran':
                include 'iuran.php';
                break;
            case 'iuran17':
                include 'iuran17.php';
                break;
            default:
                include 'anggota.php'; // Default tab
                break;
        }
        ?>
        <?php require_once 'footer.php'; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script> // Script deteksi scroll
let lastScrollTop = 0;
window.addEventListener("scroll", function() {
  let st = window.pageYOffset || document.documentElement.scrollTop;
  const nav = document.querySelector(".nav-pills-custom");

  if (st > lastScrollTop) {
    // scroll ke bawah → sembunyikan
    nav.classList.add("hide");
  } else {
    // scroll ke atas → tampilkan
    nav.classList.remove("hide");
  }
  lastScrollTop = st <= 0 ? 0 : st; // biar gak negatif
}, false);
</script>
</body>
</html>