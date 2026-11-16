<?php
require __DIR__ . '/includes/functions.php';
$pdo = db();

$id = (int) ($_GET['id'] ?? 0);
$pet = find_pet($pdo, $id);
if (!$pet) {
    flash('error', 'That pet record no longer exists.');
    redirect('index.php');
}

$st = $pdo->prepare('SELECT * FROM vet_logs WHERE pet_id = ? ORDER BY visit_date DESC, id DESC');
$st->execute([$id]);
$logs = $st->fetchAll();

$st = $pdo->prepare('SELECT * FROM adoption_inquiries WHERE pet_id = ? ORDER BY inquiry_date DESC, id DESC');
$st->execute([$id]);
$inquiries = $st->fetchAll();

$pageTitle = $pet['name'];
$activeNav = 'pets';
require __DIR__ . '/includes/header.php';
?>
<a class="btn btn-clay btn-sm mb-3" href="index.php"><?= icon('arrow-left', 16) ?>Back to registry</a>

<div class="row g-4">
  <div class="col-lg-5">
    <div class="clay-card pop-in" style="--i:0">
      <div class="d-flex align-items-center gap-3">
        <?= species_avatar($pet['species'], 84) ?>
        <div>
          <span class="code-pill"><?= e($pet['pet_code']) ?></span>
          <h1 class="h2 mt-1 mb-0"><?= e($pet['name']) ?></h1>
        </div>
      </div>
      <div class="d-flex flex-wrap gap-2 my-3">
        <?= status_pill($pet['adoption_status']) ?>
        <?= status_pill($pet['vaccination_status']) ?>
      </div>
      <dl class="detail-list">
        <dt>Species</dt><dd><?= e($pet['species']) ?></dd>
        <dt>Breed</dt><dd><?= e($pet['breed'] ?: '—') ?></dd>
        <dt>Age</dt><dd><?= e($pet['age_label'] ?: '—') ?></dd>
        <dt>Sex</dt><dd><?= e($pet['sex']) ?></dd>
        <dt>Intake date</dt><dd><?= fmt_date($pet['intake_date']) ?></dd>
        <dt>Last updated</dt><dd><?= fmt_date($pet['updated_at']) ?></dd>
      </dl>
      <?php if ($pet['notes'] !== null && $pet['notes'] !== ''): ?>
        <h2 class="h6 mt-3 mb-1">Notes</h2>
        <p class="mb-0"><?= nl2br(e($pet['notes'])) ?></p>
      <?php endif; ?>
      <div class="d-flex flex-wrap gap-2 mt-4">
        <a class="btn btn-cta" href="edit.php?id=<?= $id ?>"><?= icon('pencil', 18) ?>Edit</a>
        <button type="button" class="btn btn-clay" data-bs-toggle="modal" data-bs-target="#deleteModal"
                data-delete-action="delete.php" data-delete-id="<?= $id ?>"
                data-delete-label="<?= e($pet['name'] . ' (' . $pet['pet_code'] . ')') ?>"
                data-delete-note="Their vet logs and adoption inquiries will be deleted too. This cannot be undone."><?= icon('trash', 18) ?>Delete</button>
      </div>
    </div>
  </div>

  <div class="col-lg-7">
    <div class="clay-card clay-lav pop-in mb-4" style="--i:1">
      <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <h2 class="h4 mb-0"><?= icon('stethoscope', 22) ?> Vet care timeline</h2>
        <a class="btn btn-clay btn-sm" href="vet_care.php?pet_id=<?= $id ?>#form"><?= icon('plus', 16) ?>Log visit</a>
      </div>
      <?php if ($logs): ?>
        <ol class="timeline">
          <?php foreach ($logs as $l): ?>
            <li>
              <div class="d-flex flex-wrap align-items-center gap-2">
                <strong><?= fmt_date($l['visit_date']) ?></strong><?= status_pill($l['care_type']) ?>
              </div>
              <div><?= e($l['procedure_name']) ?><?= $l['vet_name'] !== '' ? ' · ' . e($l['vet_name']) : '' ?></div>
              <?php if ($l['next_due_date']): ?><span class="sub">Next due <?= fmt_date($l['next_due_date']) ?></span><?php endif; ?>
              <?php if ($l['notes']): ?><span class="sub"><?= e($l['notes']) ?></span><?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ol>
      <?php else: ?>
        <p class="mb-0 text-muted-ink">No vet visits logged for <?= e($pet['name']) ?> yet.</p>
      <?php endif; ?>
    </div>

    <div class="clay-card clay-pink pop-in" style="--i:2">
      <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <h2 class="h4 mb-0"><?= icon('heart', 22) ?> Adoption inquiries</h2>
        <a class="btn btn-clay btn-sm" href="adoptions.php?pet_id=<?= $id ?>#form"><?= icon('plus', 16) ?>New inquiry</a>
      </div>
      <?php if ($inquiries): ?>
        <ul class="list-unstyled mb-0 d-grid gap-3">
          <?php foreach ($inquiries as $q): ?>
            <li>
              <div class="d-flex flex-wrap align-items-center gap-2">
                <strong><?= e($q['applicant_name']) ?></strong><?= status_pill($q['inquiry_status']) ?>
              </div>
              <span class="sub"><?= fmt_date($q['inquiry_date']) ?> · <?= e($q['contact_info']) ?><?= $q['living_setup'] !== '' ? ' · ' . e($q['living_setup']) : '' ?></span>
              <?php if ($q['remarks']): ?><span class="sub"><?= e($q['remarks']) ?></span><?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p class="mb-0 text-muted-ink">Nobody has inquired about <?= e($pet['name']) ?> yet.</p>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
