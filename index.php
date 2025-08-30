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
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="container py-5">
    <header class="hero-section">
    <h1 class="display-4"><i class="fa-solid fa-people-group me-3"></i><?= ORGANIZATION_NAME ?></h1>
    <p class="fs-5 mt-3">Satu visi, satu aksi, untuk kemajuan bersama.</p>
</header>
    <div class="mb-5">
        <ul class="nav nav-pills nav-justified nav-pills-custom" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link <?= ($active_tab == 'anggota') ? 'active' : '' ?>" href="?tab=anggota&year=<?= $selectedYear ?>">
                    <i class="fa-solid fa-users icon me-2"></i> Anggota
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link <?= ($active_tab == 
'absensi') ? 'active' : '' ?>" href="?tab=absensi">
                    <i class="fa-solid fa-user-check me-2"></i> Absensi
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link <?= ($active_tab == 'kegiatan') ?
'active' : '' ?>" href="?tab=kegiatan&year=<?= $selectedYear ?>">
                    <i class="fa-solid fa-calendar-alt icon me-2"></i> Kegiatan
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link <?= ($active_tab == 'keuangan') ?
'active' : '' ?>" href="?tab=keuangan&year=<?= $selectedYear ?>">
                    <i class="fa-solid fa-wallet icon me-2"></i> Keuangan
                </a>
            </li>
            <li class="nav-item dropdown" role="presentation">
                <a class="nav-link dropdown-toggle <?= ($active_tab == 'iuran' || $active_tab == 'iuran17') ? 'active' : '' ?>" href="#" id="iuranDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-receipt icon me-2"></i> Iuran
                </a>
                <ul class="dropdown-menu" aria-labelledby="iuranDropdown">
                    <li>
                        <a class="dropdown-item <?= ($active_tab == 'iuran') ? 'active' : '' ?>" href="?tab=iuran">
                            Iuran Kas
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item <?= ($active_tab == 'iuran17') ? 'active' : '' ?>" href="?tab=iuran17">
                            Iuran 17
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
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
        <footer class="text-center mt-5">
        <div class="copyright-box">
            <p class="copyright-text" style="font-size: 0.8rem;">
                &copy; <?= date('Y') ?> <a href="http://nuxera.my.id" target="_blank" style="color: inherit; text-decoration: none;">nuxera.my.id</a>
            </p>
        </div>
    </footer>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>