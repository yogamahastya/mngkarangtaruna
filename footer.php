<footer class="footer-section mt-3">
  <div class="footer-container py-3 px-4 mx-auto rounded-4 shadow-sm">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center text-center text-sm-start gap-2">

      <!-- Kiri -->
      <div class="order-2 order-sm-1">
        <p class="mb-1 small fw-medium text-secondary">
          &copy; <?= date('Y') ?> Dibuat oleh 
          <a href="http://nuxera.my.id" target="_blank" class="text-primary text-decoration-none fw-semibold hover-link">
            nuxera
          </a>
          <a href="https://www.linkedin.com/in/yogamahastya" target="_blank" class="ms-1 text-secondary opacity-75 hover-link">
            <i class="fab fa-linkedin fa-sm"></i>
          </a>
        </p>
        <p class="mb-0 small text-muted">
          <i class="fa-solid fa-heart text-danger me-1"></i>
          Satu visi, satu aksi, untuk kemajuan bersama 💪
        </p>
      </div>

      <!-- Kanan -->
      <p class="mb-0 small text-muted order-1 order-sm-2">
        <?php
        $versionFile = __DIR__ . '/application/version.json';
        if (file_exists($versionFile)) {
            $versionData = json_decode(file_get_contents($versionFile), true);
            if ($versionData && isset($versionData['version'])) {
                echo '<i class="fa-solid fa-code-branch me-1 text-success"></i> v' . htmlspecialchars($versionData['version']);
            }
        }
        ?>
      </p>
    </div>
  </div>
</footer>

<style>
/* === FOOTER STYLE === */
.footer-section {
  display: flex;
  justify-content: center;
}

/* Biar sejajar dengan content-card */
.footer-container {
  background: linear-gradient(135deg, rgba(14,165,233,0.05), rgba(16,185,129,0.08));
  border: 1px solid #e2e8f0;
  width: 100%;
  max-width: 100%;
  box-sizing: border-box;
  transition: all 0.3s ease;
}

/* Samakan dengan content-card yang ada padding */
section.col-lg-9 .footer-container {
  width: 100%;
  margin: 0;
  border-radius: 1rem;
  background: white;
  border: 1px solid #e2e8f0;
  box-shadow: 0 6px 16px rgba(14,165,233,0.1);
}

/* Hover efek */
.footer-container:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(14,165,233,0.15);
}

/* Link hover */
.hover-link {
  transition: color 0.2s ease, opacity 0.2s ease;
}
.hover-link:hover {
  color: #0369a1 !important;
  opacity: 1 !important;
}

/* Responsif */
@media (max-width: 575.98px) {
  .footer-container {
    max-width: 100%;
    padding: 1rem;
  }
  .footer-container p {
    font-size: 0.75rem !important;
  }
}
</style>

<script>
// Efek fade-in halus
document.addEventListener("DOMContentLoaded", () => {
  const footer = document.querySelector(".footer-container");
  if (footer) {
    footer.style.opacity = 0;
    footer.style.transition = "opacity 1s ease";
    setTimeout(() => footer.style.opacity = 1, 300);
  }
});
</script>

<!-- WhatsApp Support Floating Button -->
<?php
// nomor admin — ubah sesuai kebutuhan
$wa_number = '62895359046670';

// teks default
$wa_label_full = 'Butuh bantuan? Klik WhatsApp';
$wa_label_short = 'Bantuan';
$wa_message = 'Halo Admin, saya butuh bantuan';

// jika berada di tab absensi beri instruksi khusus
if (isset($active_tab) && $active_tab === 'absensi') {
    $wa_label_full = 'Gagal absen? Hubungi admin dan sebutkan nama Anda';
    $wa_label_short = 'Gagal absen?';
    $wa_message = 'Halo Admin, saya tidak bisa absen. Nama: ';
}

$wa_href = 'https://wa.me/' . $wa_number . '?text=' . rawurlencode($wa_message);
?>

<div class="whatsapp-float" aria-hidden="false">
  <a class="whatsapp-label" href="<?= htmlspecialchars($wa_href) ?>" target="_blank" rel="noopener noreferrer">
    <span class="whatsapp-label-full"><?= htmlspecialchars($wa_label_full) ?></span>
    <span class="whatsapp-label-short"><?= htmlspecialchars($wa_label_short) ?></span>
  </a>
  <a class="whatsapp-btn" href="<?= htmlspecialchars($wa_href) ?>" target="_blank" rel="noopener noreferrer" aria-label="Chat WhatsApp Admin">
    <i class="fab fa-whatsapp"></i>
    <span class="whatsapp-badge">1</span>
  </a>
</div>

<!-- End WhatsApp Support -->
