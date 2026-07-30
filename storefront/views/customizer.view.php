<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>3D Customizer — SOLEHAUS</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="public/css/style.css">
<link rel="stylesheet" href="public/css/customizer.css">
<script src="public/js/main.js"></script>
</head>
<body>

<!-- NAV -->
<?php require __DIR__ . '/partials/nav.php'; ?>


<!-- CUSTOMIZER HEADER -->
<section class="customizer-header">
  <a href="index.php" class="customizer-back"><i class="fa-solid fa-arrow-left"></i> Back to Shop</a>
  <div class="section-eyebrow">Build Your Own Pair</div>
  <div class="section-title">3D CUSTOMIZER</div>
  <p class="customizer-sub">
    Rotate, recolor, and reshape a 3D shoe model in real time. Powered by the
    open-source <strong>3D Shoe Customizer</strong> by Natalí Palacio Pastor.
  </p>
</section>

<!-- CUSTOMIZER EMBED -->
<section class="customizer-frame-wrap">
  <iframe
    class="customizer-frame"
    src="<?= htmlspecialchars($customizerUrl) ?>"
    title="3D Shoe Customizer"
    loading="lazy"
    allow="fullscreen"
    allowfullscreen>
  </iframe>
</section>

<!-- FOOTER -->
<footer>
  <div class="logo">SOLE<span>HAUS</span></div>
  <div>© <?= htmlspecialchars($year) ?> SoleHaus. All rights reserved.</div>
  <div style="display:flex;gap:1.25rem">
    <a href="#" style="color:var(--muted)"><i class="fa-brands fa-instagram"></i></a>
    <a href="#" style="color:var(--muted)"><i class="fa-brands fa-tiktok"></i></a>
    <a href="#" style="color:var(--muted)"><i class="fa-brands fa-facebook"></i></a>
  </div>
</footer>

<div id="actionToast" class="action-toast" role="status" aria-live="polite"></div>

<script>
window.addEventListener('message', function(event) {
  if (event.data && event.data.type === 'CUSTOMIZER_READY') {
    const savedCustom = <?= json_encode($savedCustomization ?? null) ?>;
    if (savedCustom) {
      event.source.postMessage({
        type: 'LOAD_CUSTOMIZATION',
        payload: savedCustom
      }, event.origin);
    }
  }

  if (event.data && event.data.type === 'ADD_TO_CART') {
    const payload = event.data.payload;
    fetch('index.php?page=cart-action', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-Token': '<?= csrf_token() ?>'
      },
      body: JSON.stringify({
        action: 'add_custom',
        productId: payload.productId,
        editCartItemId: payload.editCartItemId || null,
        layerColor: payload.layerColor,
        layerSize: payload.layerSize,
        shoeSize: payload.shoeSize || 9
      })
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        showToast(data.message || 'Custom shoe added to cart!');
        const badge = document.querySelector('.cart-badge');
        if (badge) badge.setAttribute('data-count', data.cartCount);
        event.source.postMessage({ type: 'ADD_TO_CART_SUCCESS', cartCount: data.cartCount }, event.origin);
      } else {
        showToast(data.message || 'Error adding to cart');
        event.source.postMessage({ type: 'ADD_TO_CART_ERROR', message: data.message }, event.origin);
      }
    })
    .catch(() => {
      showToast('Connection error');
      event.source.postMessage({ type: 'ADD_TO_CART_ERROR', message: 'Connection error' }, event.origin);
    });
  }
});
</script>
</body>
</html>