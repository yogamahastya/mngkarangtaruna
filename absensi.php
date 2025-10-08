<?php 
// Fungsi untuk mendapatkan inisial dari nama lengkap
function getInitials($fullName) {
    if (empty($fullName)) return '';
    $words = explode(' ', trim($fullName));
    $initials = '';
    
    // Ambil inisial dari 2 kata pertama
    if (count($words) >= 2) {
        $initials .= strtoupper(substr($words[0], 0, 1));
        $initials .= strtoupper(substr($words[1], 0, 1));
    } else {
        // Jika hanya satu kata, ambil 2 huruf pertama
        $initials = strtoupper(substr($words[0], 0, 2));
    }
    return $initials;
}

// =================================================================================
// FUNGSI BANTUAN UNTUK WARNA AVATAR (DIPERLUKAN DI BAWAH)
function getAvatarColorClasses($id) {
    $colors = ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'dark'];
    $colorIndex = $id % count($colors);
    $randomColor = $colors[$colorIndex];

    $bgColorClass = "bg-{$randomColor}";
    
    // Teks putih untuk latar belakang gelap (primary, success, danger, dark), teks gelap untuk latar belakang terang (warning, info, light, secondary)
    $textColorClass = in_array($randomColor, ['warning', 'info', 'light']) ? 'text-dark' : 'text-white';
    
    return ['bg' => $bgColorClass, 'text' => $textColorClass];
}
// =================================================================================
?>

<?php if ($attendanceMemberBreakdown): ?>
    <a href="?tab=absensi" class="btn btn-outline-primary mb-4 rounded-pill fw-medium">
    <i class="fa-solid fa-arrow-left me-2"></i> Kembali ke Daftar Absensi
</a>

<div class="card shadow-lg border-0 border-start border-5 border-primary mb-5 rounded-4">
    <div class="card-body py-4">
        <div class="d-flex align-items-center">
            <?php $avatarClasses = getAvatarColorClasses($attendanceMemberBreakdown['member']['id'] ?? 1); ?>
            <div class="rounded-circle me-4 <?= $avatarClasses['bg'] ?> <?= $avatarClasses['text'] ?> d-flex align-items-center justify-content-center" 
                style="width: 3.5rem; height: 3.5rem; font-size: 1.25rem; font-weight: ; flex-shrink: 0;">
                <?= htmlspecialchars(getInitials($attendanceMemberBreakdown['member']['nama_lengkap'])) ?>
            </div>
            <div>
                <h6 class="card-title fw-er mb-0 text-dark">
                    <?= htmlspecialchars($attendanceMemberBreakdown['member']['nama_lengkap']) ?>
                </h6>
                <p class="card-text text-secondary small mb-1 mt-1">
                    <span class="badge bg-info-subtle text-info fw- me-2">Anggota</span>
                </p>
                <p class="card-text text-muted small mb-0">
                    <i class="fa-solid fa-calendar-day me-1"></i>Bergabung Sejak: **<?= htmlspecialchars($attendanceMemberBreakdown['member']['bergabung_sejak']) ?>**
                </p>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-lg border-0 border-start border-5 border-primary mb-4 rounded-4">
    <div class="card-body py-3">
        <div class="row gy-3 align-items-center">
            
            <!-- Filter Tahun Absensi -->
            <div class="col-md-6 col-lg-4">
                <form action="" method="GET" class="d-flex align-items-center gap-2">
                    <input type="hidden" name="tab" value="absensi">
                    <input type="hidden" name="member_id" value="<?= htmlspecialchars($attendanceMemberId) ?>">

                    <label for="year-absensi" class="form-label mb-0 fw-bold text-dark d-flex align-items-center">
                        <i class="fa-solid fa-calendar-alt me-2 text-primary"></i>
                        Tahun
                    </label>

                    <div class="input-group" style="max-width: 200px;">
                        <span class="input-group-text bg-primary text-white border-0 rounded-start-pill">
                            <i class="fa-solid fa-calendar"></i>
                        </span>
                        <select class="form-select border-0 shadow-sm rounded-end-pill px-3 py-2"
                                id="year-absensi" 
                                name="year_absensi" 
                                onchange="this.form.submit()" 
                                style="cursor:pointer;">
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
                            foreach ($years as $year): ?>
                                <option value="<?= $year ?>" <?= ($year == $selectedAttendanceYear) ? 'selected' : '' ?>>
                                    <?= $year ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>

            <!-- Badge Total Kehadiran -->
            <div class="col-md-6 col-lg-8 text-md-end">
                <?php
                $totalKehadiran = count($attendanceMemberBreakdown['breakdown'] ?? []);
                ?>
                <span class="badge bg-success-subtle text-success fw-semibold p-3 shadow-sm rounded-pill">
                    <i class="fa-solid fa-check-circle me-1"></i> 
                    Total Kehadiran <span class="text-dark">Tahun <?= $selectedAttendanceYear ?>:</span> 
                    <strong><?= $totalKehadiran ?></strong>
                </span>
            </div>

        </div>
    </div>
