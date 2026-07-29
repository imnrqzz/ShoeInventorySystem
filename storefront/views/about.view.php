<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>About Us — SOLEHAUS</title>
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
    <li><a href="index.php?page=brands">Brands</a></li>
    <li><a href="index.php?page=about" class="active">About</a></li>
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

<!-- ABOUT SECTION -->
<section class="products" style="padding-top: 8rem; min-height: calc(100vh - 150px);">
  <div class="section-header" style="max-width:800px; margin:0 auto 2rem;">
    <div>
      <div class="section-eyebrow">Our Story</div>
      <div class="section-title">ABOUT SOLEHAUS</div>
    </div>
  </div>

  <div style="max-width:800px; margin: 0 auto; line-height:1.8; color:var(--muted); font-size:1.05rem;">
    <p style="margin-bottom:1.5rem;">
      Welcome to <strong>SOLEHAUS</strong>, the ultimate destination for premium sneakers and custom footwear. 
      Founded in 2026, we specialize in offering a curated collection of classic silhouettes, high-performance athletic shoes, 
      and state-of-the-art lifestyle footwear.
    </p>
    <p style="margin-bottom:1.5rem;">
      Our mission is to push the boundaries of sneaker customization. By combining our premium supply lines with an advanced 
      <strong>3D Shoe Customizer</strong>, we enable customers to design and personalize their sneakers in real time, 
      bringing unique styles directly to life.
    </p>
    <p style="margin-bottom:1.5rem;">
      Whether you are looking for classic comfort, sport performance, or custom-tailored creations, SoleHaus connects 
      superior craftsmanship with agentic designs to give you the perfect fit.
    </p>
  </div>
</section>

<!-- FOOTER -->
<footer>
  <div class="logo">SOLE<span>HAUS</span></div>
  <div>© <?= htmlspecialchars($year) ?> SoleHaus. All rights reserved.</div>
</footer>

</body>
</html>
