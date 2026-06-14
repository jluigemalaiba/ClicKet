(() => {
  'use strict';

  const configNode = document.getElementById('ticketConfig');
  if (!configNode) return;

  const config = JSON.parse(configNode.textContent);
  const svg = document.getElementById('seatMap');
  const canvas = document.getElementById('mapCanvas');
  const viewport = document.getElementById('mapViewport');
  const tooltip = document.getElementById('seatTooltip');
  const selectedList = document.getElementById('selectedSeats');
  const selectedCount = document.getElementById('selectedCount');
  const emptyState = document.getElementById('selectionEmpty');
  const continueButton = document.getElementById('continueBooking');
  const status = document.getElementById('ticketStatus');
  const bestPanel = document.getElementById('bestAvailablePanel');
  const suggestion = document.getElementById('bestSuggestion');
  const suggestionLabel = document.getElementById('bestSuggestionLabel');
  const storageKey = `clicket_selected_seats_${config.event.key}`;
  const timerKey = `clicket_event_timer_${config.event.key}`;
  const ns = 'http://www.w3.org/2000/svg';
  const activePointers = new Map();

  const state = {
    seats: [],
    selected: [],
    category: 'all',
    scale: 1,
    x: 0,
    y: 0,
    dragging: false,
    pointerX: 0,
    pointerY: 0,
    bestQuantity: 2,
    suggestionOffset: 0,
    pinchDistance: 0,
  };

  const zoneLayouts = {
    arena: {
      'floor-left': [350, 165, 145, 155], 'floor-right': [505, 165, 145, 155],
      'lower-left': [205, 160, 125, 205], 'lower-right': [670, 160, 125, 205],
      'side-left': [100, 205, 90, 245], 'side-right': [810, 205, 90, 245],
      'upper-left': [170, 390, 250, 140], 'upper-right': [580, 390, 250, 140],
      rear: [350, 480, 300, 135],
    },
    stadium: {
      'floor-center': [370, 180, 260, 170], 'floor-left': [220, 185, 135, 170], 'floor-right': [645, 185, 135, 170],
      'lower-left': [100, 180, 105, 220], 'lower-right': [795, 180, 105, 220],
      'side-left': [80, 420, 185, 115], 'side-right': [735, 420, 185, 115],
      'upper-left': [275, 475, 205, 120], 'upper-right': [520, 475, 205, 120],
      rear: [370, 590, 260, 90],
    },
    theater: {
      'orchestra-left': [170, 175, 205, 180], 'orchestra-center': [390, 165, 220, 205], 'orchestra-right': [625, 175, 205, 180],
      'loge-left': [135, 385, 220, 105], 'loge-center': [375, 390, 250, 105], 'loge-right': [645, 385, 220, 105],
      'balcony-left': [115, 530, 235, 105], 'balcony-center': [370, 535, 260, 105], 'balcony-right': [650, 530, 235, 105],
    },
    hall: {
      'floor-left': [280, 170, 210, 210], 'floor-right': [510, 170, 210, 210],
      'lower-left': [180, 400, 250, 105], 'lower-right': [570, 400, 250, 105],
      'upper-left': [105, 535, 230, 105], 'upper-right': [665, 535, 230, 105],
      rear: [370, 525, 260, 120],
    },
    court: {
      'court-left': [155, 260, 140, 190], 'court-right': [705, 260, 140, 190],
      'lower-left': [115, 110, 270, 115], 'lower-right': [615, 110, 270, 115],
      'upper-left': [115, 490, 270, 115], 'upper-right': [615, 490, 270, 115],
      'side-left': [35, 235, 100, 240], 'side-right': [865, 235, 100, 240],
      rear: [400, 610, 200, 75],
    },
    outdoor: {
      'floor-left': [290, 155, 200, 165], 'floor-right': [510, 155, 200, 165],
      'lower-left': [180, 340, 270, 115], 'lower-right': [550, 340, 270, 115],
      'side-left': [95, 480, 250, 105], 'side-right': [655, 480, 250, 105],
      'upper-left': [185, 600, 260, 80], 'upper-right': [555, 600, 260, 80],
      rear: [370, 510, 260, 95],
    },
  };

  function svgNode(name, attrs = {}) {
    const node = document.createElementNS(ns, name);
    Object.entries(attrs).forEach(([key, value]) => node.setAttribute(key, value));
    return node;
  }

  function deterministicNumber(value) {
    let hash = 2166136261;
    for (let index = 0; index < value.length; index += 1) {
      hash ^= value.charCodeAt(index);
      hash = Math.imul(hash, 16777619);
    }
    return Math.abs(hash >>> 0);
  }

  function sectionPosition(section, index) {
    const positions = zoneLayouts[config.venue.layout] || zoneLayouts.arena;
    return positions[section.zone] || [150 + (index % 3) * 250, 180 + Math.floor(index / 3) * 170, 210, 130];
  }

  function renderStage() {
    if (config.venue.layout === 'court') {
      const court = svgNode('rect', { x: 320, y: 235, width: 360, height: 245, rx: 18, class: 'map-court' });
      canvas.appendChild(court);
      canvas.appendChild(svgNode('line', { x1: 500, y1: 235, x2: 500, y2: 480, class: 'map-court-line' }));
      canvas.appendChild(svgNode('circle', { cx: 500, cy: 357, r: 48, class: 'map-court-line' }));
      const label = svgNode('text', { x: 500, y: 365, class: 'map-section-label' });
      label.textContent = config.venue.stageLabel;
      canvas.appendChild(label);
      return;
    }

    const stageWidth = config.venue.layout === 'outdoor' ? 470 : 530;
    const stageX = (1000 - stageWidth) / 2;
    canvas.appendChild(svgNode('path', {
      d: `M ${stageX} 65 Q 500 25 ${stageX + stageWidth} 65 L ${stageX + stageWidth - 35} 135 Q 500 170 ${stageX + 35} 135 Z`,
      class: 'map-stage',
    }));
    const label = svgNode('text', { x: 500, y: 105, class: 'map-stage-label' });
    label.textContent = config.venue.stageLabel;
    canvas.appendChild(label);
  }

  function createSeats(section, group, box, sectionIndex) {
    const [x, y, width, height] = box;
    const rows = height < 90 ? 3 : 5;
    const columns = width < 120 ? 5 : Math.max(6, Math.min(10, Math.floor(width / 24)));
    const category = config.categories[section.category];
    const paddingX = Math.min(25, width * .14);
    const paddingY = Math.min(32, height * .25);
    const usableWidth = width - paddingX * 2;
    const usableHeight = height - paddingY * 2;

    for (let rowIndex = 0; rowIndex < rows; rowIndex += 1) {
      for (let columnIndex = 0; columnIndex < columns; columnIndex += 1) {
        const seatNumber = columnIndex + 1;
        const row = String.fromCharCode(65 + rowIndex);
        const id = `${section.id}-${row}-${seatNumber}`;
        const random = deterministicNumber(`${config.event.key}-${id}`);
        const unavailable = random % 100 < 28;
        const cx = x + paddingX + (columns === 1 ? 0 : (usableWidth / (columns - 1)) * columnIndex);
        const cy = y + paddingY + (rows === 1 ? 0 : (usableHeight / (rows - 1)) * rowIndex);
        const seat = {
          id,
          sectionId: section.id,
          section: section.label,
          row,
          number: String(seatNumber),
          category: category.label,
          categoryKey: section.category,
          color: category.color,
          price: category.price,
          unavailable,
          rank: category.rank * 100000 + sectionIndex * 1000 + rowIndex * 100 + Math.abs(columnIndex - (columns - 1) / 2),
          rowIndex,
          columnIndex,
        };
        state.seats.push(seat);

        const circle = svgNode('circle', {
          cx, cy, r: 6, fill: category.color,
          class: `map-seat${unavailable ? ' is-unavailable' : ''}`,
          tabindex: unavailable ? '-1' : '0',
          role: 'button',
          'aria-label': `${section.label}, row ${row}, seat ${seatNumber}, ${category.label}${unavailable ? ', unavailable' : ''}`,
          'data-seat-id': id,
        });
        group.appendChild(circle);
      }
    }
  }

  function renderMap() {
    canvas.replaceChildren();
    state.seats = [];
    renderStage();

    config.venue.sections.forEach((section, index) => {
      const box = sectionPosition(section, index);
      const [x, y, width, height] = box;
      const category = config.categories[section.category];
      const group = svgNode('g', {
        class: 'map-section',
        'data-section-id': section.id,
        'data-category': section.category,
        style: `--section-color:${category.color}`,
      });
      group.appendChild(svgNode('rect', { x, y, width, height, rx: 20, class: 'map-section-bg' }));
      createSeats(section, group, box, index);
      const label = svgNode('text', { x: x + width / 2, y: y + 18, class: 'map-section-label' });
      label.textContent = section.label;
      group.appendChild(label);
      canvas.appendChild(group);
    });

    restoreSelection();
    updateAvailability();
    applyCategoryFilter();
  }

  function availableByCategory(categoryKey) {
    return state.seats.filter(seat => !seat.unavailable && seat.categoryKey === categoryKey).length;
  }

  function updateAvailability() {
    let total = 0;
    Object.keys(config.categories).forEach(key => {
      const count = availableByCategory(key);
      total += count;
      const node = document.querySelector(`[data-availability-for="${key}"]`);
      if (node) node.textContent = count;
      const button = document.querySelector(`[data-category="${key}"]`);
      if (button) button.hidden = count === 0;
    });
    document.getElementById('totalAvailability').textContent = `${total} seats available`;
  }

  function applyTransform() {
    canvas.setAttribute('transform', `translate(${state.x} ${state.y}) scale(${state.scale})`);
  }

  function setZoom(nextScale, originX = 500, originY = 360) {
    const clamped = Math.max(.75, Math.min(4, nextScale));
    const ratio = clamped / state.scale;
    state.x = originX - (originX - state.x) * ratio;
    state.y = originY - (originY - state.y) * ratio;
    state.scale = clamped;
    applyTransform();
  }

  function resetMap() {
    state.scale = 1;
    state.x = 0;
    state.y = 0;
    applyTransform();
  }

  function focusSection(sectionId) {
    const section = config.venue.sections.find(item => item.id === sectionId);
    if (!section) return;
    const [x, y, width, height] = sectionPosition(section, 0);
    const targetScale = Math.min(3, Math.max(1.65, 520 / Math.max(width, height)));
    state.scale = targetScale;
    state.x = 500 - (x + width / 2) * targetScale;
    state.y = 360 - (y + height / 2) * targetScale;
    applyTransform();
  }

  function setCategory(category) {
    state.category = category;
    document.querySelectorAll('.ticket-category').forEach(button => {
      button.classList.toggle('is-active', button.dataset.category === category);
    });
    applyCategoryFilter();
  }

  function applyCategoryFilter() {
    document.querySelectorAll('.map-section').forEach(section => {
      const matches = state.category === 'all' || section.dataset.category === state.category;
      section.classList.toggle('is-dimmed', !matches);
      section.classList.toggle('is-focused', matches && state.category !== 'all');
    });

    if (state.category !== 'all') {
      const first = config.venue.sections.find(section => section.category === state.category);
      if (first) focusSection(first.id);
    } else {
      resetMap();
    }
  }

  function seatElement(id) {
    return canvas.querySelector(`[data-seat-id="${CSS.escape(id)}"]`);
  }

  function toggleSeat(id) {
    const seat = state.seats.find(item => item.id === id);
    if (!seat || seat.unavailable) return;
    const selectedIndex = state.selected.findIndex(item => item.id === id);

    if (selectedIndex >= 0) {
      state.selected.splice(selectedIndex, 1);
    } else {
      if (state.selected.length >= config.selectionLimit) {
        showStatus(`A maximum of ${config.selectionLimit} seats is allowed per account.`);
        return;
      }
      state.selected.push(seat);
    }

    persistSelection();
    renderSelection();
  }

  function replaceSelection(seats) {
    state.selected = seats.slice(0, config.selectionLimit);
    persistSelection();
    renderSelection();
  }

  function persistSelection() {
    sessionStorage.setItem(storageKey, JSON.stringify(state.selected.map(seat => seat.id)));
  }

  function restoreSelection() {
    let ids = [];
    try {
      ids = JSON.parse(sessionStorage.getItem(storageKey) || '[]');
    } catch (error) {
      ids = [];
    }
    state.selected = ids
      .map(id => state.seats.find(seat => seat.id === id && !seat.unavailable))
      .filter(Boolean)
      .slice(0, config.selectionLimit);
    renderSelection();
  }

  function renderSelection() {
    const selectedIds = new Set(state.selected.map(seat => seat.id));
    state.seats.forEach(seat => seatElement(seat.id)?.classList.toggle('is-selected', selectedIds.has(seat.id)));
    selectedCount.textContent = `${state.selected.length} / ${config.selectionLimit}`;
    emptyState.hidden = state.selected.length > 0;
    continueButton.disabled = state.selected.length === 0;

    selectedList.innerHTML = state.selected.map(seat => `
      <article class="ticket-selected-seat" style="--seat-color:${seat.color}">
        <span class="ticket-selected-seat-icon">${seat.row}${seat.number}</span>
        <div>
          <strong>${escapeHtml(seat.section)}</strong>
          <span>Row ${escapeHtml(seat.row)} &middot; Seat ${escapeHtml(seat.number)} &middot; ${escapeHtml(seat.category)}</span>
        </div>
        <button type="button" data-remove-seat="${escapeHtml(seat.id)}" aria-label="Remove ${escapeHtml(seat.section)} row ${escapeHtml(seat.row)} seat ${escapeHtml(seat.number)}">&times;</button>
      </article>
    `).join('');
  }

  function bestAvailable(quantity, offset = 0) {
    const available = state.seats
      .filter(seat => !seat.unavailable && !state.selected.some(selected => selected.id === seat.id))
      .sort((a, b) => a.rank - b.rank);
    const grouped = new Map();

    available.forEach(seat => {
      const key = `${seat.sectionId}-${seat.row}`;
      if (!grouped.has(key)) grouped.set(key, []);
      grouped.get(key).push(seat);
    });

    const candidates = [];
    grouped.forEach(rowSeats => {
      rowSeats.sort((a, b) => a.columnIndex - b.columnIndex);
      for (let start = 0; start <= rowSeats.length - quantity; start += 1) {
        const candidate = rowSeats.slice(start, start + quantity);
        const contiguous = candidate.every((seat, index) => index === 0 || seat.columnIndex === candidate[index - 1].columnIndex + 1);
        if (contiguous) candidates.push(candidate);
      }
    });
    candidates.sort((a, b) => a.reduce((sum, seat) => sum + seat.rank, 0) - b.reduce((sum, seat) => sum + seat.rank, 0));
    return candidates[offset % Math.max(candidates.length, 1)] || [];
  }

  function suggestBestSeats() {
    const seats = bestAvailable(state.bestQuantity, state.suggestionOffset);
    if (!seats.length) {
      showStatus('No contiguous seats are available for that quantity.');
      suggestion.hidden = true;
      return;
    }
    replaceSelection(seats);
    focusSection(seats[0].sectionId);
    suggestionLabel.textContent = `${seats[0].section}, Row ${seats[0].row}, Seats ${seats.map(seat => seat.number).join(', ')}`;
    suggestion.hidden = false;
    showStatus('Best available seats have been added. You can still change them.');
  }

  function showTooltip(seat, clientX, clientY) {
    tooltip.innerHTML = `<strong>${escapeHtml(seat.section)} &middot; Row ${escapeHtml(seat.row)} &middot; Seat ${escapeHtml(seat.number)}</strong><span>${escapeHtml(seat.category)}${seat.unavailable ? ' &middot; Unavailable' : ` &middot; PHP ${seat.price.toLocaleString()}`}</span>`;
    tooltip.hidden = false;
    const bounds = viewport.getBoundingClientRect();
    const left = Math.min(bounds.width - 205, Math.max(10, clientX - bounds.left + 14));
    const top = Math.min(bounds.height - 65, Math.max(10, clientY - bounds.top + 14));
    tooltip.style.left = `${left}px`;
    tooltip.style.top = `${top}px`;
  }

  function hideTooltip() {
    tooltip.hidden = true;
  }

  function showStatus(message) {
    status.textContent = message;
    window.clearTimeout(showStatus.timeout);
    showStatus.timeout = window.setTimeout(() => {
      if (status.textContent === message) status.textContent = '';
    }, 4200);
  }

  function escapeHtml(value) {
    return String(value)
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
  }

  function startTimer() {
    const timer = document.getElementById('ticketTimer');
    let expiresAt = Number(sessionStorage.getItem(timerKey));
    if (!expiresAt || expiresAt <= Date.now()) {
      expiresAt = Date.now() + 15 * 60 * 1000;
      sessionStorage.setItem(timerKey, String(expiresAt));
    }

    const update = () => {
      const remaining = Math.max(0, expiresAt - Date.now());
      const seconds = Math.ceil(remaining / 1000);
      timer.textContent = `${String(Math.floor(seconds / 60)).padStart(2, '0')}:${String(seconds % 60).padStart(2, '0')}`;
      if (remaining <= 0) {
        timer.textContent = 'Expired';
        continueButton.disabled = true;
        showStatus('Your booking window expired. Return to the event page to restart.');
        window.clearInterval(interval);
      }
    };
    update();
    const interval = window.setInterval(update, 1000);
  }

  document.getElementById('categoryBar').addEventListener('click', event => {
    const button = event.target.closest('[data-category]');
    if (button) setCategory(button.dataset.category);
  });

  document.querySelectorAll('[data-map-action]').forEach(button => {
    button.addEventListener('click', () => {
      if (button.dataset.mapAction === 'zoom-in') setZoom(state.scale * 1.25);
      if (button.dataset.mapAction === 'zoom-out') setZoom(state.scale / 1.25);
      if (button.dataset.mapAction === 'reset') resetMap();
    });
  });

  viewport.addEventListener('wheel', event => {
    event.preventDefault();
    const bounds = svg.getBoundingClientRect();
    const originX = ((event.clientX - bounds.left) / bounds.width) * 1000;
    const originY = ((event.clientY - bounds.top) / bounds.height) * 720;
    setZoom(state.scale * (event.deltaY < 0 ? 1.12 : .89), originX, originY);
  }, { passive: false });

  viewport.addEventListener('pointerdown', event => {
    if (event.target.matches('.map-seat')) return;
    activePointers.set(event.pointerId, { x: event.clientX, y: event.clientY });
    state.dragging = true;
    state.pointerX = event.clientX;
    state.pointerY = event.clientY;
    viewport.classList.add('is-dragging');
    viewport.setPointerCapture(event.pointerId);
  });

  viewport.addEventListener('pointermove', event => {
    const seatNode = event.target.closest?.('[data-seat-id]');
    if (seatNode) {
      const seat = state.seats.find(item => item.id === seatNode.dataset.seatId);
      if (seat) showTooltip(seat, event.clientX, event.clientY);
    } else {
      hideTooltip();
    }

    if (activePointers.has(event.pointerId)) {
      activePointers.set(event.pointerId, { x: event.clientX, y: event.clientY });
    }

    if (activePointers.size === 2) {
      const [first, second] = Array.from(activePointers.values());
      const distance = Math.hypot(second.x - first.x, second.y - first.y);
      if (state.pinchDistance > 0) {
        const bounds = svg.getBoundingClientRect();
        const midpointX = ((first.x + second.x) / 2 - bounds.left) / bounds.width * 1000;
        const midpointY = ((first.y + second.y) / 2 - bounds.top) / bounds.height * 720;
        setZoom(state.scale * (distance / state.pinchDistance), midpointX, midpointY);
      }
      state.pinchDistance = distance;
      state.dragging = false;
      return;
    }

    state.pinchDistance = 0;
    if (!state.dragging) return;
    const bounds = svg.getBoundingClientRect();
    state.x += ((event.clientX - state.pointerX) / bounds.width) * 1000;
    state.y += ((event.clientY - state.pointerY) / bounds.height) * 720;
    state.pointerX = event.clientX;
    state.pointerY = event.clientY;
    applyTransform();
  });

  viewport.addEventListener('pointerup', event => {
    activePointers.delete(event.pointerId);
    state.pinchDistance = 0;
    state.dragging = false;
    viewport.classList.remove('is-dragging');
    if (viewport.hasPointerCapture(event.pointerId)) viewport.releasePointerCapture(event.pointerId);
  });
  viewport.addEventListener('pointercancel', event => {
    activePointers.delete(event.pointerId);
    state.pinchDistance = 0;
    state.dragging = false;
    viewport.classList.remove('is-dragging');
  });
  viewport.addEventListener('pointerleave', hideTooltip);

  canvas.addEventListener('click', event => {
    const seatNode = event.target.closest('[data-seat-id]');
    if (seatNode) toggleSeat(seatNode.dataset.seatId);
  });
  canvas.addEventListener('keydown', event => {
    const seatNode = event.target.closest('[data-seat-id]');
    if (seatNode && (event.key === 'Enter' || event.key === ' ')) {
      event.preventDefault();
      toggleSeat(seatNode.dataset.seatId);
    }
  });

  selectedList.addEventListener('click', event => {
    const button = event.target.closest('[data-remove-seat]');
    if (button) toggleSeat(button.dataset.removeSeat);
  });

  document.querySelectorAll('[data-mode]').forEach(button => {
    button.addEventListener('click', () => {
      document.querySelectorAll('[data-mode]').forEach(item => item.classList.toggle('is-active', item === button));
      bestPanel.hidden = button.dataset.mode !== 'best';
    });
  });

  document.querySelectorAll('[data-best-quantity]').forEach(button => {
    button.addEventListener('click', () => {
      document.querySelectorAll('[data-best-quantity]').forEach(item => item.classList.toggle('is-active', item === button));
      state.bestQuantity = Number(button.dataset.bestQuantity);
      state.suggestionOffset = 0;
    });
  });
  document.getElementById('findBestSeats').addEventListener('click', suggestBestSeats);
  document.getElementById('tryAnotherSuggestion').addEventListener('click', () => {
    state.suggestionOffset += 1;
    suggestBestSeats();
  });
  document.getElementById('acceptSuggestion').addEventListener('click', () => {
    suggestion.hidden = true;
    showStatus('Suggested seats kept. Continue when you are ready.');
  });

  continueButton.addEventListener('click', async () => {
    if (!state.selected.length || state.selected.length > config.selectionLimit) return;
    continueButton.disabled = true;
    continueButton.textContent = 'Saving seats...';
    status.textContent = '';

    try {
      const response = await fetch('includes/ticket-selection-api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          event: config.event.key,
          performance: config.event.performance,
          performanceDate: config.event.date,
          performanceTime: config.event.time,
          seats: state.selected.map(seat => ({
            id: seat.id,
            section: seat.section,
            row: seat.row,
            number: seat.number,
            category: seat.category,
          })),
        }),
      });
      const result = await response.json();
      if (!response.ok || !result.success) throw new Error(result.message || 'Unable to save seats.');
      window.location.href = result.redirect;
    } catch (error) {
      showStatus(error.message || 'Unable to continue. Please try again.');
      continueButton.disabled = false;
      continueButton.textContent = 'Continue with selected seats';
    }
  });

  renderMap();
  startTimer();
})();
