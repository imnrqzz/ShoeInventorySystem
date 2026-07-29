<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($product['name'] ?? 'Product') ?> — SOLEHAUS</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="public/css/style.css">
<script src="public/js/main.js"></script>
<style>
  .product-detail { display: flex; gap: 3rem; flex-wrap: wrap; align-items: flex-start; max-width: 1100px; margin: 0 auto; }
  .product-detail-img { flex: 1 1 380px; min-width: 300px; border-radius: 14px; overflow: hidden; background: #111; border: 1px solid var(--border); aspect-ratio: 1 / 1; display: flex; align-items: center; justify-content: center; }
  .product-detail-img img { width: 100%; height: 100%; object-fit: cover; }
  .product-detail-info { flex: 1 1 320px; min-width: 280px; }
  .product-detail-info .card-brand { font-size: 0.95rem; margin-bottom: 0.5rem; }
  .product-detail-info h1 { font-family: 'Bebas Neue', sans-serif; font-size: clamp(2rem, 4vw, 2.8rem); margin: 0 0 1rem; letter-spacing: 0.02em; }
  .product-detail-price { font-size: 1.6rem; font-weight: 600; margin-bottom: 1rem; }
  .product-detail-actions { display: flex; gap: 0.75rem; margin-top: 1.5rem; flex-wrap: wrap; }
  .product-detail-actions button { padding: 0.9rem 1.5rem; border-radius: 8px; font-weight: 600; cursor: pointer; border: none; }
  .btn-add { background: #7CFF6B; color: #0a0a0a; }
  .btn-wish { background: transparent; color: var(--white); border: 1px solid var(--border) !important; }
</style>
</head>
<body data-logged-in="<?= !empty($_SESSION['customer']) ? '1' : '0' ?>">
<nav>
  <a href="index.php" class="nav-logo">SOLE<span>HAUS</span></a>
  <ul class="nav-links">
    <li><a href="index.php">Home</a></li>
    <li><a href="index.php#products">Collection</a></li>
    <li><a href="index.php?page=customizer">Customizer</a></li>
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

<section class="products" style="padding-top: 8rem;">
  <div style="max-width:1100px; margin: 0 auto 1rem; display:flex; align-items:center; gap:1rem;">
    <a href="javascript:history.back()" class="btn-back" style="color:var(--muted); text-decoration:none; font-weight:600;">← Back</a>
  </div>
  <?php if (!$product): ?>
    <div class="section-header">
      <div>
        <div class="section-eyebrow">Not found</div>
        <div class="section-title">PRODUCT UNAVAILABLE</div>
      </div>
    </div>
    <p style="color: var(--muted);">This product couldn't be found. <a href="index.php#products" style="color: var(--white);">Back to the collection</a>.</p>
  <?php else: ?>
    <div class="product-detail">
      <div class="product-detail-img">
        <?php if ($product['image']): ?>
          <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
        <?php else: ?>
          <div class="card-placeholder">
            <i class="fa-solid fa-shoe-prints"></i>
            <span>No image</span>
          </div>
        <?php endif; ?>
      </div>
      <div class="product-detail-info">
        <div class="card-brand"><?= htmlspecialchars($product['brand']) ?></div>
        <h1><?= htmlspecialchars($product['name']) ?></h1>
        <div class="product-detail-price">₱<?= number_format((float) $product['price'], 2) ?></div>
        <div class="card-stock <?= $product['stockClass'] ?>"><?= $product['stockLabel'] ?></div>
        <p style="color: var(--muted); line-height: 1.7; margin-top: 1.25rem;">
          A SoleHaus pick from our <?= htmlspecialchars($product['category']) ?> lineup. Crafted for everyday
          wear with premium materials and a clean silhouette.
        </p>
        <div class="product-detail-actions">
          <button type="button" class="btn-add" id="detailAddCart"
                  data-id="<?= (int) $product['id'] ?>"
                  data-name="<?= htmlspecialchars($product['name']) ?>"
                  data-brand="<?= htmlspecialchars($product['brand']) ?>"
                  data-price="<?= htmlspecialchars((string) $product['price']) ?>">
            <i class="fa-solid fa-bag-shopping"></i> Add to Cart
          </button>
          <button type="button" class="btn-wish" id="detailAddWishlist"
                  data-name="<?= htmlspecialchars($product['name']) ?>"
                  data-brand="<?= htmlspecialchars($product['brand']) ?>"
                  data-price="<?= htmlspecialchars((string) $product['price']) ?>">
            <i class="fa-regular fa-heart"></i> Wishlist
          </button>
        </div>
      </div>
    </div>
  <?php endif; ?>
</section>

<footer>
  <div class="logo">SOLE<span>HAUS</span></div>
  <div>© <?= htmlspecialchars($year) ?> SoleHaus. All rights reserved.</div>
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

<script>
(function () {
  function wire(buttonId, param) {
    const btn = document.getElementById(buttonId);
    if (!btn) return;

    btn.addEventListener('click', () => {
      const url = param === 'add'
        ? `index.php?page=cart&${param}=${encodeURIComponent(btn.dataset.name)}&brand=${encodeURIComponent(btn.dataset.brand)}&price=${encodeURIComponent(btn.dataset.price)}&product_id=${encodeURIComponent(btn.dataset.id || '')}`
        : `index.php?page=cart&${param}=${encodeURIComponent(btn.dataset.name)}&brand=${encodeURIComponent(btn.dataset.brand)}&price=${encodeURIComponent(btn.dataset.price)}`;
      submitCartAction(url).then(ok => {
        showToast(ok ? (param === 'add' ? 'Added to cart successfully' : 'Saved to wishlist') : 'Something went wrong');
      });
    });
  }

  wire('detailAddCart', 'add');
  wire('detailAddWishlist', 'wishlist');
})();
</script>
</body>
</html>