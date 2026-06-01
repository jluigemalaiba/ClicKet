/**
 * ClicKet Shopping Cart Manager
 * Handles all cart operations including add, remove, quantity updates, and UI rendering
 */

const cartManager = (() => {
  // Private state
  let cart = [];
  let offcanvasInstance = null;

  /**
   * Initialize cart on page load
   */
  function init() {
    loadFromSession();
    setupEventListeners();
    render();
  }

  /**
   * Load cart from session storage
   */
  function loadFromSession() {
    // Try to load from sessionStorage as backup
    const stored = sessionStorage.getItem('clicket_cart');
    if (stored) {
      try {
        cart = JSON.parse(stored);
      } catch (e) {
        cart = [];
      }
    }
    updateBadge();
  }

  /**
   * Save cart to session storage
   */
  function saveToSession() {
    sessionStorage.setItem('clicket_cart', JSON.stringify(cart));
    syncToServer();
  }

  /**
   * Sync cart to server via PHP session
   */
  function syncToServer() {
    // Send cart data to server to update $_SESSION['clicket_cart']
    fetch('includes/cart-api.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ action: 'sync', cart: cart }),
    }).catch((err) => console.error('Cart sync error:', err));
  }

  /**
   * Add item to cart (increments quantity if exists)
   */
  function addItem(item) {
    if (!item.id) return;

    // Check if item already exists
    const existing = cart.find((i) => i.id === item.id);
    if (existing) {
      existing.qty = (existing.qty || 1) + 1;
      showToast(`Updated quantity for ${item.title}`, 'success');
    } else {
      cart.push({
        ...item,
        qty: 1,
      });
      showToast(`Added ${item.title} to cart`, 'success');
    }

    saveToSession();
    render();
    updateBadge();

    // Trigger offcanvas if not already open
    openOffcanvas();
  }

  /**
   * Remove item from cart
   */
  function removeItem(itemId) {
    const index = cart.findIndex((i) => i.id === itemId);
    if (index !== -1) {
      const item = cart[index];
      cart.splice(index, 1);
      saveToSession();
      render();
      updateBadge();
      showToast(`Removed ${item.title} from cart`, 'info');
    }
  }

  /**
   * Update item quantity
   */
  function updateQty(itemId, qty) {
    if (qty < 1) {
      removeItem(itemId);
      return;
    }

    const item = cart.find((i) => i.id === itemId);
    if (item) {
      item.qty = qty;
      saveToSession();
      render();
      updateBadge();
    }
  }

  /**
   * Get total cart count
   */
  function getTotalCount() {
    return cart.reduce((sum, item) => sum + (item.qty || 1), 0);
  }

  /**
   * Get cart subtotal
   */
  function getSubtotal() {
    return cart.reduce((sum, item) => {
      const price = parseInt(item.price.replace(/[^0-9]/g, '')) || 0;
      return sum + price * (item.qty || 1);
    }, 0);
  }

  /**
   * Calculate tax (10%)
   */
  function getTax() {
    return Math.round(getSubtotal() * 0.1);
  }

  /**
   * Get total with tax
   */
  function getTotal() {
    return getSubtotal() + getTax();
  }

  /**
   * Update navbar badge
   */
  function updateBadge() {
    const badge = document.getElementById('cartBadge');
    if (!badge) return;

    const count = getTotalCount();
    if (count > 0) {
      badge.textContent = count;
      badge.style.display = 'flex';
    } else {
      badge.style.display = 'none';
    }
  }

  /**
   * Render cart items
   */
  function render() {
    const container = document.getElementById('cartItemsContainer');
    if (!container) return;

    if (cart.length === 0) {
      container.innerHTML = `
        <div class="cart-empty-message">
          <div class="empty-icon">🛒</div>
          <p>Your cart is empty</p>
          <p style="font-size: 12px; margin-top: 8px;">Browse events and add tickets to get started</p>
        </div>
      `;
    } else {
      container.innerHTML = cart
        .map(
          (item) => `
        <div class="cart-item" data-item-id="${item.id}">
          <div class="cart-item-image">
            <img src="${item.image}" alt="${item.title}" loading="lazy">
          </div>
          <div class="cart-item-content">
            <h4 class="cart-item-title">${item.title}</h4>
            <p class="cart-item-category">${item.category}</p>
            <p class="cart-item-price">${item.price}</p>
            <div class="cart-item-qty">
              <button class="qty-btn" type="button" onclick="cartManager.updateQty('${item.id}', ${(item.qty || 1) - 1})" aria-label="Decrease quantity">−</button>
              <span class="qty-display">${item.qty || 1}</span>
              <button class="qty-btn" type="button" onclick="cartManager.updateQty('${item.id}', ${(item.qty || 1) + 1})" aria-label="Increase quantity">+</button>
              <button class="cart-item-remove" type="button" onclick="cartManager.removeItem('${item.id}')" aria-label="Remove item" title="Remove">✕</button>
            </div>
          </div>
        </div>
      `
        )
        .join('');
    }

    updateSummary();
  }

  /**
   * Update cart summary (subtotal, tax, total)
   */
  function updateSummary() {
    const subtotal = getSubtotal();
    const tax = getTax();
    const total = getTotal();

    const subtotalEl = document.getElementById('cartSubtotal');
    const taxEl = document.getElementById('cartTax');
    const totalEl = document.getElementById('cartTotal');
    const checkoutBtn = document.getElementById('checkoutBtn');

    if (subtotalEl) {
      subtotalEl.textContent = `₱${subtotal.toLocaleString()}`;
    }
    if (taxEl) {
      taxEl.textContent = `₱${tax.toLocaleString()}`;
    }
    if (totalEl) {
      totalEl.textContent = `₱${total.toLocaleString()}`;
    }
    if (checkoutBtn) {
      checkoutBtn.disabled = cart.length === 0;
    }
  }

  /**
   * Setup event listeners
   */
  function setupEventListeners() {
    const cartBtn = document.querySelector('.nav-cart-btn');
    if (cartBtn) {
      cartBtn.addEventListener('click', openOffcanvas);
    }

    const checkoutBtn = document.getElementById('checkoutBtn');
    if (checkoutBtn) {
      checkoutBtn.addEventListener('click', () => {
        if (cart.length > 0) {
          // Placeholder for checkout redirect
          console.log('Proceeding to booking with cart:', cart);
          // In future: window.location.href = 'checkout.php';
          showToast('Booking feature coming soon!', 'info');
        }
      });
    }
  }

  /**
   * Open cart offcanvas
   */
  function openOffcanvas() {
    const offcanvasEl = document.getElementById('cartOffcanvas');
    if (!offcanvasEl) return;

    if (!offcanvasInstance) {
      offcanvasInstance = new bootstrap.Offcanvas(offcanvasEl);
    }
    offcanvasInstance.show();
  }

  /**
   * Close cart offcanvas
   */
  function closeOffcanvas() {
    if (offcanvasInstance) {
      offcanvasInstance.hide();
    }
  }

  /**
   * Show toast notification
   */
  function showToast(message, type = 'info') {
    // Create toast container if not exists
    let toastContainer = document.getElementById('cartToastContainer');
    if (!toastContainer) {
      toastContainer = document.createElement('div');
      toastContainer.id = 'cartToastContainer';
      toastContainer.style.cssText =
        'position: fixed; top: 20px; right: 20px; z-index: 2000; display: flex; flex-direction: column; gap: 10px;';
      document.body.appendChild(toastContainer);
    }

    const toast = document.createElement('div');
    const colors = {
      success: '#22c55e',
      error: '#ef4444',
      info: '#3b82f6',
    };
    const color = colors[type] || colors.info;

    toast.style.cssText = `
      background: ${color};
      color: white;
      padding: 12px 20px;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 500;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      animation: toastSlideIn 0.3s ease;
      max-width: 300px;
      word-wrap: break-word;
    `;
    toast.textContent = message;
    toastContainer.appendChild(toast);

    setTimeout(() => {
      toast.style.animation = 'toastSlideOut 0.3s ease';
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  }

  /**
   * Get cart contents (debugging)
   */
  function getCart() {
    return [...cart];
  }

  /**
   * Clear all items
   */
  function clear() {
    cart = [];
    saveToSession();
    render();
    updateBadge();
  }

  // Public API
  return {
    init,
    addItem,
    removeItem,
    updateQty,
    render,
    getCart,
    getTotalCount,
    getSubtotal,
    getTax,
    getTotal,
    clear,
    openOffcanvas,
    closeOffcanvas,
  };
})();

// Add toast animations to document
const style = document.createElement('style');
style.textContent = `
  @keyframes toastSlideIn {
    from {
      transform: translateX(400px);
      opacity: 0;
    }
    to {
      transform: translateX(0);
      opacity: 1;
    }
  }
  
  @keyframes toastSlideOut {
    from {
      transform: translateX(0);
      opacity: 1;
    }
    to {
      transform: translateX(400px);
      opacity: 0;
    }
  }

  @media (max-width: 640px) {
    @keyframes toastSlideIn {
      from {
        transform: translateX(100%);
        opacity: 0;
      }
      to {
        transform: translateX(0);
        opacity: 1;
      }
    }
    
    @keyframes toastSlideOut {
      from {
        transform: translateX(0);
        opacity: 1;
      }
      to {
        transform: translateX(100%);
        opacity: 0;
      }
    }
  }
`;
document.head.appendChild(style);
