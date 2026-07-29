<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Cart — SOLEHAUS</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="public/css/style.css">
<script src="public/js/main.js"></script>
</head>
<body>
<nav>
  <a href="index.php" class="nav-logo">SOLE<span>HAUS</span></a>
  <ul class="nav-links">
    <li><a href="index.php">Home</a></li>
    <li><a href="index.php#products">Collection</a></li>
    <li><a href="index.php?page=brands">Brands</a></li>
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

<section class="products" style="padding-top: 8rem;">
  <div class="section-header">
    <div>
      <div class="section-eyebrow">Your Orders</div>
      <div class="section-title">CART & TRACKING</div>
      <div class="checkout-notice" style="margin-top: 0.5rem; padding: 0.75rem 1rem; border-left: 4px solid var(--accent); background: rgba(37, 99, 235, 0.08); color: #2563eb; font-size: 0.9rem; border-radius: 4px; display: inline-block;">
        <i class="fa-solid fa-circle-info"></i> <strong>Reservation Only:</strong> This is a storefront demo. No payment processing is enabled; adding items allows reservation and stock simulation.
      </div>
    </div>
    <div class="filter-tabs">
      <span class="filter-tab active">Cart</span>
      <span class="filter-tab">Shipping</span>
      <span class="filter-tab">Received</span>
    </div>
  </div>

  <?php if (!empty($message)): ?>
    <div class="auth-alert success" style="margin-bottom: 1.5rem; padding: 0.75rem 1rem; border-left: 4px solid var(--accent); background: rgba(37, 99, 235, 0.08); color: #2563eb; border-radius: 4px;">
      <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($message) ?>
    </div>
  <?php endif; ?>
  <?php if (!empty($error)): ?>
    <div class="auth-alert error" style="margin-bottom: 1.5rem; padding: 0.75rem 1rem; border-left: 4px solid #FF6384; background: rgba(255, 99, 132, 0.08); color: #FF6384; border-radius: 4px;">
      <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>

  <div class="product-grid" style="grid-template-columns: 1fr; gap: 1.5rem; max-width: 800px; margin: 0 auto;">
    <div class="product-card" style="padding: 1.25rem;">
      <div class="card-brand">Cart</div>
      <div class="card-name">Items added by you</div>
      <?php if (!empty($cartItems)): ?>
        <ul style="list-style:none; display:flex; flex-direction:column; gap:0.75rem; margin-top: 1rem; color: var(--muted);">
          <?php foreach ($cartItems as $item): ?>
            <li style="display:flex; justify-content:space-between; gap:1rem; align-items:center; border-bottom:1px solid var(--border); padding-bottom:0.5rem; width: 100%;">
              <?php 
                $isCustom = !empty($_SESSION['cart_customizations'][$item['id']]);
                $itemLink = $isCustom 
                  ? "index.php?page=customizer&productId=" . urlencode($item['item_id']) . "&editCartItemId=" . urlencode($item['id'])
                  : "index.php?page=product&id=" . urlencode($item['item_id']);
              ?>
              <a href="<?= $itemLink ?>" style="text-decoration:none; color:inherit; display:block; flex-grow:1;">
                <strong style="display:block; color: var(--white);"><?= htmlspecialchars($item['name'] ?? 'Item') ?></strong>
                <small><?= htmlspecialchars($item['brand'] ?? 'SoleHaus') ?> × <?= (int)($item['qty'] ?? 1) ?></small>
                <?php if ($isCustom):
                  $custom = $_SESSION['cart_customizations'][$item['id']]; ?>
                  <div class="custom-specs" style="margin-top: 0.5rem; font-size: 0.8rem; background: rgba(255,255,255,0.03); padding: 0.5rem; border-radius: 4px; border: 1px solid var(--border);">
                    <div style="color: var(--accent); font-weight:600; margin-bottom: 2px;">3D Customization (Click to Edit):</div>
                    <div style="color: var(--white); margin-bottom: 4px; font-weight: 500;">Size: US <?= htmlspecialchars($custom['shoeSize'] ?? '9') ?></div>
                    <ul style="list-style:none; padding:0; margin:0; display:flex; flex-wrap:wrap; gap:0.5rem;">
                      <?php foreach ($custom['colors'] as $layer => $color): ?>
                        <li><span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:<?= htmlspecialchars($color) ?>; margin-right:3px; vertical-align:middle; border:1px solid #555;"></span><?= ucfirst($layer) ?></li>
                      <?php endforeach; ?>
                    </ul>
                  </div>
                <?php endif; ?>
              </a>
              <div style="display:flex; flex-direction:column; align-items:flex-end; gap:0.25rem;">
                <span style="color: var(--white); font-weight: 500;">₱<?= number_format((float)($item['price'] ?? 0) * (int)($item['qty'] ?? 1), 2) ?></span>
                <a href="index.php?page=cart&remove_cart=<?= urlencode($item['id'] ?? '') ?>" style="color: var(--muted); font-size:0.8rem; text-decoration:none;" onmouseover="this.style.color='#FF6384'" onmouseout="this.style.color='var(--muted)'">Remove</a>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
        <div style="margin-top: 1.5rem; border-top: 1px solid var(--border); padding-top: 1rem; display: flex; justify-content: space-between; align-items: center;">
          <strong style="color: var(--white);">Total Subtotal:</strong>
          <strong style="color: var(--accent); font-size: 1.2rem;">₱<?= number_format($subtotal, 2) ?></strong>
        </div>
        <form action="index.php?page=checkout" method="POST" style="margin-top: 1rem;" onsubmit="return confirm('Warning: Proceeding will reserve these items and subtract their stock from the inventory system database. Are you sure you want to place this order?');">
          <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">
          <button type="submit" class="checkout-submit-btn" style="width: 100%; justify-content: center; background: var(--accent); color: var(--black); font-weight: 600; padding: 0.75rem; border-radius: 4px; border: none; cursor: pointer; transition: opacity 0.2s; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-credit-card"></i> Proceed to Checkout
          </button>
        </form>
      <?php else: ?>
        <p style="margin-top: 1rem; color: var(--muted);">No items added yet. Browse the collection and tap the bag icon to save something here.</p>
      <?php endif; ?>
    </div>

    <div class="product-card" style="padding: 1.25rem;">
      <div class="card-brand">Wishlist</div>
      <div class="card-name">Saved for later</div>
      <?php if (!empty($wishlistItems)): ?>
        <ul style="list-style:none; display:flex; flex-direction:column; gap:0.75rem; margin-top: 1rem; color: var(--muted);">
          <?php foreach ($wishlistItems as $item): ?>
            <li style="display:flex; justify-content:space-between; gap:1rem; align-items:center; border-bottom:1px solid var(--border); padding-bottom:0.5rem;">
              <span>
                <strong style="display:block; color: var(--white);"><?= htmlspecialchars($item['name'] ?? 'Wish Item') ?></strong>
                <small><?= htmlspecialchars($item['brand'] ?? 'SoleHaus') ?> × <?= (int)($item['qty'] ?? 1) ?></small>
              </span>
              <div style="display:flex; align-items:center; gap:0.75rem;">
                <span>₱<?= number_format((float)($item['price'] ?? 0), 2) ?></span>
                <a href="index.php?page=cart&remove_wishlist=<?= urlencode($item['name'] ?? '') ?>" style="color: var(--muted); font-size:0.9rem;">Remove</a>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p style="margin-top: 1rem; color: var(--muted);">No wishlist items yet. Save favorites from the shop to see them here.</p>
      <?php endif; ?>
    </div>
  </div>
</section>

<footer>
  <div class="logo">SOLE<span>HAUS</span></div>
  <div>© <?= htmlspecialchars($year) ?> SoleHaus. All rights reserved.</div>
</footer>
</body>
</html>
