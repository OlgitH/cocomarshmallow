(function () {
  var toggle = document.querySelector('.nav-toggle');
  var nav = document.getElementById('primary-nav');
  if (!toggle || !nav) return;

  function set(open) {
    toggle.setAttribute('aria-expanded', String(open));
    document.body.classList.toggle('nav-open', open);
  }

  toggle.addEventListener('click', function () {
    set(toggle.getAttribute('aria-expanded') !== 'true');
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') {
      set(false);
      toggle.focus();
    }
  });

  // Reset when growing past the mobile breakpoint.
  window.matchMedia('(min-width: 56em)').addEventListener('change', function (e) {
    if (e.matches) set(false);
  });
})();

// Keep the desktop menu on one line: shrink its text only when it would otherwise not fit.
(function () {
  var inner = document.querySelector('.site-header__inner');
  var nav = document.getElementById('primary-nav');
  if (!inner || !nav) return;
  var desktop = window.matchMedia('(min-width: 56em)');

  function fit() {
    var scale = 1;
    nav.style.removeProperty('--nav-scale');
    if (!desktop.matches) return;
    while (inner.scrollWidth > inner.clientWidth + 1 && scale > 0.55) {
      scale = Math.round((scale - 0.02) * 100) / 100;
      nav.style.setProperty('--nav-scale', scale);
    }
  }

  fit();
  window.addEventListener('resize', fit);
  window.addEventListener('load', fit);
  if (document.fonts && document.fonts.ready) document.fonts.ready.then(fit);
})();

// Accessible tabs (e.g. Activities / Colouring sheets). Without JS every panel is shown under its own heading.
(function () {
  document.querySelectorAll('[data-tabs]').forEach(function (root) {
    var list = root.querySelector('[role="tablist"]');
    if (!list) return;
    var tabs = Array.prototype.slice.call(list.querySelectorAll('[role="tab"]'));
    var panels = tabs.map(function (t) { return document.getElementById(t.getAttribute('aria-controls')); });
    list.hidden = false;
    root.classList.add('tabs--js');

    function select(i, focus) {
      tabs.forEach(function (t, n) {
        var on = n === i;
        t.setAttribute('aria-selected', String(on));
        t.tabIndex = on ? 0 : -1;
        panels[n].hidden = !on;
      });
      if (focus) tabs[i].focus();
    }

    tabs.forEach(function (t, i) {
      t.addEventListener('click', function () {
        select(i);
        if (history.replaceState) history.replaceState(null, '', '#' + t.id.replace('tab-', ''));
      });
      t.addEventListener('keydown', function (e) {
        var n = null;
        if (e.key === 'ArrowRight') n = (i + 1) % tabs.length;
        else if (e.key === 'ArrowLeft') n = (i - 1 + tabs.length) % tabs.length;
        else if (e.key === 'Home') n = 0;
        else if (e.key === 'End') n = tabs.length - 1;
        if (n !== null) { e.preventDefault(); select(n, true); }
      });
    });

    var start = 0;
    var hash = location.hash.replace('#', '');
    tabs.forEach(function (t, i) { if (t.id === 'tab-' + hash) start = i; });
    select(start);
  });
})();

// Art gallery: open artwork larger in a dialog.
(function () {
  var dlg = document.querySelector('.art-dialog');
  if (!dlg || typeof dlg.showModal !== 'function') return;
  var img = dlg.querySelector('img');
  document.querySelectorAll('[data-art-full]').forEach(function (a) {
    a.addEventListener('click', function (e) {
      e.preventDefault();
      img.src = a.getAttribute('data-art-full');
      img.alt = a.getAttribute('data-art-alt') || '';
      dlg.showModal();
    });
  });
  dlg.addEventListener('click', function (e) { if (e.target === dlg) dlg.close(); });
})();

/* Homepage carousel: scroll-snap track with previous/next buttons and dots (no auto-play). */
(function () {
  document.querySelectorAll('[data-carousel]').forEach(function (root) {
    var track = root.querySelector('.carousel__track');
    var slides = root.querySelectorAll('.carousel__slide');
    var dots = root.querySelectorAll('[data-carousel-dot]');
    if (!track || slides.length < 2) return;

    function index() { return Math.round(track.scrollLeft / track.clientWidth); }
    function go(i) {
      i = Math.max(0, Math.min(slides.length - 1, i));
      track.scrollTo({ left: i * track.clientWidth });
    }
    function sync() {
      var i = index();
      dots.forEach(function (d, n) { if (n === i) d.setAttribute('aria-current', 'true'); else d.removeAttribute('aria-current'); });
    }
    var prev = root.querySelector('[data-carousel-prev]');
    var next = root.querySelector('[data-carousel-next]');
    if (prev) prev.addEventListener('click', function () { go(index() === 0 ? slides.length - 1 : index() - 1); });
    if (next) next.addEventListener('click', function () { go(index() === slides.length - 1 ? 0 : index() + 1); });
    dots.forEach(function (d) { d.addEventListener('click', function () { go(Number(d.getAttribute('data-carousel-dot'))); }); });
    track.addEventListener('scroll', function () { window.requestAnimationFrame(sync); }, { passive: true });
    track.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowRight') { go(index() + 1); }
      if (e.key === 'ArrowLeft') { go(index() - 1); }
    });
    window.addEventListener('resize', function () { go(index()); });
  });
})();
