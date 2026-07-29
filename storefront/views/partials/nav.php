<?php
$currentPage = $_GET['page'] ?? 'home';
?>
<nav>
  <a href="index.php" class="nav-logo">SOLE<span>HAUS</span></a>
  <ul class="nav-links">
    <li><a href="index.php" class="<?= $currentPage === 'home' ? 'active' : '' ?>">Home</a></li>
    <li><a href="index.php#products">Collection</a></li>
    <li><a href="index.php?page=brands" class="<?= $currentPage === 'brands' ? 'active' : '' ?>">Brands</a></li>
    <li><a href="index.php?page=about" class="<?= $currentPage === 'about' ? 'active' : '' ?>">About</a></li>
  </ul>
  <div class="nav-actions">
    <!-- Theme Toggle Button -->
    <button id="themeToggleBtn" type="button" class="theme-toggle-btn" title="Toggle Light/Dark Theme" style="background:transparent; border:1px solid var(--border); border-radius:50%; width:36px; height:36px; cursor:pointer; color:var(--white); display:flex; align-items:center; justify-content:center; transition:background 0.2s;">
      <i class="fa-solid fa-moon"></i>
    </button>
    <div class="nav-search-wrap">
      <button type="button" class="nav-search-toggle" id="searchToggle" aria-label="Search shoes" aria-expanded="false">
        <i class="fa-solid fa-search"></i>
      </button>
      <label class="nav-search" aria-label="Search shoes">
        <i class="fa-solid fa-search"></i>
        <input id="productSearch" type="text" placeholder="Search shoes" autocomplete="off">
      </label>
      <div id="searchResults" class="search-results" aria-live="polite"></div>
    </div>
    <a href="index.php?page=cart" class="cart-badge <?= $currentPage === 'cart' ? 'active' : '' ?>" data-count="<?= max(0, (int)($cartCount ?? 0)) ?>"><i class="fa-solid fa-bag-shopping"></i></a>
    <div class="user-menu">
      <a href="#" class="user-menu-toggle" aria-haspopup="true" aria-expanded="false">
        <i class="fa-solid fa-circle-user"></i>
      </a>
      <div class="user-menu-panel" role="menu">
        <?php if (!empty($_SESSION['customer'])): ?>
          <div class="user-menu-header">
            <strong><?= htmlspecialchars($_SESSION['customer']['name'] ?? 'Account') ?></strong>
            <span><?= htmlspecialchars($_SESSION['customer']['email'] ?? '') ?></span>
          </div>
          <a href="index.php?page=logout" class="user-menu-link" role="menuitem">Logout</a>
        <?php else: ?>
          <div class="user-menu-header">
            <strong>Sign in to continue</strong>
            <span>Save favorites and manage your orders.</span>
          </div>
          <a href="index.php?page=login" class="user-menu-link" role="menuitem">Sign In</a>
          <a href="index.php?page=register" class="user-menu-link" role="menuitem">Sign Up</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>
