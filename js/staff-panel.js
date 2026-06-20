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
  const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
  const sidebarCollapse = document.querySelector('[data-sidebar-collapse]');
  const modal = document.querySelector('[data-staff-modal]');
  const modalTitle = document.getElementById('staffModalTitle');
  const modalEyebrow = document.getElementById('staffModalEyebrow');
  const modalBody = document.querySelector('[data-modal-body]');
  const csrfToken = document.querySelector('meta[name="clicket-csrf-token"]')?.content || '';

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

    if (!childButtons.length) return;

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
    if (!childButtons.length) {
      contextPill.textContent = panelLabel;
      return;
    }
    const activeChild = childButtons.find(button => (
      button.dataset.panelTarget === panel.dataset.panelView &&
      (subtarget ? button.dataset.subtarget === subtarget : button.classList.contains('is-active'))
    ));
    contextPill.textContent = activeChild ? `${panelLabel} / ${activeChild.textContent.trim()}` : panelLabel;
  }

  function showPanel(panelKey, subtarget = '', shouldHash = true) {
    const panelFallbacks = { payments: 'orders', reservations: 'dashboard', settings: 'dashboard' };
    const nextPanelKey = panelViews.some(panel => panel.dataset.panelView === panelKey)
      ? panelKey
      : (panelFallbacks[panelKey] || 'dashboard');
    const nextPanel = panelViews.find(panel => panel.dataset.panelView === nextPanelKey) || panelViews[0];
    if (!nextPanel) return;

    panelViews.forEach(panel => {
      panel.classList.toggle('is-active', panel === nextPanel);
    });
    openGroup(nextPanelKey);
    setActiveNav(nextPanelKey, subtarget);
    updateContext(nextPanel, subtarget);
    applySearch();
    body.classList.remove('sidebar-open');

    if (shouldHash) {
      const suffix = subtarget ? `:${subtarget}` : '';
      history.replaceState(null, '', `#${nextPanelKey}${suffix}`);
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

  sidebarToggle?.addEventListener('click', () => {
    body.classList.toggle('sidebar-open');
  });

  function setSidebarCollapsed(isCollapsed) {
    body.classList.toggle('sidebar-collapsed', isCollapsed);
    sidebarCollapse?.setAttribute('aria-expanded', isCollapsed ? 'false' : 'true');
    sidebarCollapse?.setAttribute('aria-label', isCollapsed ? 'Expand sidebar' : 'Collapse sidebar');
    try {
      localStorage.setItem('clicket-admin-sidebar', isCollapsed ? 'collapsed' : 'expanded');
    } catch {
      /* localStorage may be unavailable in strict browser modes. */
    }
  }

  try {
    setSidebarCollapsed(localStorage.getItem('clicket-admin-sidebar') === 'collapsed');
  } catch {
    setSidebarCollapsed(false);
  }

  sidebarCollapse?.addEventListener('click', () => {
    setSidebarCollapsed(!body.classList.contains('sidebar-collapsed'));
  });

  const venueSelectors = Array.from(document.querySelectorAll('[data-venue-selector]'));
  const venuePanels = Array.from(document.querySelectorAll('[data-venue-panel]'));

  function activateVenue(venueId) {
    venueSelectors.forEach(selector => {
      const isActive = selector.dataset.venueSelector === venueId;
      selector.classList.toggle('is-active', isActive);
      selector.setAttribute('aria-pressed', isActive ? 'true' : 'false');
    });
    venuePanels.forEach(panel => {
      panel.classList.toggle('is-active', panel.dataset.venuePanel === venueId);
    });
  }

  venueSelectors.forEach(selector => {
    selector.addEventListener('click', () => activateVenue(selector.dataset.venueSelector || ''));
  });

  function activateVenueTab(panel, tabName) {
    if (!panel) return;
    panel.querySelectorAll('[data-venue-tab]').forEach(tab => {
      tab.classList.toggle('is-active', tab.dataset.venueTab === tabName);
    });
    panel.querySelectorAll('[data-venue-tab-trigger]').forEach(trigger => {
      const isActive = trigger.dataset.venueTabTrigger === tabName;
      trigger.classList.toggle('is-active', isActive);
      trigger.setAttribute('aria-pressed', isActive ? 'true' : 'false');
    });
  }

  document.querySelectorAll('[data-venue-tab-trigger]').forEach(trigger => {
    trigger.addEventListener('click', () => {
      activateVenueTab(trigger.closest('[data-venue-panel]'), trigger.dataset.venueTabTrigger || 'revenue');
    });
  });

  const dashboardShell = document.querySelector('.staff-dashboard-shell');
  if (dashboardShell) {
    requestAnimationFrame(() => {
      dashboardShell.classList.add('is-ready');
      document.querySelectorAll('.staff-dashboard-panel').forEach(panel => panel.classList.add('is-ready'));
    });
  }

  const revenuePeriods = Array.from(document.querySelectorAll('[data-revenue-period]'));
  const revenueSelection = document.querySelector('[data-revenue-selection]');

  function selectRevenuePeriod(periodIndex) {
    revenuePeriods.forEach(period => {
      const isActive = period.dataset.revenuePeriod === String(periodIndex);
      period.classList.toggle('is-active', isActive);
      period.setAttribute('aria-pressed', isActive ? 'true' : 'false');
      if (isActive && revenueSelection) {
        revenueSelection.textContent = `Selected period: ${period.dataset.revenueLabel || 'Period'}, ${period.dataset.revenueValue || 'PHP 0'}`;
      }
    });
    document.querySelectorAll('[data-revenue-dot]').forEach(dot => {
      dot.classList.toggle('is-active', dot.dataset.revenueDot === String(periodIndex));
    });
  }

  revenuePeriods.forEach(period => {
    period.setAttribute('aria-pressed', period.classList.contains('is-active') ? 'true' : 'false');
    period.addEventListener('click', () => selectRevenuePeriod(period.dataset.revenuePeriod || ''));
  });

  const eventVenueFilter = document.querySelector('[data-event-venue-filter]');
  const eventCards = Array.from(document.querySelectorAll('[data-event-card]'));
  const eventPanels = Array.from(document.querySelectorAll('[data-event-panel]'));
  const eventFilterCount = document.querySelector('[data-event-filter-count]');
  const eventReviewModal = document.querySelector('[data-event-review-modal]');

  function activateEvent(eventKey) {
    eventCards.forEach(card => {
      const isActive = card.dataset.eventCard === eventKey;
      card.closest('[data-event-review-card]')?.classList.toggle('is-active', isActive);
      card.setAttribute('aria-pressed', isActive ? 'true' : 'false');
    });
    eventPanels.forEach(panel => {
      panel.classList.toggle('is-active', panel.dataset.eventPanel === eventKey);
    });
  }

  function closeEventReviewModal() {
    if (!eventReviewModal) return;
    eventReviewModal.hidden = true;
    body.classList.remove('event-review-modal-open');
  }

  function openEventReviewModal(eventKey) {
    activateEvent(eventKey);
    if (!eventReviewModal) return;
    eventReviewModal.hidden = false;
    body.classList.add('event-review-modal-open');
    eventReviewModal.querySelector('.staff-event-review-modal-close')?.focus();
  }

  eventCards.forEach(card => {
    card.addEventListener('click', () => openEventReviewModal(card.dataset.eventCard || ''));
  });

  eventReviewModal?.querySelectorAll('[data-event-modal-close]').forEach(button => {
    button.addEventListener('click', closeEventReviewModal);
  });

  document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && !eventReviewModal?.hidden) closeEventReviewModal();
  });

  eventVenueFilter?.addEventListener('change', () => {
    const venue = eventVenueFilter.value;
    const visibleCards = eventCards.filter(card => {
      const visible = !venue || card.dataset.eventVenue === venue;
      card.closest('[data-event-review-card]')?.classList.toggle('is-filtered-out', !visible);
      return visible;
    });
    if (eventFilterCount) eventFilterCount.textContent = `${visibleCards.length} event${visibleCards.length === 1 ? '' : 's'}`;
    activateEvent(visibleCards[0]?.dataset.eventCard || '');
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
          <div><span>Record</span><strong>View-only ticket details</strong></div>
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
    modal.classList.remove('is-order-record', 'is-ticket-record');
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
    const statuses = body.classList.contains('staff-role-organizer')
      ? ['draft', 'published', 'paused']
      : ['draft', 'published', 'paused', 'archived'];

    return statuses.map(status => {
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

  function selectedLayoutOption(layoutId) {
    return staffEventLayoutOptions().find(option => String(option.venue_layout_id) === String(layoutId))
      || staffEventLayoutOptions()[0]
      || {};
  }

  function eventTypeOptions(category, selected) {
    const options = {
      concert: ['Local', 'International'],
      sports: ['Basketball', 'Volleyball'],
      theater: ['Musical', 'Opera']
    }[category] || ['General'];

    return options.map(option => `
      <option value="${escapeHtml(option)}" ${String(option).toLowerCase() === String(selected || '').toLowerCase() ? 'selected' : ''}>
        ${escapeHtml(option)}
      </option>
    `).join('');
  }

  function eventScheduleRows(eventData) {
    const schedules = Array.isArray(eventData.schedules) && eventData.schedules.length
      ? eventData.schedules
      : [{ date: eventData.performance_date || '', time: String(eventData.performance_time || '').slice(0, 5) }];

    return schedules.map(schedule => `
      <div class="staff-inline-row" data-schedule-row>
        <input type="date" name="performance_date[]" value="${escapeHtml(schedule.date || '')}" required>
        <input type="time" name="performance_time[]" value="${escapeHtml(String(schedule.time || '').slice(0, 5))}" required>
        <button class="staff-secondary-btn" type="button" data-remove-schedule>Remove</button>
      </div>
    `).join('');
  }

  function eventTierRows(layoutId, eventData = {}) {
    const layout = selectedLayoutOption(layoutId);
    const layoutTiers = Array.isArray(layout.tiers) ? layout.tiers : [];
    const eventTiers = Array.isArray(eventData.tiers) ? eventData.tiers : [];

    if (!layoutTiers.length) {
      return '<p class="staff-empty-state">No tiers found for this venue layout yet.</p>';
    }

    return layoutTiers.map(tier => {
      const saved = eventTiers.find(item => String(item.tier_id || item.id) === String(tier.tier_id || tier.id)) || {};
      const tierId = tier.tier_id || tier.id;
      const tierName = saved.name || tier.name || 'Tier';
      const tierColor = saved.color || tier.color || '#d8b7ff';
      const tierPrice = saved.price ?? tier.price ?? '';
      const capacity = saved.capacity || tier.capacity || 0;

      return `
        <div class="staff-event-tier-row staff-event-tier-row--editable">
          <span class="staff-event-tier-swatch" style="--tier-color:${escapeHtml(tierColor)}"></span>
          <input type="hidden" name="tier_id[]" value="${escapeHtml(tierId)}">
          <label>
            <span>Tier Title</span>
            <input type="text" name="tier_name[]" value="${escapeHtml(tierName)}" required>
          </label>
          <label class="staff-tier-color-field">
            <span>Map Color</span>
            <input type="color" name="tier_color[]" value="${escapeHtml(tierColor)}">
          </label>
          <label>
            <span>Price</span>
            <span class="staff-price-field">
              <b>PHP</b>
              <input type="number" name="tier_price[]" min="1" step="1" value="${escapeHtml(Math.round(Number(tierPrice || 0)) || '')}" placeholder="0" inputmode="numeric" required>
            </span>
          </label>
        </div>
      `;
    }).join('');
  }

  function eventMediaControl(kind, label, urlName, fileName, value, hint) {
    return `
      <div class="staff-event-media-control" data-required-media="${escapeHtml(kind)}" data-media-label="${escapeHtml(label)}" data-has-current="${value ? '1' : '0'}">
        <div>
          <span>${escapeHtml(label)}</span>
          <small>${escapeHtml(hint)}</small>
        </div>
        <label>
          <span>Upload file</span>
          <input type="file" name="${escapeHtml(fileName)}" accept="image/*">
        </label>
        <label>
          <span>Or paste URL / path</span>
          <input type="text" name="${escapeHtml(urlName)}" value="${escapeHtml(value || '')}" placeholder="Optional when file is selected">
        </label>
        <small class="staff-event-media-error">Upload a file or paste a URL.</small>
        ${value ? `<a href="${escapeHtml(value)}" target="_blank" rel="noopener">Current ${escapeHtml(kind)}</a>` : ''}
      </div>
    `;
  }

  function openEventEditModal(eventData) {
    if (!modal || !modalBody || !modalTitle) return;
    const isCreate = !(eventData.event_key || eventData.key);
    const title = eventData.title || (isCreate ? 'Add Event' : 'Edit Event');
    const initialLayout = eventData.venue_layout_id || selectedLayoutOption('').venue_layout_id || '';
    const initialCategory = eventData.category_db || selectedLayoutOption(initialLayout).category || 'concert';
    modalTitle.textContent = title;
    if (modalEyebrow) modalEyebrow.textContent = isCreate ? 'event create' : 'event edit';
    modal.classList.add('is-event-form');

    modalBody.innerHTML = `
      <form class="staff-form-grid staff-event-form" action="staff-events-api.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="${isCreate ? 'create' : 'update'}">
        <input type="hidden" name="event_key" value="${escapeHtml(eventData.event_key || eventData.key || '')}">
        <section class="staff-event-form-section staff-form-grid__wide">
          <header><span>01</span><div><p>Core details</p><h3>Event information</h3></div></header>
          <div class="staff-event-form-grid">
            <label class="staff-field-span">
              <span>Event Title</span>
              <input type="text" name="title" value="${escapeHtml(eventData.title || '')}" required>
            </label>
            <label class="staff-field-span">
              <span>About / Description</span>
              <textarea name="description" rows="4" required>${escapeHtml(eventData.description || '')}</textarea>
            </label>
            <label>
              <span>Venue</span>
              <select name="venue_layout_id" data-event-layout-select required>${eventLayoutOptions(initialLayout)}</select>
            </label>
            <label>
              <span>Category</span>
              <select name="category" data-event-category-select required>${eventCategoryOptions(initialCategory)}</select>
            </label>
            <label>
              <span>Event Type</span>
              <select name="type" data-event-type-select required>${eventTypeOptions(initialCategory, eventData.type || '')}</select>
            </label>
            <label>
              <span>Organizer / Artist / Company / League</span>
              <input type="text" name="owner_name" value="${escapeHtml(eventData.owner || '')}" required>
            </label>
          </div>
        </section>

        <section class="staff-event-form-section staff-form-grid__wide">
          <header><span>02</span><div><p>Cast and media</p><h3>Performers, poster, and banner</h3></div></header>
          <div class="staff-event-form-grid">
            <label class="staff-field-span">
              <span>Cast / Performers</span>
              <input type="text" name="cast_performers" value="${escapeHtml(eventData.cast_performers || '')}" placeholder="Separate names with commas" required>
            </label>
            ${eventMediaControl('logo', 'Cast Logo', 'cast_logo_url', 'cast_logo_file', eventData.cast_logo_url, 'Square logo or performer mark. Upload takes priority over URL.')}
            ${eventMediaControl('poster', 'Poster', 'poster_url', 'poster_file', eventData.poster_url, 'Vertical poster image. Upload takes priority over URL.')}
            ${eventMediaControl('banner', 'Banner', 'banner_url', 'banner_file', eventData.banner_url, 'Wide horizontal banner. Upload takes priority over URL.')}
          </div>
        </section>

        <section class="staff-event-form-section staff-form-grid__wide">
          <header><span>03</span><div><p>Schedule and rules</p><h3>Dates, time, and audience info</h3></div></header>
          <div class="staff-event-form-grid">
            <div class="staff-field-span staff-event-schedule-editor">
              <span>Dates and Times</span>
              <div class="staff-inline-stack" data-schedule-list>${eventScheduleRows(eventData)}</div>
              <button class="staff-secondary-btn" type="button" data-add-schedule>+ Add date/time</button>
            </div>
            <label>
              <span>Running Time (minutes)</span>
              <input type="number" name="running_minutes" min="1" value="${escapeHtml(eventData.running_minutes || '')}" required>
            </label>
            <label>
              <span>Age Range</span>
              <input type="text" name="age_range" value="${escapeHtml(eventData.age_range || '')}" placeholder="Example: 13+" required>
            </label>
            <label>
              <span>Doors Open (minutes before)</span>
              <input type="number" name="doors_open_minutes" min="1" value="${escapeHtml(eventData.doors_open_minutes || '')}" required>
            </label>
          </div>
        </section>
        <input type="hidden" name="base_price" value="0">

        <section class="staff-event-form-section staff-form-grid__wide staff-event-tier-editor">
          <header><span>04</span><div><p>Tier setup</p><h3>Title, color, and price fetched from DB</h3></div><em>Updates ticket map</em></header>
          <div data-tier-editor>${eventTierRows(initialLayout, eventData)}</div>
        </section>
        <div class="staff-form-actions">
          <button class="staff-secondary-btn" type="button" data-modal-close>Cancel</button>
          <button class="staff-action-btn" type="submit">${isCreate ? 'Create Event' : 'Save Changes'}</button>
        </div>
      </form>
    `;
    modal.hidden = false;
    modalBody.querySelectorAll('[data-modal-close]').forEach(button => {
      button.addEventListener('click', closeModal);
    });
    const layoutSelect = modalBody.querySelector('[data-event-layout-select]');
    const categorySelect = modalBody.querySelector('[data-event-category-select]');
    const typeSelect = modalBody.querySelector('[data-event-type-select]');
    const tierEditor = modalBody.querySelector('[data-tier-editor]');
    const scheduleList = modalBody.querySelector('[data-schedule-list]');
    const eventForm = modalBody.querySelector('.staff-event-form');

    const syncCategoryAndTiers = () => {
      const layout = selectedLayoutOption(layoutSelect?.value || '');
      if (layout?.category && categorySelect) {
        categorySelect.value = layout.category;
      }
      if (typeSelect && categorySelect) {
        typeSelect.innerHTML = eventTypeOptions(categorySelect.value, typeSelect.value);
      }
      if (tierEditor) {
        tierEditor.innerHTML = eventTierRows(layoutSelect?.value || '', eventData);
      }
    };

    layoutSelect?.addEventListener('change', syncCategoryAndTiers);
    categorySelect?.addEventListener('change', () => {
      if (typeSelect) typeSelect.innerHTML = eventTypeOptions(categorySelect.value, '');
    });
    tierEditor?.addEventListener('input', event => {
      const colorInput = event.target.closest('input[name="tier_color[]"]');
      if (!colorInput) return;
      const swatch = colorInput.closest('.staff-event-tier-row')?.querySelector('.staff-event-tier-swatch');
      swatch?.style.setProperty('--tier-color', colorInput.value || '#d8b7ff');
    });
    modalBody.querySelector('[data-add-schedule]')?.addEventListener('click', () => {
      scheduleList?.insertAdjacentHTML('beforeend', `
        <div class="staff-inline-row" data-schedule-row>
          <input type="date" name="performance_date[]" required>
          <input type="time" name="performance_time[]" required>
          <button class="staff-secondary-btn" type="button" data-remove-schedule>Remove</button>
        </div>
      `);
    });
    scheduleList?.addEventListener('click', event => {
      const button = event.target.closest('[data-remove-schedule]');
      if (!button) return;
      const rows = scheduleList.querySelectorAll('[data-schedule-row]');
      if (rows.length > 1) button.closest('[data-schedule-row]')?.remove();
    });
    eventForm?.addEventListener('submit', event => {
      if (!eventForm.reportValidity()) {
        event.preventDefault();
        return;
      }

      const invalidMedia = Array.from(eventForm.querySelectorAll('[data-required-media]')).find(control => {
        const file = control.querySelector('input[type="file"]');
        const url = control.querySelector(`input[name$="_url"]`);
        const hasCurrent = control.dataset.hasCurrent === '1';
        return !(hasCurrent || (file?.files?.length || 0) > 0 || String(url?.value || '').trim() !== '');
      });

      if (invalidMedia) {
        event.preventDefault();
        invalidMedia.classList.add('has-error');
        const label = invalidMedia.dataset.mediaLabel || 'Media';
        const urlInput = invalidMedia.querySelector(`input[name$="_url"]`);
        urlInput?.focus();
        window.alert(`${label} is required. Upload a file or paste a URL.`);
        return;
      }

      const invalidPrice = Array.from(eventForm.querySelectorAll('input[name="tier_price[]"]')).find(input => Number(input.value) <= 0 || !Number.isInteger(Number(input.value)));
      if (invalidPrice) {
        event.preventDefault();
        invalidPrice.focus();
        window.alert('Every ticket tier needs a whole-number price greater than zero.');
      }
    });
  }

  function closeModal() {
    if (!modal) return;
    modal.hidden = true;
    modal.classList.remove('is-order-record', 'is-ticket-record', 'is-event-form');
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

  document.querySelectorAll('[data-event-create]').forEach(button => {
    button.addEventListener('click', () => {
      if (button.disabled) return;
      openEventEditModal({});
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
    if (normalized === 'paid' || normalized === 'payment verified' || normalized === 'confirmed') {
      statusEl.classList.add('is-success');
    } else if (normalized === 'failed' || normalized === 'rejected' || normalized === 'cancelled' || normalized === 'canceled') {
      statusEl.classList.add('is-danger');
    } else if (normalized === 'refunded') {
      statusEl.classList.add('is-muted');
    } else {
      statusEl.classList.add('is-warning');
    }
  }

  function staffOrders() {
    const source = document.getElementById('staffOrdersJson');
    if (!source) return [];
    try { return JSON.parse(source.textContent || '[]'); } catch { return []; }
  }

  const managedOrders = staffOrders();
  const orderById = id => managedOrders.find(order => order.order_id === id);

  function openProofPreview(orderId, source = '') {
    if (!modal || !modalBody || !modalTitle) return;
    const order = orderById(orderId);
    const proofUrl = source || order?.proof_url || '';
    modal.classList.remove('is-order-record', 'is-ticket-record');
    modalTitle.textContent = order?.payment_reference || order?.reference || 'Proof Viewer';
    if (modalEyebrow) modalEyebrow.textContent = 'payment proof';
    modalBody.innerHTML = proofUrl
      ? `<img class="staff-modal-proof" src="${escapeHtml(proofUrl)}" alt="Payment proof for ${escapeHtml(order?.order_id || 'order')}">`
      : `<p class="staff-empty-state">No uploaded proof image is available for this payment.</p>`;
    modal.hidden = false;
  }

  document.querySelectorAll('[data-order-filter]').forEach(filter => {
    filter.addEventListener('click', () => {
      const selection = filter.dataset.orderFilter || 'all';
      document.querySelectorAll('[data-order-filter]').forEach(button => button.classList.toggle('is-active', button === filter));
      document.querySelectorAll('[data-order-filter-row]').forEach(row => { row.classList.toggle('is-order-filtered', selection !== 'all' && row.dataset.orderFilterRow !== selection); });
    });
  });

  function orderSeatList(order) {
    return (Array.isArray(order?.seats) ? order.seats : []).map(seat =>
      `<li>${escapeHtml(seat.category || 'Ticket')} · ${escapeHtml(seat.section || '')}, Row ${escapeHtml(seat.row || '')}, Seat ${escapeHtml(seat.number || '')}</li>`
    ).join('') || '<li>No seats recorded.</li>';
  }

  function orderLogList(order) {
    const logs = Array.isArray(order?.payment_logs) ? order.payment_logs : [];
    return logs.slice().reverse().map(log => `<li><strong>${escapeHtml(log.action)}</strong><span>${escapeHtml(log.note || '')}</span><small>${escapeHtml(log.actor || '')} · ${escapeHtml(log.at || '')}</small></li>`).join('') || '<li>No payment logs yet.</li>';
  }

  function showOrderDetails(orderId) {
    const order = orderById(orderId);
    if (!order || !modal || !modalBody || !modalTitle) return;
    modalTitle.textContent = order.order_id || 'Order details';
    if (modalEyebrow) modalEyebrow.textContent = 'Order & payment record';
    const proof = order.proof_url
      ? `<img class="staff-modal-proof" src="${escapeHtml(order.proof_url)}" alt="Payment proof for ${escapeHtml(order.order_id)}">`
      : `<p class="staff-empty-state">${order.proof_of_payment ? 'Legacy proof file is unavailable for preview.' : 'No proof screenshot was uploaded.'}</p>`;
    modalBody.innerHTML = `<div class="staff-order-modal">
      <div class="staff-detail-list"><div><span>Buyer</span><strong>${escapeHtml(order.buyer_name)}<small>${escapeHtml(order.buyer_email)}</small></strong></div><div><span>Event</span><strong>${escapeHtml(order.event_title || order.event)}<small>${escapeHtml(order.venue)}</small></strong></div><div><span>Payment reference</span><strong>${escapeHtml(order.payment_reference || order.reference)}<small>${escapeHtml(order.payment_method_label || order.payment_method)}</small></strong></div><div><span>Total</span><strong>PHP ${Number(order.total || 0).toLocaleString()}<small>${escapeHtml(order.payment_status)} · ${escapeHtml(order.order_status)}</small></strong></div></div>
      <section><h3>Seats</h3><ul class="staff-operation-list">${orderSeatList(order)}</ul></section>
      <section><h3>Proof screenshot</h3>${proof}</section>
      <section><h3>Payment logs</h3><ul class="staff-operation-list">${orderLogList(order)}</ul></section>
    </div>`;
    modal.hidden = false;
  }

  function orderRecordLogs(order) {
    const logs = Array.isArray(order?.payment_logs) ? [...order.payment_logs] : [];
    const derived = [
      ['Order created', 'Booking placed and payment reference generated.', order?.buyer_email || order?.buyer_name, order?.booked_at],
      ['Payment approved', 'Payment was approved and tickets were issued.', order?.approved_by, order?.approved_at],
      ['Payment rejected', order?.rejection_reason || 'Payment proof was rejected.', order?.rejected_by, order?.rejected_at],
      ['Order refunded', order?.refund_reason || 'Refund recorded for this order.', order?.refunded_by, order?.refunded_at],
      ['Order cancelled', order?.cancellation_reason || 'Order was cancelled.', order?.cancelled_by, order?.cancelled_at],
      ['Tickets reissued', order?.reissue_reason || 'Tickets were reissued.', order?.reissued_by, order?.reissued_at],
    ];
    derived.forEach(([action, note, actor, at]) => {
      if (at && !logs.some(log => log.action === action && log.at === at)) logs.push({ action, note, actor, at });
    });
    return logs.sort((a, b) => String(b.at || '').localeCompare(String(a.at || '')));
  }

  function orderRecordSeatList(order) {
    return (Array.isArray(order?.seats) ? order.seats : []).map(seat => `<li><strong>${escapeHtml(seat.category || 'Ticket')}</strong><span>${escapeHtml(seat.section || '')} &middot; Row ${escapeHtml(seat.row || '')} &middot; Seat ${escapeHtml(seat.number || '')}</span><b>PHP ${Number(seat.price || 0).toLocaleString()}</b></li>`).join('') || '<li>No seats recorded.</li>';
  }

  function orderRecordLogList(order) {
    return orderRecordLogs(order).map(log => `<li><i></i><div><strong>${escapeHtml(log.action)}</strong><span>${escapeHtml(log.note || '')}</span><small>${escapeHtml(log.actor || 'System')} &middot; ${escapeHtml(log.at || '')}</small></div></li>`).join('') || '<li><i></i><div><strong>No payment logs yet.</strong><span>Activity will appear here when the order is updated.</span></div></li>';
  }

  function showOrderDetails(orderId) {
    const order = orderById(orderId);
    if (!order || !modal || !modalBody || !modalTitle) return;
    modal.classList.add('is-order-record');
    modalTitle.textContent = order.order_id || 'Order details';
    if (modalEyebrow) modalEyebrow.textContent = 'Order record';
    const normalizedPaymentStatus = String(order.payment_status || '').toLowerCase();
    const statusClass = ['paid', 'payment verified'].includes(normalizedPaymentStatus) ? 'is-success' : (['pending', 'pending payment', 'for verification'].includes(normalizedPaymentStatus) ? 'is-warning' : (normalizedPaymentStatus === 'rejected' ? 'is-danger' : 'is-muted'));
    const proof = order.proof_url ? `<img class="staff-modal-proof" src="${escapeHtml(order.proof_url)}" alt="Payment proof for ${escapeHtml(order.order_id)}">` : `<div class="staff-order-proof-empty"><span>Proof of payment</span><strong>${order.proof_of_payment ? 'Legacy proof is unavailable' : 'No screenshot uploaded'}</strong><small>${order.proof_of_payment ? escapeHtml(order.proof_of_payment) : 'This order has no attached payment image.'}</small></div>`;
    modalBody.innerHTML = `<div class="staff-order-modal staff-order-record"><header class="staff-order-record-head"><div><span>Order total</span><strong>PHP ${Number(order.total || 0).toLocaleString()}</strong><small>${escapeHtml(order.event_title || order.event || 'ClicKet event')}</small></div><div><span class="staff-status ${statusClass}">${escapeHtml(order.payment_status || 'Pending')}</span><small>${escapeHtml(order.order_status || 'Open')}</small></div></header><div class="staff-order-summary"><div><span>Buyer</span><strong>${escapeHtml(order.buyer_name || 'Guest')}</strong><small>${escapeHtml(order.buyer_email || 'No email')}</small></div><div><span>Event & venue</span><strong>${escapeHtml(order.event_title || order.event || '')}</strong><small>${escapeHtml(order.venue || '')}</small></div><div><span>Payment method</span><strong>${escapeHtml(order.payment_method_label || order.payment_method || '—')}</strong><small>${escapeHtml(order.payment_account || '')}</small></div><div><span>Reference number</span><strong>${escapeHtml(order.payment_reference || order.reference || '—')}</strong><small>${escapeHtml(order.booked_at || '')}</small></div></div><div class="staff-order-record-grid"><section class="staff-order-record-card"><div class="staff-order-record-card__head"><span>Payment proof</span><small>${escapeHtml(order.proof_of_payment || 'No file')}</small></div>${proof}</section><section class="staff-order-record-card"><div class="staff-order-record-card__head"><span>Selected seats</span><small>${Array.isArray(order.seats) ? order.seats.length : 0} ticket(s)</small></div><ul class="staff-order-seat-list">${orderRecordSeatList(order)}</ul></section></div><section class="staff-order-log-card"><div class="staff-order-record-card__head"><span>Payment logs</span><small>${orderRecordLogs(order).length} recorded event(s)</small></div><ul class="staff-order-log-list">${orderRecordLogList(order)}</ul></section></div>`;
    modal.hidden = false;
  }

  function openOrderAction(action, orderId) {
    const order = orderById(orderId);
    if (!order || !modal || !modalBody || !modalTitle) return;
    const labels = { approve: 'Approve payment', reject: 'Reject payment', refund: 'Refund order', cancel: 'Cancel order', reissue: 'Reissue tickets' };
    const reasonRequired = ['reject', 'refund', 'cancel'].includes(action);
    modalTitle.textContent = labels[action] || 'Update order';
    if (modalEyebrow) modalEyebrow.textContent = order.order_id;
    modalBody.innerHTML = `<form class="staff-order-action-form" data-order-action-form data-action="${escapeHtml(action)}" data-order-id="${escapeHtml(orderId)}"><p>${escapeHtml(order.buyer_name)} · ${escapeHtml(order.payment_reference || order.reference || '')}</p><label>Reason / note${reasonRequired ? ' <b>(required)</b>' : ' <small>(optional)</small>'}<textarea name="reason" ${reasonRequired ? 'required' : ''} placeholder="Add a clear reason for the payment log"></textarea></label><p class="staff-form-message" data-order-form-message></p><button class="staff-action-btn" type="submit">${escapeHtml(labels[action] || 'Save update')}</button></form>`;
    modal.hidden = false;
  }

  function applyOrderUpdate(updated) {
    const local = orderById(updated.order_id);
    if (local) Object.assign(local, updated);
    document.querySelectorAll(`[data-order-row="${CSS.escape(updated.order_id)}"]`).forEach(row => {
      row.querySelectorAll('[data-order-payment-status]').forEach(status => { status.textContent = updated.payment_status; setPaymentStatusClass(status, updated.payment_status); });
      row.querySelectorAll('[data-order-status]').forEach(status => { status.textContent = updated.order_status; });
    });
    document.querySelectorAll(`[data-payment-row="${CSS.escape(updated.order_id)}"]`).forEach(row => {
      row.querySelectorAll('[data-payment-status]').forEach(status => { status.textContent = updated.payment_status; setPaymentStatusClass(status, updated.payment_status); });
      row.querySelectorAll('[data-payment-action]').forEach(button => { button.disabled = String(updated.payment_status || '').toLowerCase() !== 'pending'; });
    });
  }

  async function submitOrderAction(action, orderId, reason = '', messageNode = null, submit = null) {
    if (submit) submit.disabled = true;
    if (messageNode) messageNode.textContent = 'Saving update...';
    try {
      const response = await fetch('staff-payment-api.php', {
        method: 'POST',
        body: new URLSearchParams({ action, order_id: orderId, reason, csrf_token: csrfToken }),
      });
      const payload = await response.json();
      if (!payload.success) throw new Error(payload.message || 'Order update failed.');
      applyOrderUpdate(payload.order);
      if (messageNode) messageNode.textContent = 'Saved. The order record and payment log were updated.';
      return payload;
    } catch (error) {
      if (messageNode) messageNode.textContent = error.message || 'Order update failed.';
      throw error;
    } finally {
      if (submit) submit.disabled = false;
    }
  }

  document.addEventListener('click', event => {
    const proofPreview = event.target.closest('[data-proof-preview]');
    if (proofPreview) {
      openProofPreview(proofPreview.dataset.proofOrderId || '', proofPreview.dataset.proofPreview || '');
      return;
    }
    const details = event.target.closest('[data-order-details]');
    if (details) { showOrderDetails(details.dataset.orderDetails || ''); return; }
    const paymentAction = event.target.closest('[data-payment-action][data-order-id]');
    if (paymentAction && !paymentAction.disabled) {
      const action = paymentAction.dataset.paymentAction || '';
      const orderId = paymentAction.dataset.orderId || '';
      if (action === 'reject') {
        openOrderAction(action, orderId);
        return;
      }
      if (action === 'approve' && window.confirm('Approve this payment and activate its tickets?')) {
        submitOrderAction(action, orderId, '', null, paymentAction)
          .then(() => window.setTimeout(() => window.location.reload(), 700))
          .catch(() => {});
      }
      return;
    }
    const action = event.target.closest('[data-order-action][data-order-id]');
    if (action && !action.disabled) openOrderAction(action.dataset.orderAction || '', action.dataset.orderId || '');
  });

  document.addEventListener('submit', async event => {
    const form = event.target.closest('[data-order-action-form]');
    if (!form) return;
    event.preventDefault();
    const submit = form.querySelector('[type="submit"]');
    const message = form.querySelector('[data-order-form-message]');
    submit.disabled = true;
    if (message) message.textContent = 'Saving update…';
    try {
      const response = await fetch('staff-payment-api.php', { method: 'POST', body: new URLSearchParams({ action: form.dataset.action || '', order_id: form.dataset.orderId || '', reason: new FormData(form).get('reason') || '', csrf_token: csrfToken }) });
      const payload = await response.json();
      if (!payload.success) throw new Error(payload.message || 'Order update failed.');
      applyOrderUpdate(payload.order);
      if (message) message.textContent = 'Saved. The order record and payment log were updated.';
      window.setTimeout(closeModal, 750);
    } catch (error) {
      if (message) message.textContent = error.message || 'Order update failed.';
    } finally { submit.disabled = false; }
  });

  const newsForm = document.querySelector('[data-news-form]');
  const newsSections = document.querySelector('[data-news-sections]');
  const newsSectionTemplate = document.getElementById('staffNewsSectionTemplate');

  function numberNewsSections() {
    newsSections?.querySelectorAll('[data-news-section]').forEach((section, index) => {
      const number = section.querySelector('.staff-news-section-editor__number');
      if (number) number.textContent = String(index + 1).padStart(2, '0');
      const remove = section.querySelector('[data-remove-news-section]');
      if (remove) remove.disabled = index === 0 && newsSections.querySelectorAll('[data-news-section]').length === 1;
    });
  }

  document.querySelector('[data-add-news-section]')?.addEventListener('click', () => {
    if (!newsSections || !newsSectionTemplate) return;
    newsSections.append(newsSectionTemplate.content.cloneNode(true));
    numberNewsSections();
    newsSections.querySelector('[data-news-section]:last-child input')?.focus();
  });

  document.addEventListener('click', event => {
    const remove = event.target.closest('[data-remove-news-section]');
    if (!remove || remove.disabled) return;
    remove.closest('[data-news-section]')?.remove();
    numberNewsSections();
  });

  newsForm?.addEventListener('submit', async event => {
    event.preventDefault();
    const submitter = event.submitter;
    const status = submitter?.dataset.newsSubmit || newsForm.querySelector('[name="status"]')?.value || 'Draft';
    const statusInput = newsForm.querySelector('[name="status"]');
    if (statusInput) statusInput.value = status;
    const message = newsForm.querySelector('[data-news-form-message]');
    if (submitter) submitter.disabled = true;
    if (message) message.textContent = status === 'Published' ? 'Publishing your article…' : 'Saving draft…';
    try {
      const response = await fetch('staff-news-api.php', { method: 'POST', body: new FormData(newsForm) });
      const payload = await response.json();
      if (!payload.success) throw new Error(payload.message || 'Could not save the article.');
      if (message) message.textContent = payload.message;
      window.setTimeout(() => window.location.reload(), 700);
    } catch (error) {
      if (message) message.textContent = error.message || 'Could not save the article.';
      if (submitter) submitter.disabled = false;
    }
  });

  document.querySelector('[data-news-banner-input]')?.addEventListener('change', event => {
    const file = event.target.files?.[0];
    const preview = document.querySelector('[data-news-banner-preview]');
    const image = preview?.querySelector('img');
    if (!file || !preview || !image) { if (preview) preview.hidden = true; return; }
    image.src = URL.createObjectURL(file);
    preview.hidden = false;
  });

  numberNewsSections();

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

  const checkinForm = document.querySelector('[data-checkin-form]');
  const checkinResult = document.querySelector('[data-checkin-result]');
  const checkinMessage = document.querySelector('[data-checkin-message]');

  function checkinStatusClass(scanResult) {
    return ({
      valid: 'is-success',
      already_used: 'is-muted',
      blocked: 'is-danger',
      invalid: 'is-danger',
    })[scanResult] || 'is-info';
  }

  function renderCheckinResult(payload) {
    if (!checkinResult) return;
    const ticket = payload?.ticket || {};
    const scanResult = payload?.scan_result || 'invalid';
    const resultLabel = scanResult.replace(/_/g, ' ');
    const statusClass = checkinStatusClass(scanResult);
    const title = payload?.success ? 'Entry recorded' : 'Entry blocked';
    const seatLabel = [ticket.section, ticket.row_label, ticket.seat_number].filter(Boolean).join(' ');
    checkinResult.innerHTML = `
      <header><div><p>Result</p><h3>${escapeHtml(title)}</h3></div><span class="staff-status ${statusClass}">${escapeHtml(resultLabel)}</span></header>
      <div class="staff-checkin-result-body">
        <strong>${escapeHtml(payload?.message || 'Validation complete.')}</strong>
        ${ticket.ticket_id ? `
          <div class="staff-checkin-ticket-grid">
            <div><span>Ticket</span><b>${escapeHtml(ticket.ticket_id)}</b></div>
            <div><span>Order</span><b>${escapeHtml(ticket.order_id || '-')}</b></div>
            <div><span>Event</span><b>${escapeHtml(ticket.event_title || '-')}</b></div>
            <div><span>Venue</span><b>${escapeHtml(ticket.venue || '-')}</b></div>
            <div><span>Buyer</span><b>${escapeHtml(ticket.buyer_name || '-')}</b></div>
            <div><span>Seat</span><b>${escapeHtml(seatLabel || ticket.category || '-')}</b></div>
            <div><span>Status</span><b>${escapeHtml(ticket.status || '-')}</b></div>
            <div><span>Used At</span><b>${escapeHtml(ticket.used_at || '-')}</b></div>
          </div>
        ` : '<span>No matching ticket record.</span>'}
      </div>`;
  }

  checkinForm?.addEventListener('submit', async event => {
    event.preventDefault();
    const submit = checkinForm.querySelector('[type="submit"]');
    if (submit) submit.disabled = true;
    if (checkinMessage) {
      checkinMessage.textContent = 'Validating ticket...';
      checkinMessage.className = 'staff-checkin-message';
    }

    try {
      const response = await fetch('staff-checkin-api.php', {
        method: 'POST',
        body: new FormData(checkinForm),
      });
      const payload = await response.json();
      renderCheckinResult(payload);
      if (checkinMessage) {
        checkinMessage.textContent = payload.message || 'Validation complete.';
        checkinMessage.classList.toggle('is-success', Boolean(payload.success));
        checkinMessage.classList.toggle('is-danger', !payload.success);
      }
      if (payload.success) {
        checkinForm.querySelector('[name="validation_code"]')?.focus();
        checkinForm.querySelector('[name="validation_code"]')?.select();
      }
    } catch (error) {
      const payload = { success: false, scan_result: 'invalid', message: 'Ticket validation failed.', ticket: null };
      renderCheckinResult(payload);
      if (checkinMessage) {
        checkinMessage.textContent = payload.message;
        checkinMessage.classList.add('is-danger');
      }
    } finally {
      if (submit) submit.disabled = false;
    }
  });

  function staffPeopleData() {
    const source = document.getElementById('staffPeopleJson');
    if (!source) return { people: [], venues: [] };
    try { return JSON.parse(source.textContent || '{"people":[],"venues":[]}'); } catch { return { people: [], venues: [] }; }
  }

  const peopleData = staffPeopleData();
  const personById = id => (peopleData.people || []).find(person => person.id === id);
  const venueOptions = () => (peopleData.venues || []).map(venue => `<option value="${escapeHtml(venue.id)}">${escapeHtml(venue.label)}</option>`).join('');
  const assignmentLabel = id => (peopleData.venues || []).find(venue => venue.id === id)?.label || id;

  function showPeopleGroup(role) {
    document.querySelectorAll('[data-people-tab]').forEach(tab => {
      const active = tab.dataset.peopleTab === role;
      tab.classList.toggle('is-active', active);
      tab.setAttribute('aria-selected', active ? 'true' : 'false');
    });
    document.querySelectorAll('[data-people-group]').forEach(group => group.classList.toggle('is-active', group.dataset.peopleGroup === role));
  }

  document.querySelectorAll('[data-people-tab]').forEach(tab => tab.addEventListener('click', () => showPeopleGroup(tab.dataset.peopleTab || 'customer')));

  function openPersonDetails(id) {
    const person = personById(id);
    if (!person || !modal || !modalBody || !modalTitle) return;
    const role = String(person.role || 'customer');
    const archived = person.disabled || String(person.status || 'Active').toLowerCase() !== 'active';
    modalTitle.textContent = 'User details';
    if (modalEyebrow) modalEyebrow.textContent = `${role} account`;
    const venueScope = Array.isArray(person.venues) ? (person.venues.map(assignmentLabel).join(', ') || 'Not assigned') : 'Customer account';
    const address = [person.street, person.city, person.province, person.zip, person.country].filter(Boolean).join(', ');
    const avatar = person.avatar_url
      ? `<img class="staff-person-avatar" src="${escapeHtml(person.avatar_url)}" alt="">`
      : `<span class="staff-person-avatar">${escapeHtml((person.name || 'U').split(/\s+/).map(part => part[0]).join('').slice(0, 2))}</span>`;
    modalBody.innerHTML = `<div class="staff-person-detail"><header>${avatar}<div><h3>${escapeHtml(person.name || 'Unnamed user')}</h3><p>${escapeHtml(person.email || '')}</p></div><span class="staff-status ${archived ? 'is-muted' : 'is-success'}">${archived ? 'Archived' : 'Active'}</span></header><section><h4>Account information</h4><div class="staff-person-detail__grid"><div><span>Username</span><strong>${escapeHtml(person.username || person.name || 'Not provided')}</strong></div><div><span>Full name</span><strong>${escapeHtml([person.first_name, person.last_name].filter(Boolean).join(' ') || person.name || '')}</strong></div><div><span>Role</span><strong>${escapeHtml(role)}</strong></div><div><span>Account ID</span><strong>${escapeHtml(person.id || '')}</strong></div><div><span>Gender</span><strong>${escapeHtml(person.gender || 'Not provided')}</strong></div><div><span>Birthday</span><strong>${escapeHtml(person.birthday || 'Not provided')}</strong></div><div><span>Phone</span><strong>${escapeHtml(person.phone || 'Not provided')}</strong></div><div><span>Created</span><strong>${escapeHtml(person.created_at || '')}</strong></div></div></section><section><h4>Profile & address</h4><div class="staff-person-detail__grid"><div><span>Bio</span><strong>${escapeHtml(person.bio || 'Not provided')}</strong></div><div><span>Address</span><strong>${escapeHtml(address || 'Not provided')}</strong></div></div></section><section><h4>Access & assignment</h4><div class="staff-person-detail__grid"><div><span>Venue scope</span><strong>${escapeHtml(venueScope)}</strong></div><div><span>Account status</span><strong>${archived ? 'Archived / disabled' : 'Active'}</strong></div></div></section></div>`;
    modal.hidden = false;
  }

  function openPersonCreate(role) {
    if (!modal || !modalBody || !modalTitle) return;
    modalTitle.textContent = `Add ${role}`;
    if (modalEyebrow) modalEyebrow.textContent = 'New account';
    const venueField = role === 'organizer' ? `<label>Assigned venue<select name="venue" required><option value="">Choose a venue</option>${venueOptions()}</select></label>` : '';
    modalBody.innerHTML = `<form class="staff-person-form" data-person-form data-person-form-mode="create"><input type="hidden" name="role" value="${escapeHtml(role)}"><label>Username<input name="name" minlength="3" required placeholder="Username or display name"></label><label>Email address<input name="email" type="email" pattern="^[^\\s@]+@[^\\s@]+\\.[^\\s@]{2,}$" title="Enter a valid email address, for example name@example.com." required placeholder="name@example.com"></label><label>Password<input name="password" type="password" minlength="8" required placeholder="Minimum 8 characters"></label>${venueField}<p class="staff-form-message" data-person-form-message></p><button type="submit" class="staff-action-btn">Create ${escapeHtml(role)} account</button></form>`;
    modal.hidden = false;
  }

  function openPersonAssign(id) {
    const person = personById(id);
    if (!person || !modal || !modalBody || !modalTitle) return;
    modalTitle.textContent = 'Assign organizer venue';
    if (modalEyebrow) modalEyebrow.textContent = person.name || 'Organizer';
    modalBody.innerHTML = `<form class="staff-person-form" data-person-form data-person-form-mode="assign"><input type="hidden" name="user_id" value="${escapeHtml(id)}"><label>Assigned venue<select name="venue" required><option value="">Choose a venue</option>${venueOptions()}</select></label><p class="staff-form-message" data-person-form-message></p><button type="submit" class="staff-action-btn">Save assignment</button></form>`;
    modal.hidden = false;
  }

  async function savePersonAction(params, messageNode, submit) {
    if (submit) submit.disabled = true;
    if (messageNode) messageNode.textContent = 'Saving…';
    try {
      const response = await fetch('staff-people-api.php', { method: 'POST', body: new URLSearchParams(params) });
      const payload = await response.json();
      if (!payload.success) throw new Error(payload.message || 'Could not update the account.');
      if (messageNode) messageNode.textContent = payload.message;
      window.setTimeout(() => window.location.reload(), 600);
    } catch (error) {
      if (messageNode) messageNode.textContent = error.message || 'Could not update the account.';
      if (submit) submit.disabled = false;
    }
  }

  document.addEventListener('click', event => {
    const view = event.target.closest('[data-person-view]');
    if (view) { openPersonDetails(view.dataset.personView || ''); return; }
    const create = event.target.closest('[data-person-create]');
    if (create) { openPersonCreate(create.dataset.personCreate || 'organizer'); return; }
    const assign = event.target.closest('[data-person-assign]');
    if (assign) { openPersonAssign(assign.dataset.personAssign || ''); return; }
    const action = event.target.closest('[data-person-action][data-person-id]');
    if (action && window.confirm(`Archive and disable this account? It will no longer be able to sign in.`)) savePersonAction({ action: action.dataset.personAction || 'archive', user_id: action.dataset.personId || '' }, null, action);
  });

  document.addEventListener('submit', event => {
    const form = event.target.closest('[data-person-form]');
    if (!form) return;
    event.preventDefault();
    const data = Object.fromEntries(new FormData(form).entries());
    data.action = form.dataset.personFormMode || 'create';
    savePersonAction(data, form.querySelector('[data-person-form-message]'), form.querySelector('[type="submit"]'));
  });

  const ticketOrders = staffTicketOrders();

  function staffTickets() {
    const source = document.getElementById('staffTicketsJson');
    if (!source) return [];
    try { return JSON.parse(source.textContent || '[]'); } catch { return []; }
  }

  const ticketRecords = staffTickets();
  const ticketById = id => ticketRecords.find(ticket => ticket.ticket_id === id);

  function showTicketDetails(ticketId) {
    const ticket = ticketById(ticketId);
    if (!ticket || !modal || !modalBody || !modalTitle) return;
    modal.classList.add('is-ticket-record');
    modalTitle.textContent = ticket.ticket_id || 'Ticket details';
    if (modalEyebrow) modalEyebrow.textContent = 'Ticket record';
    const status = String(ticket.status || '').toLowerCase();
    const statusClass = status === 'valid' ? 'is-success' : (status === 'used' ? 'is-info' : 'is-muted');
    modalBody.innerHTML = `<div class="staff-ticket-record"><header class="staff-ticket-record-head"><div><span>Ticket ID</span><strong>${escapeHtml(ticket.ticket_id || '')}</strong><small>Linked order ${escapeHtml(ticket.order_id || '—')}</small></div><span class="staff-status ${statusClass}">${escapeHtml(ticket.status || 'Valid')}</span></header><div class="staff-ticket-record-grid"><section><span>Event</span><strong>${escapeHtml(ticket.event_title || '')}</strong><small>${escapeHtml(ticket.venue || '')}</small></section><section><span>Customer</span><strong>${escapeHtml(ticket.buyer_name || 'ClicKet customer')}</strong><small>Ticket holder</small></section><section><span>Seat assignment</span><strong>${escapeHtml(ticket.section || '')} ${escapeHtml(ticket.row || '')}-${escapeHtml(ticket.number || '')}</strong><small>${escapeHtml(ticket.category || 'Admission')} · PHP ${Number(ticket.price || 0).toLocaleString()}</small></section><section><span>Voucher ID</span><strong>${escapeHtml(ticket.voucher_id || '—')}</strong><small>Proof of ticket issuance</small></section></div><section class="staff-ticket-validation"><div><span>Validation code</span><strong>${escapeHtml(ticket.validation_code || '—')}</strong><small>Use this record to verify ticket authenticity at the venue.</small></div><div class="staff-ticket-validation__mark">CK</div></section></div>`;
    modal.hidden = false;
  }

  document.addEventListener('click', event => {
    const ticketButton = event.target.closest('[data-ticket-details]');
    if (ticketButton) showTicketDetails(ticketButton.dataset.ticketDetails || '');
  });

  document.querySelectorAll('[data-ticket-filter]').forEach(button => {
    button.addEventListener('click', () => {
      const value = button.dataset.ticketFilter || 'all';
      document.querySelectorAll('[data-ticket-filter]').forEach(item => item.classList.toggle('is-active', item === button));
      document.querySelectorAll('[data-ticket-filter-row]').forEach(row => row.classList.toggle('is-ticket-filtered', value !== 'all' && row.dataset.ticketFilterRow !== value));
    });
  });

  document.querySelector('[data-ticket-local-search]')?.addEventListener('input', event => {
    const term = event.target.value.trim().toLowerCase();
    document.querySelectorAll('[data-ticket-row]').forEach(row => row.classList.toggle('is-ticket-search-hidden', term !== '' && !row.textContent.toLowerCase().includes(term)));
  });

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
