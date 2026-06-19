(() => {
  const body = document.body;
  const clock = document.querySelector('[data-live-clock]');
  const search = document.getElementById('staffPanelSearch');
  const contextPill = document.getElementById('staffContextPill');
  const panelViews = Array.from(document.querySelectorAll('[data-panel-view]'));
  const navGroups = Array.from(document.querySelectorAll('[data-nav-group]'));
  const parentButtons = Array.from(document.querySelectorAll('.staff-nav-parent[data-panel-target]'));
  const childButtons = Array.from(document.querySelectorAll('.staff-nav-child[data-panel-target]'));
  const orderPrintSearch = document.getElementById('staffOrderPrintSearch');
  const printResult = document.getElementById('staffPrintResult');
  const themeToggle = document.querySelector('[data-theme-toggle]');
  const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
  const modal = document.querySelector('[data-staff-modal]');
  const modalTitle = document.getElementById('staffModalTitle');
  const modalEyebrow = document.getElementById('staffModalEyebrow');
  const modalBody = document.querySelector('[data-modal-body]');

  function updateClock() {
    if (!clock) return;
    const stamp = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    clock.textContent = `Live sync ${stamp}`;
  }

  function rowsInActivePanel() {
    const activePanel = document.querySelector('.staff-panel-view.is-active');
    return Array.from(activePanel?.querySelectorAll('[data-search-row]') || []);
  }

  function applySearch() {
    const term = (search?.value || '').trim().toLowerCase();
    document.querySelectorAll('[data-search-row][hidden]').forEach(row => {
      row.hidden = false;
    });
    if (!term) return;
    rowsInActivePanel().forEach(row => {
      row.hidden = !row.textContent.toLowerCase().includes(term);
    });
  }

  function openGroup(panelKey) {
    navGroups.forEach(group => {
      const isOpen = group.dataset.navGroup === panelKey;
      group.classList.toggle('is-open', isOpen);
      const parent = group.querySelector('.staff-nav-parent');
      parent?.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
  }

  function setActiveNav(panelKey, subtarget = '') {
    parentButtons.forEach(button => {
      button.classList.toggle('is-active', button.dataset.panelTarget === panelKey);
    });

    const firstChild = childButtons.find(button => button.dataset.panelTarget === panelKey);
    childButtons.forEach(button => {
      const target = subtarget || firstChild?.dataset.subtarget || '';
      button.classList.toggle(
        'is-active',
        button.dataset.panelTarget === panelKey && button.dataset.subtarget === target
      );
    });
  }

  function updateContext(panel, subtarget = '') {
    if (!contextPill || !panel) return;
    const panelLabel = panel.dataset.panelLabel || panel.id.replace(/^panel-/, '');
    const activeChild = childButtons.find(button => (
      button.dataset.panelTarget === panel.dataset.panelView &&
      (subtarget ? button.dataset.subtarget === subtarget : button.classList.contains('is-active'))
    ));
    contextPill.textContent = activeChild ? `${panelLabel} / ${activeChild.textContent.trim()}` : panelLabel;
  }

  function showPanel(panelKey, subtarget = '', shouldHash = true) {
    const nextPanel = panelViews.find(panel => panel.dataset.panelView === panelKey);
    if (!nextPanel) return;

    panelViews.forEach(panel => {
      panel.classList.toggle('is-active', panel === nextPanel);
    });
    openGroup(panelKey);
    setActiveNav(panelKey, subtarget);
    updateContext(nextPanel, subtarget);
    applySearch();
    body.classList.remove('sidebar-open');

    if (shouldHash) {
      const suffix = subtarget ? `:${subtarget}` : '';
      history.replaceState(null, '', `#${panelKey}${suffix}`);
    }
  }

  parentButtons.forEach(button => {
    button.addEventListener('click', () => {
      const panelKey = button.dataset.panelTarget;
      const firstChild = childButtons.find(child => child.dataset.panelTarget === panelKey);
      showPanel(panelKey, firstChild?.dataset.subtarget || '');
    });
  });

  childButtons.forEach(button => {
    button.addEventListener('click', () => {
      showPanel(button.dataset.panelTarget, button.dataset.subtarget || '');
    });
  });

  document.querySelectorAll('[data-panel-shortcut]').forEach(button => {
    button.addEventListener('click', () => {
      showPanel(button.dataset.panelShortcut || 'dashboard');
    });
  });

  function setTheme(theme) {
    body.dataset.theme = theme;
    try {
      localStorage.setItem('clicket-admin-theme', theme);
    } catch {
      /* localStorage may be unavailable in strict browser modes. */
    }
  }

  try {
    const storedTheme = localStorage.getItem('clicket-admin-theme');
    if (storedTheme === 'dark' || storedTheme === 'light') {
      body.dataset.theme = storedTheme;
    }
  } catch {
    body.dataset.theme = body.dataset.theme || 'light';
  }

  themeToggle?.addEventListener('click', () => {
    setTheme(body.dataset.theme === 'dark' ? 'light' : 'dark');
  });

  sidebarToggle?.addEventListener('click', () => {
    body.classList.toggle('sidebar-open');
  });

  function modalTemplate(type, title) {
    const safeTitle = escapeHtml(title || 'Details');
    const templates = {
      'event-form': `
        <div class="staff-detail-list">
          <div><span>Workflow</span><strong>Create event</strong></div>
          <div><span>Required</span><strong>Venue, schedule, poster, tiers</strong></div>
          <div><span>Status</span><strong>Draft before publish</strong></div>
          <div><span>Audit</span><strong>Event creation log enabled</strong></div>
        </div>
        <p>Use the Events screen form to configure title, venue, category, base price, poster, banner, schedule, and publishing state.</p>
      `,
      'event-performance': `
        <div class="staff-detail-list">
          <div><span>Event</span><strong>${safeTitle}</strong></div>
          <div><span>Sales</span><strong>Revenue and ticket velocity</strong></div>
          <div><span>Inventory</span><strong>Tier and section availability</strong></div>
          <div><span>Status</span><strong>Draft, published, sold out, cancelled, archived</strong></div>
        </div>
      `,
      'venue-detail': `
        <div class="staff-detail-list">
          <div><span>Venue</span><strong>${safeTitle}</strong></div>
          <div><span>Maps</span><strong>SVG seat map viewer</strong></div>
          <div><span>Access</span><strong>Organizer assignment</strong></div>
          <div><span>Status</span><strong>Enable or disable venue selling</strong></div>
        </div>
      `,
      'seat-map': `
        <div class="staff-detail-list">
          <div><span>Viewer</span><strong>SVG seat map</strong></div>
          <div><span>Legend</span><strong>Available, sold, held, blocked, accessible, complimentary</strong></div>
          <div><span>Search</span><strong>Seat, section, row, and tier</strong></div>
          <div><span>Controls</span><strong>Block, release, assign accessibility, comp</strong></div>
        </div>
      `,
      assignment: `
        <div class="staff-detail-list">
          <div><span>Scope</span><strong>Assigned venues/events</strong></div>
          <div><span>Organizer</span><strong>Can manage only assigned records</strong></div>
          <div><span>Customer</span><strong>Self-service ticket access</strong></div>
          <div><span>Audit</span><strong>Assignment change logged</strong></div>
        </div>
      `,
      'status-control': `
        <div class="staff-detail-list">
          <div><span>Enabled</span><strong>Visible for event creation and selling</strong></div>
          <div><span>Disabled</span><strong>Hidden from new workflows</strong></div>
          <div><span>Protection</span><strong>Existing orders remain preserved</strong></div>
          <div><span>Audit</span><strong>Reason required</strong></div>
        </div>
      `,
      'tier-price': `
        <div class="staff-detail-list">
          <div><span>Before</span><strong>Current tier price</strong></div>
          <div><span>After</span><strong>New tier price</strong></div>
          <div><span>Scope</span><strong>Selected event or venue tier</strong></div>
          <div><span>Audit</span><strong>Price change log</strong></div>
        </div>
      `,
      'order-detail': `
        <div class="staff-detail-list">
          <div><span>Order</span><strong>${safeTitle}</strong></div>
          <div><span>Buyer</span><strong>Name, email, order history</strong></div>
          <div><span>Seats</span><strong>Section, row, number, tier</strong></div>
          <div><span>Actions</span><strong>Reissue, refund, cancel, archive</strong></div>
        </div>
      `,
      'proof-viewer': `
        <div class="staff-detail-list">
          <div><span>Proof</span><strong>Uploaded screenshot preview</strong></div>
          <div><span>Decision</span><strong>Approve or reject payment</strong></div>
          <div><span>Reference</span><strong>Payment and order IDs</strong></div>
          <div><span>Audit</span><strong>Payment approval log</strong></div>
        </div>
      `,
      'payment-status': `
        <div class="staff-detail-list">
          <div><span>Paid</span><strong>Confirm tickets and voucher</strong></div>
          <div><span>Pending</span><strong>Queue for proof review</strong></div>
          <div><span>Failed</span><strong>Reject proof and notify buyer</strong></div>
          <div><span>Refunded</span><strong>Release refund workflow</strong></div>
        </div>
      `,
      'ticket-detail': `
        <div class="staff-detail-list">
          <div><span>Ticket</span><strong>${safeTitle}</strong></div>
          <div><span>Validation</span><strong>Ticket ID, voucher ID, validation code</strong></div>
          <div><span>Status</span><strong>Valid, used, cancelled, refunded, reissued</strong></div>
          <div><span>Actions</span><strong>Reissue, void, print</strong></div>
        </div>
      `,
      'user-history': `
        <div class="staff-detail-list">
          <div><span>User</span><strong>${safeTitle}</strong></div>
          <div><span>Orders</span><strong>Purchase history and refunds</strong></div>
          <div><span>Access</span><strong>Role and venue assignment</strong></div>
          <div><span>Controls</span><strong>Suspend or disable account</strong></div>
        </div>
      `,
      'role-management': `
        <div class="staff-detail-list">
          <div><span>Admin</span><strong>Full access</strong></div>
          <div><span>Organizer</span><strong>Assigned venues and events</strong></div>
          <div><span>Customer</span><strong>Self-service ticket access</strong></div>
          <div><span>Customer</span><strong>Self-service access</strong></div>
        </div>
      `,
      'report-export': `
        <div class="staff-detail-list">
          <div><span>PDF</span><strong>Presentation-ready report</strong></div>
          <div><span>Excel</span><strong>Tabular export</strong></div>
          <div><span>Scope</span><strong>Role-filtered records</strong></div>
          <div><span>Audit</span><strong>Export action logged</strong></div>
        </div>
      `,
    };

    return templates[type] || `
      <div class="staff-detail-list">
        <div><span>Screen</span><strong>${safeTitle}</strong></div>
        <div><span>Workflow</span><strong>Admin action preview</strong></div>
        <div><span>Status</span><strong>Ready</strong></div>
        <div><span>Audit</span><strong>Tracked when committed</strong></div>
      </div>
    `;
  }

  function openModal(title, type) {
    if (!modal || !modalBody || !modalTitle) return;
    modalTitle.textContent = title || 'Details';
    if (modalEyebrow) {
      modalEyebrow.textContent = type ? type.replace(/-/g, ' ') : 'Workflow';
    }
    modalBody.innerHTML = modalTemplate(type, title);
    modal.hidden = false;
  }

  function staffEventLayoutOptions() {
    const source = document.getElementById('staffEventLayoutOptionsJson');
    if (!source) return [];
    try {
      return JSON.parse(source.textContent || '[]');
    } catch {
      return [];
    }
  }

  function eventStatusOptions(selected) {
    return ['draft', 'published', 'paused', 'archived'].map(status => {
      const label = status.replace(/_/g, ' ').replace(/\b\w/g, char => char.toUpperCase());
      return `<option value="${status}" ${status === selected ? 'selected' : ''}>${label}</option>`;
    }).join('');
  }

  function eventCategoryOptions(selected) {
    return ['concert', 'sports', 'theater'].map(category => {
      const label = category.replace(/\b\w/g, char => char.toUpperCase());
      return `<option value="${category}" ${category === selected ? 'selected' : ''}>${label}</option>`;
    }).join('');
  }

  function eventLayoutOptions(selectedLayoutId) {
    return staffEventLayoutOptions().map(option => `
      <option value="${escapeHtml(option.venue_layout_id)}" ${String(option.venue_layout_id) === String(selectedLayoutId) ? 'selected' : ''}>
        ${escapeHtml(option.label)}
      </option>
    `).join('');
  }

  function openEventEditModal(eventData) {
    if (!modal || !modalBody || !modalTitle) return;
    const title = eventData.title || 'Edit Event';
    modalTitle.textContent = title;
    if (modalEyebrow) modalEyebrow.textContent = 'event edit';

    modalBody.innerHTML = `
      <form class="staff-form-grid" action="staff-events-api.php" method="post">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="event_key" value="${escapeHtml(eventData.event_key || eventData.key || '')}">
        <label>
          <span>Event Title</span>
          <input type="text" name="title" value="${escapeHtml(eventData.title || '')}" required>
        </label>
        <label>
          <span>Venue</span>
          <select name="venue_layout_id" required>${eventLayoutOptions(eventData.venue_layout_id)}</select>
        </label>
        <label>
          <span>Category</span>
          <select name="category" required>${eventCategoryOptions(eventData.category_db || 'concert')}</select>
        </label>
        <label>
          <span>Event Type</span>
          <input type="text" name="type" value="${escapeHtml(eventData.type || '')}">
        </label>
        <label>
          <span>Owner / Artist / Company / League</span>
          <input type="text" name="owner_name" value="${escapeHtml(eventData.owner || '')}">
        </label>
        <label>
          <span>Base Price</span>
          <input type="number" name="base_price" min="0" step="0.01" value="${escapeHtml(eventData.base_price ?? 0)}" required>
        </label>
        <label>
          <span>Performance Date</span>
          <input type="date" name="performance_date" value="${escapeHtml(eventData.performance_date || '')}" required>
        </label>
        <label>
          <span>Performance Time</span>
          <input type="time" name="performance_time" value="${escapeHtml(String(eventData.performance_time || '').slice(0, 5))}" required>
        </label>
        <label>
          <span>Status</span>
          <select name="status" required>${eventStatusOptions(eventData.status_value || 'draft')}</select>
        </label>
        <label>
          <span>Poster URL</span>
          <input type="url" name="poster_url" value="${escapeHtml(eventData.poster_url || '')}">
        </label>
        <label>
          <span>Banner URL</span>
          <input type="url" name="banner_url" value="${escapeHtml(eventData.banner_url || '')}">
        </label>
        <div class="staff-form-actions">
          <button class="staff-secondary-btn" type="button" data-modal-close>Cancel</button>
          <button class="staff-action-btn" type="submit">Save Changes</button>
        </div>
      </form>
    `;
    modal.hidden = false;
    modalBody.querySelectorAll('[data-modal-close]').forEach(button => {
      button.addEventListener('click', closeModal);
    });
  }

  function closeModal() {
    if (!modal) return;
    modal.hidden = true;
  }

  document.querySelectorAll('[data-open-modal]').forEach(button => {
    button.addEventListener('click', () => {
      if (button.disabled) return;
      openModal(button.dataset.modalTitle || button.textContent.trim(), button.dataset.modalType || '');
    });
  });

  document.querySelectorAll('[data-event-edit]').forEach(button => {
    button.addEventListener('click', () => {
      if (button.disabled) return;
      try {
        openEventEditModal(JSON.parse(button.dataset.event || '{}'));
      } catch {
        openEventEditModal({});
      }
    });
  });

  document.querySelectorAll('[data-modal-close]').forEach(button => {
    button.addEventListener('click', closeModal);
  });

  document.addEventListener('keydown', event => {
    if (event.key === 'Escape') {
      closeModal();
      body.classList.remove('sidebar-open');
    }
  });

  function setPaymentStatusClass(statusEl, status) {
    statusEl.classList.remove('is-success', 'is-danger', 'is-warning', 'is-muted', 'is-info');
    const normalized = String(status || '').toLowerCase();
    if (normalized === 'paid') {
      statusEl.classList.add('is-success');
    } else if (normalized === 'failed') {
      statusEl.classList.add('is-danger');
    } else if (normalized === 'refunded') {
      statusEl.classList.add('is-muted');
    } else {
      statusEl.classList.add('is-warning');
    }
  }

  document.querySelectorAll('[data-payment-action][data-order-id]').forEach(button => {
    button.addEventListener('click', async () => {
      const action = button.dataset.paymentAction;
      const orderId = button.dataset.orderId;
      const row = button.closest('[data-payment-row]');
      if (!action || !orderId || button.disabled) return;

      button.disabled = true;
      const previousText = button.textContent;
      button.textContent = action === 'approve' ? 'Approving...' : 'Rejecting...';

      try {
        const bodyParams = new URLSearchParams({ action, order_id: orderId });
        const response = await fetch('staff-payment-api.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: bodyParams,
        });
        const payload = await response.json();
        if (!payload.success) {
          throw new Error(payload.message || 'Payment update failed.');
        }

        const status = row?.querySelector('[data-payment-status]');
        if (status) {
          status.textContent = payload.order.payment_status;
          setPaymentStatusClass(status, payload.order.payment_status);
        }
      } catch (error) {
        window.alert(error.message || 'Payment update failed.');
      } finally {
        button.disabled = false;
        button.textContent = previousText;
      }
    });
  });

  function staffTicketOrders() {
    const source = document.getElementById('staffTicketOrdersJson');
    if (!source) return [];
    try {
      return JSON.parse(source.textContent || '[]');
    } catch {
      return [];
    }
  }

  function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, char => ({
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;',
    }[char]));
  }

  const ticketOrders = staffTicketOrders();

  function renderPrintLookup() {
    if (!orderPrintSearch || !printResult) return;
    const term = orderPrintSearch.value.trim().toLowerCase();
    if (!term) {
      printResult.innerHTML = '<p>Enter an order ID from the user form or on-site request. Results are limited to your assigned venue scope.</p>';
      return;
    }

    const order = ticketOrders.find(item => item.order_id.toLowerCase() === term);
    if (!order) {
      printResult.innerHTML = '<p>No order found in your assigned venue scope.</p>';
      return;
    }

    const tickets = Array.isArray(order.tickets) ? order.tickets : [];
    const ticketRows = tickets.map(ticket => `
      <div class="staff-print-ticket">
        <span>
          <strong>${escapeHtml(ticket.ticket_id)}</strong>
          <small>${escapeHtml(ticket.category)} &middot; ${escapeHtml(ticket.section)} ${escapeHtml(ticket.row)}-${escapeHtml(ticket.number)}</small>
        </span>
        <a href="staff-voucher.php?ticket=${encodeURIComponent(ticket.ticket_id)}" target="_blank" rel="noopener">Print</a>
      </div>
    `).join('');

    printResult.innerHTML = `
      <article class="staff-print-order">
        <header>
          <div>
            <p>${escapeHtml(order.venue)}</p>
            <h3>${escapeHtml(order.event_title)}</h3>
            <span>${escapeHtml(order.buyer_name)} &middot; ${escapeHtml(order.buyer_email)}</span>
          </div>
          <a class="staff-print-all" href="staff-voucher.php?order=${encodeURIComponent(order.order_id)}" target="_blank" rel="noopener">Print All ${tickets.length}</a>
        </header>
        <div class="staff-print-meta">
          <span>Order <strong>${escapeHtml(order.order_id)}</strong></span>
          <span>Payment <strong>${escapeHtml(order.payment_status)}</strong></span>
          <span>Status <strong>${escapeHtml(order.order_status)}</strong></span>
        </div>
        <div class="staff-print-ticket-list">${ticketRows || '<p>No tickets are attached to this order yet.</p>'}</div>
      </article>
    `;
  }

  function updateCountdowns() {
    const now = Math.floor(Date.now() / 1000);
    document.querySelectorAll('[data-countdown]').forEach(node => {
      const target = Number(node.dataset.countdown || 0);
      const diff = target - now;
      if (diff <= 0) {
        node.textContent = 'Expired';
        node.closest('.staff-reservation-card')?.querySelector('.staff-status')?.classList.add('is-danger');
        return;
      }
      const minutes = Math.floor(diff / 60);
      const seconds = diff % 60;
      node.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    });
  }

  updateClock();
  updateCountdowns();
  window.setInterval(updateClock, 30000);
  window.setInterval(updateCountdowns, 1000);
  search?.addEventListener('input', applySearch);
  orderPrintSearch?.addEventListener('input', renderPrintLookup);

  const [hashPanel, hashSubtarget] = window.location.hash.replace(/^#/, '').split(':');
  showPanel(hashPanel || 'dashboard', hashSubtarget || 'overview', false);
})();
