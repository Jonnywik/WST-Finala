// AdopIT! - vanilla JS, no libraries.
// PART 1 is the app behaviour and always runs.
// PART 2 is the motion layer: it only runs when <html> has the "motion" class
// (set in <head> unless the OS asks for reduced motion).
(function () {
  'use strict';

  var root = document.documentElement;
  var motion = root.classList.contains('motion');
  var finePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
  function clamp(v, a, b) { return v < a ? a : v > b ? b : v; }
  function lerp(a, b, t) { return a + (b - a) * t; }

  /* =====================================================================
     PART 1 - APP BEHAVIOUR
     ===================================================================== */

  try { sessionStorage.setItem('adopit-intro', '1'); } catch (e) {}

  var greet = document.querySelector('[data-greeting]');
  if (greet) {
    var h = new Date().getHours();
    greet.textContent = h < 12 ? 'Good morning' : h < 18 ? 'Good afternoon' : 'Good evening';
  }

  // Live search + dropdown filters (see data-filter-* attributes in the pages).
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
        if (ok) { shown++; row.classList.add('is-in'); }
      });
      if (count) count.textContent = shown + ' of ' + rows.length + ' shown';
      if (empty) empty.hidden = shown !== 0 || rows.length === 0;
    }

    // With motion on, the surviving rows glide to their new places (View Transitions API).
    function applyAnimated() {
      if (!motion || !document.startViewTransition || rows.length > 60) { apply(); return; }
      rows.forEach(function (row, i) { row.style.viewTransitionName = 'row-' + i; });
      root.classList.add('vt-filter');
      var done = function () {
        rows.forEach(function (row) { row.style.viewTransitionName = ''; });
        root.classList.remove('vt-filter');
      };
      try { document.startViewTransition(apply).finished.then(done, done); } catch (err) { apply(); done(); }
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

  // Table rows that belong to a pet open that pet's profile when clicked anywhere.
  document.querySelectorAll('tr[data-row]').forEach(function (row) {
    var link = row.querySelector('.pet-cell a');
    if (!link) return;
    row.classList.add('row-link');
    row.setAttribute('data-cursor', 'View');
    row.addEventListener('click', function (ev) {
      if (ev.target.closest('a, button, input, select, label')) return;
      if (String(window.getSelection ? window.getSelection() : '')) return;   // let people select text
      link.click();
    });
  });

  // Success toasts close themselves when their timer bar runs out (motion only; hover pauses it).
  document.querySelectorAll('.flash-timer').forEach(function (bar) {
    bar.addEventListener('animationend', function () {
      var alert = bar.closest('.alert');
      if (alert && window.bootstrap) window.bootstrap.Alert.getOrCreateInstance(alert).close();
    });
  });

  // Scroll progress (top line + back-to-top ring) and the back-to-top button.
  var toTop = document.querySelector('[data-to-top]');
  if (toTop) toTop.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: motion ? 'smooth' : 'auto' }); });
  var progQueued = false;
  function updateProgress() {
    progQueued = false;
    var max = root.scrollHeight - window.innerHeight;
    root.style.setProperty('--prog', max > 0 ? (window.scrollY / max).toFixed(4) : 0);
    if (toTop) toTop.classList.toggle('is-shown', window.scrollY > 500);
  }
  window.addEventListener('scroll', function () { if (!progQueued) { progQueued = true; requestAnimationFrame(updateProgress); } }, { passive: true });
  updateProgress();

  /* =====================================================================
     PART 2 - MOTION
     ===================================================================== */
  if (!motion) return;

  /* ---- 2a. Loading screen counter (index only) -------------------------- */
  // Safety net: the loading screen hides itself with a CSS animation; this removes it even if that never runs.
  setTimeout(function () { root.classList.add('intro-seen'); }, 4500);
  var introCount = document.querySelector('[data-intro-count]');
  if (introCount && getComputedStyle(introCount.closest('.intro')).display !== 'none') {
    var t0 = performance.now();
    (function tick(now) {
      var t = clamp((now - t0) / 1500, 0, 1);
      introCount.textContent = Math.round(100 * (1 - Math.pow(1 - t, 3)));
      if (t < 1) requestAnimationFrame(tick);
    })(t0);
  }

  /* ---- 2b. Scroll reveals -------------------------------------------------
     Elements start hidden in CSS. An IntersectionObserver adds .is-in the first time
     each one enters the viewport and the CSS transition plays. */
  function split(el, mode) {
    var text = el.textContent.replace(/\s+/g, ' ').trim();
    var k = 0;
    el.setAttribute('aria-label', text);
    el.textContent = '';
    text.split(' ').forEach(function (word, wi) {
      var mask = document.createElement('span');
      mask.className = 'w';
      mask.setAttribute('aria-hidden', 'true');
      if (mode === 'chars') {
        Array.from(word).forEach(function (ch) {
          var c = document.createElement('span');
          c.className = 'ch';
          c.style.setProperty('--i', k++);
          c.textContent = ch;
          mask.appendChild(c);
        });
      } else {
        var inner = document.createElement('span');
        inner.className = 'wi';
        inner.style.setProperty('--i', wi);
        inner.textContent = word;
        mask.appendChild(inner);
      }
      el.appendChild(mask);
      el.appendChild(document.createTextNode(' '));
    });
  }
  document.querySelectorAll('[data-split]').forEach(function (el) { split(el, 'words'); });
  document.querySelectorAll('[data-chars]').forEach(function (el) { split(el, 'chars'); });
  document.querySelectorAll('tr[data-row]').forEach(function (row) { row.setAttribute('data-reveal', 'row'); });

  var revealer = new IntersectionObserver(function (entries) {
    var n = 0;
    entries.forEach(function (entry) {
      if (!entry.isIntersecting) return;
      var el = entry.target;
      if (!el.style.getPropertyValue('--i')) el.style.setProperty('--i', n++);
      el.classList.add('is-in');
      revealer.unobserve(el);
      var num = el.matches('[data-count]') ? el : el.querySelector('[data-count]');
      if (num) countUp(num);
      // hand the element back to its normal styles once the reveal has played
      if (el.hasAttribute('data-reveal')) setTimeout(function () { el.removeAttribute('data-reveal'); }, 2200);
    });
  }, { threshold: 0.12, rootMargin: '0px 0px -6% 0px' });
  document.querySelectorAll('[data-reveal], [data-split], [data-chars]').forEach(function (el) { revealer.observe(el); });

  function countUp(el) {
    var target = parseInt(el.dataset.count, 10) || 0;
    var start = performance.now();
    (function frame(now) {
      var t = Math.min((now - start) / 1500, 1);
      el.textContent = Math.round(target * (1 - Math.pow(2, -10 * t)) / (1 - Math.pow(2, -10)));
      if (t < 1) requestAnimationFrame(frame);
    })(start);
  }

  /* ---- 2c. Page colour follows the section in the middle of the screen --- */
  var bgObserver = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (!entry.isIntersecting) return;
      root.style.setProperty('--bg', entry.target.dataset.bg);
      root.style.setProperty('--fg', entry.target.dataset.fg);
    });
  }, { rootMargin: '-50% 0px -50% 0px' });
  document.querySelectorAll('[data-bg]').forEach(function (el) { bgObserver.observe(el); });

  /* ---- 2d. The scroll loop ------------------------------------------------
     One requestAnimationFrame loop reads the scroll position once per frame and
     turns it into transforms: hero shrink, word-by-word statement, sideways gallery,
     marquee speed, velocity skew, footer letters, nav hide/show, cursor and preview.
     It reads every position first, then writes, so the browser lays out once per frame. */
  var nav = document.querySelector('.nav-wrap');
  var navToggle = document.getElementById('mainNav');
  var stage = document.querySelector('[data-stage]');
  var hero = document.querySelector('[data-hero]');
  var heroTitle = document.querySelector('[data-hero-title]');
  var floaters = Array.prototype.slice.call(document.querySelectorAll('.floater')).map(function (el) {
    return { el: el, depth: parseFloat(el.dataset.depth) || 1, x: 0, y: 0 };
  });
  var badge = document.querySelector('[data-spin]');
  var statement = document.querySelector('[data-statement]');
  var words = statement ? Array.prototype.slice.call(statement.querySelectorAll('.sw')) : [];
  var lit = -1;
  var hs = document.querySelector('[data-hs]');
  var hsTrack = hs && hs.querySelector('[data-hs-track]');
  var hsIndex = hs && hs.querySelector('[data-hs-index]');
  var hsBar = hs && hs.querySelector('.hs-bar');
  var hsCards = hsTrack ? hsTrack.querySelectorAll('.pet-card:not(.card-new)').length : 0;
  var hsDist = 0;
  var skewers = Array.prototype.slice.call(document.querySelectorAll('[data-skew]'));
  var marquees = Array.prototype.slice.call(document.querySelectorAll('[data-marquee]')).map(function (m) {
    var track = m.firstElementChild;
    track.innerHTML += track.innerHTML;          // two copies so the loop is seamless
    return { track: track, x: 0, base: parseFloat(m.dataset.marquee) || -1, dir: 1 };
  });
  var footer = document.querySelector('.site-footer');
  var wmChars = Array.prototype.slice.call(document.querySelectorAll('.wm-c'));

  function measure() {
    if (!hs || !hsTrack) return;
    var on = window.innerWidth >= 768;
    hs.classList.toggle('hs-on', on);
    if (on) {
      hsDist = Math.max(0, hsTrack.scrollWidth - window.innerWidth);
      hs.style.height = (window.innerHeight + hsDist) + 'px';
    } else {
      hsDist = 0;
      hs.style.height = '';
      hsTrack.style.transform = '';
    }
  }
  measure();
  window.addEventListener('resize', measure);
  window.addEventListener('load', measure);
  if (document.fonts && document.fonts.ready) document.fonts.ready.then(measure);

  var lastY = window.scrollY;
  var vel = 0;
  var navHidden = false;
  var angle = 0;
  var spinDir = 1;
  var mouse = { x: window.innerWidth / 2, y: window.innerHeight / 2, px: 0 };
  var ring = { x: mouse.x, y: mouse.y };
  var peekPos = { x: mouse.x, y: mouse.y, r: 0 };
  var cursor = null;
  var dot = null;
  var cursorLabel = null;
  var peek = document.querySelector('.peek');
  var peekOn = false;

  // Ring style depends on what is under the pointer: a label for [data-cursor] areas, bigger over anything clickable.
  function cursorTarget(t) {
    if (!cursor || !t || !t.closest) return;
    var labelled = t.closest('[data-cursor]');
    var clickable = t.closest('a, button, select, label, .form-check-input, [data-tilt]');
    if (labelled && clickable && clickable !== labelled && labelled.contains(clickable)) labelled = null;  // a real button inside a labelled area wins
    cursor.classList.toggle('is-label', !!labelled);
    cursor.classList.toggle('is-hover', !labelled && !!clickable);
    if (labelled) cursorLabel.textContent = labelled.getAttribute('data-cursor');
  }

  function frame() {
    var vw = window.innerWidth;
    var vh = window.innerHeight;
    var y = window.scrollY;
    var dy = y - lastY;
    lastY = y;
    vel = lerp(vel, dy, 0.14);
    var skew = clamp(vel * 0.09, -6, 6);

    // ---- reads ----
    var stageRect = stage ? stage.getBoundingClientRect() : null;
    var stRect = statement ? statement.getBoundingClientRect() : null;
    var hsRect = hs && hsDist ? hs.getBoundingClientRect() : null;
    var ftRect = footer && wmChars.length ? footer.getBoundingClientRect() : null;

    // ---- writes ----
    if (nav) {
      var menuOpen = navToggle && navToggle.classList.contains('show');
      if (y < 120 || dy < -3 || menuOpen) navHidden = false;
      else if (dy > 4) navHidden = true;
      nav.classList.toggle('is-hidden', navHidden);
      nav.classList.toggle('is-scrolled', y > 30);
    }

    // Hero: shrinks and rounds off while its stage scrolls past; avatars drift with scroll and pointer.
    if (stageRect && hero && stageRect.bottom > 0) {
      var span = stageRect.height - vh;
      var hp = span > 40 ? clamp(-stageRect.top / span, 0, 1) : 0;
      hero.style.transform = 'scale(' + (1 - hp * 0.12).toFixed(4) + ')';
      hero.style.borderRadius = (30 + hp * 34).toFixed(1) + 'px';
      if (heroTitle) heroTitle.style.transform = 'translate3d(0,' + (-hp * 14).toFixed(2) + 'vh,0)';
      var mx = mouse.x / vw - 0.5;
      var my = mouse.y / vh - 0.5;
      floaters.forEach(function (f) {
        f.x = lerp(f.x, mx * f.depth * 70, 0.07);
        f.y = lerp(f.y, my * f.depth * 70 - hp * f.depth * 260, 0.07);
        f.el.style.transform = 'translate3d(' + f.x.toFixed(1) + 'px,' + f.y.toFixed(1) + 'px,0)';
      });
    }

    // Badge spins steadily, faster while scrolling, and backwards when scrolling up.
    if (badge) {
      if (Math.abs(vel) > 0.5) spinDir = vel > 0 ? 1 : -1;
      angle += spinDir * (0.35 + Math.min(Math.abs(vel), 80) * 0.3);
      badge.style.transform = 'rotate(' + (angle % 360).toFixed(2) + 'deg)';
    }

    // Statement: words light up in reading order as it moves through the screen.
    if (stRect && words.length) {
      var sp = clamp((vh * 0.85 - stRect.top) / (stRect.height + vh * 0.35), 0, 1);
      var count = Math.round(sp * words.length);
      if (count !== lit) {
        lit = count;
        words.forEach(function (w, i) { w.classList.toggle('on', i < count); });
      }
    }

    // Gallery: the section is as tall as the cards are wide; scrolling down slides the track left.
    // On phones the row is a normal swipeable strip; the counter then follows its scroll position.
    var gp = -1;
    if (hsRect) {
      gp = clamp(-hsRect.top / hsDist, 0, 1);
      hsTrack.style.transform = 'translate3d(' + (-gp * hsDist).toFixed(1) + 'px,0,0) skewX(' + (-skew).toFixed(2) + 'deg)';
    } else if (hsTrack && !hsDist && hsTrack.scrollWidth > hsTrack.clientWidth) {
      gp = clamp(hsTrack.scrollLeft / (hsTrack.scrollWidth - hsTrack.clientWidth), 0, 1);
    }
    if (gp >= 0) {
      if (hsBar) hsBar.style.setProperty('--hp', gp.toFixed(4));
      if (hsIndex && hsCards) {
        var idx = String(Math.min(hsCards, Math.floor(gp * hsCards) + 1));
        if (idx.length < 2) idx = '0' + idx;
        if (hsIndex.textContent !== idx) hsIndex.textContent = idx;
      }
    }

    // Marquees: base speed plus scroll speed; scrolling up reverses them.
    marquees.forEach(function (m) {
      if (Math.abs(vel) > 0.5) m.dir = vel > 0 ? 1 : -1;
      var half = m.track.scrollWidth / 2;
      m.x += m.base * m.dir * (0.7 + Math.min(Math.abs(vel), 60) * 0.4);
      if (m.x <= -half) m.x += half;
      if (m.x > 0) m.x -= half;
      m.track.style.transform = 'translate3d(' + m.x.toFixed(1) + 'px,0,0) skewX(' + (-skew).toFixed(2) + 'deg)';
    });

    // Headings lean with scroll speed.
    skewers.forEach(function (el) { el.style.transform = 'skewY(' + (skew * 0.5).toFixed(2) + 'deg)'; });

    // Footer wordmark: letters rise one after another as the footer scrolls in.
    if (ftRect && ftRect.top < vh) {
      var fp = clamp((vh - ftRect.top) / (ftRect.height * 0.9), 0, 1);
      wmChars.forEach(function (c, i) {
        var cp = clamp(fp * 1.7 - i * 0.09, 0, 1);
        c.style.transform = 'translate3d(0,' + ((1 - cp) * 105).toFixed(1) + '%,0)';
      });
    }

    // Cursor ring trails the pointer: each frame it moves 18% of the remaining distance.
    if (cursor) {
      if (dy !== 0 && cursor.classList.contains('is-on')) cursorTarget(document.elementFromPoint(mouse.x, mouse.y));
      ring.x = lerp(ring.x, mouse.x, 0.18);
      ring.y = lerp(ring.y, mouse.y, 0.18);
      cursor.style.transform = 'translate3d(' + ring.x.toFixed(1) + 'px,' + ring.y.toFixed(1) + 'px,0)';
      dot.style.transform = 'translate3d(' + mouse.x + 'px,' + mouse.y + 'px,0)';
    }

    // Row preview follows the pointer and swings with sideways pointer speed.
    if (peek && peekOn) {
      var flip = mouse.x > vw - 270;
      var tx = flip ? mouse.x - 236 : mouse.x + 26;
      var ty = clamp(mouse.y - 90, 12, vh - 250);
      peekPos.x = lerp(peekPos.x, tx, 0.16);
      peekPos.y = lerp(peekPos.y, ty, 0.16);
      peekPos.r = lerp(peekPos.r, clamp(mouse.px * 0.6, -14, 14), 0.1);
      peek.style.transform = 'translate3d(' + peekPos.x.toFixed(1) + 'px,' + peekPos.y.toFixed(1) + 'px,0) rotate(' + peekPos.r.toFixed(2) + 'deg)';
    }
    mouse.px *= 0.85;

    requestAnimationFrame(frame);
  }

  /* ---- 2e. Pointer: cursor, preview, spotlight ---------------------------- */
  window.addEventListener('pointermove', function (ev) {
    mouse.px = ev.clientX - mouse.x;
    mouse.x = ev.clientX;
    mouse.y = ev.clientY;
  }, { passive: true });

  if (finePointer) {
    cursor = document.createElement('div');
    cursor.className = 'cursor';
    cursor.setAttribute('aria-hidden', 'true');
    cursorLabel = document.createElement('span');
    cursorLabel.className = 'cursor-label';
    cursor.appendChild(cursorLabel);
    dot = document.createElement('div');
    dot.className = 'cursor-dot';
    dot.setAttribute('aria-hidden', 'true');
    document.body.appendChild(cursor);
    document.body.appendChild(dot);

    window.addEventListener('pointermove', function (ev) {
      if (!cursor.classList.contains('is-on')) {
        ring.x = ev.clientX; ring.y = ev.clientY;
        cursor.classList.add('is-on'); dot.classList.add('is-on');
      }
      cursorTarget(ev.target);
    }, { passive: true });
    window.addEventListener('pointerdown', function () { cursor.classList.add('is-down'); });
    window.addEventListener('pointerup', function () { cursor.classList.remove('is-down'); });
    document.addEventListener('mouseleave', function () { cursor.classList.remove('is-on'); dot.classList.remove('is-on'); });

    // Spotlight glow inside cards and panels.
    document.addEventListener('pointermove', function (ev) {
      var s = ev.target.closest && ev.target.closest('.spot, .panel');
      if (!s) return;
      var r = s.getBoundingClientRect();
      s.style.setProperty('--mx', (ev.clientX - r.left).toFixed(0) + 'px');
      s.style.setProperty('--my', (ev.clientY - r.top).toFixed(0) + 'px');
    }, { passive: true });

    // Row preview: a sun-yellow card with the pet's avatar and pills.
    if (peek) {
      var peekAvatar = peek.querySelector('.peek-avatar');
      var peekName = peek.querySelector('.peek-name');
      var peekPills = peek.querySelector('.peek-pills');
      document.querySelectorAll('tr[data-row]').forEach(function (row) {
        var avatar = row.querySelector('.avatar');
        var name = row.querySelector('.pet-cell a');
        if (!avatar || !name) return;
        row.addEventListener('pointerenter', function () {
          peekAvatar.innerHTML = '';
          var clone = avatar.cloneNode(true);
          clone.style.viewTransitionName = '';
          peekAvatar.appendChild(clone);
          peekName.textContent = name.textContent;
          peekPills.innerHTML = '';
          row.querySelectorAll('.pill').forEach(function (p) { peekPills.appendChild(p.cloneNode(true)); });
          if (!peekOn) { peekPos.x = mouse.x + 26; peekPos.y = mouse.y - 90; }
          peekOn = true;
          peek.classList.add('is-on');
        });
        row.addEventListener('pointerleave', function () { peekOn = false; peek.classList.remove('is-on'); });
      });
    }

    // Magnetic: buttons and the badge lean toward the pointer.
    document.querySelectorAll('.btn-cta, .badge-spin, .to-top').forEach(function (el) {
      el.addEventListener('pointermove', function (ev) {
        var r = el.getBoundingClientRect();
        var dx = ev.clientX - (r.left + r.width / 2);
        var dy = ev.clientY - (r.top + r.height / 2);
        el.style.transform = 'translate(' + (dx * 0.28).toFixed(1) + 'px,' + (dy * 0.38).toFixed(1) + 'px)';
      });
      el.addEventListener('pointerleave', function () { el.style.transform = ''; });
    });

    // Tilt: gallery cards rotate in 3D toward the pointer.
    document.querySelectorAll('[data-tilt]').forEach(function (el) {
      el.addEventListener('pointermove', function (ev) {
        var r = el.getBoundingClientRect();
        var px = (ev.clientX - r.left) / r.width - 0.5;
        var py = (ev.clientY - r.top) / r.height - 0.5;
        el.classList.add('is-tilting');
        el.style.transform = 'perspective(900px) rotateX(' + (-py * 10).toFixed(2) + 'deg) rotateY(' + (px * 12).toFixed(2) + 'deg) translateY(-6px)';
      });
      el.addEventListener('pointerleave', function () { el.classList.remove('is-tilting'); el.style.transform = ''; });
    });
  }

  requestAnimationFrame(frame);

  /* ---- 2f. Hover and click effects ---------------------------------------- */

  // Button fill grows from the point where the pointer came in (and shrinks toward where it left).
  document.querySelectorAll('.btn').forEach(function (b) {
    function setPoint(ev) {
      var r = b.getBoundingClientRect();
      b.style.setProperty('--x', (ev.clientX - r.left).toFixed(0) + 'px');
      b.style.setProperty('--y', (ev.clientY - r.top).toFixed(0) + 'px');
      b.style.setProperty('--d', (Math.max(r.width, r.height) * 2.6).toFixed(0) + 'px');
    }
    b.addEventListener('pointerenter', setPoint);
    b.addEventListener('pointerleave', setPoint);
  });

  // Scramble: codes, labels and numbers shuffle through random characters before settling.
  var GLYPHS = 'ABCDEFGHJKLMNPQRSTUVWXYZ0123456789#%&';
  function scramble(el, pool, finalText) {
    if (el._scrambling) return;
    var text = finalText || el.dataset.text || (el.dataset.text = el.textContent);
    var start = performance.now();
    el._scrambling = true;
    el.style.minWidth = el.getBoundingClientRect().width + 'px';
    (function step(now) {
      var t = clamp((now - start) / 520, 0, 1);
      var fixed = Math.floor(t * text.length);
      var out = '';
      for (var i = 0; i < text.length; i++) {
        var c = text[i];
        out += (i < fixed || c === ' ' || c === '-' || c === '(' || c === ')') ? c : pool[(Math.random() * pool.length) | 0];
      }
      el.textContent = out;
      if (t < 1) requestAnimationFrame(step);
      else { el.textContent = text; el._scrambling = false; el.style.minWidth = ''; }
    })(start);
  }
  document.querySelectorAll('.code-pill, [data-scramble]').forEach(function (el) {
    el.addEventListener('pointerenter', function () { scramble(el, GLYPHS); });
  });
  document.querySelectorAll('[data-scramble]').forEach(function (el) {
    var once = new IntersectionObserver(function (entries) {
      if (entries[0].isIntersecting) { scramble(el, GLYPHS); once.disconnect(); }
    }, { threshold: 1 });
    once.observe(el);
  });
  document.querySelectorAll('.stat').forEach(function (s) {
    var num = s.querySelector('[data-count]');
    s.addEventListener('pointerenter', function () { scramble(num, '0123456789', num.dataset.count); });
  });

  // Ripple on ordinary buttons.
  document.addEventListener('pointerdown', function (ev) {
    var btn = ev.target.closest('.btn');
    if (!btn || btn.hasAttribute('data-burst')) return;
    var r = btn.getBoundingClientRect();
    var dotEl = document.createElement('span');
    dotEl.className = 'ripple';
    dotEl.style.left = (ev.clientX - r.left) + 'px';
    dotEl.style.top = (ev.clientY - r.top) + 'px';
    btn.appendChild(dotEl);
    dotEl.addEventListener('animationend', function () { dotEl.remove(); });
  });

  // Paw burst: clicking a [data-burst] element throws paw prints out of the click point.
  var PAW = '<svg viewBox="0 0 40 40" width="22" height="22" aria-hidden="true"><g fill="currentColor"><ellipse cx="20" cy="26" rx="8.5" ry="7"/><circle cx="9" cy="16" r="3.8"/><circle cx="16" cy="9" r="3.8"/><circle cx="24" cy="9" r="3.8"/><circle cx="31" cy="16" r="3.8"/></g></svg>';
  var COLORS = ['#FFD23F', '#FF6B4A', '#B9A6FF', '#7BE0B3', '#F4EFE6'];
  document.addEventListener('click', function (ev) {
    var src = ev.target.closest('[data-burst]');
    if (!src || !ev.clientX) return;
    for (var i = 0; i < 12; i++) {
      var p = document.createElement('span');
      p.className = 'paw-p';
      p.innerHTML = PAW;
      p.style.left = ev.clientX + 'px';
      p.style.top = ev.clientY + 'px';
      p.style.color = COLORS[i % COLORS.length];
      document.body.appendChild(p);
      var a = (Math.PI * 2 * i) / 12 + Math.random() * 0.5;
      var dist = 70 + Math.random() * 90;
      var dx = Math.cos(a) * dist;
      var dyp = Math.sin(a) * dist;
      var rot = (Math.random() - 0.5) * 140;
      p.animate([
        { transform: 'translate(0,0) scale(0) rotate(0deg)', opacity: 1 },
        { transform: 'translate(' + dx + 'px,' + dyp + 'px) scale(1) rotate(' + rot + 'deg)', opacity: 1, offset: 0.55 },
        { transform: 'translate(' + dx * 1.15 + 'px,' + (dyp * 1.15 + 50) + 'px) scale(0) rotate(' + rot * 1.6 + 'deg)', opacity: 0 }
      ], { duration: 900 + Math.random() * 400, easing: 'cubic-bezier(.16,1,.3,1)' }).onfinish = (function (el) { return function () { el.remove(); }; })(p);
    }
  });

  /* ---- 2g. Page transitions ------------------------------------------------
     The circle reveal is CSS (@view-transition in app.css). JS hands the click point
     from the old page to the new one, and tags the clicked pet's avatar so the
     browser morphs it into the big avatar on the profile page. */
  var lastPoint = null;
  window.addEventListener('pointerdown', function (ev) { lastPoint = { x: ev.clientX / window.innerWidth, y: ev.clientY / window.innerHeight, t: Date.now() }; });
  window.addEventListener('pageswap', function (ev) {
    if (!ev.viewTransition) return;
    var pt = lastPoint && Date.now() - lastPoint.t < 4000 ? lastPoint : { x: 0.5, y: 0.5 };
    try { sessionStorage.setItem('adopit-vt', JSON.stringify(pt)); } catch (e) {}
  });
  window.addEventListener('pagereveal', function (ev) {
    var pt = null;
    try { pt = JSON.parse(sessionStorage.getItem('adopit-vt') || 'null'); sessionStorage.removeItem('adopit-vt'); } catch (e) {}
    if (pt && ev.viewTransition) {
      root.style.setProperty('--vt-x', (pt.x * 100).toFixed(1) + '%');
      root.style.setProperty('--vt-y', (pt.y * 100).toFixed(1) + '%');
    }
    // coming back from a profile: morph that pet's avatar back into its row or card
    var from = window.navigation && navigation.activation && navigation.activation.from;
    var match = from && /view\.php\?id=(\d+)/.exec(from.url || '');
    if (!match) return;
    var link = document.querySelector('.pet-cell a[href="view.php?id=' + match[1] + '"]');
    var avatar = link && link.closest('tr').querySelector('.avatar');
    if (avatar) {
      avatar.style.viewTransitionName = 'pet-avatar';
      setTimeout(function () { avatar.style.viewTransitionName = ''; }, 1000);
    }
  });
  document.addEventListener('click', function (ev) {
    var link = ev.target.closest('a[href*="view.php?id="]');
    if (!link) return;
    var holder = link.closest('tr, .pet-card');
    var avatar = holder && holder.querySelector('.avatar');
    if (avatar) avatar.style.viewTransitionName = 'pet-avatar';
  });
})();
