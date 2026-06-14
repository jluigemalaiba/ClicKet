(() => {
  'use strict';

  const topbar = document.querySelector('.ticket-topbar');
  if (!topbar) return;

  let ticking = false;

  const updateTopbar = () => {
    topbar.classList.toggle('is-scrolled', window.scrollY > 60);
    ticking = false;
  };

  window.addEventListener('scroll', () => {
    if (ticking) return;
    ticking = true;
    window.requestAnimationFrame(updateTopbar);
  }, { passive: true });

  updateTopbar();
})();
