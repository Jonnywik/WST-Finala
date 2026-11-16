<?php
// Vet Care Logs: list + add/edit form + delete, all on one page.
require __DIR__ . '/includes/functions.php';
$pdo = db();

$pets = $pdo->query('SELECT id, pet_code, name FROM pets ORDER BY name')->fetchAll();
$petIds = array_map('intval', array_column($pets, 'id'));

$errors = [];
$backPet = (int) ($_GET['pet_id'] ?? 0);          // set when we arrive from a pet profile
$form = [
    'id' => 0, 'pet_id' => in_array($backPet, $petIds, true) ? $backPet : 0, 'care_type' => 'Checkup',
    'procedure_name' => '', 'vet_name' => '', 'visit_date' => date('Y-m-d'), 'next_due_date' => '', 'notes' => '',
];
$markVaccinated = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    if (post('action') === 'delete') {
        $st = $pdo->prepare('DELETE FROM vet_logs WHERE id = ?');
        $st->execute([(int) post('id')]);
        flash($st->rowCount() ? 'success' : 'error', $st->rowCount() ? 'Vet visit deleted.' : 'That vet visit no longer exists.');
        redirect('vet_care.php');
    }

    $form = [
        'id' => (int) post('id'), 'pet_id' => (int) post('pet_id'), 'care_type' => post('care_type'),
        'procedure_name' => post('procedure_name'), 'vet_name' => post('vet_name'),
        'visit_date' => post('visit_date'), 'next_due_date' => post('next_due_date'), 'notes' => post('notes'),
    ];
    $markVaccinated = post('mark_vaccinated') === '1';
    $backPet = (int) post('back_pet');

    if (!in_array($form['pet_id'], $petIds, true)) {
        $errors['pet_id'] = 'Choose a pet.';
    }
    if (!in_array($form['care_type'], CARE_TYPES, true)) {
        $errors['care_type'] = 'Choose a care type.';
    }
    if ($form['procedure_name'] === '' || mb_strlen($form['procedure_name']) > 100) {
        $errors['procedure_name'] = 'Describe the procedure (up to 100 characters).';
    }
    if (mb_strlen($form['vet_name']) > 60) {
        $errors['vet_name'] = 'Name is too long (60 characters max).';
    }
    if (!valid_date($form['visit_date']) || $form['visit_date'] > date('Y-m-d')) {
        $errors['visit_date'] = 'Enter a valid visit date that is not in the future.';
    }
    if ($form['next_due_date'] !== '' && (!valid_date($form['next_due_date']) || $form['next_due_date'] < $form['visit_date'])) {
        $errors['next_due_date'] = 'Next due date must be on or after the visit date.';
    }
    if (mb_strlen($form['notes']) > 1000) {
        $errors['notes'] = 'Notes are too long (1000 characters max).';
    }

    if (!$errors) {
        $params = [
            $form['pet_id'], $form['care_type'], $form['procedure_name'], $form['vet_name'],
            $form['visit_date'], $form['next_due_date'] ?: null, $form['notes'] ?: null,
        ];
        if ($form['id']) {
            $pdo->prepare('UPDATE vet_logs SET pet_id = ?, care_type = ?, procedure_name = ?, vet_name = ?, visit_date = ?, next_due_date = ?, notes = ? WHERE id = ?')
                ->execute([...$params, $form['id']]);
            $msg = 'Vet visit updated.';
        } else {
            $pdo->prepare('INSERT INTO vet_logs (pet_id, care_type, procedure_name, vet_name, visit_date, next_due_date, notes) VALUES (?, ?, ?, ?, ?, ?, ?)')
                ->execute($params);
            $msg = 'Vet visit logged.';
        }
        if ($form['care_type'] === 'Vaccination' && $markVaccinated) {
            $pdo->prepare("UPDATE pets SET vaccination_status = 'Up to Date' WHERE id = ?")->execute([$form['pet_id']]);
            $msg .= ' Pet marked as vaccinated up to date.';
        }
        flash('success', $msg);
        redirect($backPet ? 'view.php?id=' . $backPet : 'vet_care.php');
    }
} elseif (isset($_GET['edit'])) {
    $st = $pdo->prepare('SELECT * FROM vet_logs WHERE id = ?');
    $st->execute([(int) $_GET['edit']]);
    $row = $st->fetch();
    if (!$row) {
        flash('error', 'That vet visit no longer exists.');
        redirect('vet_care.php');
    }
    $form = array_intersect_key($row, $form);
    $form['next_due_date'] = (string) $form['next_due_date'];
    $form['notes'] = (string) $form['notes'];
    $markVaccinated = false;   // do not silently change the pet when only fixing a typo
}

