/**
 * navbar.js — ClicKet Navbar Scripts
 * Handles: search toggle, profile avatar preview, bio counter,
 *          custom gender dropdown, custom birthday calendar, map picker,
 *          profile offcanvas, and toast notifications.
 */

(() => {
  'use strict';

  document.addEventListener('click', (event) => {
    const topLink = event.target.closest('[data-scroll-top]');
    if (!topLink) return;

    event.preventDefault();
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });

  /* ─── Search Toggle ────────────────────────────────────────────────────── */

  const searchForms = document.querySelectorAll('.nav-search-form');
  const searchDataNode = document.getElementById('navSearchEventData');
  let searchEvents = [];
  try {
    searchEvents = JSON.parse(searchDataNode?.textContent || '[]');
  } catch (error) {
    searchEvents = [];
  }

  function closeSearch(form) {
    const input = form.querySelector('input[type="search"]');
    const suggestions = form.querySelector('.nav-search-suggestions');
    form.classList.remove('is-open');
    suggestions.hidden = true;
    input?.setAttribute('aria-expanded', 'false');
    if (input) input.blur();
  }

  searchForms.forEach((form) => {
    const input  = form.querySelector('input[type="search"]');
    const button = form.querySelector('.nav-search-btn');
    const clearButton = form.querySelector('.nav-search-clear');
    const suggestions = form.querySelector('.nav-search-suggestions');
    const suggestionList = form.querySelector('.nav-search-suggestions-list');
    let activeIndex = -1;

    const suggestionLinks = () => Array.from(suggestionList.querySelectorAll('.nav-search-suggestion'));

    const setActiveSuggestion = (nextIndex) => {
      const links = suggestionLinks();
      if (!links.length) return;
      activeIndex = (nextIndex + links.length) % links.length;
      links.forEach((link, index) => {
        const active = index === activeIndex;
        link.classList.toggle('is-active', active);
        link.setAttribute('aria-selected', String(active));
      });
      links[activeIndex].scrollIntoView({ block: 'nearest' });
    };

    const closeSuggestions = () => {
      suggestions.hidden = true;
      input.setAttribute('aria-expanded', 'false');
      activeIndex = -1;
    };

    const renderSuggestions = () => {
      const query = input.value.trim().toLocaleLowerCase();
      clearButton.hidden = query === '';
      form.classList.toggle('has-value', query !== '');

      if (!query) {
        suggestionList.replaceChildren();
        closeSuggestions();
        return;
      }

      const matches = searchEvents.filter((event) => [
        event.title,
        event.performer,
        event.venue,
        event.category,
        event.type,
      ].some((value) => String(value || '').toLocaleLowerCase().includes(query)));

      suggestionList.replaceChildren();
      activeIndex = -1;

      if (!matches.length) {
        const empty = document.createElement('div');
        empty.className = 'nav-search-empty';
        empty.innerHTML = '<strong>No matching events</strong><span>Press Enter to search all events.</span>';
        suggestionList.appendChild(empty);
      } else {
        matches.forEach((event) => {
          const link = document.createElement('a');
          link.className = 'nav-search-suggestion';
          link.href = event.url;
          link.setAttribute('role', 'option');
          link.setAttribute('aria-selected', 'false');
          link.innerHTML = `
            <span class="nav-search-suggestion-poster" aria-hidden="true">${escapeSearchText(String(event.category || 'E').charAt(0))}</span>
            <span class="nav-search-suggestion-copy">
              <strong>${escapeSearchText(event.title)}</strong>
              <span>${escapeSearchText(event.date)} &middot; ${escapeSearchText(event.venue)}</span>
            </span>
            <span class="nav-search-suggestion-type">${escapeSearchText(event.category)}</span>`;
          suggestionList.appendChild(link);
        });
      }

      suggestions.hidden = false;
      input.setAttribute('aria-expanded', 'true');
    };

    button.addEventListener('click', (e) => {
      e.stopPropagation();
      if (!form.classList.contains('is-open')) {
        form.classList.add('is-open');
        input.focus();
        return;
      }
      if (input.value.trim()) {
        form.submit();
        return;
      }
      input.focus();
    });

    form.addEventListener('click', (e) => e.stopPropagation());
    input.addEventListener('focus', () => {
      form.classList.add('is-open');
      if (input.value.trim()) renderSuggestions();
    });
    input.addEventListener('input', renderSuggestions);
    input.addEventListener('keydown', (e) => {
      if (e.key === 'ArrowDown' && !suggestions.hidden) {
        e.preventDefault();
        setActiveSuggestion(activeIndex + 1);
      } else if (e.key === 'ArrowUp' && !suggestions.hidden) {
        e.preventDefault();
        setActiveSuggestion(activeIndex - 1);
      } else if (e.key === 'Enter' && activeIndex >= 0) {
        e.preventDefault();
        suggestionLinks()[activeIndex]?.click();
      } else if (e.key === 'Escape') {
        if (!suggestions.hidden) {
          closeSuggestions();
        } else {
          closeSearch(form);
        }
      }
    });

    clearButton.addEventListener('click', (e) => {
      e.stopPropagation();
      input.value = '';
      form.classList.remove('has-value');
      clearButton.hidden = true;
      suggestionList.replaceChildren();
      closeSuggestions();
      input.focus();
    });
  });

  function escapeSearchText(value) {
    return String(value ?? '')
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
  }

  document.addEventListener('click', (e) => {
    searchForms.forEach((form) => {
      if (form.classList.contains('is-open') && !form.contains(e.target)) {
        closeSearch(form);
      }
    });
  });

  /* ─── Profile Avatar Preview ───────────────────────────────────────────── */

  const avatarInput   = document.getElementById('profileAvatarInput');
  const avatarImg     = document.getElementById('profileAvatarImg');
  const avatarInitial = document.getElementById('profileAvatarInitial');

  if (avatarInput) {
    avatarInput.addEventListener('change', () => {
      const file = avatarInput.files[0];
      if (!file || !file.type.startsWith('image/')) return;
      const reader = new FileReader();
      reader.onload = (e) => {
        avatarImg.src = e.target.result;
        avatarImg.style.display = 'block';
        if (avatarInitial) avatarInitial.style.display = 'none';
      };
      reader.readAsDataURL(file);
    });
  }

  /* ─── Bio Character Counter ────────────────────────────────────────────── */

  const bioTextarea = document.getElementById('peBio');
  const bioCount    = document.getElementById('peBioCount');

  if (bioTextarea && bioCount) {
    const updateCount = () => { bioCount.textContent = bioTextarea.value.length; };
    bioTextarea.addEventListener('input', updateCount);
    updateCount();
  }

  const profileForm = document.getElementById('profileEditForm');
  const profileSave = document.getElementById('profileEditSave');
  if (profileForm && profileSave) {
    profileSave.addEventListener('click', async () => {
      if (!profileForm.reportValidity()) return;
      const original = profileSave.innerHTML;
      profileSave.disabled = true;
      profileSave.textContent = 'Saving...';
      try {
        const response = await fetch('profile-api.php', { method: 'POST', body: new FormData(profileForm) });
        const payload = await response.json();
        if (!response.ok || !payload.success) throw new Error(payload.message || 'Could not save profile.');
        window.location.reload();
      } catch (error) {
        window.alert(error.message || 'Could not save profile.');
        profileSave.disabled = false;
        profileSave.innerHTML = original;
      }
    });
  }

  /* ─── Custom Gender Dropdown ───────────────────────────────────────────── */

  (function initGenderDropdown() {
    const wrap     = document.getElementById('peGenderWrap');
    const trigger  = document.getElementById('peGenderTrigger');
    const dropdown = document.getElementById('peGenderDropdown');
    const valEl    = document.getElementById('peGenderValue');
    const hidden   = document.getElementById('peGender');
    if (!wrap) return;

    const gLabels = { male: 'Male', female: 'Female', other: 'Other', prefer_not: 'Rather not say' };
    let open = false;

    if (hidden.value) valEl.classList.remove('ck-select-placeholder');

    function toggleG(force) {
      open = force !== undefined ? force : !open;
      trigger.classList.toggle('is-open', open);
      dropdown.classList.toggle('is-open', open);
      trigger.setAttribute('aria-expanded', open);
    }

    trigger.addEventListener('click', (e) => { e.stopPropagation(); toggleG(); });
    trigger.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); toggleG(); }
    });

    dropdown.querySelectorAll('.ck-option').forEach((opt) => {
      opt.addEventListener('click', () => {
        dropdown.querySelectorAll('.ck-option').forEach((o) => o.classList.remove('is-selected'));
        opt.classList.add('is-selected');
        valEl.textContent = gLabels[opt.dataset.value];
        valEl.classList.remove('ck-select-placeholder');
        hidden.value = opt.dataset.value;
        toggleG(false);
      });
    });

    document.addEventListener('click', (e) => {
      if (!wrap.contains(e.target)) toggleG(false);
    });
  })();

  /* ─── Custom Birthday Calendar ─────────────────────────────────────────── */

  (function initBirthdayCalendar() {
    const wrap    = document.getElementById('peDateWrap');
    const trigger = document.getElementById('peDateTrigger');
    const display = document.getElementById('peDateDisplay');
    const hidden  = document.getElementById('peBirthday');
    const panel   = document.getElementById('peCalPanel');
    if (!wrap) return;

    const today   = new Date();
    const maxDate = today;

    const MONTHS       = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    const MONTHS_SHORT = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    const DAYS         = ['Su','Mo','Tu','We','Th','Fr','Sa'];

    let calOpen = false;
    let calView = 'days';
    let sel = hidden.value ? new Date(hidden.value + 'T00:00:00') : null;
    let viewYear  = sel ? sel.getFullYear()  : today.getFullYear();
    let viewMonth = sel ? sel.getMonth()     : today.getMonth();

    if (sel) {
      display.textContent = sel.toLocaleDateString('en-PH', { year: 'numeric', month: 'long', day: 'numeric' });
      display.classList.remove('ck-select-placeholder');
      wrap.classList.add('has-value');
    }

    /* Open / close */
    function toggleCal(force) {
      calOpen = force !== undefined ? force : !calOpen;
      trigger.classList.toggle('is-open', calOpen);
      panel.classList.toggle('is-open', calOpen);
      if (calOpen) { calView = 'days'; renderCal(); }
    }

    trigger.addEventListener('click', (e) => { e.stopPropagation(); toggleCal(); });
    trigger.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); toggleCal(); }
    });

    /* Clicking inside the calendar panel must NOT close it */
    panel.addEventListener('click', (e) => e.stopPropagation());

    /* Clicking outside closes it */
    document.addEventListener('click', (e) => {
      if (!wrap.contains(e.target)) toggleCal(false);
    });

    /* Render dispatcher */
    function renderCal() {
      if      (calView === 'days')   renderDays();
      else if (calView === 'months') renderMonths();
      else                           renderYears();
    }

    /* Days view */
    function renderDays() {
      const firstDay = new Date(viewYear, viewMonth, 1).getDay();
      const dIM      = new Date(viewYear, viewMonth + 1, 0).getDate();
      const dIP      = new Date(viewYear, viewMonth, 0).getDate();

      let h = `
        <div class="ck-cal-header">
          <button class="ck-cal-nav" id="calPrev" type="button">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
          </button>
          <div class="ck-cal-title-wrap">
            <button class="ck-cal-month-btn" id="calMonthBtn" type="button">${MONTHS[viewMonth]}</button>
            <button class="ck-cal-year-btn"  id="calYearBtn"  type="button">${viewYear}</button>
          </div>
          <button class="ck-cal-nav" id="calNext" type="button">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
          </button>
        </div>
        <div class="ck-cal-weekdays">${DAYS.map((d) => `<div class="ck-cal-weekday">${d}</div>`).join('')}</div>
        <div class="ck-cal-days">
      `;

      for (let i = firstDay - 1; i >= 0; i--)
        h += `<div class="ck-cal-day is-other-month is-disabled">${dIP - i}</div>`;

      for (let d = 1; d <= dIM; d++) {
        const dt = new Date(viewYear, viewMonth, d);
        h += `<div class="ck-cal-day${dt.toDateString() === today.toDateString() ? ' is-today' : ''}${sel && dt.toDateString() === sel.toDateString() ? ' is-selected' : ''}${dt > maxDate ? ' is-disabled' : ''}" data-d="${d}">${d}</div>`;
      }

      const rem = (firstDay + dIM) % 7 === 0 ? 0 : 7 - (firstDay + dIM) % 7;
      for (let d = 1; d <= rem; d++)
        h += `<div class="ck-cal-day is-other-month is-disabled">${d}</div>`;

      h += `</div>`;
      panel.innerHTML = h;

      panel.querySelector('#calPrev').addEventListener('click', () => {
        if (viewMonth === 0) { viewMonth = 11; viewYear--; } else viewMonth--;
        renderCal();
      });
      panel.querySelector('#calNext').addEventListener('click', () => {
        if (viewMonth === 11) { viewMonth = 0; viewYear++; } else viewMonth++;
        renderCal();
      });
      panel.querySelector('#calMonthBtn').addEventListener('click', () => { calView = 'months'; renderCal(); });
      panel.querySelector('#calYearBtn').addEventListener('click',  () => { calView = 'years';  renderCal(); });

      panel.querySelectorAll('.ck-cal-day[data-d]').forEach((el) => {
        el.addEventListener('click', () => {
          if (el.classList.contains('is-disabled')) return;
          const d   = parseInt(el.dataset.d);
          sel       = new Date(viewYear, viewMonth, d);
          const iso = `${viewYear}-${String(viewMonth + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
          hidden.value        = iso;
          display.textContent = sel.toLocaleDateString('en-PH', { year: 'numeric', month: 'long', day: 'numeric' });
          display.classList.remove('ck-select-placeholder');
          wrap.classList.add('has-value');
          toggleCal(false);
        });
      });
    }

    /* Months view */
    function renderMonths() {
      let h = `
        <div class="ck-cal-header">
          <button class="ck-cal-nav" id="calPrev" type="button">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
          </button>
          <div class="ck-cal-title-wrap">
            <button class="ck-cal-year-btn is-active" id="calYearBtn" type="button">${viewYear}</button>
          </div>
          <button class="ck-cal-nav" id="calNext" type="button">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
          </button>
        </div>
        <div class="ck-cal-grid">
      `;

      MONTHS_SHORT.forEach((m, i) => {
        const isSel = sel && sel.getFullYear() === viewYear && sel.getMonth() === i;
        const isCur = today.getFullYear() === viewYear && today.getMonth() === i;
        h += `<div class="ck-cal-grid-item${isSel ? ' is-selected' : isCur ? ' is-current' : ''}" data-m="${i}">${m}</div>`;
      });

      h += `</div>`;
      panel.innerHTML = h;

      panel.querySelector('#calPrev').addEventListener('click', () => { viewYear--; renderCal(); });
      panel.querySelector('#calNext').addEventListener('click', () => { viewYear++; renderCal(); });
      panel.querySelector('#calYearBtn').addEventListener('click', () => { calView = 'years'; renderCal(); });

      panel.querySelectorAll('.ck-cal-grid-item[data-m]').forEach((el) => {
        el.addEventListener('click', () => {
          viewMonth = parseInt(el.dataset.m);
          calView   = 'days';
          renderCal();
        });
      });
    }

    /* Years view */
    function renderYears() {
      const start = Math.floor(viewYear / 12) * 12;

      let h = `
        <div class="ck-cal-header">
          <button class="ck-cal-nav" id="calPrev" type="button">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
          </button>
          <div class="ck-cal-title-wrap">
            <button class="ck-cal-month-btn is-active" type="button">${start}–${start + 11}</button>
          </div>
          <button class="ck-cal-nav" id="calNext" type="button">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
          </button>
        </div>
        <div class="ck-cal-grid">
      `;

      for (let y = start; y < start + 12; y++) {
        const isSel = sel && sel.getFullYear() === y;
        const isCur = today.getFullYear() === y;
        h += `<div class="ck-cal-grid-item${isSel ? ' is-selected' : isCur ? ' is-current' : ''}" data-y="${y}">${y}</div>`;
      }

      h += `</div>`;
      panel.innerHTML = h;

      panel.querySelector('#calPrev').addEventListener('click', () => { viewYear -= 12; renderCal(); });
      panel.querySelector('#calNext').addEventListener('click', () => { viewYear += 12; renderCal(); });

      panel.querySelectorAll('.ck-cal-grid-item[data-y]').forEach((el) => {
        el.addEventListener('click', () => {
          viewYear = parseInt(el.dataset.y);
          calView  = 'months';
          renderCal();
        });
      });
    }
  })();

  /* ─── Map Picker (Leaflet, lazy-loaded) ────────────────────────────────── */

  let peMap = null, peMarker = null, peMapReady = false;

  function loadLeaflet(cb) {
    if (window.L) { cb(); return; }
    const css = document.createElement('link');
    css.rel   = 'stylesheet';
    css.href  = 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css';
    document.head.appendChild(css);
    const js  = document.createElement('script');
    js.src    = 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js';
    js.onload = cb;
    document.head.appendChild(js);
  }

  function initPeMap() {
    if (peMapReady) return;
    peMapReady = true;

    const defaultLat = 12.8797, defaultLng = 121.7740, defaultZoom = 6;
    peMap = L.map('peMap', { zoomControl: true, attributionControl: false })
             .setView([defaultLat, defaultLng], defaultZoom);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(peMap);

    const redIcon = L.divIcon({
      className: 'pe-map-pin',
      html: `<svg viewBox="0 0 24 24" fill="var(--red-primary)" xmlns="http://www.w3.org/2000/svg" style="width:32px;height:32px;filter:drop-shadow(0 2px 6px rgba(232,22,43,.45))"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>`,
      iconSize:    [32, 32],
      iconAnchor:  [16, 32],
      popupAnchor: [0, -34],
    });

    const existingStreet = document.getElementById('peStreet')?.value;
    const existingCity   = document.getElementById('peCity')?.value;
    if (existingCity) {
      const q = [existingStreet, existingCity].filter(Boolean).join(', ');
      fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(q)}&limit=1`)
        .then((r) => r.json())
        .then((results) => {
          if (results && results[0]) {
            const lat = parseFloat(results[0].lat), lng = parseFloat(results[0].lon);
            peMap.setView([lat, lng], 15);
            peMarker = L.marker([lat, lng], { icon: redIcon }).addTo(peMap);
          }
        })
        .catch(() => {});
    }

    peMap.on('click', (e) => reverseGeocode(e.latlng.lat, e.latlng.lng, redIcon));

    const locateBtn = document.getElementById('peMapLocateBtn');
    if (locateBtn) {
      locateBtn.addEventListener('click', () => {
        if (!navigator.geolocation) return;
        locateBtn.classList.add('is-loading');
        navigator.geolocation.getCurrentPosition(
          (pos) => {
            locateBtn.classList.remove('is-loading');
            reverseGeocode(pos.coords.latitude, pos.coords.longitude, redIcon, true);
          },
          () => { locateBtn.classList.remove('is-loading'); }
        );
      });
    }
  }

  function reverseGeocode(lat, lng, icon, flyTo = false) {
    const statusEl = document.getElementById('peMapStatusText');
    if (statusEl) statusEl.textContent = 'Fetching address…';

    if (peMarker) peMarker.setLatLng([lat, lng]);
    else peMarker = L.marker([lat, lng], { icon }).addTo(peMap);

    if (flyTo) peMap.flyTo([lat, lng], 16, { duration: 1 });
    else peMap.panTo([lat, lng]);

    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&addressdetails=1`)
      .then((r) => r.json())
      .then((data) => {
        const a = data.address || {};
        const street   = [a.road, a.suburb, a.village, a.neighbourhood].filter(Boolean).join(', ');
        const city     = a.city || a.town || a.municipality || a.county || '';
        const province = a.state || a.province || a.region || '';
        const zip      = (a.postcode || '').replace(/\D/g, '').slice(0, 4);
        const country  = a.country || '';

          // Clear all address fields first before filling with new location data
          ['peStreet','peCity','peProvince','peZip','peCountry'].forEach(id => {
            const el = document.getElementById(id); if (el) el.value = '';
          });

          const set = (id, val) => { const el = document.getElementById(id); if (el && val && val.trim()) el.value = val; };
        set('peStreet',   street);
        set('peCity',     city);
        set('peProvince', province);
        set('peZip',      zip);
        set('peCountry',  country);

        if (statusEl) {
          const display = [street, city].filter(Boolean).join(', ') || data.display_name || 'Location pinned';
          statusEl.textContent = display.length > 60 ? display.slice(0, 57) + '…' : display;
        }
      })
      .catch(() => {
        const statusEl = document.getElementById('peMapStatusText');
        if (statusEl) statusEl.textContent = 'Could not fetch address. Fill in manually.';
      });
  }

  /* Lazy-init map when the offcanvas opens */
  document.addEventListener('shown.bs.offcanvas', (e) => {
    if (e.target?.id === 'profileEditPanel') {
      loadLeaflet(() => {
        setTimeout(() => {
          initPeMap();
          if (peMap) peMap.invalidateSize();
        }, 120);
      });
    }
  });

  /* Close dropdown when profile panel opens */
  document.addEventListener('show.bs.offcanvas', (e) => {
    if (['profileEditPanel', 'orderHistoryPanel', 'favoritesPanel', 'myTicketsPanel'].includes(e.target?.id)) {
      document.querySelectorAll('.nav-profile .dropdown-menu.show').forEach((m) => {
        const dropdownEl = m.closest('.dropdown');
        if (dropdownEl) {
          const toggle = dropdownEl.querySelector('[data-bs-toggle="dropdown"]');
          if (toggle) {
            const dd = bootstrap.Dropdown.getInstance(toggle);
            if (dd) dd.hide();
          }
        }
      });
    }
  });

  const orderPanel = document.getElementById('orderHistoryPanel');
  const query = new URLSearchParams(window.location.search);
  if (orderPanel && query.get('panel') === 'orders' && window.bootstrap?.Offcanvas) {
    bootstrap.Offcanvas.getOrCreateInstance(orderPanel).show();
  }

  const ticketsPanel = document.getElementById('myTicketsPanel');
  if (ticketsPanel && query.get('panel') === 'tickets' && window.bootstrap?.Offcanvas) {
    bootstrap.Offcanvas.getOrCreateInstance(ticketsPanel).show();
  }

  const favoritesPanel = document.getElementById('favoritesPanel');
  if (favoritesPanel && query.get('panel') === 'favorites' && window.bootstrap?.Offcanvas) {
    bootstrap.Offcanvas.getOrCreateInstance(favoritesPanel).show();
  }

  /* ─── Toast Notification ────────────────────────────────────────────────── */

  const toast = document.getElementById('ckToast');
  if (toast) {
    const closeBtn = toast.querySelector('.ck-toast__close');
    const closeToast = () => {
      toast.classList.remove('is-visible');
      window.setTimeout(() => toast.remove(), 260);
    };
    requestAnimationFrame(() => toast.classList.add('is-visible'));
    if (closeBtn) closeBtn.addEventListener('click', closeToast);
    window.setTimeout(closeToast, 5200);
  }

})();
