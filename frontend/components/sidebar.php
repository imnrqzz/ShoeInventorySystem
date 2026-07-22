        <?php
        /**
         * components/sidebar.php â€” Reusable Sidebar Navigation
         *
         * Desktop: fixed sidebar on the left side.
         * Mobile (<768px): sidebar hidden, replaced by a top bar with hamburger
         * button. Tapping it slides the sidebar in with a dark overlay.
         *
         * Set $activePage before including: 'dashboard', 'items', 'suppliers', etc.
         */
        $activePage = $activePage ?? '';
        ?>
        <!-- â”€â”€ Mobile Top Bar â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
             Only visible on screens < 768px. Gives mobile users a proper
             header with the hamburger menu, app name, and user initial. -->
        <div class="mobile-topbar">
            <button class="mobile-topbar-menu" onclick="document.querySelector('.sidebar').classList.add('open'); document.querySelector('.sidebar-overlay').classList.add('active');" aria-label="Open menu">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>
            <span class="mobile-topbar-title">ShoeInventory</span>
            <div class="mobile-topbar-avatar"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></div>
        </div>
        <!-- Overlay behind sidebar â€” tap to close -->
        <div class="sidebar-overlay" onclick="document.querySelector('.sidebar').classList.remove('open'); this.classList.remove('active');"></div>
        <aside class="sidebar">
            <div class="sidebar-brand">
                <div class="brand-icon"><img src="../images/shoes.png" alt="Logo"></div>
                <span>ShoeInventory</span>
            </div>
            <ul class="sidebar-nav">
                <li><a href="index.php" <?= $activePage === 'dashboard' ? 'class="active"' : '' ?>><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
                <li><a href="item.php" <?= $activePage === 'items' ? 'class="active"' : '' ?>><i class="fa-solid fa-shoe-prints"></i> Items</a></li>
                <li><a href="Supplier.php" <?= $activePage === 'suppliers' ? 'class="active"' : '' ?>><i class="fa-solid fa-truck-field"></i> Suppliers</a></li>
                <li><a href="stock.php" <?= $activePage === 'stock' ? 'class="active"' : '' ?>><i class="fa-solid fa-boxes-stacked"></i> Stock</a></li>
                <li><a href="transactions.php" <?= $activePage === 'transactions' ? 'class="active"' : '' ?>><i class="fa-solid fa-arrow-right-arrow-left"></i> Transactions</a></li>
                <?php if (isset($_SESSION['role']) && strtolower((string)$_SESSION['role']) === 'admin'): ?>
                <li><a href="user.php" <?= $activePage === 'users' ? 'class="active"' : '' ?>><i class="fa-solid fa-users"></i> Users</a></li>
                <?php endif; ?>

            </ul>
            <div class="sidebar-user">
                <div class="user-avatar"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></div>
                <div class="user-info">
                    <div class="user-name"><?= safe($_SESSION['username']) ?></div>
                    <div class="user-role"><?= safe($_SESSION['role'] ?? 'User') ?></div>
                </div>
                <a href="../backend/logout.php" class="logout-btn" title="Logout" onclick="event.preventDefault(); confirmLogout();">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                </a>
            </div>
        </aside>