</div>


<div class="card shadow-sm h-100 rounded-4">
    <div class="card-header bg-primary text-white fw- py-3 rounded-top-4">
        <h6 class="card-title mb-0"><i class="fa-solid fa-calendar-check me-2"></i>Daftar Kehadiran</h6>
    </div>
    <div class="card-body p-0">
        <ul class="list-group list-group-flush">
            <?php if (count($attendanceMemberBreakdown['breakdown'] ?? []) > 0): ?>
                <?php foreach ($attendanceMemberBreakdown['breakdown'] as $item): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-3 border-bottom-0">
                        <div class="d-flex align-items-center">
                            <div class="text-center me-3 p-2 rounded-3 bg-success text-white" style="width: 4rem; flex-shrink: 0;">
                                <div class="fw-er fs-6 lh-1"><?= htmlspecialchars(date('d', strtotime($item['tanggal_absen']))) ?></div>
                                <div class="small fw-medium lh-1"><?= htmlspecialchars(date('M', strtotime($item['tanggal_absen']))) ?></div>
                            </div>
                            
                            <div class="ms-1">
                                <p class="mb-1 fw- text-dark fs-6"><?= htmlspecialchars(date('d F Y', strtotime($item['tanggal_absen']))) ?></p>
                                <span class="badge bg-secondary-subtle text-secondary fw-medium small">
                                    <i class="fa-solid fa-clock me-1"></i> Pukul: **<?= htmlspecialchars(date('H:i', strtotime($item['tanggal_absen']))) ?>** WIB
                                </span>
                            </div>
                        </div>
                        
                        <span class="badge bg-success fw- py-2 px-3 rounded-pill shadow-sm">
                            <i class="fa-solid fa-check me-1"></i> Hadir
                        </span>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li class="list-group-item text-center text-muted py-5">
                    <i class="fa-solid fa-box-open fa-3x mb-3 d-block"></i>
                    <p class="mb-0">Tidak ada data absensi yang tercatat untuk tahun **<?= $selectedAttendanceYear ?>**.</p>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</div>
