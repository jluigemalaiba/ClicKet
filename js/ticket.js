(() => {
  'use strict';

  const configNode = document.getElementById('ticketConfig');
  if (!configNode) return;

  const config = JSON.parse(configNode.textContent);
  const svg = document.getElementById('seatMap');
  const canvas = document.getElementById('mapCanvas');
  const viewport = document.getElementById('mapViewport');
  const mapHint = document.getElementById('mapHint');
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
  const isDetailedBowl = ['cuneta', 'moa-sports', 'moa-concert'].includes(config.venue.mapKey);
  const detailedBowlTiers = {
    cuneta: {
      floor: { innerRx: 980, innerRy: 625, outerRx: 1235, outerRy: 815, columns: 16 },
      lower: { innerRx: 1300, innerRy: 865, outerRx: 1570, outerRy: 1065, columns: 18 },
      upper: { innerRx: 1650, innerRy: 1120, outerRx: 2025, outerRy: 1395, columns: 20 },
      general: { innerRx: 2110, innerRy: 1470, outerRx: 2325, outerRy: 1650, columns: 18 },
    },
    'moa-sports': {
      lower: { innerRx: 950, innerRy: 620, outerRx: 1370, outerRy: 900, columns: 18 },
      suite: { innerRx: 1460, innerRy: 970, outerRx: 1640, outerRy: 1090, columns: 12 },
      club: { innerRx: 1730, innerRy: 1160, outerRx: 1950, outerRy: 1315, columns: 16 },
      upper: { innerRx: 2050, innerRy: 1400, outerRx: 2380, outerRy: 1665, columns: 16 },
    },
    'moa-concert': {
      standing: { columns: 18 },
      patron: { columns: 16 },
      lower: { innerRx: 1120, innerRy: 720, outerRx: 1490, outerRy: 1000, columns: 18 },
      upper: { innerRx: 1580, innerRy: 1080, outerRx: 1940, outerRy: 1340, columns: 18 },
      general: { innerRx: 2040, innerRy: 1420, outerRx: 2350, outerRy: 1660, columns: 18 },
    },
  };
  const bowlTiers = detailedBowlTiers[config.venue.mapKey] || null;
  const mapDimensions = isDetailedBowl
    ? { width: 5000, height: 3600, centerX: 2500, centerY: 1800, maxZoom: 10 }
    : { width: 1000, height: 720, centerX: 500, centerY: 360, maxZoom: 4 };
  svg.setAttribute('viewBox', `0 0 ${mapDimensions.width} ${mapDimensions.height}`);

  const state = {
    seats: [],
    seatById: new Map(),
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
    sectionBounds: new Map(),
    sectionCenters: new Map(),
    activeSectionId: null,
    dragMoved: false,
    pointerDownSectionId: null,
    pointerCaptured: false,
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
      rear: [355, 575, 290, 105],
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

  function rowName(index) {
    let value = index + 1;
    let label = '';
    while (value > 0) {
      value -= 1;
      label = String.fromCharCode(65 + (value % 26)) + label;
      value = Math.floor(value / 26);
    }
    return label;
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

  function ellipsePoint(cx, cy, rx, ry, degrees) {
    const radians = degrees * Math.PI / 180;
    return [cx + Math.cos(radians) * rx, cy + Math.sin(radians) * ry];
  }

  function ringSegmentPath(cx, cy, innerRx, innerRy, outerRx, outerRy, startAngle, endAngle) {
    const steps = 6;
    const outer = [];
    const inner = [];

    for (let step = 0; step <= steps; step += 1) {
      const angle = startAngle + ((endAngle - startAngle) * step / steps);
      outer.push(ellipsePoint(cx, cy, outerRx, outerRy, angle));
      inner.unshift(ellipsePoint(cx, cy, innerRx, innerRy, angle));
    }

    return [...outer, ...inner]
      .map(([x, y], index) => `${index === 0 ? 'M' : 'L'} ${x.toFixed(2)} ${y.toFixed(2)}`)
      .join(' ') + ' Z';
  }

  function appendInteractiveSeat(group, section, sectionIndex, rowIndex, columnIndex, cx, cy, includeCheck = true) {
    const category = config.categories[section.category];
    const row = rowName(rowIndex);
    const seatNumber = columnIndex + 1;
    const id = `${section.id}-${row}-${seatNumber}`;
    const random = deterministicNumber(`${config.event.key}-${id}`);
    const unavailable = random % 100 < 28 || unavailableSeatIds.has(id);
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
      rank: category.rank * 100000 + sectionIndex * 1000 + rowIndex * 100 + columnIndex,
      rowIndex,
      columnIndex,
    };
    state.seats.push(seat);
    state.seatById.set(id, seat);

    const circle = svgNode('circle', {
      cx, cy, r: 6.4, fill: category.color,
      class: `map-seat${unavailable ? ' is-unavailable' : ''}`,
      tabindex: unavailable ? '-1' : '0',
      role: 'button',
      'aria-pressed': 'false',
      'aria-label': `${section.label}, row ${row}, seat ${seatNumber}, ${category.label}${unavailable ? ', unavailable' : ''}`,
      'data-seat-id': id,
    });
    group.appendChild(circle);
    if (includeCheck) group.appendChild(createSeatCheck(circle, id));
  }

  function createSeatCheck(circle, id) {
    const cx = Number(circle.getAttribute('cx'));
    const cy = Number(circle.getAttribute('cy'));
    return svgNode('path', {
      d: `M ${cx - 4.2} ${cy - .2} L ${cx - 1} ${cy + 3.3} L ${cx + 4.8} ${cy - 3.8}`,
      class: 'map-seat-check',
      'data-seat-check': id,
    });
  }

  function bowlSectionGap(tierName) {
    if (config.venue.mapKey === 'moa-concert') {
      return tierName === 'general' ? 5.5 : 4.2;
    }
    if (config.venue.mapKey === 'moa-sports') {
      return tierName === 'upper' ? 2.6 : 4.4;
    }
    return tierName === 'general' ? 3.4 : 4.2;
  }

  function moaConcertFloorBox(section) {
    const tierSections = config.venue.sections.filter(item => item.tier === section.tier);
    const index = tierSections.findIndex(item => item.id === section.id);
    if (section.tier === 'standing') {
      return index === 0
        ? [1810, 660, 560, 720]
        : [2630, 660, 560, 720];
    }
    const boxes = [
      [1770, 1510, 335, 620],
      [2120, 1510, 335, 620],
      [2545, 1510, 335, 620],
      [2895, 1510, 335, 620],
    ];
    return boxes[index];
  }

  function bowlSectionAngles(section, tierSections, sectionIndex) {
    if (config.venue.mapKey === 'moa-concert') {
      const span = 170;
      const angleStep = span / tierSections.length;
      const gap = bowlSectionGap(section.tier);
      return [
        5 + sectionIndex * angleStep + gap / 2,
        5 + (sectionIndex + 1) * angleStep - gap / 2,
      ];
    }
    const angleStep = 360 / tierSections.length;
    const gap = bowlSectionGap(section.tier);
    return [
      -90 + sectionIndex * angleStep + gap / 2,
      -90 + (sectionIndex + 1) * angleStep - gap / 2,
    ];
  }

  function renderCunetaBowl() {
    const cx = mapDimensions.centerX;
    const cy = mapDimensions.centerY;
    const tiers = bowlTiers;
    const isMoa = config.venue.mapKey === 'moa-sports';
    const isMoaConcert = config.venue.mapKey === 'moa-concert';

    if (isMoaConcert) {
      renderDetailedBowlSections();
      renderMoaConcertStage();
      return;
    }

    const walkways = isMoa
      ? [[1415, 930], [1685, 1120], [1995, 1350]]
      : [[1265, 840], [1610, 1092], [2068, 1432]];
    walkways.forEach(([rx, ry]) => {
      canvas.appendChild(svgNode('ellipse', { cx, cy, rx, ry, class: 'cuneta-walkway' }));
    });

    const court = svgNode('rect', {
      x: isMoa ? 1760 : 1740,
      y: isMoa ? 1390 : 1325,
      width: isMoa ? 1480 : 1520,
      height: isMoa ? 820 : 950,
      rx: isMoa ? 8 : 24,
      class: `map-court cuneta-court${isMoa ? ' moa-sports-court' : ''}`,
    });
    canvas.appendChild(court);
    const courtTop = isMoa ? 1390 : 1325;
    const courtBottom = isMoa ? 2210 : 2275;
    canvas.appendChild(svgNode('line', { x1: cx, y1: courtTop, x2: cx, y2: courtBottom, class: 'map-court-line cuneta-court-line' }));
    canvas.appendChild(svgNode('circle', { cx, cy, r: isMoa ? 125 : 175, class: 'map-court-line cuneta-court-line' }));
    canvas.appendChild(svgNode('rect', { x: isMoa ? 1760 : 1740, y: 1580, width: isMoa ? 300 : 330, height: isMoa ? 440 : 460, class: 'map-court-line cuneta-court-line' }));
    canvas.appendChild(svgNode('rect', { x: isMoa ? 2940 : 2930, y: 1580, width: isMoa ? 300 : 330, height: isMoa ? 440 : 460, class: 'map-court-line cuneta-court-line' }));
    canvas.appendChild(svgNode('circle', { cx: isMoa ? 1980 : 1980, cy, r: isMoa ? 105 : 125, class: 'map-court-line cuneta-court-line' }));
    canvas.appendChild(svgNode('circle', { cx: isMoa ? 3020 : 3020, cy, r: isMoa ? 105 : 125, class: 'map-court-line cuneta-court-line' }));
    const courtLabel = svgNode('text', {
      x: cx,
      y: cy + 28,
      class: `cuneta-court-label${isMoa ? ' moa-court-label' : ''}`,
    });
    courtLabel.textContent = config.venue.stageLabel;
    canvas.appendChild(courtLabel);
    if (isMoa) {
      const visitor = svgNode('text', { x: 1985, y: 2260, class: 'moa-court-side-label' });
      visitor.textContent = 'VISITOR';
      canvas.appendChild(visitor);
      const home = svgNode('text', { x: 3015, y: 2260, class: 'moa-court-side-label' });
      home.textContent = 'HOME';
      canvas.appendChild(home);
    }

    const gates = isMoa
      ? [
          { x: 2150, y: 42, w: 700, h: 105, label: 'UPPER LEVEL' },
          { x: 2150, y: 3453, w: 700, h: 105, label: 'UPPER LEVEL' },
        ]
      : [
          { x: 2075, y: 18, w: 850, h: 128, label: 'ENTRY GATES' },
          { x: 2075, y: 3454, w: 850, h: 128, label: 'ENTRY GATE' },
          { x: 18, y: 1475, w: 128, h: 650, label: 'ENTRY GATES', rotate: -90 },
          { x: 4854, y: 1475, w: 128, h: 650, label: 'ENTRY GATES', rotate: 90 },
        ];
    gates.forEach(gate => {
      const group = svgNode('g', { class: 'cuneta-gate' });
      group.appendChild(svgNode('rect', { x: gate.x, y: gate.y, width: gate.w, height: gate.h, rx: 12 }));
      const labelX = gate.x + gate.w / 2;
      const labelY = gate.y + gate.h / 2 + 3;
      const label = svgNode('text', {
        x: labelX,
        y: labelY,
        transform: gate.rotate ? `rotate(${gate.rotate} ${labelX} ${labelY})` : '',
      });
      label.textContent = gate.label;
      group.appendChild(label);
      canvas.appendChild(group);
    });

    renderDetailedBowlSections();
  }

  function renderMoaConcertStage() {
    const stage = svgNode('rect', {
      x: 1940,
      y: 185,
      width: 1120,
      height: 410,
      rx: 36,
      class: 'moa-concert-stage',
    });
    canvas.appendChild(stage);
    const stageLabel = svgNode('text', { x: 2500, y: 420, class: 'moa-concert-stage-label' });
    stageLabel.textContent = config.venue.stageLabel;
    canvas.appendChild(stageLabel);
    canvas.appendChild(svgNode('path', {
      d: 'M 2260 595 L 2260 1110 L 2740 1110 L 2740 595 L 2910 595 L 2910 1375 L 2090 1375 L 2090 595 Z',
      class: 'moa-concert-catwalk',
    }));
    const booth = svgNode('rect', { x: 2250, y: 2180, width: 500, height: 125, rx: 14, class: 'moa-tech-booth' });
    canvas.appendChild(booth);
    const boothLabel = svgNode('text', { x: 2500, y: 2260, class: 'moa-tech-booth-label' });
    boothLabel.textContent = 'TECH BOOTH';
    canvas.appendChild(boothLabel);
    const left = svgNode('text', { x: 2090, y: 1030, class: 'moa-standing-label' });
    left.textContent = 'LEFT STANDING';
    canvas.appendChild(left);
    const right = svgNode('text', { x: 2910, y: 1030, class: 'moa-standing-label' });
    right.textContent = 'RIGHT STANDING';
    canvas.appendChild(right);
    const pacificDrive = svgNode('text', {
      x: 125,
      y: 1880,
      transform: 'rotate(-90 125 1880)',
      class: 'moa-venue-side-label',
    });
    pacificDrive.textContent = 'PACIFIC DRIVE';
    canvas.appendChild(pacificDrive);
    const dioknoBoulevard = svgNode('text', {
      x: 4875,
      y: 1880,
      transform: 'rotate(90 4875 1880)',
      class: 'moa-venue-side-label',
    });
    dioknoBoulevard.textContent = 'DIOKNO BOULEVARD';
    canvas.appendChild(dioknoBoulevard);
  }

  function renderDetailedBowlSections() {
    const cx = mapDimensions.centerX;
    const cy = mapDimensions.centerY;
    const tiers = bowlTiers;

    Object.entries(tiers).forEach(([tierName, tier]) => {
      const sections = config.venue.sections.filter(section => section.tier === tierName);

      sections.forEach((section, sectionIndex) => {
        const globalIndex = config.venue.sections.findIndex(item => item.id === section.id);
        const category = config.categories[section.category];
        const sectionColor = section.mapColor || category.color;
        const group = svgNode('g', {
          class: 'map-section cuneta-section',
          'data-section-id': section.id,
          'data-category': section.category,
          style: `--section-color:${sectionColor}`,
          tabindex: '0',
          role: 'button',
          'aria-label': `${section.label}, ${Number(section.capacity).toLocaleString()} seats. Select section to view seats.`,
        });
        let labelX;
        let labelY;
        let width;
        let height;

        if (config.venue.mapKey === 'moa-concert' && ['standing', 'patron'].includes(tierName)) {
          const [x, y, boxWidth, boxHeight] = moaConcertFloorBox(section);
          group.appendChild(svgNode('rect', {
            x, y, width: boxWidth, height: boxHeight, rx: 24,
            class: 'cuneta-section-wedge moa-concert-floor-section',
          }));
          labelX = x + boxWidth / 2;
          labelY = y + boxHeight / 2;
          width = boxWidth;
          height = boxHeight;
        } else {
          const [startAngle, endAngle] = bowlSectionAngles(section, sections, sectionIndex);
          const middleAngle = (startAngle + endAngle) / 2;
          group.appendChild(svgNode('path', {
            d: ringSegmentPath(cx, cy, tier.innerRx, tier.innerRy, tier.outerRx, tier.outerRy, startAngle, endAngle),
            class: 'cuneta-section-wedge',
          }));
          const labelRx = (tier.innerRx + tier.outerRx) / 2;
          const labelRy = (tier.innerRy + tier.outerRy) / 2;
          [labelX, labelY] = ellipsePoint(cx, cy, labelRx, labelRy, middleAngle);
          width = Math.max(430, (tier.outerRx - tier.innerRx) * 2.4);
          height = Math.max(360, (tier.outerRy - tier.innerRy) * 2.4);
        }

        const number = svgNode('text', { x: labelX, y: labelY + 12, class: 'cuneta-section-number' });
        number.textContent = section.number;
        group.appendChild(number);
        canvas.appendChild(group);

        state.sectionBounds.set(section.id, [labelX - width / 2, labelY - height / 2, width, height]);
        state.sectionCenters.set(section.id, [labelX, labelY]);
        createCunetaSeatData(section, globalIndex);
      });
    });
  }

  function createCunetaSeatData(section, sectionIndex) {
    const category = config.categories[section.category];
    const columns = bowlTiers[section.tier].columns;
    const rows = Math.ceil(Number(section.capacity) / columns);

    for (let rowIndex = 0; rowIndex < rows; rowIndex += 1) {
      for (let columnIndex = 0; columnIndex < columns; columnIndex += 1) {
        const seatIndex = rowIndex * columns + columnIndex;
        if (seatIndex >= Number(section.capacity)) break;
        const row = rowName(rowIndex);
        const seatNumber = columnIndex + 1;
        const id = `${section.id}-${row}-${seatNumber}`;
        const random = deterministicNumber(`${config.event.key}-${id}`);
        const unavailable = random % 100 < 28 || unavailableSeatIds.has(id);
        const seat = {
          id,
          sectionId: section.id,
          section: section.label,
          row,
          number: String(seatNumber),
          category: category.label,
          categoryKey: section.category,
          color: section.mapColor || category.color,
          price: category.price,
          unavailable,
          rank: category.rank * 100000 + sectionIndex * 1000 + rowIndex * 100 + Math.abs(columnIndex - (columns - 1) / 2),
          rowIndex,
          columnIndex,
        };
        state.seats.push(seat);
        state.seatById.set(id, seat);
      }
    }
  }

  function renderCunetaAllSeats(sectionId) {
    canvas.querySelectorAll('.cuneta-seat-layer').forEach(layer => layer.remove());
    config.venue.sections.forEach(section => {
      const tier = bowlTiers[section.tier];
      const tierSections = config.venue.sections.filter(item => item.tier === section.tier);
      const sectionIndex = tierSections.findIndex(item => item.id === section.id);
      const seats = state.seats.filter(seat => seat.sectionId === section.id);
      const rows = Math.max(...seats.map(seat => seat.rowIndex)) + 1;
      const columns = tier.columns;
      const layer = svgNode('g', {
        class: `cuneta-seat-layer${section.id === sectionId ? ' is-focus-section' : ''}`,
        'data-seat-layer-for': section.id,
        style: `--section-color:${section.mapColor || config.categories[section.category].color}`,
      });

      seats.forEach(seat => {
        let seatX;
        let seatY;
        if (config.venue.mapKey === 'moa-concert' && ['standing', 'patron'].includes(section.tier)) {
          const [x, y, width, height] = moaConcertFloorBox(section);
          seatX = x + width * ((seat.columnIndex + 1) / (columns + 1));
          seatY = y + height * ((seat.rowIndex + 1) / (rows + 1));
        } else {
          const [startAngle, endAngle] = bowlSectionAngles(section, tierSections, sectionIndex);
          const radiusProgress = (seat.rowIndex + 1) / (rows + 1);
          const rx = tier.innerRx + (tier.outerRx - tier.innerRx) * radiusProgress;
          const ry = tier.innerRy + (tier.outerRy - tier.innerRy) * radiusProgress;
          const seatProgress = (seat.columnIndex + 1) / (columns + 1);
          const angle = startAngle + (endAngle - startAngle) * seatProgress;
          [seatX, seatY] = ellipsePoint(mapDimensions.centerX, mapDimensions.centerY, rx, ry, angle);
        }
        layer.appendChild(svgNode('circle', {
          cx: seatX,
          cy: seatY,
          r: section.id === sectionId ? 4.8 : 3.8,
          fill: seat.color,
          class: `map-seat${seat.unavailable ? ' is-unavailable' : ''}`,
          tabindex: seat.unavailable ? '-1' : '0',
          role: 'button',
          'aria-pressed': 'false',
          'aria-label': `${seat.section}, row ${seat.row}, seat ${seat.number}, ${seat.category}${seat.unavailable ? ', unavailable' : ''}`,
          'data-seat-id': seat.id,
        }));
      });
      canvas.appendChild(layer);
    });

    canvas.classList.add('is-cuneta-seat-view');
    if (mapHint) mapHint.textContent = 'All venue seats are visible. The selected section is centered and highlighted.';
    state.activeSectionId = sectionId;
    renderSelection();
  }

  function createSeats(section, group, box, sectionIndex) {
    const [x, y, width, height] = box;
    const category = config.categories[section.category];
    const seatRadius = 7;
    const seatPitch = 27;
    const columns = Math.max(3, Math.min(13, Math.floor((width - 18) / seatPitch) + 1));
    const rows = Math.max(2, Math.min(8, Math.floor((height - 12) / seatPitch) + 1));
    const baseGridWidth = (columns - 1) * seatPitch;
    const gridHeight = (rows - 1) * seatPitch;
    const centerX = x + width / 2;
    const startY = y + Math.max(6, (height - gridHeight) / 2);
    const isFloorBlock = /^floor-/i.test(section.zone);
    const isLeftWing = !isFloorBlock && /left|west/i.test(section.zone);
    const isRightWing = !isFloorBlock && /right|east/i.test(section.zone);
    const isCenter = /center|rear/i.test(section.zone);
    const reverseFan = config.venue.mapType === 'theater-reverse';

    for (let rowIndex = 0; rowIndex < rows; rowIndex += 1) {
      const rowProgress = rows === 1 ? 0 : rowIndex / (rows - 1);
      const fanProgress = reverseFan ? 1 - rowProgress : rowProgress;
      const rowScale = isCenter ? .76 + fanProgress * .24 : .9 + fanProgress * .1;
      const rowWidth = baseGridWidth * rowScale;
      const rowStartX = centerX - rowWidth / 2;
      const wingShift = isLeftWing
        ? (rowIndex - (rows - 1) / 2) * 4.4
        : isRightWing
          ? ((rows - 1) / 2 - rowIndex) * 4.4
          : 0;
      const row = String.fromCharCode(65 + rowIndex);
      const rowY = startY + seatPitch * rowIndex;
      let firstSeatX = 0;
      let lastSeatX = 0;

      for (let columnIndex = 0; columnIndex < columns; columnIndex += 1) {
        const seatNumber = columnIndex + 1;
        const id = `${section.id}-${row}-${seatNumber}`;
        const random = deterministicNumber(`${config.event.key}-${id}`);
        const unavailable = random % 100 < 28 || unavailableSeatIds.has(id);
        const columnProgress = columns === 1 ? 0 : columnIndex / (columns - 1);
        const cx = rowStartX + rowWidth * columnProgress + wingShift;
        const curveDepth = isCenter ? Math.abs(columnIndex - (columns - 1) / 2) * 1.15 : 0;
        const wingSlope = isLeftWing
          ? columnIndex * 1.8
          : isRightWing
            ? (columns - 1 - columnIndex) * 1.8
            : 0;
        const cy = rowY + curveDepth + wingSlope;
        if (columnIndex === 0) firstSeatX = cx;
        if (columnIndex === columns - 1) lastSeatX = cx;
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

      const leftRowLabel = svgNode('text', {
        x: firstSeatX - 14,
        y: rowY + 3,
        class: 'map-row-label',
        'text-anchor': 'end',
      });
      leftRowLabel.textContent = row;
      group.appendChild(leftRowLabel);

      const rightRowLabel = svgNode('text', {
        x: lastSeatX + 14,
        y: rowY + 3,
        class: 'map-row-label',
        'text-anchor': 'start',
      });
      rightRowLabel.textContent = row;
      group.appendChild(rightRowLabel);
    }
  }

  function renderMap() {
    canvas.replaceChildren();
    state.seats = [];
    state.seatById.clear();
    state.sectionBounds.clear();
    state.sectionCenters.clear();
    state.activeSectionId = null;

    if (isDetailedBowl) {
      renderCunetaBowl();
      restoreSelection();
      updateAvailability();
      applyCategoryFilter();
      return;
    }

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
      createSeats(section, group, box, index);
      const label = svgNode('text', { x: x + width / 2, y: y - 8, class: 'map-section-label' });
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
      let count = availableByCategory(key);
      if (config.venue.capacity && isDetailedBowl) {
        const categorySections = config.venue.sections.filter(section => section.category === key);
        const categoryCapacity = categorySections.reduce((sum, section) => sum + Number(section.capacity || 0), 0);
        const renderedSeats = state.seats.filter(seat => seat.categoryKey === key);
        const availableSeats = renderedSeats.filter(seat => !seat.unavailable);
        count = renderedSeats.length
          ? Math.round(categoryCapacity * (availableSeats.length / renderedSeats.length))
          : 0;
      }
      total += count;
      const node = document.querySelector(`[data-availability-for="${key}"]`);
      if (node) node.textContent = count;
      const button = document.querySelector(`[data-category="${key}"]`);
      if (button) button.hidden = count === 0;
    });
    const totalNode = document.getElementById('totalAvailability');
    totalNode.textContent = config.venue.capacity
      ? `${total.toLocaleString()} available of ${Number(config.venue.capacity).toLocaleString()} seats`
      : `${total} seats available`;
  }

  function applyTransform() {
    canvas.setAttribute('transform', `translate(${state.x} ${state.y}) scale(${state.scale})`);
  }

  function setZoom(nextScale, originX = mapDimensions.centerX, originY = mapDimensions.centerY) {
    const clamped = Math.max(.75, Math.min(mapDimensions.maxZoom, nextScale));
    const ratio = clamped / state.scale;
    state.x = originX - (originX - state.x) * ratio;
    state.y = originY - (originY - state.y) * ratio;
    state.scale = clamped;
    applyTransform();
    if (isDetailedBowl) {
      if (state.scale < 1.65) {
        canvas.querySelectorAll('.cuneta-seat-layer').forEach(layer => layer.remove());
        canvas.classList.remove('is-cuneta-seat-view');
        if (mapHint) {
          const section = config.venue.sections.find(item => item.id === state.activeSectionId);
          mapHint.textContent = section
            ? `${section.label} highlighted. Continue zooming to view all venue seats.`
            : 'Select a section to highlight it. Use + or scroll to zoom.';
        }
      } else if (!state.activeSectionId) {
        activateNearestCunetaSection();
      } else if (!canvas.classList.contains('is-cuneta-seat-view')) {
        renderCunetaAllSeats(state.activeSectionId);
      }
    }
  }

  function resetMap() {
    state.scale = 1;
    state.x = 0;
    state.y = 0;
    hideTooltip();
    applyTransform();
    if (isDetailedBowl) {
      state.activeSectionId = null;
      canvas.querySelectorAll('.cuneta-seat-layer').forEach(layer => layer.remove());
      canvas.querySelectorAll('.cuneta-section').forEach(group => group.classList.remove('is-active-section'));
      canvas.classList.remove('is-cuneta-seat-view');
      if (mapHint) mapHint.textContent = 'Select a section to view seats. Drag, scroll, or pinch to navigate.';
    }
  }

  function activateNearestCunetaSection() {
    const viewportCenterX = (mapDimensions.centerX - state.x) / state.scale;
    const viewportCenterY = (mapDimensions.centerY - state.y) / state.scale;
    let nearestId = null;
    let nearestDistance = Infinity;
    state.sectionCenters.forEach(([sectionX, sectionY], sectionId) => {
      const distance = Math.hypot(sectionX - viewportCenterX, sectionY - viewportCenterY);
      if (distance < nearestDistance) {
        nearestDistance = distance;
        nearestId = sectionId;
      }
    });
    if (nearestId) {
      state.activeSectionId = nearestId;
      renderCunetaAllSeats(nearestId);
      showStatus('Seats are shown for the section nearest the center of the map.');
    }
  }

  function focusSection(sectionId) {
    const section = config.venue.sections.find(item => item.id === sectionId);
    if (!section) return;
    const [x, y, width, height] = state.sectionBounds.get(sectionId) || sectionPosition(section, 0);
    const targetScale = isDetailedBowl
      ? Math.min(5.6, Math.max(3.25, 2100 / Math.max(width, height)))
      : Math.min(3, Math.max(1.65, 520 / Math.max(width, height)));
    state.scale = targetScale;
    state.x = mapDimensions.centerX - (x + width / 2) * targetScale;
    state.y = mapDimensions.centerY - (y + height / 2) * targetScale;
    applyTransform();
    if (isDetailedBowl) renderCunetaAllSeats(sectionId);
  }

  function selectCunetaSection(sectionId) {
    state.activeSectionId = sectionId;
    canvas.querySelectorAll('.cuneta-section').forEach(group => {
      group.classList.toggle('is-active-section', group.dataset.sectionId === sectionId);
    });
    const section = config.venue.sections.find(item => item.id === sectionId);
    if (mapHint) mapHint.textContent = `${section?.label || 'Section'} highlighted. Use + or scroll to zoom into its seats.`;
    showStatus(`${section?.label || 'Section'} highlighted. Zoom in when you are ready.`);
  }

  function setCategory(category) {
    state.category = category;
    if (isDetailedBowl && canvas.classList.contains('is-cuneta-seat-view')) {
      resetMap();
    }
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

    state.activeSectionId = null;
    canvas.querySelectorAll('.cuneta-section').forEach(section => section.classList.remove('is-active-section'));
    if (mapHint && isDetailedBowl) {
      mapHint.textContent = state.category === 'all'
        ? 'Select a section to highlight it. Use + or scroll to zoom.'
        : 'Matching sections are highlighted. Select one section, then use + or scroll to zoom.';
    }
  }

  function seatElement(id) {
    return canvas.querySelector(`[data-seat-id="${CSS.escape(id)}"]`);
  }

  function toggleSeat(id) {
    const seat = state.seatById.get(id);
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
    canvas.querySelectorAll('.map-seat.is-selected').forEach(element => {
      element.classList.remove('is-selected');
      element.setAttribute('aria-pressed', 'false');
    });
    canvas.querySelectorAll('.map-seat-check.is-visible').forEach(check => check.classList.remove('is-visible'));
    selectedIds.forEach(id => {
      const element = seatElement(id);
      if (!element) return;
      element.classList.add('is-selected');
      element.setAttribute('aria-pressed', 'true');
      let check = canvas.querySelector(`[data-seat-check="${CSS.escape(id)}"]`);
      if (!check) {
        check = createSeatCheck(element, id);
        element.parentNode.appendChild(check);
      }
      check.classList.add('is-visible');
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

  function showTooltip(seat, seatNode) {
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
    const seatBounds = seatNode.getBoundingClientRect();
    const tooltipWidth = Math.min(400, bounds.width - 24);
    const anchorX = seatBounds.left + seatBounds.width / 2 - bounds.left;
    const left = Math.min(bounds.width - tooltipWidth - 12, Math.max(12, anchorX - tooltipWidth / 2));
    const top = seatBounds.bottom - bounds.top + 14;
    tooltip.style.setProperty('--tooltip-arrow-left', `${Math.max(18, Math.min(tooltipWidth - 18, anchorX - left))}px`);
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
    button.addEventListener('pointerdown', event => {
      event.stopPropagation();
    });
    button.addEventListener('click', event => {
      event.preventDefault();
      event.stopPropagation();
      if (button.dataset.mapAction === 'zoom-in') {
        if (isDetailedBowl && state.activeSectionId && !canvas.classList.contains('is-cuneta-seat-view')) {
          focusSection(state.activeSectionId);
        } else {
          setZoom(state.scale * 1.4);
        }
      }
      if (button.dataset.mapAction === 'zoom-out') setZoom(state.scale / 1.4);
      if (button.dataset.mapAction === 'reset') resetMap();
    });
  });

  viewport.addEventListener('wheel', event => {
    event.preventDefault();
    const bounds = svg.getBoundingClientRect();
    const originX = ((event.clientX - bounds.left) / bounds.width) * mapDimensions.width;
    const originY = ((event.clientY - bounds.top) / bounds.height) * mapDimensions.height;
    setZoom(state.scale * (event.deltaY < 0 ? 1.12 : .89), originX, originY);
  }, { passive: false });

  viewport.addEventListener('pointerdown', event => {
    if (event.target.closest('.ticket-map-controls') || event.target.closest('.map-seat')) return;
    activePointers.set(event.pointerId, { x: event.clientX, y: event.clientY });
    state.dragging = true;
    state.pointerX = event.clientX;
    state.pointerY = event.clientY;
    state.dragMoved = false;
    state.pointerCaptured = false;
    state.pointerDownSectionId = event.target.closest('[data-section-id]')?.dataset.sectionId || null;
    viewport.classList.add('is-dragging');
  });

  viewport.addEventListener('pointermove', event => {
    const seatNode = event.target.closest?.('[data-seat-id]');
    if (seatNode) {
      const seat = state.seatById.get(seatNode.dataset.seatId);
      if (seat) showTooltip(seat, seatNode);
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
        const midpointX = ((first.x + second.x) / 2 - bounds.left) / bounds.width * mapDimensions.width;
        const midpointY = ((first.y + second.y) / 2 - bounds.top) / bounds.height * mapDimensions.height;
        setZoom(state.scale * (distance / state.pinchDistance), midpointX, midpointY);
      }
      state.pinchDistance = distance;
      state.dragging = false;
      return;
    }

    state.pinchDistance = 0;
    if (!state.dragging) return;
    const bounds = svg.getBoundingClientRect();
    const moveX = event.clientX - state.pointerX;
    const moveY = event.clientY - state.pointerY;
    if (Math.hypot(moveX, moveY) > 3) {
      state.dragMoved = true;
      state.pointerDownSectionId = null;
      if (!state.pointerCaptured) {
        viewport.setPointerCapture(event.pointerId);
        state.pointerCaptured = true;
      }
    }
    state.x += (moveX / bounds.width) * mapDimensions.width;
    state.y += (moveY / bounds.height) * mapDimensions.height;
    state.pointerX = event.clientX;
    state.pointerY = event.clientY;
    applyTransform();
  });

  viewport.addEventListener('pointerup', event => {
    const sectionId = state.pointerDownSectionId;
    const shouldActivateSection = Boolean(sectionId && !state.dragMoved && isDetailedBowl);
    activePointers.delete(event.pointerId);
    state.pinchDistance = 0;
    state.dragging = false;
    state.pointerDownSectionId = null;
    viewport.classList.remove('is-dragging');
    if (viewport.hasPointerCapture(event.pointerId)) viewport.releasePointerCapture(event.pointerId);
    state.pointerCaptured = false;
    if (shouldActivateSection) {
      focusSection(sectionId);
      const section = config.venue.sections.find(item => item.id === sectionId);
      showStatus(`${section?.label || 'Section'} selected. All venue seats are now visible.`);
    }
  });
  viewport.addEventListener('pointercancel', event => {
    activePointers.delete(event.pointerId);
    state.pinchDistance = 0;
    state.dragging = false;
    state.pointerDownSectionId = null;
    state.pointerCaptured = false;
    viewport.classList.remove('is-dragging');
  });
  viewport.addEventListener('pointerleave', hideTooltip);

  canvas.addEventListener('click', event => {
    const seatNode = event.target.closest('[data-seat-id]');
    if (seatNode) {
      toggleSeat(seatNode.dataset.seatId);
      return;
    }
  });
  canvas.addEventListener('keydown', event => {
    const seatNode = event.target.closest('[data-seat-id]');
    if (seatNode && (event.key === 'Enter' || event.key === ' ')) {
      event.preventDefault();
      toggleSeat(seatNode.dataset.seatId);
      return;
    }
    const sectionNode = event.target.closest('[data-section-id]');
    if (sectionNode && (event.key === 'Enter' || event.key === ' ')) {
      event.preventDefault();
      focusSection(sectionNode.dataset.sectionId);
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
