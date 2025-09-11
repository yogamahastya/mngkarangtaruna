<footer class="text-center mt-5">
    <div class="copyright-box">
        <p class="copyright-text" style="font-size: 0.8rem;">
            &copy; <?= date('Y') ?> 
            <a href="http://nuxera.my.id" target="_blank" style="color: inherit; text-decoration: none;">nuxera</a>
            <a href="https://www.linkedin.com/in/yogamahastya" target="_blank">
                <i class="fab fa-linkedin" style="color: #0077B5; margin-left: 5px;"></i>
            </a>
        </p>
        <p class="copyright-text" style="font-size: 0.8rem; margin-top: -10px;">
            <?php
            // Temukan jalur file dari lokasi footer.php, lalu navigasi ke folder application
            $versionFile = __DIR__ . '/application/version.json';
            
            if (file_exists($versionFile)) {
                $versionData = json_decode(file_get_contents($versionFile), true);
                if ($versionData && isset($versionData['version'])) {
                    echo 'v' . htmlspecialchars($versionData['version']);
                }
            }
            ?>
        </p>
    </div>
</footer>