<br>
<?php else: ?>
    <?php
    // =============== LOGIKA PHP UNTUK TOP 5 DAN CHART (DIPERTahankan) ===============
    
    // Pastikan $conn sudah terdefinisi dan terhubung ke database.

    // Mengambil total anggota (diperlukan untuk menghitung yang tidak hadir)
    $totalAnggota = 0;
    if (isset($conn)) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM anggota");
        if ($stmt) {
            $stmt->execute();
            $stmt->bind_result($totalAnggota);
            $stmt->fetch();
            $stmt->close();
        }
    }


    // Mengambil data kehadiran bulanan dan ketidakhadiran untuk 12 bulan terakhir
    $hadirData = [];
    $tidakHadirData = [];
    $labels = [];

    if (isset($conn) && $totalAnggota > 0) {
        // Loop untuk 12 bulan ke belakang
        for ($i = 11; $i >= 0; $i--) {
            $date = new DateTime("-$i months");
            $month = $date->format('m');
            $year = $date->format('Y');
            $monthName = $date->format('M Y'); // Contoh: Okt 2024
    
            // Kueri untuk menghitung jumlah kehadiran unik per bulan
            $stmt = $conn->prepare("SELECT COUNT(DISTINCT anggota_id) FROM absensi WHERE MONTH(tanggal_absen) = ? AND YEAR(tanggal_absen) = ?");
            if ($stmt) {
                $stmt->bind_param("ss", $month, $year);
                $stmt->execute();
                $stmt->bind_result($hadirCount);
                $stmt->fetch();
                $stmt->close();
        
                // HANYA TAMBAHKAN DATA JIKA ADA KEHADIRAN DI BULAN TERSEBUT
                if ($hadirCount > 0) {
                    $tidakHadirCount = $totalAnggota - $hadirCount;
                    $hadirData[] = $hadirCount;
                    $tidakHadirData[] = $tidakHadirCount;
                    $labels[] = $monthName;
                }
            }
        }
    }

    // Mengubah array PHP ke JSON untuk digunakan di JavaScript
    $hadirDataJson = json_encode($hadirData);
    $tidakHadirDataJson = json_encode($tidakHadirData);
    $labelsJson = json_encode($labels);

    // ============================
    // Top 5 Absen Tercepat Bulan Ini & Bulan Lalu (DARI KODE ASLI)
    // ============================
    $topThisMonth = [];
    $topLastMonth = [];

    if (isset($conn)) {
        // Bulan ini
        $stmt = $conn->prepare("
            SELECT a.anggota_id, an.nama_lengkap, MIN(a.tanggal_absen) AS waktu_absen
            FROM absensi a
            JOIN anggota an ON a.anggota_id = an.id
            WHERE MONTH(a.tanggal_absen) = MONTH(CURDATE()) 
            AND YEAR(a.tanggal_absen) = YEAR(CURDATE())
            GROUP BY a.anggota_id
            ORDER BY waktu_absen ASC
            LIMIT 5
        ");
        if ($stmt) {
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $topThisMonth[] = $row;
            }
            $stmt->close();
        }

        // Bulan lalu
        $stmt = $conn->prepare("
            SELECT a.anggota_id, an.nama_lengkap, MIN(a.tanggal_absen) AS waktu_absen
            FROM absensi a
            JOIN anggota an ON a.anggota_id = an.id
            WHERE MONTH(a.tanggal_absen) = MONTH(CURDATE() - INTERVAL 1 MONTH) 
            AND YEAR(a.tanggal_absen) = YEAR(CURDATE() - INTERVAL 1 MONTH)
            GROUP BY a.anggota_id
            ORDER BY waktu_absen ASC
            LIMIT 5
        ");
        if ($stmt) {
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $topLastMonth[] = $row;
            }
            $stmt->close();
        }
    }
    // =========================================================================
    ?>

    <div class="row mb-4 g-4">
        <div class="col-md-6">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header bg-success-subtle border-0 py-3 rounded-top-4">
                    <h6 class="mb-0 text-success fw-">
                        <i class="fa-solid fa-trophy me-2"></i>Top 5 Absen Tercepat Bulan Ini
                    </h6>
                </div>
                <ul class="list-group list-group-flush">
                    <?php if (!empty($topThisMonth)): ?>
                        <?php foreach ($topThisMonth as $i => $row): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-success me-3 rounded-circle" style="width: 2rem; height: 2rem; display: flex; align-items: center; justify-content: center; font-size: 1rem;">
                                        <?= $i+1 ?>
                                    </span>
                                    <span class="fw-medium text-dark fs-6"><?= htmlspecialchars($row['nama_lengkap']) ?></span>
                                </div>
                                <span class="badge bg-success fw- px-3 py-2 rounded-pill"><?= date('d M H:i', strtotime($row['waktu_absen'])) ?></span>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="list-group-item text-center text-muted py-3">Belum ada data kehadiran bulan ini.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header bg-primary-subtle border-0 py-3 rounded-top-4">
                    <h6 class="mb-0 text-primary fw-">
                        <i class="fa-solid fa-trophy me-2"></i>Top 5 Absen Tercepat Bulan Lalu
                    </h6>
                </div>
                <ul class="list-group list-group-flush">
                    <?php if (!empty($topLastMonth)): ?>
                        <?php foreach ($topLastMonth as $i => $row): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-primary me-3 rounded-circle" style="width: 2rem; height: 2rem; display: flex; align-items: center; justify-content: center; font-size: 1rem;">
                                        <?= $i+1 ?>
                                    </span>
                                    <span class="fw-medium text-dark fs-6"><?= htmlspecialchars($row['nama_lengkap']) ?></span>
                                </div>
                                <span class="badge bg-primary fw- px-3 py-2 rounded-pill"><?= date('d M H:i', strtotime($row['waktu_absen'])) ?></span>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="list-group-item text-center text-muted py-3">Belum ada data kehadiran bulan lalu.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="card shadow-sm mb-4 rounded-4">
        <div class="card-header bg-primary text-white fw- py-3 rounded-top-4">
            <h6 class="mb-0"><i class="fa-solid fa-chart-bar me-2"></i>Progres Kehadiran Bulanan (1 Tahun Terakhir)</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <canvas id="monthlyBarChart" style="max-height: 400px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Container utama dengan border menonjol untuk fokus pada fitur penting -->
