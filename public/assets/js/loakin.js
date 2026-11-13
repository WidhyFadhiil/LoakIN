/* LoakIN - JavaScript ringan (tanpa library) */
(function () {
  // Menu samping (drawer kiri)
  var drawer = document.getElementById('drawer');
  var scrim = document.getElementById('scrim');
  var openBtn = document.getElementById('menu-open');
  var closeBtn = document.getElementById('menu-close');
  function setDrawer(open) {
    if (!drawer) { return; }
    drawer.classList.toggle('open', open);
    drawer.setAttribute('aria-hidden', open ? 'false' : 'true');
    if (open) { drawer.removeAttribute('inert'); } else { drawer.setAttribute('inert', ''); }
    scrim.hidden = !open;
    document.body.classList.toggle('no-scroll', open);
    openBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    (open ? closeBtn : openBtn).focus();
  }
  if (drawer && openBtn) {
    openBtn.addEventListener('click', function () { setDrawer(true); });
    closeBtn.addEventListener('click', function () { setDrawer(false); });
    scrim.addEventListener('click', function () { setDrawer(false); });
  }

  // Dropdown akun: tutup saat klik di luar atau tekan Esc
  var userMenu = document.querySelector('.user-menu');
  document.addEventListener('click', function (e) {
    if (userMenu && userMenu.open && !userMenu.contains(e.target)) { userMenu.open = false; }
  });
  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape') { return; }
    if (drawer && drawer.classList.contains('open')) { setDrawer(false); }
    if (userMenu && userMenu.open) { userMenu.open = false; userMenu.querySelector('summary').focus(); }
  });

  // Konfirmasi untuk tombol berbahaya: <form data-confirm="Yakin?">
  document.addEventListener('submit', function (e) {
    var msg = e.target.getAttribute('data-confirm');
    if (msg && !window.confirm(msg)) { e.preventDefault(); }
  });

  // Galeri foto di halaman detail
  document.querySelectorAll('.gallery').forEach(function (g) {
    var main = g.querySelector('.main img');
    g.querySelectorAll('.thumbs button').forEach(function (b) {
      b.addEventListener('click', function () {
        main.src = b.getAttribute('data-src');
        g.querySelectorAll('.thumbs button').forEach(function (x) { x.setAttribute('aria-current', 'false'); });
        b.setAttribute('aria-current', 'true');
      });
    });
  });

  // Form posting: tampilkan kolom harga hanya jika berbayar / bisa nego
  var priceType = document.querySelectorAll('input[name="price_type"]');
  var priceField = document.getElementById('price-field');
  function syncPrice() {
    if (!priceField) { return; }
    var v = document.querySelector('input[name="price_type"]:checked');
    priceField.hidden = !v || v.value === 'gratis';
  }
  priceType.forEach(function (r) { r.addEventListener('change', syncPrice); });
  syncPrice();

  // Pratinjau foto sebelum diunggah
  var fileInput = document.getElementById('images');
  var preview = document.getElementById('preview');
  if (fileInput && preview) {
    fileInput.addEventListener('change', function () {
      preview.innerHTML = '';
      Array.prototype.slice.call(fileInput.files, 0, 5).forEach(function (f) {
        if (!/^image\//.test(f.type)) { return; }
        var img = document.createElement('img');
        img.alt = f.name;
        img.style.cssText = 'width:84px;height:64px;object-fit:cover;border-radius:8px;border:1px solid #d5dbd2';
        img.src = URL.createObjectURL(f);
        preview.appendChild(img);
      });
    });
  }

  // Pesan: scroll ke bawah utas percakapan
  var thread = document.querySelector('.thread');
  if (thread) { thread.scrollTop = thread.scrollHeight; }
})();
