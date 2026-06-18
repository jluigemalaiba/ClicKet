(() => {
  const clock = document.querySelector('[data-live-clock]');
  const search = document.getElementById('staffPanelSearch');
  const contextPill = document.getElementById('staffContextPill');
  const panelViews = Array.from(document.querySelectorAll('[data-panel-view]'));
  const navGroups = Array.from(document.querySelectorAll('[data-nav-group]'));
  const parentButtons = Array.from(document.querySelectorAll('.staff-nav-parent[data-panel-target]'));
  const childButtons = Array.from(document.querySelectorAll('.staff-nav-child[data-panel-target]'));
  const orderPrintSearch = document.getElementById('staffOrderPrintSearch');
  const printResult = document.getElementById('staffPrintResult');

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
    childButtons.forEach(button => {
      const isActivePanel = button.dataset.panelTarget === panelKey;
      const isActiveChild = subtarget
        ? button.dataset.subtarget === subtarget
        : isActivePanel && !button.parentElement?.querySelector('.staff-nav-child.is-active');
      button.classList.toggle('is-active', Boolean(isActiveChild));
    });
  }

  function updateContext(panel, subtarget = '') {
    if (!contextPill || !panel) return;
    const panelLabel = panel.dataset.panelLabel || panel.id.replace(/^panel-/, '');
    const child = childButtons.find(button => (
      button.dataset.panelTarget === panel.dataset.panelView &&
      (subtarget ? button.dataset.subtarget === subtarget : button.classList.contains('is-active'))
    ));
    contextPill.textContent = child ? `${panelLabel} / ${child.textContent.trim()}` : panelLabel;
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

  updateClock();
  window.setInterval(updateClock, 30000);
  search?.addEventListener('input', applySearch);

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
        const body = new URLSearchParams({ action, order_id: orderId });
        const response = await fetch('staff-payment-api.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body,
        });
        const payload = await response.json();
        if (!payload.success) {
          throw new Error(payload.message || 'Payment update failed.');
        }

        const status = row?.querySelector('[data-payment-status]');
        if (status) {
          status.textContent = payload.order.payment_status;
          status.classList.toggle('is-success', payload.order.payment_status === 'Paid');
          status.classList.toggle('is-danger', payload.order.payment_status === 'Failed');
          status.classList.toggle('is-warning', !['Paid', 'Failed'].includes(payload.order.payment_status));
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

  orderPrintSearch?.addEventListener('input', renderPrintLookup);

  const [hashPanel, hashSubtarget] = window.location.hash.replace(/^#/, '').split(':');
  showPanel(hashPanel || 'dashboard', hashSubtarget || 'overview', false);
})();
