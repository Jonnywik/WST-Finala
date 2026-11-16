// AdopIT! - vanilla JS: live search/filter, delete confirmation, form validation.
(function () {
  'use strict';

  // ---- Greeting from the visitor's own clock ----
  var greet = document.querySelector('[data-greeting]');
  if (greet) {
    var h = new Date().getHours();
    greet.textContent = h < 12 ? 'Good morning' : h < 18 ? 'Good afternoon' : 'Good evening';
  }

  // ---- Live search + dropdown filters ----
  // Markup contract: [data-filter-scope] wraps a search input [data-filter-search],
  // selects [data-filter-attr="species"], rows [data-row] carrying data-search / data-species,
  // an optional counter [data-filter-count] and an optional "no match" row [data-filter-empty].
  document.querySelectorAll('[data-filter-scope]').forEach(function (scope) {
    var search = scope.querySelector('[data-filter-search]');
    var selects = scope.querySelectorAll('[data-filter-attr]');
    var rows = scope.querySelectorAll('[data-row]');
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
        if (ok) shown++;
      });
      if (count) count.textContent = shown + ' of ' + rows.length + ' shown';
      if (empty) empty.hidden = shown !== 0 || rows.length === 0;
    }

    if (search) search.addEventListener('input', apply);
    selects.forEach(function (sel) { sel.addEventListener('change', apply); });
    apply();
  });

  // ---- Delete confirmation modal ----
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

  // ---- Client-side validation (PHP validates again on the server) ----
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

  // ---- Show an option block only for one value of a select ----
  // <select data-toggle-target="#box" data-toggle-value="Vaccination">
  document.querySelectorAll('[data-toggle-target]').forEach(function (sel) {
    var box = document.querySelector(sel.dataset.toggleTarget);
    if (!box) return;
    function sync() { box.hidden = sel.value !== sel.dataset.toggleValue; }
    sel.addEventListener('change', sync);
    sync();
  });

  // ---- Next due date can never be before the visit date ----
  var visit = document.getElementById('visit_date');
  var due = document.getElementById('next_due_date');
  if (visit && due) {
    var syncMin = function () { due.min = visit.value; };
    visit.addEventListener('change', syncMin);
    syncMin();
  }
})();