$logs = $pdo->query('SELECT v.*, p.name, p.pet_code, p.species FROM vet_logs v
                     JOIN pets p ON p.id = v.pet_id ORDER BY v.visit_date DESC, v.id DESC')->fetchAll();
$editing = (bool) $form['id'];
$today = date('Y-m-d');

$pageTitle = 'Vet Care Logs';
$activeNav = 'vet';
require __DIR__ . '/includes/header.php';
?>
<div class="d-flex flex-wrap align-items-end justify-content-between gap-2 mb-3">
  <div>
    <h1 data-split class="h2 mb-0">Vet Care Logs</h1>
    <span class="text-muted-ink">Vaccinations, checkups, deworming, medication and grooming for every pet.</span>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-4">
    <div class="panel tone-lav pop-in" id="form">
      <h2 class="h4"><?= $editing ? 'Edit vet visit' : 'Log a vet visit' ?></h2>
      <?php if (!$pets): ?>
        <p class="mb-0">Register a pet first, then you can log its vet care. <a href="create.php">Register Pet</a></p>
      <?php else: ?>
      <form method="post" action="vet_care.php<?= $editing ? '?edit=' . (int) $form['id'] : '' ?>" class="needs-validation" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int) $form['id'] ?>">
        <input type="hidden" name="back_pet" value="<?= $backPet ?>">
        <div class="mb-3">
          <label class="form-label" for="pet_id">Pet <span class="req">*</span></label>
          <select class="form-select<?= inv($errors, 'pet_id') ?>" id="pet_id" name="pet_id" required>
            <option value="">Choose a pet…</option>
            <?php foreach ($pets as $p): ?>
              <option value="<?= (int) $p['id'] ?>"<?= (int) $p['id'] === (int) $form['pet_id'] ? ' selected' : '' ?>><?= e($p['name'] . ' · ' . $p['pet_code']) ?></option>
            <?php endforeach; ?>
          </select>
          <?= fb($errors, 'pet_id', 'Choose a pet.') ?>
        </div>
        <div class="mb-3">
          <label class="form-label" for="care_type">Care type <span class="req">*</span></label>
          <select class="form-select<?= inv($errors, 'care_type') ?>" id="care_type" name="care_type" required
                  data-toggle-target="#vaccBox" data-toggle-value="Vaccination"><?= options(CARE_TYPES, $form['care_type']) ?></select>
          <?= fb($errors, 'care_type', 'Choose a care type.') ?>
        </div>
        <div class="form-check mb-3" id="vaccBox" hidden>
          <input class="form-check-input" type="checkbox" id="mark_vaccinated" name="mark_vaccinated" value="1"<?= $markVaccinated ? ' checked' : '' ?>>
          <label class="form-check-label" for="mark_vaccinated">Mark this pet's vaccination as <strong>Up to Date</strong></label>
        </div>
        <div class="mb-3">
          <label class="form-label" for="procedure_name">Procedure <span class="req">*</span></label>
          <input class="form-control<?= inv($errors, 'procedure_name') ?>" id="procedure_name" name="procedure_name" value="<?= e($form['procedure_name']) ?>"
                 required maxlength="100" placeholder="e.g. Anti-rabies booster">
          <?= fb($errors, 'procedure_name', 'Describe the procedure.') ?>
        </div>
        <div class="mb-3">
          <label class="form-label" for="vet_name">Vet / staff</label>
          <input class="form-control<?= inv($errors, 'vet_name') ?>" id="vet_name" name="vet_name" value="<?= e($form['vet_name']) ?>" maxlength="60" placeholder="e.g. Dr. Santos">
          <?= fb($errors, 'vet_name', 'Name is too long.') ?>
        </div>
        <div class="row g-3 mb-3">
          <div class="col-6 col-lg-12 col-xl-6">
            <label class="form-label" for="visit_date">Visit date <span class="req">*</span></label>
            <input type="date" class="form-control<?= inv($errors, 'visit_date') ?>" id="visit_date" name="visit_date" value="<?= e($form['visit_date']) ?>" max="<?= $today ?>" required>
            <?= fb($errors, 'visit_date', 'Enter a valid date that is not in the future.') ?>
          </div>
          <div class="col-6 col-lg-12 col-xl-6">
            <label class="form-label" for="next_due_date">Next due</label>
            <input type="date" class="form-control<?= inv($errors, 'next_due_date') ?>" id="next_due_date" name="next_due_date" value="<?= e($form['next_due_date']) ?>">
            <?= fb($errors, 'next_due_date', 'Must be on or after the visit date.') ?>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label" for="notes">Notes</label>
          <textarea class="form-control<?= inv($errors, 'notes') ?>" id="notes" name="notes" rows="2" maxlength="1000"><?= e($form['notes']) ?></textarea>
          <?= fb($errors, 'notes', 'Notes are too long.') ?>
        </div>
        <div class="d-flex flex-wrap gap-2">
          <button type="submit" class="btn btn-cta"><?= icon('check', 18) ?><?= $editing ? 'Save Changes' : 'Log Visit' ?></button>
          <?php if ($editing || $backPet): ?>
            <a class="btn btn-ghost" href="<?= $backPet ? 'view.php?id=' . $backPet : 'vet_care.php' ?>">Cancel</a>
          <?php endif; ?>
        </div>
      </form>
      <?php endif; ?>
    </div>
  </div>

  <div class="col-lg-8" data-filter-scope>
    <div class="panel filter-bar mb-3">
      <div class="row g-2 align-items-center">
        <div class="col-md">
          <div class="search-box">
            <?= icon('search', 18) ?>
            <input type="search" class="form-control" data-filter-search placeholder="Search pet, procedure or vet" aria-label="Search vet visits">
          </div>
        </div>
        <div class="col-6 col-md-auto">
          <select class="form-select" data-filter-attr="type" aria-label="Filter by care type">
            <option value="">All care types</option><?= options(CARE_TYPES, '') ?>
          </select>
        </div>
        <div class="col-6 col-md-auto"><span class="count-chip" data-filter-count aria-live="polite"><?= count($logs) ?> records</span></div>
      </div>
    </div>

    <div class="panel table-card">
      <div class="table-responsive">
        <table class="table data-table">
          <thead>
            <tr>
              <th scope="col">Pet</th>
              <th scope="col">Care</th>
              <th scope="col">Procedure</th>
              <th scope="col">Visit</th>
              <th scope="col">Next due</th>
              <th scope="col" class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($logs as $l): ?>
            <tr data-row data-type="<?= e($l['care_type']) ?>"
                data-search="<?= e(mb_strtolower($l['name'] . ' ' . $l['pet_code'] . ' ' . $l['procedure_name'] . ' ' . $l['vet_name'])) ?>">
              <td>
                <div class="pet-cell">
                  <?= species_avatar($l['species'], 40) ?>
                  <div>
                    <a href="view.php?id=<?= (int) $l['pet_id'] ?>"><?= e($l['name']) ?></a>
                    <span class="sub"><?= e($l['pet_code']) ?></span>
                  </div>
                </div>
              </td>
              <td><?= status_pill($l['care_type']) ?></td>
              <td class="col-wide"><?= e($l['procedure_name']) ?><span class="sub"><?= e($l['vet_name'] ?: 'No vet recorded') ?></span></td>
              <td class="text-nowrap"><?= fmt_date($l['visit_date']) ?></td>
              <td class="text-nowrap">
                <?= fmt_date($l['next_due_date']) ?>
                <?php if ($l['next_due_date'] && $l['next_due_date'] < $today): ?><span class="sub"><span class="pill pill-coral"><?= icon('alert', 14) ?>Overdue</span></span><?php endif; ?>
              </td>
              <td>
                <div class="actions">
                  <a class="icon-btn edit" href="vet_care.php?edit=<?= (int) $l['id'] ?>#form" aria-label="Edit visit for <?= e($l['name']) ?>" title="Edit"><?= icon('pencil', 18) ?></a>
                  <button type="button" class="icon-btn del" data-bs-toggle="modal" data-bs-target="#deleteModal"
                          data-delete-action="vet_care.php" data-delete-id="<?= (int) $l['id'] ?>"
                          data-delete-label="<?= e($l['procedure_name'] . ' for ' . $l['name']) ?>"
                          aria-label="Delete visit for <?= e($l['name']) ?>" title="Delete"><?= icon('trash', 18) ?></button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
            <tr data-filter-empty hidden><td colspan="6" class="empty-state">No vet visits match your search or filter.</td></tr>
          <?php if (!$logs): ?>
            <tr><td colspan="6" class="empty-state">No vet visits logged yet. Use the form to add the first one.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
