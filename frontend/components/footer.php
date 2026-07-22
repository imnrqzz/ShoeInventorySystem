    <?php
    /**
     * components/footer.php - Shared page footer
     *
     * Loads shared JavaScript and closes the HTML document.
     */
    require_once __DIR__ . '/../../backend/utils/helpers.php';
    $serverIp = getServerIp();
    ?>
    <script>window.csrfToken = '<?= csrf_token() ?>';</script>
    <script>window.serverIp = '<?= $serverIp ?>';</script>
    <script src="../js/validation-rules.js"></script>
    <script src="../js/form-validation.js"></script>
    <script src="../js/confirm-modal.js"></script>
    <script src="../js/qr-scanner.js"></script>
</body>
</html>