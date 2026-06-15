document.addEventListener('DOMContentLoaded', () => {
  const receipt = document.getElementById('receiptSection');
  const downloadReceipt = document.getElementById('downloadReceipt');
  const printReceipt = document.getElementById('printReceipt');

  if (receipt && downloadReceipt) {
    downloadReceipt.addEventListener('click', async () => {
      const reference = receipt.dataset.reference || 'clicket';
      const filename = `${reference.replace(/[^a-z0-9-]/gi, '_')}-receipt.pdf`;

      if (typeof window.html2pdf !== 'function') {
        window.alert('PDF generation could not be loaded. Please use Print Receipt instead.');
        return;
      }

      downloadReceipt.disabled = true;
      const downloadLabel = downloadReceipt.querySelector('span');
      if (downloadLabel) downloadLabel.textContent = 'Generating PDF...';
      receipt.classList.add('is-exporting-pdf');

      try {
        await window.html2pdf().set({
          margin: [8, 8, 8, 8],
          filename,
          image: { type: 'jpeg', quality: 0.98 },
          html2canvas: {
            scale: 2,
            useCORS: true,
            backgroundColor: '#ffffff',
            scrollX: 0,
            scrollY: 0,
          },
          jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
          pagebreak: { mode: ['avoid-all', 'css', 'legacy'] },
        }).from(receipt).save();
      } catch (error) {
        window.alert('The PDF could not be generated. Please try Print Receipt instead.');
      } finally {
        receipt.classList.remove('is-exporting-pdf');
        downloadReceipt.disabled = false;
        if (downloadLabel) downloadLabel.textContent = 'Download Receipt';
      }
    });
  }

  if (receipt && printReceipt) {
    printReceipt.addEventListener('click', () => window.print());
  }

  const form = document.getElementById('paymentForm');
  const terms = document.getElementById('termsAccepted');
  const submit = document.getElementById('paymentSubmit');
  const methodError = document.getElementById('paymentMethodError');
  const termsError = document.getElementById('termsError');
  const detailsError = document.getElementById('paymentDetailsError');

  if (!form || !terms || !submit) return;
  const panels = Array.from(form.querySelectorAll('[data-payment-panel]'));

  const selectedMethod = () => form.querySelector('input[name="payment_method"]:checked');
  const panelForMethod = (method) => {
    if (['visa', 'mastercard', 'jcb'].includes(method)) return 'card';
    if (['gcash', 'maya'].includes(method)) return 'wallet';
    if (method === 'qrph') return 'qr';
    return '';
  };

  const activeFields = () => {
    const method = selectedMethod();
    const panelName = method ? panelForMethod(method.value) : '';
    const panel = panels.find((item) => item.dataset.paymentPanel === panelName);
    return panel ? Array.from(panel.querySelectorAll('[data-payment-required]')) : [];
  };

  const expiryIsValid = (value) => {
    const match = value.match(/^(0[1-9]|1[0-2])\/(\d{2})$/);
    if (!match) return false;
    const month = Number(match[1]);
    const year = 2000 + Number(match[2]);
    const expiry = new Date(year, month, 0, 23, 59, 59);
    return expiry >= new Date();
  };

  const detailsAreValid = () => activeFields().every((field) => {
    if (field.type === 'checkbox') return field.checked;
    if (field.type === 'file') return Boolean(field.files?.length);
    const value = field.value.trim();
    if (!value) return false;
    if (field.name === 'card_number') return /^\d{16,19}$/.test(value.replace(/\D/g, ''));
    if (field.name === 'card_expiry') return expiryIsValid(value);
    if (field.name === 'card_cvv') return /^\d{3,4}$/.test(value);
    return true;
  });

  const syncState = () => {
    const method = selectedMethod();
    const hasMethod = Boolean(method);
    const activePanel = hasMethod ? panelForMethod(method.value) : '';

    panels.forEach((panel) => {
      const isActive = panel.dataset.paymentPanel === activePanel;
      panel.hidden = !isActive;
      panel.querySelectorAll('input').forEach((input) => {
        input.disabled = !isActive;
      });
    });

    submit.disabled = !(hasMethod && detailsAreValid() && terms.checked);
    form.querySelectorAll('.payment-option').forEach((option) => {
      option.classList.toggle('is-selected', option.querySelector('input').checked);
    });
    if (hasMethod) methodError.classList.remove('is-visible');
    if (detailsAreValid()) detailsError.classList.remove('is-visible');
    if (terms.checked) {
      termsError.classList.remove('is-visible');
    } else if (hasMethod) {
      termsError.textContent = 'Please agree to the Terms and Conditions before proceeding.';
      termsError.classList.add('is-visible');
    }
  };

  form.addEventListener('change', syncState);
  form.addEventListener('input', (event) => {
    const input = event.target;
    if (input.name === 'card_number') {
      input.value = input.value.replace(/\D/g, '').slice(0, 19).replace(/(.{4})/g, '$1 ').trim();
    }
    if (input.name === 'card_expiry') {
      const digits = input.value.replace(/\D/g, '').slice(0, 4);
      input.value = digits.length > 2 ? `${digits.slice(0, 2)}/${digits.slice(2)}` : digits;
    }
    if (input.name === 'card_cvv') input.value = input.value.replace(/\D/g, '').slice(0, 4);
    if (input.name === 'wallet_mobile') input.value = input.value.replace(/\D/g, '').slice(0, 12);
    syncState();
  });
  form.addEventListener('beforeinput', (event) => {
    if (!['card_number', 'card_expiry', 'card_cvv', 'wallet_mobile'].includes(event.target.name) || !event.data) return;
    if (/\D/.test(event.data)) event.preventDefault();
  });
  form.addEventListener('paste', (event) => {
    if (!['card_number', 'card_expiry', 'card_cvv', 'wallet_mobile'].includes(event.target.name)) return;
    event.preventDefault();
    const input = event.target;
    const limits = { card_number: 19, card_expiry: 4, card_cvv: 4, wallet_mobile: 12 };
    const digits = event.clipboardData.getData('text').replace(/\D/g, '').slice(0, limits[input.name]);
    if (input.name === 'card_number') input.value = digits.replace(/(.{4})/g, '$1 ').trim();
    else if (input.name === 'card_expiry') input.value = digits.length > 2 ? `${digits.slice(0, 2)}/${digits.slice(2)}` : digits;
    else input.value = digits;
    syncState();
  });
  form.addEventListener('submit', (event) => {
    let valid = true;
    if (!selectedMethod()) {
      methodError.textContent = 'Please select a payment method.';
      methodError.classList.add('is-visible');
      valid = false;
    }
    if (selectedMethod() && !detailsAreValid()) {
      detailsError.textContent = 'Complete all required payment details before confirming payment.';
      detailsError.classList.add('is-visible');
      valid = false;
    }
    if (!terms.checked) {
      termsError.textContent = 'Please agree to the Terms and Conditions before proceeding.';
      termsError.classList.add('is-visible');
      valid = false;
    }
    if (!valid) {
      event.preventDefault();
      const firstError = form.querySelector('.payment-validation.is-visible');
      if (firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  });

  syncState();
});
