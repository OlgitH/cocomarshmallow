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
