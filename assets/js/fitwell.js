/* Fitwell Studio — lightweight interactions (no dependencies) */
(function () {
  'use strict';
  var doc = document;
  var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function ready(fn) {
    if (doc.readyState !== 'loading') fn(); else doc.addEventListener('DOMContentLoaded', fn);
  }

  ready(function () {
    /* ---- Sticky header state ---- */
    var header = doc.querySelector('[data-header]');
    if (header) {
      var onScroll = function () { header.classList.toggle('is-scrolled', window.scrollY > 12); };
      onScroll();
      window.addEventListener('scroll', onScroll, { passive: true });
    }

    /* ---- Mobile menu ---- */
    var toggle = doc.querySelector('[data-menu-toggle]');
    var menu = doc.querySelector('[data-mobile-menu]');
    if (toggle && menu) {
      var closeTimer;
      var setOpen = function (open) {
        clearTimeout(closeTimer);
        toggle.setAttribute('aria-expanded', String(open));
        toggle.querySelector('.sr-only').textContent = open ? 'Close menu' : 'Open menu';
        if (open) {
          menu.hidden = false;
          requestAnimationFrame(function () { doc.body.classList.add('menu-open'); });
          var first = menu.querySelector('a');
          if (first) setTimeout(function () { first.focus({ preventScroll: true }); }, 200);
        } else {
          doc.body.classList.remove('menu-open');
          closeTimer = setTimeout(function () { menu.hidden = true; }, 450);
        }
      };
      toggle.addEventListener('click', function () { setOpen(toggle.getAttribute('aria-expanded') !== 'true'); });
      doc.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && doc.body.classList.contains('menu-open')) { setOpen(false); toggle.focus(); }
      });
      window.addEventListener('resize', function () { if (window.innerWidth > 1180 && doc.body.classList.contains('menu-open')) setOpen(false); });
    }

    /* ---- Reveal on scroll ---- */
    var revealEls = doc.querySelectorAll('[data-reveal]');
    if (!reduceMotion && 'IntersectionObserver' in window) {
      var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) { entry.target.classList.add('is-in'); io.unobserve(entry.target); }
        });
      }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });
      revealEls.forEach(function (el) { io.observe(el); });
    } else {
      revealEls.forEach(function (el) { el.classList.add('is-in'); });
    }

    /* ---- Toasts ---- */
    doc.querySelectorAll('[data-toast]').forEach(function (t, i) {
      var dismiss = function () {
        t.classList.add('is-leaving');
        setTimeout(function () { t.remove(); }, 400);
      };
      var btn = t.querySelector('[data-toast-close]');
      if (btn) btn.addEventListener('click', dismiss);
      var timer = setTimeout(dismiss, 6500 + i * 600);
      t.addEventListener('mouseenter', function () { clearTimeout(timer); });
    });

    /* ---- Prevent double submits + loading state ---- */
    doc.querySelectorAll('form[data-loading-form], form.form').forEach(function (form) {
      form.addEventListener('submit', function () {
        var btn = form.querySelector('button[type="submit"]');
        if (!btn) return;
        if (form.dataset.submitted) { return; }
        form.dataset.submitted = '1';
        btn.classList.add('is-loading');
        btn.setAttribute('aria-busy', 'true');
      });
    });

    /* ---- Two-step confirm (no browser dialogs) ---- */
    doc.querySelectorAll('form[data-confirm-form]').forEach(function (form) {
      var btn = form.querySelector('button[type="submit"]');
      if (!btn) return;
      var original = btn.innerHTML;
      var timer;
      form.addEventListener('submit', function (e) {
        if (btn.classList.contains('is-confirming')) {
          btn.classList.add('is-loading');
          return;
        }
        e.preventDefault();
        btn.classList.add('is-confirming');
        btn.textContent = btn.getAttribute('data-confirm-label') || 'Confirm';
        timer = setTimeout(function () { btn.classList.remove('is-confirming'); btn.innerHTML = original; }, 4000);
      });
    });

    /* ---- Password visibility ---- */
    doc.querySelectorAll('[data-pw-toggle]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var input = doc.getElementById(btn.getAttribute('aria-controls'));
        if (!input) return;
        var show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.setAttribute('aria-pressed', String(show));
        btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
      });
    });

    /* ---- Class filters (classes page) ---- */
    var filterWrap = doc.querySelector('[data-filters]');
    if (filterWrap) {
      var items = doc.querySelectorAll('[data-filter-item]');
      var empty = doc.querySelector('[data-filter-empty]');
      filterWrap.addEventListener('click', function (e) {
        var chip = e.target.closest('[data-filter]');
        if (!chip) return;
        e.preventDefault();
        var value = chip.getAttribute('data-filter');
        filterWrap.querySelectorAll('[data-filter]').forEach(function (c) { c.setAttribute('aria-pressed', String(c === chip)); });
        var shown = 0;
        items.forEach(function (item) {
          var match = value === 'all' || item.getAttribute('data-category') === value;
          item.hidden = !match;
          if (match) { shown++; item.classList.add('is-in'); }
        });
        if (empty) empty.hidden = shown > 0;
        if (history.replaceState) history.replaceState(null, '', value === 'all' ? location.pathname : '?category=' + value);
      });
    }

    /* ---- Timetable day filter ---- */
    var dayWrap = doc.querySelector('[data-days]');
    if (dayWrap) {
      var dayBlocks = doc.querySelectorAll('[data-day-block]');
      var noDay = doc.querySelector('[data-day-empty]');
      var select = function (value, focus) {
        dayWrap.querySelectorAll('[data-day]').forEach(function (b) { b.setAttribute('aria-pressed', String(b.getAttribute('data-day') === value)); });
        var shown = 0;
        dayBlocks.forEach(function (block) {
          var match = value === 'all' || block.getAttribute('data-day-block') === value;
          block.hidden = !match;
          if (match) shown++;
        });
        if (noDay) noDay.hidden = shown > 0;
        if (focus) {
          var active = dayWrap.querySelector('[aria-pressed="true"]');
          if (active && active.scrollIntoView) active.scrollIntoView({ inline: 'center', block: 'nearest', behavior: reduceMotion ? 'auto' : 'smooth' });
        }
      };
      dayWrap.addEventListener('click', function (e) {
        var b = e.target.closest('[data-day]');
        if (!b) return;
        select(b.getAttribute('data-day'), true);
      });
      // Deep link to a session keeps "All" so the target row is visible
      if (!location.hash) {
        var initial = dayWrap.getAttribute('data-initial');
        if (initial) select(initial, false);
      }
    }
  });
})();

/* ---- Admin: photo upload preview ---- */
(function () {
  var input = document.querySelector('[data-upload-input]');
  if (!input) return;
  input.addEventListener('change', function () {
    var file = input.files && input.files[0];
    if (!file) return;
    var preview = document.querySelector('[data-upload-preview]');
    var name = document.querySelector('[data-upload-name]');
    if (name) name.textContent = file.name + ' · ' + Math.round(file.size / 1024) + ' KB — will be saved when you submit.';
    if (preview && window.URL) {
      var img = document.createElement('img');
      img.alt = 'Selected photo preview';
      img.src = URL.createObjectURL(file);
      preview.innerHTML = '';
      preview.appendChild(img);
    }
  });
})();
