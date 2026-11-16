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
