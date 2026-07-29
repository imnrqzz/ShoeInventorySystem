(function () {
  const savedTheme = localStorage.getItem('solehaus-theme') || 'light';
  document.documentElement.setAttribute('data-theme', savedTheme);
})();

let activeProductCategory = 'all';
let productSearchTerm = '';

function applyProductFilters() {
  document.querySelectorAll('.product-card').forEach(card => {
    const category = card.dataset.cat || 'lifestyle';
    const name = card.querySelector('.card-name')?.textContent?.toLowerCase() || '';
    const brand = card.querySelector('.card-brand')?.textContent?.toLowerCase() || '';
    const matchesCategory = activeProductCategory === 'all' || category === activeProductCategory;
    const matchesSearch = !productSearchTerm || name.includes(productSearchTerm) || brand.includes(productSearchTerm);

    card.style.display = matchesCategory && matchesSearch ? '' : 'none';
  });
}

function filterTab(el, cat) {
  document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
  el.classList.add('active');
  activeProductCategory = cat;
  applyProductFilters();
}

function closeUserMenu() {
  document.querySelectorAll('.user-menu-panel').forEach(panel => panel.classList.remove('show'));
  document.querySelectorAll('.user-menu-toggle').forEach(toggle => toggle.classList.remove('active'));
}

function showAuthModal() {
  const modal = document.getElementById('authModal');
  if (modal) {
    modal.classList.add('show');
    modal.setAttribute('aria-hidden', 'false');
  }
}

function hideAuthModal() {
  const modal = document.getElementById('authModal');
  if (modal) {
    modal.classList.remove('show');
    modal.setAttribute('aria-hidden', 'true');
  }
}

function showToast(message) {
  const toast = document.getElementById('actionToast');
  if (!toast) return;

  toast.textContent = message;
  toast.classList.add('show');
  window.clearTimeout(showToast.timeout);
  showToast.timeout = window.setTimeout(() => {
    toast.classList.remove('show');
  }, 1800);
}

function submitCartAction(url) {
  const isLoggedIn = document.body.dataset.loggedIn === '1';
  if (!isLoggedIn) {
    showAuthModal();
    return Promise.resolve(false);
  }
  window.location.href = url;
  return Promise.resolve(true);
}

function setupProductActions() {
  document.querySelectorAll('.action-btn').forEach(button => {
    button.addEventListener('click', (event) => {
      event.preventDefault();
      event.stopPropagation();

      const action = button.dataset.action;
      const card = button.closest('.product-card');
      const productName = card?.querySelector('.card-name')?.textContent?.trim() || 'this product';

      if (action === 'wishlist' || action === 'cart') {
        const isLoggedIn = document.body.dataset.loggedIn === '1';
        if (!isLoggedIn) {
          showAuthModal();
          return;
        }
        const name = productName;
        const brand = card?.querySelector('.card-brand')?.textContent?.trim() || 'SoleHaus';
        const priceText = card?.querySelector('.card-price')?.textContent?.trim().replace('₱', '').replace(',', '') || '0';
        showToast(action === 'cart' ? 'Added to cart' : 'Saved to wishlist');
        window.setTimeout(() => {
          window.location.href = `index.php?page=cart&${action === 'cart' ? 'add' : 'wishlist'}=${encodeURIComponent(name)}&brand=${encodeURIComponent(brand)}&price=${encodeURIComponent(priceText)}`;
        }, 300);
        return;
      }
    });
  });

  document.querySelectorAll('.product-card').forEach(card => {
    card.style.cursor = 'pointer';
    card.addEventListener('click', (event) => {
      if (event.target.closest('.action-btn')) {
        return;
      }
      const productId = card.dataset.id;
      if (productId) {
        window.location.href = `index.php?page=product&id=${productId}`;
      }
    });
  });
}

document.addEventListener('DOMContentLoaded', () => {
  const toggles = document.querySelectorAll('.user-menu-toggle');
  const panels = document.querySelectorAll('.user-menu-panel');
  const searchInput = document.getElementById('productSearch');
  const searchToggle = document.getElementById('searchToggle');
  const searchBox = document.querySelector('.nav-search');
  const searchResults = document.getElementById('searchResults');

  const productItems = Array.from(document.querySelectorAll('.product-card')).map(card => ({
    name: card.querySelector('.card-name')?.textContent?.trim() || '',
    brand: card.querySelector('.card-brand')?.textContent?.trim() || ''
  }));

  function renderSearchResults(term) {
    if (!searchResults) return;
    const normalized = term.trim().toLowerCase();
    const matches = productItems.filter(item => {
      return !normalized || item.name.toLowerCase().includes(normalized) || item.brand.toLowerCase().includes(normalized);
    });

    if (!normalized || matches.length === 0) {
      searchResults.innerHTML = '';
      searchResults.classList.remove('show');
      return;
    }

    searchResults.innerHTML = matches.slice(0, 6).map(item => `
      <a href="#products" data-search-result="${item.name}">${item.name} <small>• ${item.brand}</small></a>
    `).join('');
    searchResults.classList.add('show');
  }

  if (searchInput && searchToggle && searchBox) {
    searchToggle.addEventListener('click', () => {
      const isOpen = searchBox.classList.toggle('show');
      searchToggle.classList.toggle('active', isOpen);
      searchToggle.setAttribute('aria-expanded', String(isOpen));
      if (isOpen) {
        searchInput.focus();
        renderSearchResults(searchInput.value);
      } else {
        searchResults?.classList.remove('show');
      }
    });

    searchInput.addEventListener('input', () => {
      renderSearchResults(searchInput.value);
    });
  }

  toggles.forEach(toggle => {
    toggle.addEventListener('click', (event) => {
      event.preventDefault();
      const panel = toggle.closest('.user-menu')?.querySelector('.user-menu-panel');
      if (!panel) return;

      const isOpen = panel.classList.contains('show');
      closeUserMenu();

      if (!isOpen) {
        panel.classList.add('show');
        toggle.classList.add('active');
        toggle.setAttribute('aria-expanded', 'true');
      } else {
        toggle.setAttribute('aria-expanded', 'false');
      }
    });
  });

  setupProductActions();

  // Dark/Light Theme Toggle
  const themeToggleBtn = document.getElementById('themeToggleBtn');
  if (themeToggleBtn) {
    const icon = themeToggleBtn.querySelector('i');
    
    // Set initial toggle button icon state
    const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
    if (icon) {
      icon.className = currentTheme === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
    }

    themeToggleBtn.addEventListener('click', () => {
      const activeTheme = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
      document.documentElement.setAttribute('data-theme', activeTheme);
      localStorage.setItem('solehaus-theme', activeTheme);
      if (icon) {
        icon.className = activeTheme === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
      }
    });
  }

  const authModal = document.getElementById('authModal');
  if (authModal) {
    authModal.addEventListener('click', (event) => {
      if (event.target === authModal) {
        hideAuthModal();
      }
    });

    authModal.querySelector('.auth-modal-close')?.addEventListener('click', hideAuthModal);
  }

  document.addEventListener('click', (event) => {
    if (!event.target.closest('.user-menu')) {
      closeUserMenu();
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      closeUserMenu();
    }
  });
});
