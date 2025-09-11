<?php if ($attendanceMemberBreakdown): ?>
    <a href="?tab=absensi" class="btn btn-outline-primary mb-4">
        <i class="fa-solid fa-arrow-left me-2"></i> Kembali ke Daftar Absensi
    </a>
    <h2 class="mb-4 text-primary"><i class="fa-solid fa-user me-2"></i>Riwayat Absensi</h2>
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
    <?php
    // Pastikan $conn sudah terdefinisi dan terhubung ke database.

    // Mengambil total anggota (diperlukan untuk menghitung yang tidak hadir)
    $stmt = $conn->prepare("SELECT COUNT(*) FROM anggota");
    $stmt->execute();
    $stmt->bind_result($totalAnggota);
    $stmt->fetch();
    $stmt->close();

    // Mengambil data kehadiran bulanan dan ketidakhadiran untuk 12 bulan terakhir
    $hadirData = [];
    $tidakHadirData = [];
    $labels = [];

    // Loop untuk 12 bulan ke belakang
    for ($i = 11; $i >= 0; $i--) {
        $date = new DateTime("-$i months");
        $month = $date->format('m');
        $year = $date->format('Y');
        $monthName = $date->format('M Y'); // Contoh: Okt 2024

        // Kueri untuk menghitung jumlah kehadiran unik per bulan
        $stmt = $conn->prepare("SELECT COUNT(DISTINCT anggota_id) FROM absensi WHERE MONTH(tanggal_absen) = ? AND YEAR(tanggal_absen) = ?");
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

        // Mengubah array PHP ke JSON untuk digunakan di JavaScript
        $hadirDataJson = json_encode($hadirData);
        $tidakHadirDataJson = json_encode($tidakHadirData);
        $labelsJson = json_encode($labels);

        // ============================
        // Top 3 Absen Tercepat Bulan Ini & Bulan Lalu
        // ============================
        $topThisMonth = [];
        $topLastMonth = [];

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
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $topThisMonth[] = $row;
        }
        $stmt->close();

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
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $topLastMonth[] = $row;
        }
        $stmt->close();
    ?>

    <!-- ============================ -->
    <!-- TAMPILAN TOP 5 ABSEN -->
    <!-- ============================ -->
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white fw-bold">
                    <i class="fa-solid fa-trophy me-2"></i>Top 5 Absen Tercepat Bulan Ini
                </div>
                <ul class="list-group list-group-flush">
                    <?php if (!empty($topThisMonth)): ?>
                        <?php foreach ($topThisMonth as $i => $row): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><strong>#<?= $i+1 ?></strong> <?= htmlspecialchars($row['nama_lengkap']) ?></span>
                                <span class="badge bg-success"><?= date('d M H:i', strtotime($row['waktu_absen'])) ?></span>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="list-group-item text-center text-muted">Belum ada data bulan ini.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white fw-bold">
                    <i class="fa-solid fa-trophy me-2"></i>Top 5 Absen Tercepat Bulan Lalu
                </div>
                <ul class="list-group list-group-flush">
                    <?php if (!empty($topLastMonth)): ?>
                        <?php foreach ($topLastMonth as $i => $row): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><strong>#<?= $i+1 ?></strong> <?= htmlspecialchars($row['nama_lengkap']) ?></span>
                                <span class="badge bg-primary"><?= date('d M H:i', strtotime($row['waktu_absen'])) ?></span>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="list-group-item text-center text-muted">Belum ada data bulan lalu.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Progres Kehadiran Bulanan (1 Tahun Terakhir)</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <canvas id="monthlyBarChart" style="max-height: 400px;"></canvas>
                </div>
            </div>
        </div>
    </div>

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

    <?php if ($is_absensi_active): ?>
        <div id="countdown-timer" class="alert alert-warning text-center fw-bold mt-3" role="alert">
            Waktu Absensi: <span id="timer-display"></span>
        </div>
    <?php else: ?>
        <div class="alert alert-danger text-center fw-bold mt-3" role="alert">
            Sesi Absensi Sudah Berakhir.
        </div>
    <?php endif; ?>

    <?php if (isset($anggota) && is_array($anggota) && count($anggota) > 0): ?>
        <div class="">
            <div class="row">
                <?php 
                foreach ($anggota as $row): 
                    // Memeriksa status absensi hari ini
                    // Pastikan $conn sudah terdefinisi di file yang sama atau di-include
                    // RESET SETIAP HARI $stmt = $conn->prepare("SELECT COUNT(*) FROM absensi WHERE anggota_id = ? AND DATE(tanggal_absen) = CURDATE()");
                    $stmt = $conn->prepare("SELECT COUNT(*) FROM absensi WHERE anggota_id = ? AND MONTH(tanggal_absen) = MONTH(CURDATE()) AND YEAR(tanggal_absen) = YEAR(CURDATE())");
                    if (!$stmt) {
                        // Jika ada error pada prepare statement
                        // Anda bisa menambahkan logging atau pesan error di sini
                    } else {
                        $stmt->bind_param("i", $row['id']);
                        $stmt->execute();
                        $stmt->bind_result($isAbsent);
                        $stmt->fetch();
                        $stmt->close();
                    }
                ?>
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <img src="<?= htmlspecialchars($profile_image) ?>" alt="Profil Anggota" class="avatar-md rounded-circle img-thumbnail" />
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
                                            <input type="hidden" name="anggota_id" value="<?= htmlspecialchars($row['id']) ?>">
                                            <input type="hidden" name="latitude" id="userLat_<?= $row['id'] ?>">
                                            <input type="hidden" name="longitude" id="userLon_<?= $row['id'] ?>">
                                        </form>
                                        <button type="button" class="btn btn-primary btn-sm w-50" onclick="getLocationAndSubmit(<?= htmlspecialchars($row['id']) ?>)">
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
    <?php else: ?>
        <div class="alert alert-warning text-center">
            <i class="fas fa-exclamation-triangle me-2"></i> Tidak ada data anggota yang ditemukan.
        </div>
    <?php endif; ?>
    <nav aria-label="Page navigation example">
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                    <a class="page-link" href="?tab=absensi&page=<?= $i ?><?= !empty($searchTerm) ? '&search=' . htmlspecialchars($searchTerm) : '' ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
    <script>
    // =================================================================
    // Logika JavaScript untuk Timer dan Geolocation
    // =================================================================
    document.addEventListener('DOMContentLoaded', function() {
        // Menggunakan PHP untuk mengirim data ke JavaScript
        const remainingTime = <?= json_encode($remaining_time); ?>;
        const isAbsensiActive = <?= json_encode($is_absensi_active); ?>;
        const timerDisplay = document.getElementById('timer-display');
        const absenButtons = document.querySelectorAll('.absen-btn');
    
        // Memastikan timer hanya berjalan jika sesi aktif dan ada sisa waktu
        if (isAbsensiActive && remainingTime > 0) {
            let time = remainingTime;
            const timerInterval = setInterval(() => {
                time--;
                timerDisplay.textContent = time + ' detik';
    
                if (time <= 0) {
                    clearInterval(timerInterval);
                    timerDisplay.textContent = 'Waktu habis!';
                    absenButtons.forEach(button => {
                        button.disabled = true;
                        button.textContent = 'Absensi Berakhir';
                        button.classList.remove('btn-primary');
                        button.classList.add('btn-secondary');
                    });
                }
            }, 1000);
        } else {
            // Jika sesi tidak aktif dari awal, nonaktifkan tombol absensi
            absenButtons.forEach(button => {
                button.disabled = true;
                button.textContent = 'Absensi Berakhir';
                button.classList.remove('btn-primary');
                button.classList.add('btn-secondary');
            });
        }
    });

    function getLocationAndSubmit(anggotaId) {
        // Cek kembali status absensi sebelum mengirim
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

        new Chart(document.getElementById('monthlyBarChart'), config);
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php endif; ?>