    <?php
    /**
     * components/footer.php - Shared page footer
     *
     * Loads shared JavaScript and closes the HTML document.
     * - validation-rules.js: shared validation rules (mirrors PHP validation_rules.php)
     * - form-validation.js: automatic validation for forms with data-validate
     * - confirm-modal.js: styled confirmation dialogs for delete/logout
     *
     * Include this at the bottom of every authenticated page.
     */
    // Auto-detect server IP for QR scanner (works across WiFi networks)
    $serverIp = gethostbyname(gethostname());
    if ($serverIp === '127.0.0.1' || $serverIp === gethostname()) {
        $sock = @socket_create(AF_INET, SOCK_DGRAM, 0);
        if ($sock) {
            @socket_connect($sock, '8.8.8.8', 80);
            @socket_getsockname($sock, $serverIp);
            @socket_close($sock);
        }
    }
    ?>
    <script>window.csrfToken = '<?= csrf_token() ?>';</script>
    <script>window.serverIp = '<?= $serverIp ?>';</script>
    <script src="../js/validation-rules.js"></script>
    <script src="../js/form-validation.js"></script>
    <script src="../js/confirm-modal.js"></script>
    <script src="../js/qr-scanner.js"></script>
</body>
</html>