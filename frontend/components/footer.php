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
    ?>
    <script>window.csrfToken = '<?= csrf_token() ?>';</script>
    <script src="../js/validation-rules.js"></script>
    <script src="../js/form-validation.js"></script>
    <script src="../js/confirm-modal.js"></script>
</body>
</html>