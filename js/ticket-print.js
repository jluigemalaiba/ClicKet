document.addEventListener('DOMContentLoaded', () => {
  const voucher = document.getElementById('voucherDocument');
  const printButton = document.getElementById('voucherPrint');
  const pdfButton = document.getElementById('voucherPdf');

  printButton?.addEventListener('click', () => window.print());

  if (!voucher || !pdfButton) return;

  pdfButton.addEventListener('click', async () => {
    if (typeof window.html2pdf !== 'function') {
      window.alert('PDF generation is unavailable. Please use Print Voucher instead.');
      return;
    }

    const ticketId = voucher.dataset.ticketId || 'clicket-ticket';
    pdfButton.disabled = true;
    pdfButton.textContent = 'Generating PDF...';
    voucher.classList.add('is-exporting');

    try {
      await window.html2pdf().set({
        margin: [6, 6, 6, 6],
        filename: `${ticketId.replace(/[^a-z0-9-]/gi, '_')}-voucher.pdf`,
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
      }).from(voucher).save();
    } catch (error) {
      window.alert('The PDF could not be generated. Please use Print Voucher instead.');
    } finally {
      voucher.classList.remove('is-exporting');
      pdfButton.disabled = false;
      pdfButton.textContent = 'Download PDF';
    }
  });
});
