            <!-- Actions Dropdown Menu -->
            <div id="actionsMenu" class="dropdown-menu" style="display:none;position:absolute;right:20px;top:60px;background:var(--color-surface);border:1px solid var(--color-border);border-radius:var(--radius-sm);min-width:180px;z-index:20;box-shadow:0 4px 12px rgba(0,0,0,0.1);">
                <a href="#" onclick="document.getElementById('addItemModal').style.display='flex'; closeMenu(); return false;" style="display:flex;align-items:center;gap:8px;padding:10px 14px;color:var(--color-text);text-decoration:none;font-size:var(--font-size-sm);"><i class="fa-solid fa-plus"></i> Add New Item</a>
                <a href="#" onclick="window.qrScanner.open(); closeMenu(); return false;" style="display:flex;align-items:center;gap:8px;padding:10px 14px;color:var(--color-text);text-decoration:none;font-size:var(--font-size-sm);"><i class="fa-solid fa-qrcode"></i> Scan QR / Barcode</a>
                <a href="qr_generator.php" style="display:flex;align-items:center;gap:8px;padding:10px 14px;color:var(--color-text);text-decoration:none;font-size:var(--font-size-sm);"><i class="fa-solid fa-print"></i> Print QR Codes</a>
                <a href="export_xml.php" style="display:flex;align-items:center;gap:8px;padding:10px 14px;color:var(--color-text);text-decoration:none;font-size:var(--font-size-sm);"><i class="fa-solid fa-file-export"></i> Export XML</a>
                <a href="#" onclick="document.getElementById('importXmlModal').style.display='flex'; closeMenu(); return false;" style="display:flex;align-items:center;gap:8px;padding:10px 14px;color:var(--color-text);text-decoration:none;font-size:var(--font-size-sm);"><i class="fa-solid fa-file-import"></i> Import XML</a>
            </div>
            <script>
            function toggleActionsMenu(e) {
                e.stopPropagation();
                var menu = document.getElementById('actionsMenu');
                menu.style.display = menu.style.display === "none" ? "block" : "none";
            }
            function closeMenu() { document.getElementById('actionsMenu').style.display = "none"; }
            document.addEventListener("click", function(e) {
                var menu = document.getElementById('actionsMenu');
                if (menu && !menu.contains(e.target)) closeMenu();
            });
            </script>