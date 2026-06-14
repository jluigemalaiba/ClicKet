document.addEventListener('DOMContentLoaded', () => {
  const endpoint = 'includes/favorite-api.php';
  const escapeHtml = (value) => String(value ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');

  const updateCount = () => {
    const count = document.querySelectorAll('[data-favorite-item]').length;
    const countNode = document.querySelector('[data-favorites-count]');
    if (countNode) countNode.textContent = `${count} ${count === 1 ? 'saved event' : 'saved events'}`;
  };

  const emptyMarkup = () => `
    <div class="favorites-empty" data-favorites-empty>
      <span><svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z"/></svg></span>
      <h3>No favorites yet</h3>
      <p>Tap the heart on an event card to save it here.</p>
      <a href="events.php">Explore events</a>
    </div>`;

  const syncHearts = (eventId, favorite) => {
    document.querySelectorAll(`[data-favorite-toggle="${CSS.escape(eventId)}"]`).forEach((button) => {
      button.classList.toggle('is-favorite', favorite);
      button.setAttribute('aria-pressed', favorite ? 'true' : 'false');
      button.setAttribute('aria-label', favorite ? 'Remove from favorites' : 'Add to favorites');
    });
  };

  const favoriteMarkup = (item) => `
    <article class="favorite-item" data-favorite-item="${escapeHtml(item.event_id)}">
      <a class="favorite-item__poster" href="${escapeHtml(item.url)}">
        <img src="${escapeHtml(item.poster)}" alt="${escapeHtml(item.title)} poster">
      </a>
      <div class="favorite-item__content">
        <span>${escapeHtml(item.category || 'Event')}</span>
        <h3><a href="${escapeHtml(item.url)}">${escapeHtml(item.title)}</a></h3>
        <p>${escapeHtml(item.date)} at ${escapeHtml(item.time)}</p>
        <p>${escapeHtml(item.venue)}</p>
        <div>
          <a href="${escapeHtml(item.url)}">View event</a>
          <button type="button" data-favorite-remove="${escapeHtml(item.event_id)}">
            <svg viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z"/></svg>
            Unfavorite
          </button>
        </div>
      </div>
    </article>`;

  const syncFavoritePanel = (eventId, favorite, favorites) => {
    const list = document.getElementById('favoritesList');
    if (!list) return;

    list.querySelector(`[data-favorite-item="${CSS.escape(eventId)}"]`)?.remove();
    if (favorite) {
      const item = favorites.find((entry) => entry.event_id === eventId);
      if (item) {
        list.querySelector('[data-favorites-empty]')?.remove();
        list.insertAdjacentHTML('afterbegin', favoriteMarkup(item));
      }
    }

    if (!list.querySelector('[data-favorite-item]')) {
      list.innerHTML = emptyMarkup();
    }
    updateCount();
  };

  const requestFavorite = async (eventId, favorite, button) => {
    button?.classList.add('is-loading');
    const body = new URLSearchParams({ event_id: eventId, favorite: favorite ? '1' : '0' });

    try {
      const response = await fetch(endpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
        body,
      });
      const result = await response.json();

      if (response.status === 401) {
        window.location.href = `auth.php?mode=login&return=${encodeURIComponent(`events.php?favorite=${eventId}`)}`;
        return false;
      }
      if (!response.ok || !result.success) throw new Error(result.message || 'Unable to update favorites.');

      syncHearts(eventId, favorite);
      syncFavoritePanel(eventId, favorite, result.favorites || []);
      window.dispatchEvent(new CustomEvent('clicket:favorite-changed', {
        detail: { eventId, favorite },
      }));
      return true;
    } catch (error) {
      window.alert(error.message || 'Unable to update favorites right now.');
      return false;
    } finally {
      button?.classList.remove('is-loading');
    }
  };

  document.addEventListener('click', async (event) => {
    const toggle = event.target.closest('[data-favorite-toggle]');
    if (toggle) {
      event.preventDefault();
      event.stopPropagation();
      const eventId = toggle.dataset.favoriteToggle;
      const nextState = !toggle.classList.contains('is-favorite');
      await requestFavorite(eventId, nextState, toggle);
      return;
    }

    const remove = event.target.closest('[data-favorite-remove]');
    if (!remove) return;

    const eventId = remove.dataset.favoriteRemove;
    const success = await requestFavorite(eventId, false, remove);
    if (!success) return;

  });

  updateCount();

  const params = new URLSearchParams(window.location.search);
  const favoriteAfterLogin = params.get('favorite');
  if (favoriteAfterLogin) {
    const button = document.querySelector(`[data-favorite-toggle="${CSS.escape(favoriteAfterLogin)}"]`);
    if (button && !button.classList.contains('is-favorite')) {
      button.click();
      params.delete('favorite');
      const nextQuery = params.toString();
      window.history.replaceState({}, '', `${window.location.pathname}${nextQuery ? `?${nextQuery}` : ''}`);
    }
  }
});
