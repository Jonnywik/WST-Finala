</main>

<footer class="container-xl site-footer">
  <div class="footer-meta">
    <span>Pet Adoption &amp; Vet Care Registry</span>
    <span>PHP + MySQL on localhost</span>
  </div>
  <span class="wordmark" data-scrub aria-hidden="true">AdopIT!</span>
</footer>

<!-- One shared delete confirmation, filled in by assets/app.js from the clicked button's data-* attributes -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content panel" method="post" id="deleteForm">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="delete">
      <input type="hidden" name="id" id="deleteId">
      <div class="modal-body text-center p-4">
        <div class="danger-badge"><?= icon('trash', 28) ?></div>
        <h2 class="h4 mt-3" id="deleteModalTitle">Delete this record?</h2>
        <p class="mb-1">You are about to delete <strong id="deleteLabel"></strong>.</p>
        <p class="text-muted-ink small mb-4" id="deleteNote">This cannot be undone.</p>
        <div class="d-flex gap-2 justify-content-center">
          <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger-solid"><?= icon('trash', 18) ?>Yes, delete</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script src="assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
<script src="assets/app.js"></script>
</body>
</html>
