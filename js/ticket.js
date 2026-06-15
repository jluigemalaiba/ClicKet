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
  if (config.reservationExpired) sessionStorage.removeItem(storageKey);
  const ns = 'http://www.w3.org/2000/svg';
  const activePointers = new Map();
  const unavailableSeatIds = new Set(config.unavailableSeatIds || []);

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
    'moa-concert': {
      'floor-left': [350, 165, 145, 155], 'floor-right': [505, 165, 145, 155],
      'lower-left': [205, 160, 125, 205], 'lower-right': [670, 160, 125, 205],
      'side-left': [85, 210, 100, 170], 'side-right': [815, 210, 100, 170],
      'upper-left': [185, 405, 230, 115], 'upper-right': [585, 405, 230, 115],
      rear: [365, 545, 270, 95],
    },
    'moa-sports': {
      'floor-left': [300, 260, 125, 190], 'floor-right': [575, 260, 125, 190],
      'lower-left': [155, 185, 125, 280], 'lower-right': [720, 185, 125, 280],
      'side-left': [40, 245, 95, 200], 'side-right': [865, 245, 95, 200],
      'upper-left': [160, 70, 260, 105], 'upper-right': [580, 70, 260, 105],
      rear: [370, 555, 260, 100],
    },
    'philippine-concert': {
      'floor-center': [405, 175, 190, 170], 'floor-left': [255, 190, 135, 155], 'floor-right': [610, 190, 135, 155],
      'lower-left': [115, 180, 125, 230], 'lower-right': [760, 180, 125, 230],
      'side-left': [70, 430, 195, 105], 'side-right': [735, 430, 195, 105],
      'upper-left': [270, 500, 210, 105], 'upper-right': [520, 500, 210, 105],
      rear: [370, 610, 260, 80],
    },
    'philippine-sports': {
      'floor-center': [390, 505, 220, 95], 'floor-left': [275, 260, 100, 190], 'floor-right': [625, 260, 100, 190],
      'lower-left': [135, 185, 120, 265], 'lower-right': [745, 185, 120, 265],
      'side-left': [35, 250, 85, 195], 'side-right': [880, 250, 85, 195],
      'upper-left': [175, 65, 255, 105], 'upper-right': [570, 65, 255, 105],
      rear: [370, 610, 260, 75],
    },
    'araneta-concert': {
      'floor-left': [365, 165, 130, 165], 'floor-right': [505, 165, 130, 165],
      'lower-left': [225, 180, 125, 205], 'lower-right': [650, 180, 125, 205],
      'side-left': [105, 220, 100, 170], 'side-right': [795, 220, 100, 170],
      'upper-left': [180, 420, 245, 120], 'upper-right': [575, 420, 245, 120],
      rear: [355, 545, 290, 105],
    },
    'araneta-sports': {
      'floor-left': [300, 255, 120, 200], 'floor-right': [580, 255, 120, 200],
      'lower-left': [160, 175, 120, 285], 'lower-right': [720, 175, 120, 285],
      'side-left': [45, 240, 95, 205], 'side-right': [860, 240, 95, 205],
      'upper-left': [175, 65, 250, 100], 'upper-right': [575, 65, 250, 100],
      rear: [365, 565, 270, 95],
    },
    newport: {
      'orchestra-left': [170, 175, 205, 180], 'orchestra-center': [390, 165, 220, 205], 'orchestra-right': [625, 175, 205, 180],
      'loge-left': [135, 385, 220, 105], 'loge-center': [375, 390, 250, 105], 'loge-right': [645, 385, 220, 105],
      'balcony-left': [115, 530, 235, 105], 'balcony-center': [370, 535, 260, 105], 'balcony-right': [650, 530, 235, 105],
    },
    metropolitan: {
      'orchestra-left': [245, 410, 180, 170], 'orchestra-center': [435, 420, 130, 180], 'orchestra-right': [575, 410, 180, 170],
      'loge-left': [185, 300, 200, 95], 'loge-center': [395, 305, 210, 95], 'loge-right': [615, 300, 200, 95],
      'balcony-left': [125, 175, 230, 100], 'balcony-center': [370, 160, 260, 110], 'balcony-right': [645, 175, 230, 100],
    },
    solaire: {
      'orchestra-left': [165, 405, 215, 175], 'orchestra-center': [395, 390, 210, 200], 'orchestra-right': [620, 405, 215, 175],
      'loge-left': [125, 280, 230, 105], 'loge-center': [370, 270, 260, 110], 'loge-right': [645, 280, 230, 105],
      'balcony-left': [100, 145, 250, 105], 'balcony-center': [365, 130, 270, 115], 'balcony-right': [650, 145, 250, 105],
    },
    tanghalan: {
      'orchestra-left': [130, 155, 220, 115], 'orchestra-center': [390, 125, 220, 115], 'orchestra-right': [650, 155, 220, 115],
      'loge-left': [115, 300, 185, 120], 'loge-center': [350, 490, 300, 105], 'loge-right': [700, 300, 185, 120],
      'balcony-left': [130, 530, 200, 95], 'balcony-center': [390, 610, 220, 70], 'balcony-right': [670, 530, 200, 95],
    },
    resorts: {
      'orchestra-left': [240, 165, 170, 190], 'orchestra-center': [415, 165, 170, 190], 'orchestra-right': [590, 165, 170, 190],
      'loge-left': [125, 245, 100, 225], 'loge-center': [390, 375, 220, 105], 'loge-right': [775, 245, 100, 225],
      'balcony-left': [120, 495, 240, 105], 'balcony-center': [380, 510, 240, 105], 'balcony-right': [640, 495, 240, 105],
    },
    samsung: {
      'floor-left': [300, 380, 190, 190], 'floor-right': [510, 380, 190, 190],
      'lower-left': [185, 265, 250, 100], 'lower-right': [565, 265, 250, 100],
      'upper-left': [150, 145, 260, 95], 'upper-right': [590, 145, 260, 95],
      rear: [370, 65, 260, 70],
    },
    philsports: {
      'court-left': [155, 260, 140, 190], 'court-right': [705, 260, 140, 190],
      'lower-left': [115, 110, 270, 115], 'lower-right': [615, 110, 270, 115],
      'upper-left': [115, 490, 270, 115], 'upper-right': [615, 490, 270, 115],
      'side-left': [35, 235, 100, 240], 'side-right': [865, 235, 100, 240],
      rear: [400, 610, 200, 75],
    },
    filoil: {
      'court-left': [155, 255, 115, 205], 'court-right': [730, 255, 115, 205],
      'lower-left': [150, 115, 260, 115], 'lower-right': [590, 115, 260, 115],
      'upper-left': [150, 485, 260, 115], 'upper-right': [590, 485, 260, 115],
      'side-left': [40, 225, 95, 250], 'side-right': [865, 225, 95, 250],
      rear: [360, 610, 280, 75],
    },
    cuneta: {
      'court-left': [150, 250, 120, 210], 'court-right': [730, 250, 120, 210],
      'lower-left': [120, 115, 280, 115], 'lower-right': [600, 115, 280, 115],
      'upper-left': [120, 485, 280, 115], 'upper-right': [600, 485, 280, 115],
      'side-left': [35, 230, 95, 245], 'side-right': [870, 230, 95, 245],
      rear: [385, 610, 230, 75],
    },
    muntinlupa: {
      'court-left': [135, 250, 125, 210], 'court-right': [740, 250, 125, 210],
      'lower-left': [135, 125, 270, 105], 'lower-right': [595, 125, 270, 105],
      'upper-left': [135, 485, 270, 105], 'upper-right': [595, 485, 270, 105],
      'side-left': [35, 250, 80, 210], 'side-right': [885, 250, 80, 210],
      rear: [390, 600, 220, 80],
    },
    ninoy: {
      'floor-center': [390, 500, 220, 90],
      'lower-left': [155, 185, 125, 270], 'lower-right': [720, 185, 125, 270],
      'side-left': [45, 240, 90, 210], 'side-right': [865, 240, 90, 210],
      'upper-left': [170, 75, 260, 100], 'upper-right': [570, 75, 260, 100],
      rear: [365, 600, 270, 80],
    },
    nuvali: {
      'floor-left': [155, 250, 125, 200], 'floor-right': [720, 250, 125, 200],
      'lower-left': [210, 485, 250, 105], 'lower-right': [540, 485, 250, 105],
      'side-left': [115, 145, 250, 90], 'side-right': [635, 145, 250, 90],
      'upper-left': [170, 600, 260, 75], 'upper-right': [570, 600, 260, 75],
      rear: [370, 65, 260, 70],
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
    const positions = zoneLayouts[config.venue.mapKey] || zoneLayouts['moa-concert'];
    return positions[section.zone] || [150 + (index % 3) * 250, 180 + Math.floor(index / 3) * 170, 210, 130];
  }

  function renderStage() {
    const mapType = config.venue.mapType || 'end-stage';

    if (mapType === 'court' || mapType === 'tennis') {
      const court = svgNode('rect', { x: 320, y: 235, width: 360, height: 245, rx: 18, class: 'map-court' });
      canvas.appendChild(court);
      canvas.appendChild(svgNode('line', { x1: 500, y1: 235, x2: 500, y2: 480, class: 'map-court-line' }));
      if (mapType === 'court') {
        canvas.appendChild(svgNode('circle', { cx: 500, cy: 357, r: 48, class: 'map-court-line' }));
      } else {
        canvas.appendChild(svgNode('rect', { x: 425, y: 275, width: 150, height: 165, class: 'map-court-line' }));
      }
      const label = svgNode('text', { x: 500, y: 365, class: 'map-section-label' });
      label.textContent = config.venue.stageLabel;
      canvas.appendChild(label);
      return;
    }

    if (mapType === 'theater-round') {
      canvas.appendChild(svgNode('ellipse', { cx: 500, cy: 345, rx: 145, ry: 82, class: 'map-stage' }));
      const label = svgNode('text', { x: 500, y: 352, class: 'map-stage-label' });
      label.textContent = config.venue.stageLabel;
      canvas.appendChild(label);
      return;
    }

    const reverse = mapType === 'theater-reverse';
    const stageWidth = mapType === 'theater' ? 610 : 530;
    const stageX = (1000 - stageWidth) / 2;
    const top = reverse ? 640 : 65;
    canvas.appendChild(svgNode('path', {
      d: reverse
        ? `M ${stageX + 35} ${top - 70} Q 500 ${top - 105} ${stageX + stageWidth - 35} ${top - 70} L ${stageX + stageWidth} ${top} Q 500 ${top + 35} ${stageX} ${top} Z`
        : `M ${stageX} ${top} Q 500 ${top - 40} ${stageX + stageWidth} ${top} L ${stageX + stageWidth - 35} ${top + 70} Q 500 ${top + 105} ${stageX + 35} ${top + 70} Z`,
      class: 'map-stage',
    }));
    const label = svgNode('text', { x: 500, y: reverse ? top - 37 : top + 40, class: 'map-stage-label' });
    label.textContent = config.venue.stageLabel;
    canvas.appendChild(label);
  }

  function createSeats(section, group, box, sectionIndex) {
    const [x, y, width, height] = box;
    const category = config.categories[section.category];
    const seatRadius = 7;
    const seatPitch = 23;
    const labelSpace = 30;
    const columns = Math.max(3, Math.min(9, Math.floor((width - 28) / seatPitch) + 1));
    const rows = Math.max(2, Math.min(5, Math.floor((height - labelSpace - 18) / seatPitch) + 1));
    const gridWidth = (columns - 1) * seatPitch;
    const gridHeight = (rows - 1) * seatPitch;
    const startX = x + (width - gridWidth) / 2;
    const startY = y + labelSpace + Math.max(8, (height - labelSpace - gridHeight) / 2);

    for (let rowIndex = 0; rowIndex < rows; rowIndex += 1) {
      for (let columnIndex = 0; columnIndex < columns; columnIndex += 1) {
        const seatNumber = columnIndex + 1;
        const row = String.fromCharCode(65 + rowIndex);
        const id = `${section.id}-${row}-${seatNumber}`;
        const random = deterministicNumber(`${config.event.key}-${id}`);
        const unavailable = random % 100 < 28 || unavailableSeatIds.has(id);
        const cx = startX + seatPitch * columnIndex;
        const cy = startY + seatPitch * rowIndex;
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
          cx, cy, r: seatRadius, fill: category.color,
          class: `map-seat${unavailable ? ' is-unavailable' : ''}`,
          tabindex: unavailable ? '-1' : '0',
          role: 'button',
          'aria-pressed': 'false',
          'aria-label': `${section.label}, row ${row}, seat ${seatNumber}, ${category.label}${unavailable ? ', unavailable' : ''}`,
          'data-seat-id': id,
        });
        group.appendChild(circle);
        group.appendChild(svgNode('path', {
          d: `M ${cx - 4.2} ${cy - .2} L ${cx - 1} ${cy + 3.3} L ${cx + 4.8} ${cy - 3.8}`,
          class: 'map-seat-check',
          'data-seat-check': id,
        }));
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
    state.seats.forEach(seat => {
      const selected = selectedIds.has(seat.id);
      const element = seatElement(seat.id);
      element?.classList.toggle('is-selected', selected);
      element?.setAttribute('aria-pressed', String(selected));
      canvas.querySelector(`[data-seat-check="${CSS.escape(seat.id)}"]`)?.classList.toggle('is-visible', selected);
    });
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
    const price = seat.unavailable ? 'Unavailable' : `PHP ${seat.price.toLocaleString()}`;
    const fee = Math.max(50, Math.round(seat.price * .029 / 5) * 5);
    tooltip.style.setProperty('--tooltip-color', seat.unavailable ? '#c7c7cb' : seat.color);
    tooltip.innerHTML = `
      <div class="ticket-seat-tooltip-grid">
        <span><small>Section</small><strong>${escapeHtml(seat.section)}</strong></span>
        <span><small>Row</small><strong>${escapeHtml(seat.row)}</strong></span>
        <span><small>Seat</small><strong>${escapeHtml(seat.number)}</strong></span>
        <span class="ticket-seat-tooltip-price">
          <small>Price</small>
          <strong>${escapeHtml(price)}</strong>
          ${seat.unavailable ? '' : `<em>(incl. PHP ${fee.toLocaleString()} fee)</em>`}
        </span>
      </div>
      <div class="ticket-seat-tooltip-detail">
        <strong>${escapeHtml(seat.section)} (${escapeHtml(seat.category)} Section)</strong>
        <span>${seat.unavailable ? 'This seat is no longer available' : 'Adult'}</span>
      </div>`;
    tooltip.hidden = false;
    const bounds = viewport.getBoundingClientRect();
    const tooltipWidth = Math.min(400, bounds.width - 24);
    const left = Math.min(bounds.width - tooltipWidth - 12, Math.max(12, clientX - bounds.left + 18));
    const top = Math.min(bounds.height - 205, Math.max(12, clientY - bounds.top + 18));
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
      if (result.expired && result.redirect) {
        window.location.replace(result.redirect);
        return;
      }
      if (!response.ok || !result.success) throw new Error(result.message || 'Unable to save seats.');
      window.location.href = result.redirect;
    } catch (error) {
      showStatus(error.message || 'Unable to continue. Please try again.');
      continueButton.disabled = false;
      continueButton.textContent = 'Continue with selected seats';
    }
  });

  renderMap();
})();
