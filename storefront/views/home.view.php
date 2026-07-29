<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SOLEHAUS — Premium Sneakers</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="public/css/style.css">
<script src="public/js/main.js"></script>
</head>
<body data-logged-in="<?= !empty($_SESSION['customer']) ? '1' : '0' ?>">

<!-- NAV -->
<?php require __DIR__ . '/partials/nav.php'; ?>

<!-- HERO -->
<section class="hero">
  <div class="hero-bg-text">SNEAKERS</div>
  <div class="hero-content">
    <div class="hero-tag">New Drop — SS 2026</div>
    <h1>STEP INTO <em>YOUR</em> STYLE</h1>
    <p>Premium sneakers sourced from the world's most iconic brands. Built for those who treat every step as a statement.</p>
    <div class="hero-actions">
      <a href="#products" class="btn-primary">Shop Now <i class="fa-solid fa-arrow-right"></i></a>
      <a href="#products" class="btn-ghost">View All <i class="fa-solid fa-chevron-right" style="font-size:0.7rem"></i></a>
    </div>
    <div class="hero-stats">
      <div>
        <div class="stat-num"><?= count($products) ?>+</div>
        <div class="stat-label">Products</div>
      </div>
      <div>
        <div class="stat-num"><?= (int) $brandCount ?>+</div>
        <div class="stat-label">Brands</div>
      </div>
      <div>
        <div class="stat-num">100%</div>
        <div class="stat-label">Authentic</div>
      </div>
    </div>
  </div>
  <div class="hero-image-wrap">
    <div class="hero-glow"></div>
    <img class="hero-img"
         src="/ShoeInventorySystem/images/solehaus_logo.jpg"
         alt="SOLEHAUS Logo"
         style="border-radius: 20px; box-shadow: var(--border);">
  </div>
</section>

<!-- MARQUEE -->
<div class="marquee-wrap" id="brands">
  <div class="marquee-track">
    <?php
    // Print the brand list twice for a seamless CSS marquee loop
    foreach (array_merge($brands, $brands) as $b) {
        echo "<div class='marquee-item'>" . htmlspecialchars($b) . " <span class='dot'>●</span></div>";
    }
    ?>
  </div>
</div>

<!-- PRODUCTS -->
<section class="products" id="products">
  <div class="section-header">
    <div>
      <div class="section-eyebrow">Our Collection</div>
      <div class="section-title">FEATURED DROPS</div>
    </div>
    <div class="filter-tabs">
      <button class="filter-tab active" onclick="filterTab(this,'all')">All</button>
      <button class="filter-tab" onclick="filterTab(this,'lifestyle')">Lifestyle</button>
      <button class="filter-tab" onclick="filterTab(this,'classic')">Classic</button>
      <button class="filter-tab" onclick="filterTab(this,'sport')">Sport</button>
    </div>
  </div>

  <div class="product-grid">
    <?php foreach ($products as $p): ?>
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
</section>

<!-- BANNER -->
<div class="banner" id="sale">
  <div class="banner-bg">CUSTOMIZE</div>
  <div class="banner-content">
    <h2>BUILD YOUR OWN PAIR</h2>
    <p>Use our 3D customizer to design your perfect sneaker from the ground up.</p>
  </div>
  <a href="index.php?page=customizer" class="btn-dark">Try 3D Customizer</a>
</div>

<!-- FOOTER -->
<footer id="about">
  <div class="logo">SOLE<span>HAUS</span></div>
  <div>© <?= htmlspecialchars($year) ?> SoleHaus. All rights reserved.</div>
  <div style="display:flex;gap:1.25rem">
    <a href="https://www.instagram.com/" target="_blank" rel="noopener noreferrer" style="color:var(--muted)"><i class="fa-brands fa-instagram"></i></a>
    <a href="https://www.tiktok.com/" target="_blank" rel="noopener noreferrer" style="color:var(--muted)"><i class="fa-brands fa-tiktok"></i></a>
    <a href="https://www.facebook.com/" target="_blank" rel="noopener noreferrer" style="color:var(--muted)"><i class="fa-brands fa-facebook"></i></a>
  </div>
</footer>

<div id="authModal" class="auth-modal" aria-hidden="true">
  <div class="auth-modal-card" role="dialog" aria-modal="true" aria-labelledby="authModalTitle">
    <button type="button" class="auth-modal-close" aria-label="Close">×</button>
    <div class="section-eyebrow">Access required</div>
    <h3 id="authModalTitle" class="section-title" style="font-size: clamp(1.6rem, 3vw, 2.3rem);">SIGN IN TO CONTINUE</h3>
    <p style="color: var(--muted); line-height: 1.7; margin-top: 0.75rem;">Create an account or log in to save favorites, add items to your cart, and track your orders.</p>
    <div class="hero-actions" style="margin-top: 1.5rem; flex-wrap: wrap;">
      <a href="index.php?page=login" class="btn-primary">Log In</a>
      <a href="index.php?page=register" class="btn-ghost">Create Account</a>
    </div>
  </div>
</div>

<div id="actionToast" class="action-toast" role="status" aria-live="polite"></div>

</body>
</html>