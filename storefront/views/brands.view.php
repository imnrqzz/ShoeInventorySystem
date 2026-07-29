<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Our Brands — SOLEHAUS</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="public/css/style.css">
<script src="public/js/main.js"></script>
</head>
<body data-logged-in="<?= !empty($_SESSION['customer']) ? '1' : '0' ?>">

<!-- NAV -->
<nav>
  <a href="index.php" class="nav-logo">SOLE<span>HAUS</span></a>
  <ul class="nav-links">
    <li><a href="index.php">Home</a></li>
    <li><a href="index.php#products">Collection</a></li>
    <li><a href="index.php?page=brands" class="active">Brands</a></li>
    <li><a href="index.php?page=about">About</a></li>
  </ul>
  <div class="nav-actions">
    <!-- Theme Toggle Button -->
    <button id="themeToggleBtn" type="button" class="theme-toggle-btn" title="Toggle Light/Dark Theme" style="background:transparent; border:1px solid var(--border); border-radius:50%; width:36px; height:36px; cursor:pointer; color:var(--white); display:flex; align-items:center; justify-content:center; transition:background 0.2s; margin-right: 0.5rem;">
      <i class="fa-solid fa-moon"></i>
    </button>
    <a href="index.php?page=cart" class="cart-badge" data-count="<?= max(0, (int)($cartCount ?? 0)) ?>"><i class="fa-solid fa-bag-shopping"></i></a>
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

<!-- BRANDS SECTION -->
<section class="products" style="padding-top: 8rem; min-height: calc(100vh - 150px);">
  <div class="section-header">
    <div>
      <div class="section-eyebrow">Curated Collection</div>
      <div class="section-title">BRANDS WE CARRY</div>
    </div>
  </div>

  <?php if (empty($brands)): ?>
    <p style="color: var(--muted); text-align: center; margin-top: 3rem;">No brands found.</p>
  <?php else: ?>
    <?php foreach ($brands as $brandName => $brandProducts): ?>
      <div style="margin-bottom: 4rem;">
        <h2 style="font-family: var(--display); font-size: 2.2rem; letter-spacing: 0.05em; color: var(--white); border-bottom: 1px solid var(--border); padding-bottom: 0.5rem; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between;">
          <span><?= htmlspecialchars($brandName) ?></span>
          <span style="font-size: 0.9rem; font-family: var(--body); color: var(--muted); font-weight: normal;"><?= count($brandProducts) ?> Items</span>
        </h2>
        <div class="product-grid" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 2rem;">
          <?php foreach ($brandProducts as $p): ?>
            <div class="product-card" data-cat="<?= htmlspecialchars($p['category']) ?>">
              <a href="index.php?page=product&id=<?= urlencode($p['id']) ?>" style="text-decoration:none; color:inherit; display:block;">
                <div class="card-img-wrap">
                  <?php if ($p['image']): ?>
                    <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                  <?php else: ?>
                    <div class="card-placeholder">
                      <i class="fa-solid fa-shoe-prints"></i>
                      <span>No image</span>
                    </div>
                  <?php endif; ?>
                  <div class="card-badge">New</div>
                  <div class="card-actions">
                    <button class="action-btn" data-action="wishlist" title="Wishlist"><i class="fa-solid fa-heart"></i></button>
                    <button class="action-btn" data-action="cart" title="Add to Cart"><i class="fa-solid fa-bag-shopping"></i></button>
                  </div>
                </div>
                <div class="card-body">
                  <div class="card-brand"><?= htmlspecialchars($p['brand']) ?></div>
                  <div class="card-name"><?= htmlspecialchars($p['name']) ?></div>
                  <div class="card-footer">
                    <div class="card-price">₱<?= number_format($p['price'], 2) ?></div>
                    <div class="card-stock <?= $p['stockClass'] ?>"><?= $p['stockLabel'] ?></div>
                  </div>
                </div>
              </a>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</section>

<!-- FOOTER -->
<footer>
  <div class="logo">SOLE<span>HAUS</span></div>
  <div>© <?= htmlspecialchars($year) ?> SoleHaus. All rights reserved.</div>
</footer>

</body>
</html>
