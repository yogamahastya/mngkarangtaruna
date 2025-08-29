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
            <li class="nav-item" role="presentation">
                <a class="nav-link <?= ($active_tab == 'iuran') ?
'active' : '' ?>" href="?tab=iuran">
                    <i class="fa-solid fa-receipt icon me-2"></i> Iuran
                </a>
            </li>
        </ul>
    </div>
    <div class="content-card">
        <?php if ($active_tab == 'anggota'): ?>
            <h2 class="mb-4 text-primary"><i class="fa-solid fa-user-group me-2"></i>Data Anggota</h2>
            <div class="row mb-3 align-items-center gy-2">
                <div class="col-12 col-md-4">
                    <p class="fs-5 mb-0">Total Anggota: <span class="badge bg-primary"><?= $total_anggota ?></span></p>
                </div>
                <div class="col-12 col-md-8 text-md-end">
                    <form action="" method="GET" class="d-flex justify-content-start justify-content-md-end">
                        <input type="hidden" name="tab" value="anggota">
                        <div class="input-group search-input-desktop">
                            <input type="text" class="form-control" placeholder="Cari anggota..." name="search" value="<?= htmlspecialchars($searchTerm) ?>">
                            <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search"></i></button>
                            <?php if (!empty($searchTerm)): ?>
                                <a href="?tab=anggota" class="btn btn-outline-secondary" title="Hapus Pencarian"><i class="fas fa-times"></i></a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
            <style>
                /* CSS Kustom untuk mengontrol lebar di desktop */
                @media (min-width: 768px) {
                    .search-input-desktop {
                        max-width: 300px;
                    }
                }
            </style>
            <div class="table-responsive">
                <table class="table table-hover table-striped" id="anggotaTable">
                <?php if (count($anggota) > 0): ?>
                    <div class="row">
                    <?php foreach ($anggota as $row): ?>
                        <?php
                            // Tentukan kelas CSS badge berdasarkan nilai jabatan
                            $jabatan = htmlspecialchars($row['jabatan']);
                            $badge_class = 'badge '; // Kelas dasar untuk badge
                            switch (strtolower($jabatan)) {
                                case 'ketua':
                                    $badge_class .= 'ketua';
                                    break;
                                case 'wakil ketua': 
                                    $badge_class .= 'wakilketua';
                                    break;
                                case 'sekretaris': 
                                    $badge_class .= 'sekretaris';
                                    break;
                                case 'bendahara': 
                                    $badge_class .= 'bendahara';
                                    break;
                                case 'humas': 
                                    $badge_class .= 'humas';
                                    break;
                                default:
                                    $badge_class .= 'anggota'; // Kelas default jika jabatan tidak cocok
                                    break;
                            }
                        ?>
                        <div class="col-lg-4 col-md-6 col-sm-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div><img src="<?= $profile_image ?>" alt="<?= htmlspecialchars($row['nama_lengkap']) ?>" class="avatar-md rounded-circle img-thumbnail" /></div>
                                        <div class="flex-1 ms-3">
                                            <h5 class="font-size-16 mb-1"><a href="#" class="text-dark"><?= htmlspecialchars($row['nama_lengkap']) ?></a></h5>
                                            <span class="<?= $badge_class ?> mb-0"><?= $jabatan ?></span>
                                        </div>
                                    </div>
                                    <div class="mt-3 pt-1">
                                        <p class="text-muted mb-0"><i class="mdi mdi-calendar font-size-15 align-middle pe-2 text-primary"></i> Bergabung: <?= htmlspecialchars($row['bergabung_sejak']) ?></p>
                                        </div>
                                    </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted">Tidak ada data anggota.</div>
                <?php endif; ?>
                </table>
            </div>
            <nav aria-label="Page navigation example">
                <ul class="pagination justify-content-center">
                    <!--<li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?tab=anggota&page=<?= $page - 1 ?><?= !empty($searchTerm) ? '&search=' . htmlspecialchars($searchTerm) : '' ?>" tabindex="-1">Previous</a>
                    </li>-->
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                            <a class="page-link" href="?tab=anggota&page=<?= $i ?><?= !empty($searchTerm) ? '&search=' . htmlspecialchars($searchTerm) : '' ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                   <!-- <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?tab=anggota&page=<?= $page + 1 ?><?= !empty($searchTerm) ? '&search=' . htmlspecialchars($searchTerm) : '' ?>">Next</a>
                    </li>-->
                </ul>
            </nav>
        <?php elseif ($active_tab == 'absensi'): ?>
            <?php if ($attendanceMemberBreakdown): ?>
                <a href="?tab=absensi" class="btn btn-outline-primary mb-4">
                    <i class="fa-solid fa-arrow-left me-2"></i> Kembali ke Daftar Absensi
                </a>
                <h2 class="mb-4 text-primary"><i class="fa-solid fa-user me-2"></i>Riwayat Absensi <?= htmlspecialchars($attendanceMemberBreakdown['member']['nama_lengkap']) ?></h2>
                <div class="card detail-card mb-4">
                    <div class="card-body">
                        <h4 class="card-title fw-bold"><i class="fa-solid fa-user-circle me-2"></i><?= htmlspecialchars($attendanceMemberBreakdown['member']['nama_lengkap']) ?></h4>
                        <p class="card-text text-muted">
                            <i class="fa-solid fa-calendar me-2"></i>Bergabung Sejak: <?= htmlspecialchars($attendanceMemberBreakdown['member']['bergabung_sejak']) ?>
                        </p>
                    </div>
                </div>
                
                <div class="row mb-3 gy-2 align-items-center">
                    <div class="col-12">
                        <form action="" method="GET" class="d-flex align-items-center w-100">
                            <input type="hidden" name="tab" value="absensi">
                            <input type="hidden" name="member_id" value="<?= htmlspecialchars($attendanceMemberId) ?>">
                            <label for="year-absensi" class="form-label mb-0 me-2 fw-bold">Pilih Tahun:</label>
                            <select class="form-select w-auto" id="year-absensi" name="year_absensi" onchange="this.form.submit()">
                                <?php
                                $resultYears = $conn->query("SELECT DISTINCT YEAR(tanggal_absen) AS year FROM absensi WHERE anggota_id = {$attendanceMemberId} ORDER BY year DESC");
                                $years = [];
                                if ($resultYears) {
                                    while ($row = $resultYears->fetch_assoc()) {
                                        $years[] = $row['year'];
                                    }
                                }
                                $currentYear = date('Y');
                                if (!in_array($currentYear, $years)) {
                                    $years[] = $currentYear;
                                    rsort($years);
                                }
                                foreach ($years as $year):
                                ?>
                                    <option value="<?= $year ?>" <?= ($year == $selectedAttendanceYear) ? 'selected' : '' ?>><?= $year ?></option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </div>
                </div>
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bx bx-list-check me-2"></i>Rincian Absensi (Tahun <?= $selectedAttendanceYear ?>)</h5>
                        <ul class="list-group list-group-flush">
                            <?php if (count($attendanceMemberBreakdown['breakdown']) > 0): ?>
                                <?php foreach ($attendanceMemberBreakdown['breakdown'] as $item): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm">
                                                <div class="avatar-title bg-soft-primary text-primary m-0 rounded-circle">
                                                    <i class="mdi mdi-calendar-month"></i>
                                                </div>
                                            </div>
                                            <div class="ms-3">
                                                <h6 class="mb-0"><?= htmlspecialchars(date('d F Y', strtotime($item['tanggal_absen']))) ?></h6>
                                                <small class="text-muted"><?= htmlspecialchars(date('H:i', strtotime($item['tanggal_absen']))) ?></small>
                                            </div>
                                        </div>
                                        <span class="badge badge-soft-success">Hadir</span>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="list-group-item text-center text-muted">Tidak ada data absensi yang tercatat untuk tahun ini.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            <?php else: ?>
                <h2 class="mb-4 text-primary"><i class="fa-solid fa-user-check me-2"></i>Absensi Perkumpulan Hari Ini</h2>
                <div class="row mb-3 gy-2 align-items-center">
                    <div class="col-12 col-md-6">
                        <div class="alert alert-info mb-0" role="alert">
                            <i class="fa-solid fa-location-dot me-2"></i> Pilih nama Anda untuk absen. Pastikan GPS aktif.
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <form action="" method="GET" class="d-flex w-100 justify-content-end">
                            <input type="hidden" name="tab" value="absensi">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Cari anggota..." name="search" value="<?= htmlspecialchars($searchTerm) ?>">
                                <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search"></i></button>
                                <?php if (!empty($searchTerm)): ?>
                                    <a href="?tab=absensi" class="btn btn-outline-secondary" title="Hapus Pencarian"><i class="fas fa-times"></i></a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?= $messageType ?> mt-3 mb-4" role="alert">
                        <?= $message ?>
                    </div>
                <?php endif; ?>
                <?php if (count($anggota) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <tbody>
                                <div class="">
                                    <div class="row">
                                        <?php 
                                        foreach ($anggota as $row): 
                                            // Memeriksa status absensi hari ini
                                            $stmt = $conn->prepare("SELECT COUNT(*) FROM absensi WHERE anggota_id = ? AND DATE(tanggal_absen) = CURDATE()");
                                            if (!$stmt) {
                                                // Handle error (optional, sudah ada di proses_data)
                                            } else {
                                                $stmt->bind_param("i", $row['id']);
                                                $stmt->execute();
                                                $stmt->bind_result($isAbsent);
                                                $stmt->fetch();
                                                $stmt->close();
                                            }
                                        ?>
                                        <div class="col-lg-4 col-md-6 col-sm-12">
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center">
                                                        <div>
                                                            <img src="<?= $profile_image ?>" alt="" class="avatar-md rounded-circle img-thumbnail" />
                                                        </div>
                                                        <div class="flex-1 ms-3">
                                                            <h5 class="font-size-16 mb-1"><a href="#" class="text-dark"><?= htmlspecialchars($row['nama_lengkap']) ?></a></h5>
                                                            <?php if (isset($isAbsent) && $isAbsent > 0): ?>
                                                                <span class="badge badge-soft-success mb-0">Hadir</span>
                                                            <?php else: ?>
                                                                <span class="badge badge-soft-danger mb-0">Belum Hadir</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex gap-2 pt-4">
                                                        <?php if (!isset($isAbsent) || $isAbsent == 0): ?>
                                                            <a href="?tab=absensi&member_id=<?= htmlspecialchars($row['id']) ?>" class="btn btn-soft-primary btn-sm w-50">
                                                                <i class="bx bx-receipt me-1"></i> Riwayat Absen
                                                            </a>
                                                            <form id="formAbsen_<?= $row['id'] ?>" action="?tab=absensi" method="POST" style="display:none;">
                                                                <input type="hidden" name="absen_submit" value="1">
                                                                <input type="hidden" name="anggota_id" value="<?= $row['id'] ?>">
                                                                <input type="hidden" name="latitude" id="userLat_<?= $row['id'] ?>">
                                                                <input type="hidden" name="longitude" id="userLon_<?= $row['id'] ?>">
                                                            </form>
                                                            <button type="button" class="btn btn-primary btn-sm w-50" onclick="getLocationAndSubmit(<?= $row['id'] ?>)">
                                                                <i class="bx bx-check me-1"></i> Absen
                                                            </button>                                                            
                                                        <?php else: ?>
                                                            <a href="?tab=absensi&member_id=<?= htmlspecialchars($row['id']) ?>" class="btn btn-soft-primary btn-sm w-50">
                                                                <i class="bx bx-receipt me-1"></i> Riwayat Absen
                                                            </a>
                                                            <button class="btn btn-soft-secondary btn-sm w-50 me-2" disabled>
                                                                <i class="bx bx-check-circle me-1"></i> Sudah Absen
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="col-12 text-center text-muted mt-5">
                        <p>Tidak ada data absensi.</p>
                    </div>
                    <!--<div class="alert alert-warning" role="alert">
                        <i class="fa-solid fa-exclamation-triangle me-2"></i> **Peringatan:** Bagian ini kosong karena **tabel 'anggota' di database Anda tidak memiliki data**. Silakan tambahkan anggota terlebih dahulu.
                    </div>-->
                <?php endif; ?>
                <nav aria-label="Page navigation example">
                    <ul class="pagination justify-content-center">
                        <!--<li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?tab=absensi&page=<?= $page - 1 ?><?= !empty($searchTerm) ? '&search=' . htmlspecialchars($searchTerm) : '' ?>" tabindex="-1">Previous</a>
                        </li>-->
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                <a class="page-link" href="?tab=absensi&page=<?= $i ?><?= !empty($searchTerm) ? '&search=' . htmlspecialchars($searchTerm) : '' ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    <!-- <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?tab=absensi&page=<?= $page + 1 ?><?= !empty($searchTerm) ? '&search=' . htmlspecialchars($searchTerm) : '' ?>">Next</a>
                        </li>-->
                    </ul>
                </nav>
                <script>
                function getLocationAndSubmit(anggotaId) {
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(
                            function(position) {
                                const latitude = position.coords.latitude;
                                const longitude = position.coords.longitude;
                                // Menetapkan nilai ke input tersembunyi
                                document.getElementById('userLat_' + anggotaId).value = latitude;
                                document.getElementById('userLon_' + anggotaId).value = longitude;
                                // Mengirim form
                                document.getElementById('formAbsen_' + anggotaId).submit();
                            },
                            function(error) {
                                let errorMessage = 'Gagal mendapatkan lokasi. Silakan izinkan akses lokasi di browser Anda.';
                                switch(error.code) {
                                    case error.PERMISSION_DENIED:
                                        errorMessage = "Anda menolak permintaan Geolocation. Silakan berikan izin lokasi untuk absen.";
                                        break;
                                    case error.POSITION_UNAVAILABLE:
                                        errorMessage = "Informasi lokasi tidak tersedia.";
                                        break;
                                    case error.TIMEOUT:
                                        errorMessage = "Waktu permintaan untuk mendapatkan lokasi habis.";
                                        break;
                                    case error.UNKNOWN_ERROR:
                                        errorMessage = "Terjadi kesalahan yang tidak diketahui.";
                                        break;
                                }
                                alert(errorMessage);
                            }
                        );
                    } else {
                        alert("Geolocation tidak didukung oleh browser ini.");
                    }
                }
            </script>
            <?php endif; ?>
        <?php elseif ($active_tab == 'kegiatan'): ?>
            <h2 class="mb-4 text-primary"><i class="fa-solid fa-calendar-alt me-2"></i>Daftar Kegiatan</h2>
            <div class="row mb-3 gy-2 align-items-center">
                <div class="col-12 col-md-6">
                    <p class="fs-5 mb-0">Total Kegiatan: <span class="badge bg-primary"><?= $total_kegiatan ?></span></p>
                </div>
                <div class="col-12 col-md-6">
                    <form action="" method="GET" class="d-flex w-100 justify-content-end">
                        <input type="hidden" name="tab" value="kegiatan">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Cari kegiatan..." name="search" value="<?= htmlspecialchars($searchTerm) ?>">
                            <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search"></i></button>
                            <?php if (!empty($searchTerm)): ?>
                                <a href="?tab=kegiatan" class="btn btn-outline-secondary" title="Hapus Pencarian"><i class="fas fa-times"></i></a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-striped" id="kegiatanTable">
                    <tbody>
                        <?php if (count($kegiatan) > 0): ?>
                            <div class="row">
                                <?php foreach ($kegiatan as $row): ?>
                                    <div class="col-lg-4 col-md-6 col-sm-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-md">
                                                        <div class="avatar-title bg-soft-primary text-primary display-6 m-0 rounded-circle">
                                                            <i class="bx bx-calendar-event"></i>
                                                        </div>
                                                    </div>
                                                    <div class="flex-1 ms-3">
                                                        <h5 class="font-size-16 mb-1"><a href="#" class="text-dark"><?= htmlspecialchars($row['nama_kegiatan']) ?></a></h5>
                                                        <span class="badge badge-soft-success mb-0">Aktif</span>
                                                    </div>
                                                </div>
                                                <div class="mt-3 pt-1">
                                                    <p class="text-muted mb-0"><i class="mdi mdi-map-marker-outline font-size-15 align-middle pe-2 text-primary"></i> <?= htmlspecialchars($row['lokasi']) ?></p>
                                                    <p class="text-muted mb-0 mt-2"><i class="mdi mdi-calendar-range font-size-15 align-middle pe-2 text-primary"></i> <?= htmlspecialchars($row['tanggal_mulai']) ?></p>
                                                </div>
                                                </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="col-12 text-center text-muted mt-5">
                                <p>Tidak ada data kegiatan.</p>
                            </div>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <nav aria-label="Page navigation example">
                <ul class="pagination justify-content-center">
                    <!--<li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?tab=kegiatan&page=<?= $page - 1 ?><?= !empty($searchTerm) ? '&search=' . htmlspecialchars($searchTerm) : '' ?>" tabindex="-1">Previous</a>
                    </li>-->
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                            <a class="page-link" href="?tab=kegiatan&page=<?= $i ?><?= !empty($searchTerm) ? '&search=' . htmlspecialchars($searchTerm) : '' ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                <!-- <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?tab=kegiatan&page=<?= $page + 1 ?><?= !empty($searchTerm) ? '&search=' . htmlspecialchars($searchTerm) : '' ?>">Next</a>
                    </li>-->
                </ul>
            </nav>
        <?php elseif ($active_tab == 'keuangan'): ?>
            <h2 class="mb-4 text-primary"><i class="fa-solid fa-wallet me-2"></i>Laporan Keuangan</h2>
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
            <div class="table-responsive">
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
                                                <div class="avatar-md">
                                                    <div class="avatar-title bg-soft-primary text-primary display-6 m-0 rounded-circle">
                                                        <i class="<?= $icon_class ?>"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-1 ms-3">
                                                    <h5 class="font-size-16 mb-1"><a href="#" class="text-dark"><?= htmlspecialchars($title_text) ?></a></h5>
                                                    <span class="badge <?= $badge_class ?> mb-0"><?= htmlspecialchars(ucfirst($row['deskripsi'])) ?></span>
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
                <!-- <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?tab=keuangan&year=<?= $selectedYear ?>&page=<?= $page - 1 ?><?= !empty($searchTerm) ? '&search=' . htmlspecialchars($searchTerm) : '' ?>" tabindex="-1">Previous</a>
                    </li>-->
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                            <a class="page-link" href="?tab=keuangan&year=<?= $selectedYear ?>&page=<?= $i ?><?= !empty($searchTerm) ? '&search=' . htmlspecialchars($searchTerm) : '' ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                <!-- <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?tab=keuangan&year=<?= $selectedYear ?>&page=<?= $page + 1 ?><?= !empty($searchTerm) ? '&search=' . htmlspecialchars($searchTerm) : '' ?>">Next</a>
                    </li>-->
                </ul>
            </nav>
        <?php elseif ($active_tab == 'iuran'): ?>
            <?php if ($memberDuesBreakdown): ?>
                <a href="?tab=iuran&year=<?= $selectedYear ?>" class="btn btn-outline-primary mb-4">
                    <i class="fa-solid fa-arrow-left me-2"></i> Kembali ke Daftar Iuran
                </a>
                <h2 class="mb-4 text-primary"><i class="fa-solid fa-user me-2"></i>Rekapitulasi Iuran Anggota</h2>
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
                            <input type="hidden" name="tab" value="iuran">
                            <input type="hidden" name="member_id" value="<?= htmlspecialchars($attendanceMemberId) ?>">
                            <label for="year-iuran" class="form-label mb-0 me-2 fw-bold">Pilih Tahun:</label>
                            <select class="form-select w-auto" id="year-iuran" name="year" onchange="this.form.submit()">
                                <?php
                                $currentYear = date('Y');
                                $resultYears = $conn->query("SELECT DISTINCT YEAR(tanggal_bayar) AS year FROM iuran ORDER BY year DESC");
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
                                        <li class="list-group-item text-center text-muted">Tidak ada data iuran yang tercatat untuk tahun ini.</li>
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
                <h2 class="mb-4 text-primary"><i class="fa-solid fa-receipt me-2"></i>Rekapitulasi Iuran</h2>
                <div class="row mb-3 gy-2 align-items-center">
                    <div class="col-12 col-md-6">
                        <form action="" method="GET" class="d-flex align-items-center w-100">
                            <input type="hidden" name="tab" value="iuran">
                            <label for="year-iuran" class="form-label mb-0 me-2 fw-bold">Pilih Tahun:</label>
                            <select class="form-select w-auto" id="year-iuran" name="year" onchange="this.form.submit()">
                                <?php
                                $currentYear = date('Y');
                                $resultYears = $conn->query("SELECT DISTINCT YEAR(tanggal_bayar) AS year FROM iuran ORDER BY year DESC");
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
                            <input type="hidden" name="tab" value="iuran">
                            <input type="hidden" name="year" value="<?= $selectedYear ?>">
                            <div class="input-group">
                                <input type="text" id="searchInputIuran" class="form-control" placeholder="Cari anggota..." name="search" value="<?= htmlspecialchars($searchTerm) ?>">
                                <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search"></i></button>
                                <?php if (!empty($searchTerm)): ?>
                                    <a href="?tab=iuran&year=<?= $selectedYear ?>" class="btn btn-outline-secondary" title="Hapus Pencarian"><i class="fas fa-times"></i></a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="table-responsive">
                    <div class="row">
                        <?php if (!empty($iuran)): ?>
                            <?php foreach ($iuran as $row): ?>
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
                                                    <h5 class="font-size-16 mb-1"><a href="?tab=iuran&member_id=<?= htmlspecialchars($row['anggota_id']) ?>&year=<?= $selectedYear ?>" class="text-dark"><?= htmlspecialchars($row['nama_lengkap']) ?></a></h5>
                                                    <?php 
                                                        // Logika perhitungan status
                                                        $monthlyFee = DUES_MONTHLY_FEE;
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
                                                <a href="?tab=iuran&member_id=<?= htmlspecialchars($row['anggota_id']) ?>&year=<?= $selectedYear ?>" class="btn btn-soft-primary btn-sm w-100">
                                                    <i class="bx bx-receipt me-1"></i> Detail Riwayat
                                                </a>
                                            </div>
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
                            <!--<li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?tab=iuran&page=<?= $page - 1 ?><?= !empty($searchTerm) ? '&search=' . htmlspecialchars($searchTerm) : '' ?>&year=<?= $selectedYear ?>" tabindex="-1">Previous</a>
                            </li>-->
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                    <a class="page-link" href="?tab=iuran&page=<?= $i ?><?= !empty($searchTerm) ? '&search=' . htmlspecialchars($searchTerm) : '' ?>&year=<?= $selectedYear ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                        <!-- <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?tab=iuran&page=<?= $page + 1 ?><?= !empty($searchTerm) ? '&search=' . htmlspecialchars($searchTerm) : '' ?>&year=<?= $selectedYear ?>">Next</a>
                            </li>-->
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        <?php endif; ?>
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