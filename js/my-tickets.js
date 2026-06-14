document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('ticketDetailModal');
  const panel = modal?.querySelector('.ticket-detail-modal__panel');
  const body = document.getElementById('ticketDetailBody');
  const dataNode = document.getElementById('myTicketsData');
  const triggers = document.querySelectorAll('[data-ticket-open]');
  let previousFocus = null;

  if (!modal || !panel || !body || !dataNode || !triggers.length) return;

  let records = [];
  try {
    records = JSON.parse(dataNode.textContent || '[]');
  } catch (error) {
    return;
  }

  const escapeHtml = (value) => String(value ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');

  const formatDate = (value) => {
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return escapeHtml(value);
    return date.toLocaleString('en-PH', {
      timeZone: 'Asia/Manila',
      month: 'long',
      day: 'numeric',
      year: 'numeric',
      hour: 'numeric',
      minute: '2-digit',
    });
  };

  const render = ({ order, ticket }) => `
    <section class="ticket-detail-hero">
      <img class="ticket-detail-hero__poster" src="${escapeHtml(order.event_poster || 'assets/Icon_Logo.png')}" alt="${escapeHtml(order.event_title)} poster">
      <div class="ticket-detail-hero__copy">
        <span>${escapeHtml(ticket.status || 'Valid')} &middot; ${escapeHtml(ticket.category || 'Admission')}</span>
        <h3>${escapeHtml(order.event_title || 'ClicKet Event')}</h3>
        <p>${escapeHtml(order.event_date)} at ${escapeHtml(order.event_time)}</p>
        <p>${escapeHtml(order.venue)}</p>
        <div class="ticket-detail-hero__seat">
          <div><span>Section</span><strong>${escapeHtml(ticket.section)}</strong></div>
          <div><span>Row</span><strong>${escapeHtml(ticket.row)}</strong></div>
          <div><span>Seat</span><strong>${escapeHtml(ticket.number)}</strong></div>
        </div>
      </div>
    </section>
    <section class="ticket-detail-grid">
      <div><span>Ticket ID</span><strong>${escapeHtml(ticket.ticket_id)}</strong></div>
      <div><span>Order ID</span><strong>${escapeHtml(order.order_id)}</strong></div>
      <div><span>Voucher ID</span><strong>${escapeHtml(ticket.voucher_id)}</strong></div>
      <div><span>Ticket holder</span><strong>${escapeHtml(order.buyer_name || order.buyer_email)}</strong></div>
      <div><span>Purchase date</span><strong>${formatDate(order.booked_at)}</strong></div>
      <div><span>Payment status</span><strong>${escapeHtml(order.payment_status || 'Paid')}</strong></div>
    </section>
    <section class="ticket-detail-actions">
      <p><strong>Non-transferable ticket</strong>This ticket is linked to the purchasing account. Bring a matching valid ID for admission.</p>
      <a href="voucher.php?ticket=${encodeURIComponent(ticket.ticket_id)}" target="_blank" rel="noopener">Print Form</a>
    </section>
  `;

  const openModal = (record, trigger) => {
    previousFocus = trigger;
    body.innerHTML = render(record);
    modal.hidden = false;
    document.body.classList.add('ticket-detail-open');
    requestAnimationFrame(() => {
      modal.classList.add('is-open');
      panel.focus();
    });
  };

  const closeModal = () => {
    modal.classList.remove('is-open');
    document.body.classList.remove('ticket-detail-open');
    window.setTimeout(() => {
      modal.hidden = true;
      body.innerHTML = '';
      previousFocus?.focus();
    }, 180);
  };

  triggers.forEach((trigger) => {
    trigger.addEventListener('click', () => {
      const record = records.find((item) => item.ticket?.ticket_id === trigger.dataset.ticketOpen);
      if (!record) return;

      const offcanvas = trigger.closest('.offcanvas');
      const instance = offcanvas && window.bootstrap?.Offcanvas
        ? bootstrap.Offcanvas.getInstance(offcanvas)
        : null;

      if (offcanvas && instance) {
        offcanvas.addEventListener('hidden.bs.offcanvas', () => openModal(record, trigger), { once: true });
        instance.hide();
        return;
      }

      openModal(record, trigger);
    });
  });

  modal.querySelectorAll('[data-ticket-close]').forEach((element) => element.addEventListener('click', closeModal));
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && modal.classList.contains('is-open')) closeModal();
  });
});

