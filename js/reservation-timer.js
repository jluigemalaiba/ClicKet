(() => {
  'use strict';

  const timers = Array.from(document.querySelectorAll('[data-reservation-timer]'));
  if (!timers.length) return;

  const expiresAt = Number(timers[0].dataset.expiresAt || 0);
  const expiryUrl = timers[0].dataset.expiryUrl || '';
  let redirected = false;

  const expire = () => {
    if (redirected) return;
    redirected = true;
    timers.forEach(timer => {
      timer.textContent = 'Expired';
      timer.closest('.ticket-session-clock')?.classList.add('is-expired');
    });
    if (expiryUrl) window.location.replace(expiryUrl);
  };

  const update = () => {
    const remaining = expiresAt - Date.now();
    if (!expiresAt || remaining <= 0) {
      expire();
      return;
    }

    const seconds = Math.ceil(remaining / 1000);
    const label = `${String(Math.floor(seconds / 60)).padStart(2, '0')}:${String(seconds % 60).padStart(2, '0')}`;
    timers.forEach(timer => {
      timer.textContent = label;
      timer.closest('.ticket-session-clock')?.classList.toggle('is-urgent', seconds <= 120);
    });
  };

  update();
  window.setInterval(update, 1000);
})();
