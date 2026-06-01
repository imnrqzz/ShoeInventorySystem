        <?php
        /**
         * components/sidebar.php — Reusable Sidebar Navigation
         *
         * Best Practice: Extract repeated HTML into a component file so changes
         * only need to be made in one place.
         *
         * Set $activePage before including: 'dashboard', 'items', 'suppliers', etc.
         *
         * On mobile (below 768px), the sidebar is hidden off-screen. The hamburger
         * button (mobile-menu-toggle) slides it in. The overlay closes it on tap.
         */
        $activePage = $activePage ?? '';
        ?>
        <!-- Mobile hamburger button — only visible on small screens -->
        <button class="mobile-menu-toggle" onclick="document.querySelector('.sidebar').classList.toggle('open'); document.querySelector('.sidebar-overlay').classList.toggle('active');" aria-label="Toggle menu">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>
        <!-- Overlay behind sidebar on mobile — tap to close -->
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
                <li><a href="user.php" <?= $activePage === 'users' ? 'class="active"' : '' ?>><i class="fa-solid fa-users"></i> Users</a></li>
                <li><a href="reports.php" <?= $activePage === 'reports' ? 'class="active"' : '' ?>><i class="fa-solid fa-file-lines"></i> Reports</a></li>
            </ul>
            <div class="sidebar-user">
                <div class="user-avatar"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></div>
                <div class="user-info">
                    <div class="user-name"><?= safe($_SESSION['username']) ?></div>
                    <div class="user-role">User</div>
                </div>
                <a href="../backend/logout.php" class="logout-btn" title="Logout" onclick="event.preventDefault(); confirmLogout();">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                </a>
            </div>
        </aside>