<div class="card shadow-lg border-0 border-start border-5 border-info mb-4 rounded-4">
    <div class="card-body py-3">
        <div class="row gy-3 align-items-center">

            <!-- Instruksi Absensi (Info Box yang ditingkatkan) -->
            <div class="col-12 col-md-6">
                <!-- Styling dengan warna biru muda (info) yang menonjolkan pesan GPS -->
                <div class="d-flex align-items-center p-3 rounded-4 h-100 shadow-sm" style="background-color: #f0f8ff; border: 1px solid #b3e5fc;">
                    <i class="fa-solid fa-map-location-dot me-3 fa-2x text-info flex-shrink-0"></i>
                    <p class="mb-0 fw-medium text-dark">
                        Pilih nama Anda untuk absen. <br>Pastikan **Layanan Lokasi (GPS) Anda aktif**!
                    </p>
                </div>
            </div>

            <!-- Search Anggota (Mengadopsi Desain Rounded-Pill) -->
            <div class="col-12 col-md-6">
                <form action="" method="GET" class="d-flex w-100 justify-content-md-end">
                    <input type="hidden" name="tab" value="absensi">

                    <div class="input-group" style="max-width: 350px;">
                        <!-- Icon pencarian di awal input -->
                        <span class="input-group-text bg-light border-0 rounded-start-pill">
                            <i class="fas fa-user-search text-primary"></i>
                        </span>
                        <!-- Input pencarian dengan shadow lembut -->
                        <input type="text" id="searchInputAbsensi" 
                               class="form-control border-0 shadow-sm px-3 py-2"
                               placeholder="Cari anggota untuk absen..." 
                               name="search" 
                               value="<?= htmlspecialchars($searchTerm) ?>">
                        <!-- Tombol submit utama -->
                        <button class="btn btn-primary rounded-end-pill" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                        <!-- Tombol hapus pencarian -->
                        <?php if (!empty($searchTerm)): ?>
                            <a href="?tab=absensi" 
                               class="btn btn-outline-danger ms-2 rounded-pill" 
                               title="Hapus Pencarian">
                               <i class="fas fa-times"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>


    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= $messageType ?> mt-3 mb-4 shadow-sm" role="alert">
            <i class="fa-solid fa-info-circle me-2"></i><?= $message ?>
        </div>
    <?php endif; ?>

    <?php if ($is_absensi_active): ?>
        <div id="countdown-timer" class="alert alert-warning text-center fw- mt-3 shadow-sm rounded-4 fs-6" role="alert">
            Waktu Absensi Tersisa: <span id="timer-display" class="text-danger fw-er"></span>
        </div>
    <?php else: ?>
        <div class="alert alert-danger text-center fw- mt-3 shadow-sm rounded-4 fs-6" role="alert">
            <i class="fa-solid fa-times-circle me-2"></i> Sesi Absensi Sudah Berakhir.
        </div>
    <?php endif; ?>

    <?php if (isset($anggota) && is_array($anggota) && count($anggota) > 0): ?>
        <div class="row g-3 mt-3">
            <?php 
            foreach ($anggota as $row): 
                
                $isMemberAbsent = false;
                if (isset($conn)) {
                    $stmt = $conn->prepare("SELECT COUNT(*) FROM absensi WHERE anggota_id = ? AND MONTH(tanggal_absen) = MONTH(CURDATE()) AND YEAR(tanggal_absen) = YEAR(CURDATE())");
                    if ($stmt) {
                        $stmt->bind_param("i", $row['id']);
                        $stmt->execute();
                        $stmt->bind_result($isAbsent);
                        $stmt->fetch();
                        $stmt->close();
                        $isMemberAbsent = (isset($isAbsent) && $isAbsent > 0);
                    }
                }

                $statusBadgeClass = $isMemberAbsent ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger';
                $statusText = $isMemberAbsent ? 'Hadir (Bulan Ini)' : 'Belum Hadir';
                $statusIcon = $isMemberAbsent ? 'fa-check-circle' : 'fa-times-circle';

                // --- START: Logika Warna Avatar Dinamis ---
                $avatarClasses = getAvatarColorClasses($row['id']);
                // --- END: Logika Warna Avatar Dinamis ---
            ?>
                <div class="col-lg-4 col-md-6 col-sm-12">
    <div class="card mb-3 shadow-lg border-0 rounded-4 h-100">
    <div class="card-body">
        <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
                <div class="rounded-circle d-flex align-items-center justify-content-center <?= $avatarClasses['bg'] ?> <?= $avatarClasses['text'] ?>" 
                    style="width: 3.5rem; height: 3.5rem; font-size: 1.25rem; font-weight: ;">
                    <?= htmlspecialchars(getInitials($row['nama_lengkap'])) ?>
                </div>
            </div>
            <div class="flex-1 ms-3">
                <h6 class="font-size-16 mb-1 fw- text-dark"><?= htmlspecialchars($row['nama_lengkap']) ?></h6>
                <span class="badge <?= $statusBadgeClass ?> mb-0 fw-medium small">
                    <i class="fa-solid <?= $statusIcon ?> me-1"></i> <?= $statusText ?>
                </span>
            </div>
        </div>
        <div class="d-flex gap-2 pt-4 border-top mt-3 justify-content-center">
            <a href="?tab=absensi&member_id=<?= htmlspecialchars($row['id']) ?>" class="btn btn-outline-primary btn-sm w-50 rounded-pill d-flex flex-column justify-content-center align-items-center">
                <i class="fa-solid fa-receipt"></i> 
                <span>Riwayat Absensi</span>
            </a>
            
            <?php if (!$isMemberAbsent && $is_absensi_active): ?>
                <form id="formAbsen_<?= $row['id'] ?>" action="?tab=absensi" method="POST" style="display:none;">
                    <input type="hidden" name="absen_submit" value="1">
                    <input type="hidden" name="anggota_id" value="<?= htmlspecialchars($row['id']) ?>">
                    <input type="hidden" name="latitude" id="userLat_<?= $row['id'] ?>">
                    <input type="hidden" name="longitude" id="userLon_<?= $row['id'] ?>">
                </form>
                
                <button type="button" id="absenBtn_<?= $row['id'] ?>" class="btn btn-primary btn-sm w-50 rounded-pill absen-btn" onclick="getLocationAndSubmit(<?= htmlspecialchars($row['id']) ?>)">
                    <i class="fa-solid fa-check-to-slot me-1"></i> Absen
                </button>
            <?php else: ?>
                <button class="btn btn-secondary btn-sm w-50 rounded-pill" disabled>
                    <i class="fa-solid fa-lock me-1"></i> Absensi Tidak Aktif
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>
</div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-warning text-center">
            <i class="fas fa-exclamation-triangle me-2"></i> Tidak ada data anggota yang ditemukan.
        </div>
    <?php endif; ?>

    <nav aria-label="Page navigation example" class="mt-4">
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                    <a class="page-link" href="?tab=absensi&page=<?= $i ?><?= !empty($searchTerm) ? '&search=' . htmlspecialchars($searchTerm) : '' ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const remainingTime = <?= json_encode($remaining_time ?? 0); ?>;
        const isAbsensiActive = <?= json_encode($is_absensi_active ?? false); ?>;
        const timerDisplay = document.getElementById('timer-display');
        const absenButtons = document.querySelectorAll('.absen-btn');
    
        if (isAbsensiActive && remainingTime > 0) {
            let time = remainingTime;
            const timerInterval = setInterval(() => {
                time--;
                if (timerDisplay) {
                    timerDisplay.textContent = time + ' detik';
                }
    
                if (time <= 0) {
                    clearInterval(timerInterval);
                    if (timerDisplay) {
                        timerDisplay.textContent = 'Waktu habis!';
                    }
                    absenButtons.forEach(button => {
                        button.disabled = true;
                        button.textContent = 'Absensi Berakhir';
                        button.classList.remove('btn-primary');
                        button.classList.add('btn-secondary');
                    });
                }
            }, 1000);
        } else {
            absenButtons.forEach(button => {
                button.disabled = true;
                button.textContent = 'Absensi Berakhir';
                button.classList.remove('btn-primary');
                button.classList.add('btn-secondary');
            });
        }
    });

    function getLocationAndSubmit(anggotaId) {
        const absenBtn = document.getElementById('absenBtn_' + anggotaId);
        if (absenBtn && absenBtn.disabled) {
            alert("Absensi sudah berakhir.");
            return;
        }

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const latitude = position.coords.latitude;
                    const longitude = position.coords.longitude;
                    document.getElementById('userLat_' + anggotaId).value = latitude;
                    document.getElementById('userLon_' + anggotaId).value = longitude;
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
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const hadirData = <?= $hadirDataJson ?>;
            const tidakHadirData = <?= $tidakHadirDataJson ?>;
            const labels = <?= $labelsJson ?>;
    
            const data = {
                labels: labels,
                datasets: [
                    {
                        label: 'Hadir',
                        data: hadirData,
                        backgroundColor: 'rgba(40, 167, 69, 0.7)',
                        borderColor: 'rgba(40, 167, 69, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Tidak Hadir',
                        data: tidakHadirData,
                        backgroundColor: 'rgba(220, 53, 69, 0.7)',
                        borderColor: 'rgba(220, 53, 69, 1)',
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
                        x: {
                            stacked: true,
                            title: {
                                display: true,
                                text: 'Bulan'
                            }
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Jumlah Anggota'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    label += context.raw;
                                    return label;
                                }
                            }
                        }
                    }
                }
            };
    
            if(document.getElementById('monthlyBarChart')) {
                new Chart(document.getElementById('monthlyBarChart'), config);
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php endif; ?>