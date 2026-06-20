document.addEventListener('DOMContentLoaded', () => {
  const voucher = document.getElementById('voucherDocument');
  const printButton = document.getElementById('voucherPrint');
  const pdfButton = document.getElementById('voucherPdf');

  const waitForDocumentAssets = async () => {
    if (document.fonts?.ready) await document.fonts.ready;
    await Promise.all(Array.from(voucher?.querySelectorAll('img') || []).map(async (image) => {
      if (image.complete && image.naturalWidth > 0) return;
      try {
        await image.decode();
      } catch (error) {
        await new Promise(resolve => {
          image.addEventListener('load', resolve, { once: true });
          image.addEventListener('error', resolve, { once: true });
        });
      }
    }));
  };

  printButton?.addEventListener('click', async () => {
    printButton.disabled = true;
    await waitForDocumentAssets();
    printButton.disabled = false;
    window.print();
  });

  if (!voucher || !pdfButton) return;

  pdfButton.addEventListener('click', async () => {
    if (typeof window.html2pdf !== 'function') {
      window.alert('PDF generation is unavailable. Please use Print Voucher instead.');
      return;
    }

    const ticketId = voucher.dataset.ticketId || 'clicket-ticket';
    pdfButton.disabled = true;
    pdfButton.textContent = 'Generating PDF...';

    try {
      await waitForDocumentAssets();
      await window.html2pdf().set({
        margin: [8, 8, 8, 8],
        filename: `${ticketId.replace(/[^a-z0-9-]/gi, '_')}-voucher.pdf`,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: {
          scale: 2,
          useCORS: true,
          backgroundColor: '#ffffff',
          scrollX: 0,
          scrollY: 0,
          windowWidth: Math.max(voucher.scrollWidth, voucher.offsetWidth),
          windowHeight: Math.max(voucher.scrollHeight, voucher.offsetHeight),
          imageTimeout: 0,
          logging: false,
        },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
        pagebreak: { mode: ['avoid-all', 'css', 'legacy'], avoid: ['tr', '.voucher__claim-notice', '.voucher__redemption', '.voucher__footer'] },
      }).from(voucher).save();
    } catch (error) {
      window.alert('The PDF could not be generated. Please use Print Voucher instead.');
    } finally {
      pdfButton.disabled = false;
      pdfButton.textContent = 'Download PDF';
    }
  });
});
