// Interaksi ringan: modal, konfirmasi hapus, tutup alert, auto-submit filter.
(function () {
  function openModal(el) {
    if (!el) return;
    el.classList.add('open');
    document.body.classList.add('modal-open');
    var first = el.querySelector('input:not([type=hidden]), select, textarea');
    if (first) setTimeout(function () { first.focus(); }, 50);
  }
  function closeModal(el) {
    el.classList.remove('open');
    if (!document.querySelector('.overlay.open')) document.body.classList.remove('modal-open');
  }

  document.addEventListener('click', function (e) {
    var opener = e.target.closest('[data-open]');
    if (opener) { e.preventDefault(); openModal(document.getElementById(opener.getAttribute('data-open'))); return; }

    var closer = e.target.closest('[data-close]');
    if (closer) { closeModal(closer.closest('.overlay')); return; }

    if (e.target.classList && e.target.classList.contains('overlay')) { closeModal(e.target); return; }

    var dismiss = e.target.closest('[data-dismiss]');
    if (dismiss) dismiss.closest('[data-alert]').remove();
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') document.querySelectorAll('.overlay.open').forEach(closeModal);
  });

  // Konfirmasi hapus memakai modal #confirmModal di layout
  var pending = null;
  document.addEventListener('submit', function (e) {
    var form = e.target;
    if (!form.hasAttribute('data-confirm') || form.dataset.confirmed) return;
    e.preventDefault();
    var modal = document.getElementById('confirmModal');
    if (!modal) { if (confirm(form.getAttribute('data-confirm'))) { form.dataset.confirmed = 1; form.submit(); } return; }
    modal.querySelector('[data-confirm-text]').textContent = form.getAttribute('data-confirm');
    pending = form;
    openModal(modal);
  });
  document.addEventListener('click', function (e) {
    if (e.target.closest('[data-confirm-ok]') && pending) {
      pending.dataset.confirmed = 1;
      pending.submit();
    }
  });

  // Filter otomatis terkirim saat select berubah
  document.addEventListener('change', function (e) {
    if (e.target.matches('[data-autosubmit]')) e.target.form.submit();
  });

  // Modal yang harus langsung terbuka (mis. ?view=ID atau form gagal validasi)
  document.querySelectorAll('[data-autoopen]').forEach(openModal);

  // Alert sukses hilang otomatis
  setTimeout(function () {
    document.querySelectorAll('.alert-success[data-alert]').forEach(function (a) { a.remove(); });
  }, 6000);
})();
