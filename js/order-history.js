document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('orderDetailsModal');
  const modalPanel = modal?.querySelector('.order-modal__panel');
  const modalBody = document.getElementById('orderModalBody');
  const dataNode = document.getElementById('orderHistoryData');
  const triggers = document.querySelectorAll('[data-order-id]');
  let previousFocus = null;

  if (!modal || !modalPanel || !modalBody || !dataNode || !triggers.length) return;

  let orders = [];
  try {
    orders = JSON.parse(dataNode.textContent || '[]');
  } catch (error) {
    return;
  }

  const escapeHtml = (value) => String(value ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');

  const money = (value) => `PHP ${Number(value || 0).toLocaleString('en-PH')}`;
  const dateTime = (value) => {
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

  const ticketRows = (order) => (order.seats || []).map((seat, index) => `
    <article class="order-ticket">
      <div class="order-ticket__number">${String(index + 1).padStart(2, '0')}</div>
      <div class="order-ticket__info">
        <span>Ticket ${index + 1}</span>
        <strong>${escapeHtml(seat.category || 'Admission')}</strong>
        <p>${escapeHtml(seat.section)} &middot; Row ${escapeHtml(seat.row)} &middot; Seat ${escapeHtml(seat.number)}</p>
      </div>
      <div class="order-ticket__code">
        <span>Ticket code</span>
        <strong>${escapeHtml(seat.ticket_code || 'Issued at venue')}</strong>
        <small>Non-transferable</small>
      </div>
      <b>${money(seat.price)}</b>
    </article>
  `).join('');

  const renderOrder = (order) => {
    const buyer = order.buyer_name || order.buyer_email || 'ClicKet account holder';
    const paymentMethod = String(order.payment_method || '').toLowerCase();
    const paymentStatus = String(order.payment_status_key || order.payment_status || '').toLowerCase();
    const canUploadProof = paymentMethod === 'qrph' && ['pending', 'rejected'].includes(paymentStatus);
    const qr = order.payment_qr || null;
    const qrImage = qr?.exists && qr?.path
      ? `<img src="${escapeHtml(qr.path)}" alt="${escapeHtml(qr.venue_label || order.venue)} QR Ph code">`
      : '<span>QR image unavailable</span>';
    const proofContent = order.proof_url
      ? `<div class="order-modal__proof-preview"><img src="${escapeHtml(order.proof_url)}" alt="Proof of payment for ${escapeHtml(order.order_id)}"><p>Uploaded proof of payment</p></div>`
      : canUploadProof
        ? `<form class="order-proof-form order-modal__proof-form" method="post" action="payment-proof-upload.php" enctype="multipart/form-data">
            <input type="hidden" name="order_id" value="${escapeHtml(order.order_id)}">
            <label class="order-proof-picker">
              <input type="file" name="payment_proof" accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp" required>
              <span class="order-proof-picker__button"><svg viewBox="0 0 24 24" fill="none" stroke-width="2"><path d="M12 16V4"></path><path d="m7 9 5-5 5 5"></path><path d="M5 20h14"></path></svg>Choose proof image</span>
              <small data-proof-file-name>JPG, PNG, or WEBP up to 5 MB</small>
            </label>
            <button type="submit">${paymentStatus === 'rejected' ? 'Upload New Proof' : 'Submit Proof'}</button>
          </form>`
        : `<p class="order-modal__proof-state">${paymentStatus === 'under_review' ? 'Proof submitted. Organizer verification is in progress.' : paymentStatus === 'approved' ? 'Payment verified. No additional proof is required.' : 'No proof upload is required for this payment.'}</p>`;
    return `
      <div class="order-modal__hero">
        <div>
          <span class="order-modal__event-label">Event</span>
          <h3>${escapeHtml(order.event_title || 'ClicKet Event')}</h3>
          <p>${escapeHtml(order.event_date)} at ${escapeHtml(order.event_time)}</p>
          <p>${escapeHtml(order.venue)}</p>
        </div>
        <div class="order-modal__status">
          <span>${escapeHtml(order.payment_status || 'Paid')}</span>
          <strong>${escapeHtml(order.order_status || 'Confirmed')}</strong>
        </div>
      </div>

      <div class="order-modal__reference-grid">
        <div><span>Order ID</span><strong>${escapeHtml(order.order_id)}</strong></div>
        <div><span>Payment reference</span><strong>${escapeHtml(order.payment_reference || order.reference)}</strong></div>
        <div><span>Buyer / account</span><strong>${escapeHtml(buyer)}</strong><small>${escapeHtml(order.buyer_email || '')}</small></div>
        <div><span>Order date and time</span><strong>${dateTime(order.booked_at)}</strong></div>
      </div>

      <section class="order-modal__section">
        <div class="order-modal__section-heading">
          <div><span>Complete ticket information</span><h4>${(order.seats || []).length} ${(order.seats || []).length === 1 ? 'ticket' : 'tickets'}</h4></div>
          <span>Present the ticket code with a valid ID at entry.</span>
        </div>
        <div class="order-ticket-list">${ticketRows(order)}</div>
      </section>

      <section class="order-modal__payment">
        <div>
          <span>Payment method</span>
          <strong>${escapeHtml(order.payment_method_label || order.payment_method || 'Payment')}</strong>
          <small>${escapeHtml(order.payment_account || '')}</small>
        </div>
        <div class="order-modal__totals">
          <p><span>Tickets subtotal</span><strong>${money(order.subtotal)}</strong></p>
          <p><span>Service fee</span><strong>${money(order.service_fee)}</strong></p>
          <p class="is-total"><span>${paymentStatus === 'approved' ? 'Total amount paid' : 'Total amount due'}</span><strong>${money(order.total)}</strong></p>
        </div>
      </section>

      ${paymentMethod === 'qrph' ? `
        <section class="order-modal__proof">
          <div class="order-modal__proof-qr">${qrImage}<small>${escapeHtml(qr?.venue_label || order.venue)} QR Ph</small></div>
          <div><span>Payment verification</span><h4>Upload proof of payment</h4>${proofContent}</div>
        </section>
      ` : ''}

      <div class="order-modal__notice">
        <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="M9 12l2 2 4-4"/></svg>
        <div><strong>Non-transferable ticket notice</strong><p>These tickets are assigned to ${escapeHtml(buyer)} and may require account ownership and valid identification for admission.</p></div>
      </div>
    `;
  };

  const closeModal = () => {
    modal.classList.remove('is-open');
    document.body.classList.remove('order-modal-open');
    window.setTimeout(() => {
      modal.hidden = true;
      modalBody.innerHTML = '';
      previousFocus?.focus();
    }, 180);
  };

  const openOrder = (order, trigger) => {
      previousFocus = trigger;
      modalBody.innerHTML = renderOrder(order);
      modal.hidden = false;
      document.body.classList.add('order-modal-open');
      requestAnimationFrame(() => {
        modal.classList.add('is-open');
        modalPanel.focus();
      });
  };

  modalBody.addEventListener('change', (event) => {
    const input = event.target.closest('input[name="payment_proof"]');
    if (!input) return;
    const fileName = input.files?.[0]?.name || 'JPG, PNG, or WEBP up to 5 MB';
    const picker = input.closest('.order-proof-picker');
    const label = picker?.querySelector('[data-proof-file-name]');
    if (label) label.textContent = fileName;
    picker?.classList.toggle('has-file', Boolean(input.files?.length));
  });

  triggers.forEach((trigger) => {
    trigger.addEventListener('click', () => {
      const order = orders.find((item) => item.order_id === trigger.dataset.orderId);
      if (!order) return;

      const offcanvas = trigger.closest('.offcanvas');
      const offcanvasInstance = offcanvas && window.bootstrap?.Offcanvas
        ? bootstrap.Offcanvas.getInstance(offcanvas)
        : null;

      if (offcanvas && offcanvasInstance) {
        offcanvas.addEventListener('hidden.bs.offcanvas', () => openOrder(order, trigger), { once: true });
        offcanvasInstance.hide();
        return;
      }

      openOrder(order, trigger);
    });
  });

  modal.querySelectorAll('[data-order-close]').forEach((button) => button.addEventListener('click', closeModal));
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && modal.classList.contains('is-open')) closeModal();
  });
});
