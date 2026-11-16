// AdopIT! - vanilla JS. Part 1 is the app behaviour (filters, delete dialog, validation).
// Part 2 is the motion layer: scroll reveals, scroll-linked effects and pointer/click effects.
(function () {
  'use strict';

  var root = document.documentElement;
  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var finePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;

  /* =====================================================================
     PART 1 - APP BEHAVIOUR
     ===================================================================== */

  // Greeting from the visitor's own clock
  var greet = document.querySelector('[data-greeting]');
  if (greet) {
    var h = new Date().getHours();
    greet.textContent = h < 12 ? 'Good morning' : h < 18 ? 'Good afternoon' : 'Good evening';
  }

  // Live search + dropdown filters.
  // [data-filter-scope] wraps a search input [data-filter-search], selects [data-filter-attr="species"],
  // rows [data-row] carrying data-search / data-species, a counter [data-filter-count]
  // and a "no match" row [data-filter-empty].
  document.querySelectorAll('[data-filter-scope]').forEach(function (scope) {
    var search = scope.querySelector('[data-filter-search]');
    var selects = scope.querySelectorAll('[data-filter-attr]');
    var rows = Array.prototype.slice.call(scope.querySelectorAll('[data-row]'));
    var count = scope.querySelector('[data-filter-count]');
    var empty = scope.querySelector('[data-filter-empty]');

    function apply() {
      var q = search ? search.value.trim().toLowerCase() : '';
      var shown = 0;
      rows.forEach(function (row) {
        var ok = !q || row.dataset.search.indexOf(q) !== -1;
        selects.forEach(function (sel) {
          if (sel.value && row.dataset[sel.dataset.filterAttr] !== sel.value) ok = false;
        });
        row.hidden = !ok;
        if (ok) { shown++; row.classList.add('is-in'); }   // filtered-in rows skip the scroll reveal
      });
      if (count) count.textContent = shown + ' of ' + rows.length + ' shown';
      if (empty) empty.hidden = shown !== 0 || rows.length === 0;
    }

    // Motion: with the View Transitions API the surviving rows glide to their new positions
    // (the browser snapshots before/after and tweens between them - a free FLIP animation).
    function applyAnimated() {
      if (reduceMotion || !document.startViewTransition || rows.length > 60) { apply(); return; }
      rows.forEach(function (row, i) { row.style.viewTransitionName = 'row-' + i; });
      root.classList.add('vt-filter');
      var done = function () {
        rows.forEach(function (row) { row.style.viewTransitionName = ''; });
        root.classList.remove('vt-filter');
      };
      try {
        document.startViewTransition(apply).finished.then(done, done);
      } catch (err) { apply(); done(); }
    }

    if (search) search.addEventListener('input', applyAnimated);
    selects.forEach(function (sel) { sel.addEventListener('change', applyAnimated); });
    if (count) count.textContent = rows.length + ' of ' + rows.length + ' shown';
  });

  // Delete confirmation modal
  var modal = document.getElementById('deleteModal');
  if (modal) {
    modal.addEventListener('show.bs.modal', function (ev) {
      var btn = ev.relatedTarget;
      if (!btn) return;
      document.getElementById('deleteForm').action = btn.dataset.deleteAction;
      document.getElementById('deleteId').value = btn.dataset.deleteId;
      document.getElementById('deleteLabel').textContent = btn.dataset.deleteLabel;
      document.getElementById('deleteNote').textContent = btn.dataset.deleteNote || 'This cannot be undone.';
    });
  }

  // Client-side validation (PHP validates again on the server)
  document.querySelectorAll('form.needs-validation').forEach(function (form) {
    form.addEventListener('submit', function (ev) {
      if (!form.checkValidity()) {
        ev.preventDefault();
        ev.stopPropagation();
        var first = form.querySelector(':invalid');
        if (first) first.focus();
      }
      form.classList.add('was-validated');
    });
  });

  // Show an option block only for one value of a select
  document.querySelectorAll('[data-toggle-target]').forEach(function (sel) {
    var box = document.querySelector(sel.dataset.toggleTarget);
    if (!box) return;
    function sync() { box.hidden = sel.value !== sel.dataset.toggleValue; }
    sel.addEventListener('change', sync);
    sync();
  });

  // Next due date can never be before the visit date
  var visit = document.getElementById('visit_date');
  var due = document.getElementById('next_due_date');
  if (visit && due) {
    var syncMin = function () { due.min = visit.value; };
    visit.addEventListener('change', syncMin);
    syncMin();
  }

  /* =====================================================================
     PART 2 - MOTION
     Everything below is decoration. With reduced motion on, we stop here
     and the CSS shows every element in its final state.
     ===================================================================== */
  if (reduceMotion) return;

  /* ---- 2a. Scroll reveals ------------------------------------------------
     How it works: elements start hidden/offset in CSS. An IntersectionObserver
     watches them and adds .is-in the first time they enter the viewport; the
     CSS transition does the actual animating. Elements that enter in the same
     frame get an increasing --i so they stagger. */

  // Split headlines into words so each word can slide up out of its own mask.
  document.querySelectorAll('[data-split]').forEach(function (el) {
    var text = el.textContent.replace(/\s+/g, ' ').trim();
    el.setAttribute('aria-label', text);
    el.textContent = '';
    text.split(' ').forEach(function (word, i) {
      var mask = document.createElement('span');
      var inner = document.createElement('span');
      mask.className = 'w';
      mask.setAttribute('aria-hidden', 'true');
      inner.className = 'wi';
      inner.style.setProperty('--i', i);
      inner.textContent = word;
      mask.appendChild(inner);
      el.appendChild(mask);
      el.appendChild(document.createTextNode(' '));
    });
  });

  // Table rows reveal one by one as they scroll in.
  document.querySelectorAll('tr[data-row]').forEach(function (row) { row.setAttribute('data-reveal', 'row'); });

  var revealer = new IntersectionObserver(function (entries) {
    var n = 0;
    entries.forEach(function (entry) {
      if (!entry.isIntersecting) return;
      var el = entry.target;
      if (!el.style.getPropertyValue('--i')) el.style.setProperty('--i', n++);
      el.classList.add('is-in');
      revealer.unobserve(el);
      if (el.hasAttribute('data-count')) countUp(el);
      // once revealed, hand the element back to its normal styles (hover transitions etc.)
      if (el.hasAttribute('data-reveal')) setTimeout(function () { el.removeAttribute('data-reveal'); }, 2200);
    });
  }, { threshold: 0.12, rootMargin: '0px 0px -6% 0px' });
  document.querySelectorAll('[data-reveal], [data-split], [data-count]').forEach(function (el) { revealer.observe(el); });

  // Numbers count up from zero when they scroll into view.
  function countUp(el) {
    var target = parseInt(el.dataset.count, 10) || 0;
    var start = performance.now();
    var duration = 1400;
    (function frame(now) {
      var t = Math.min((now - start) / duration, 1);
      el.textContent = Math.round(target * (1 - Math.pow(2, -10 * t)) / (1 - Math.pow(2, -10)));   // ease-out-expo
      if (t < 1) requestAnimationFrame(frame);
    })(start);
  }

  /* ---- 2b. Scroll-linked effects -----------------------------------------
     How it works: one requestAnimationFrame loop reads window.scrollY and
     turns it into numbers the page can use:
       --prog   0..1 page progress        -> the accent line at the top
       velocity px per frame, smoothed    -> the marquee speeds up / reverses
       parallax scrollY * speed           -> hero layers drift at different rates
       --p      0..1 "how far into view"  -> the footer wordmark rises */
  var nav = document.querySelector('.nav-wrap');
  var parallax = Array.prototype.slice.call(document.querySelectorAll('[data-parallax]'));
  var scrubbed = Array.prototype.slice.call(document.querySelectorAll('[data-scrub]'));
  var marquees = Array.prototype.slice.call(document.querySelectorAll('[data-marquee]')).map(function (m) {
    var track = m.firstElementChild;
    track.innerHTML += track.innerHTML;            // duplicate once so the loop is seamless
    return { track: track, x: 0, dir: -1 };
  });

  var lastY = window.scrollY;
  var velocity = 0;
  var drawnY = -1;

  // Cursor follower: a ring that trails the pointer and swells over anything clickable.
  var cursor = null;
  var mouse = { x: 0, y: 0 };
  var ring = { x: 0, y: 0 };
  if (finePointer) {
    cursor = document.createElement('div');
    cursor.className = 'cursor';
    cursor.setAttribute('aria-hidden', 'true');
    document.body.appendChild(cursor);
    window.addEventListener('pointermove', function (ev) {
      if (!cursor.classList.contains('is-on')) { ring.x = ev.clientX; ring.y = ev.clientY; cursor.classList.add('is-on'); }
      mouse.x = ev.clientX; mouse.y = ev.clientY;
      cursor.classList.toggle('is-hover', !!ev.target.closest('a, button, select, .form-check-input, [data-tilt]'));
    }, { passive: true });
    window.addEventListener('pointerdown', function () { cursor.classList.add('is-down'); });
    window.addEventListener('pointerup', function () { cursor.classList.remove('is-down'); });
    document.addEventListener('mouseleave', function () { cursor.classList.remove('is-on'); });
  }

  function loop() {
    var y = window.scrollY;
    velocity += ((y - lastY) - velocity) * 0.12;   // smooth the raw per-frame delta
    lastY = y;

    if (y !== drawnY) {
      drawnY = y;
      var max = root.scrollHeight - window.innerHeight;
      root.style.setProperty('--prog', max > 0 ? (y / max).toFixed(4) : 0);
      if (nav) nav.classList.toggle('is-scrolled', y > 30);
      if (y < 1400) {
        parallax.forEach(function (el) {
          el.style.transform = 'translate3d(0,' + (y * parseFloat(el.dataset.parallax)).toFixed(1) + 'px,0)';
        });
      }
      var vh = window.innerHeight;
      scrubbed.forEach(function (el) {
        var r = el.parentNode.getBoundingClientRect();
        el.style.setProperty('--p', Math.max(0, Math.min(1, (vh - r.top) / r.height)).toFixed(3));
      });
    }

    marquees.forEach(function (m) {
      if (Math.abs(velocity) > 0.5) m.dir = velocity > 0 ? -1 : 1;     // scroll up -> marquee runs backwards
      var half = m.track.scrollWidth / 2;
      m.x += m.dir * (0.6 + Math.min(Math.abs(velocity), 60) * 0.35);
      if (m.x <= -half) m.x += half;
      if (m.x > 0) m.x -= half;
      m.track.style.transform = 'translate3d(' + m.x.toFixed(1) + 'px,0,0)';
    });

    if (cursor) {
      ring.x += (mouse.x - ring.x) * 0.18;         // lerp: move 18% of the remaining distance each frame
      ring.y += (mouse.y - ring.y) * 0.18;
      cursor.style.transform = 'translate3d(' + ring.x.toFixed(1) + 'px,' + ring.y.toFixed(1) + 'px,0)';
    }
    requestAnimationFrame(loop);
  }
  requestAnimationFrame(loop);

  /* ---- 2c. Click and pointer effects ------------------------------------- */

  // Ripple: a dot is dropped where the button was pressed and scales out (CSS @keyframes ripple).
  document.addEventListener('pointerdown', function (ev) {
    var btn = ev.target.closest('.btn');
    if (!btn) return;
    var r = btn.getBoundingClientRect();
    var dot = document.createElement('span');
    dot.className = 'ripple';
    dot.style.left = (ev.clientX - r.left) + 'px';
    dot.style.top = (ev.clientY - r.top) + 'px';
    btn.appendChild(dot);
    dot.addEventListener('animationend', function () { dot.remove(); });
  });

  if (finePointer) {
    // Magnetic buttons: the button leans toward the pointer while it is over it.
    document.querySelectorAll('.btn-cta, [data-magnetic]').forEach(function (el) {
      el.addEventListener('pointermove', function (ev) {
        var r = el.getBoundingClientRect();
        var dx = ev.clientX - (r.left + r.width / 2);
        var dy = ev.clientY - (r.top + r.height / 2);
        el.style.transform = 'translate(' + (dx * 0.25).toFixed(1) + 'px,' + (dy * 0.35).toFixed(1) + 'px)';
      });
      el.addEventListener('pointerleave', function () { el.style.transform = ''; });
    });

    // Tilt: the card rotates in 3D toward the pointer.
    document.querySelectorAll('[data-tilt]').forEach(function (el) {
      el.addEventListener('pointermove', function (ev) {
        var r = el.getBoundingClientRect();
        var px = (ev.clientX - r.left) / r.width - 0.5;
        var py = (ev.clientY - r.top) / r.height - 0.5;
        el.classList.add('is-tilting');
        el.style.transform = 'perspective(900px) rotateX(' + (-py * 9).toFixed(2) + 'deg) rotateY(' + (px * 11).toFixed(2) + 'deg) translateZ(0)';
      });
      el.addEventListener('pointerleave', function () {
        el.classList.remove('is-tilting');
        el.style.transform = '';
      });
    });
  }

  /* ---- 2d. Page transitions ----------------------------------------------
     The wipe between pages is pure CSS (@view-transition in app.css). The one
     thing JS adds: when a pet link is clicked, tag THAT pet's avatar with the
     same view-transition-name the profile page uses, so the browser morphs the
     small table avatar into the big profile avatar. */
  document.addEventListener('click', function (ev) {
    var link = ev.target.closest('a[href*="view.php?id="]');
    if (!link) return;
    var holder = link.closest('tr, .spotlight');
    var avatar = holder && holder.querySelector('.avatar');
    if (avatar) avatar.style.viewTransitionName = 'pet-avatar';
  });
  // Same morph in reverse when coming back from a profile to a list.
  window.addEventListener('pagereveal', function () {
    var from = window.navigation && navigation.activation && navigation.activation.from;
    var match = from && /view\.php\?id=(\d+)/.exec(from.url || '');
    if (!match) return;
    var link = document.querySelector('tr a[href="view.php?id=' + match[1] + '"]');
    var avatar = link && link.closest('tr').querySelector('.avatar');
    if (avatar) {
      avatar.style.viewTransitionName = 'pet-avatar';
      setTimeout(function () { avatar.style.viewTransitionName = ''; }, 900);
    }
  });
})();
