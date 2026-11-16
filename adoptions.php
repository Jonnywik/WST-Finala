<?php
// Adoption Inquiries: list + add/edit form + delete, all on one page.
require __DIR__ . '/includes/functions.php';
$pdo = db();

$pets = $pdo->query('SELECT id, pet_code, name, adoption_status FROM pets ORDER BY name')->fetchAll();
$petIds = array_map('intval', array_column($pets, 'id'));

$errors = [];
$backPet = (int) ($_GET['pet_id'] ?? 0);          // set when we arrive from a pet profile or the spotlight card
$form = [
    'id' => 0, 'pet_id' => in_array($backPet, $petIds, true) ? $backPet : 0, 'applicant_name' => '', 'contact_info' => '',
    'living_setup' => '', 'inquiry_status' => 'Pending Review', 'inquiry_date' => date('Y-m-d'), 'remarks' => '',
];
$syncPet = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    if (post('action') === 'delete') {
        $st = $pdo->prepare('DELETE FROM adoption_inquiries WHERE id = ?');
        $st->execute([(int) post('id')]);
        flash($st->rowCount() ? 'success' : 'error', $st->rowCount() ? 'Inquiry deleted.' : 'That inquiry no longer exists.');
        redirect('adoptions.php');
    }

    $form = [
        'id' => (int) post('id'), 'pet_id' => (int) post('pet_id'), 'applicant_name' => post('applicant_name'),
        'contact_info' => post('contact_info'), 'living_setup' => post('living_setup'),
        'inquiry_status' => post('inquiry_status'), 'inquiry_date' => post('inquiry_date'), 'remarks' => post('remarks'),
    ];
    $syncPet = post('sync_pet') === '1';
    $backPet = (int) post('back_pet');

    if (!in_array($form['pet_id'], $petIds, true)) {
        $errors['pet_id'] = 'Choose a pet.';
    }
    if ($form['applicant_name'] === '' || mb_strlen($form['applicant_name']) > 80) {
        $errors['applicant_name'] = 'Applicant name is required (up to 80 characters).';
    }
    if (mb_strlen($form['contact_info']) < 5 || mb_strlen($form['contact_info']) > 100) {
        $errors['contact_info'] = 'Enter a phone number or email address.';
    }
    if (!in_array($form['living_setup'], LIVING_SETUPS, true)) {
        $errors['living_setup'] = 'Choose a living setup.';
    }
    if (!in_array($form['inquiry_status'], INQUIRY_STATUSES, true)) {
        $errors['inquiry_status'] = 'Choose a status.';
    }
    if (!valid_date($form['inquiry_date']) || $form['inquiry_date'] > date('Y-m-d')) {
        $errors['inquiry_date'] = 'Enter a valid inquiry date that is not in the future.';
    }
    if (mb_strlen($form['remarks']) > 1000) {
        $errors['remarks'] = 'Remarks are too long (1000 characters max).';
    }

    if (!$errors) {
        $params = [
            $form['pet_id'], $form['applicant_name'], $form['contact_info'], $form['living_setup'],
            $form['inquiry_status'], $form['inquiry_date'], $form['remarks'] ?: null,
        ];
        if ($form['id']) {
            $pdo->prepare('UPDATE adoption_inquiries SET pet_id = ?, applicant_name = ?, contact_info = ?, living_setup = ?, inquiry_status = ?, inquiry_date = ?, remarks = ? WHERE id = ?')
                ->execute([...$params, $form['id']]);
            $msg = 'Inquiry updated.';
        } else {
            $pdo->prepare('INSERT INTO adoption_inquiries (pet_id, applicant_name, contact_info, living_setup, inquiry_status, inquiry_date, remarks) VALUES (?, ?, ?, ?, ?, ?, ?)')
                ->execute($params);
            $msg = 'Inquiry saved.';
        }
        if ($syncPet) {
            $before = find_pet($pdo, $form['pet_id'])['adoption_status'];
            sync_pet_adoption($pdo, $form['pet_id']);
            $after = find_pet($pdo, $form['pet_id'])['adoption_status'];
            if ($before !== $after) {
                $msg .= ' Pet status changed to ' . $after . '.';
            }
        }
        flash('success', $msg);
        redirect($backPet ? 'view.php?id=' . $backPet : 'adoptions.php');
    }
} elseif (isset($_GET['edit'])) {
    $st = $pdo->prepare('SELECT * FROM adoption_inquiries WHERE id = ?');
    $st->execute([(int) $_GET['edit']]);
    $row = $st->fetch();
    if (!$row) {
        flash('error', 'That inquiry no longer exists.');
        redirect('adoptions.php');
    }
    $form = array_intersect_key($row, $form);
    $form['remarks'] = (string) $form['remarks'];
}

$inquiries = $pdo->query('SELECT a.*, p.name, p.pet_code, p.species FROM adoption_inquiries a
                          JOIN pets p ON p.id = a.pet_id ORDER BY a.inquiry_date DESC, a.id DESC')->fetchAll();
$editing = (bool) $form['id'];

$pageTitle = 'Adoption Inquiries';
$activeNav = 'adoptions';
require __DIR__ . '/includes/header.php';
?>
<div class="mb-3">
  <h1 class="h2 mb-0">Adoption Inquiries</h1>
  <span class="text-muted-ink">Applications from people who want to give a pet a home.</span>
</div>

<div class="row g-4">
  <div class="col-lg-4">
    <div class="clay-card clay-pink pop-in" id="form">
      <h2 class="h4"><?= $editing ? 'Edit inquiry' : 'New inquiry' ?></h2>
      <?php if (!$pets): ?>
        <p class="mb-0">Register a pet first, then you can record inquiries for it. <a href="create.php">Register Pet</a></p>
      <?php else: ?>
      <form method="post" action="adoptions.php<?= $editing ? '?edit=' . (int) $form['id'] : '' ?>" class="needs-validation" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int) $form['id'] ?>">
        <input type="hidden" name="back_pet" value="<?= $backPet ?>">
        <div class="mb-3">
          <label class="form-label" for="pet_id">Pet <span class="req">*</span></label>
          <select class="form-select<?= inv($errors, 'pet_id') ?>" id="pet_id" name="pet_id" required>
            <option value="">Choose a pet…</option>
            <?php foreach ($pets as $p): ?>
              <option value="<?= (int) $p['id'] ?>"<?= (int) $p['id'] === (int) $form['pet_id'] ? ' selected' : '' ?>><?= e($p['name'] . ' · ' . $p['pet_code'] . ' · ' . $p['adoption_status']) ?></option>
            <?php endforeach; ?>
          </select>
          <?= fb($errors, 'pet_id', 'Choose a pet.') ?>
        </div>
        <div class="mb-3">
          <label class="form-label" for="applicant_name">Applicant name <span class="req">*</span></label>
          <input class="form-control<?= inv($errors, 'applicant_name') ?>" id="applicant_name" name="applicant_name" value="<?= e($form['applicant_name']) ?>" required maxlength="80" autocomplete="name">
          <?= fb($errors, 'applicant_name', 'Applicant name is required.') ?>
        </div>
        <div class="mb-3">
          <label class="form-label" for="contact_info">Phone or email <span class="req">*</span></label>
          <input class="form-control<?= inv($errors, 'contact_info') ?>" id="contact_info" name="contact_info" value="<?= e($form['contact_info']) ?>" required minlength="5" maxlength="100">
          <?= fb($errors, 'contact_info', 'Enter a phone number or email address.') ?>
        </div>
        <div class="mb-3">
          <label class="form-label" for="living_setup">Living setup <span class="req">*</span></label>
          <select class="form-select<?= inv($errors, 'living_setup') ?>" id="living_setup" name="living_setup" required>
            <option value="">Choose…</option><?= options(LIVING_SETUPS, $form['living_setup']) ?>
          </select>
          <?= fb($errors, 'living_setup', 'Choose a living setup.') ?>
        </div>
        <div class="row g-3 mb-3">
          <div class="col-6 col-lg-12 col-xl-6">
            <label class="form-label" for="inquiry_status">Status <span class="req">*</span></label>
            <select class="form-select<?= inv($errors, 'inquiry_status') ?>" id="inquiry_status" name="inquiry_status" required><?= options(INQUIRY_STATUSES, $form['inquiry_status']) ?></select>
            <?= fb($errors, 'inquiry_status', 'Choose a status.') ?>
          </div>
          <div class="col-6 col-lg-12 col-xl-6">
            <label class="form-label" for="inquiry_date">Inquiry date <span class="req">*</span></label>
            <input type="date" class="form-control<?= inv($errors, 'inquiry_date') ?>" id="inquiry_date" name="inquiry_date" value="<?= e($form['inquiry_date']) ?>" max="<?= date('Y-m-d') ?>" required>
            <?= fb($errors, 'inquiry_date', 'Enter a valid date that is not in the future.') ?>
          </div>
        </div>
        <div class="form-check mb-3">
          <input class="form-check-input" type="checkbox" id="sync_pet" name="sync_pet" value="1"<?= $syncPet ? ' checked' : '' ?>>
          <label class="form-check-label" for="sync_pet">Update the pet's adoption status to match</label>
          <div class="form-text">Meet &amp; Greet sets the pet to Pending Adoption, Approved sets it to Adopted.</div>
        </div>
        <div class="mb-3">
          <label class="form-label" for="remarks">Remarks</label>
          <textarea class="form-control<?= inv($errors, 'remarks') ?>" id="remarks" name="remarks" rows="2" maxlength="1000"><?= e($form['remarks']) ?></textarea>
          <?= fb($errors, 'remarks', 'Remarks are too long.') ?>
        </div>
        <div class="d-flex flex-wrap gap-2">
          <button type="submit" class="btn btn-cta"><?= icon('check', 18) ?><?= $editing ? 'Save Changes' : 'Save Inquiry' ?></button>
          <?php if ($editing || $backPet): ?>
            <a class="btn btn-clay" href="<?= $backPet ? 'view.php?id=' . $backPet : 'adoptions.php' ?>">Cancel</a>
          <?php endif; ?>
        </div>
      </form>
      <?php endif; ?>
    </div>
  </div>

  <div class="col-lg-8" data-filter-scope>
    <div class="clay-card filter-bar mb-3">
      <div class="row g-2 align-items-center">
        <div class="col-md">
          <div class="search-box">
            <?= icon('search', 18) ?>
            <input type="search" class="form-control" data-filter-search placeholder="Search applicant, pet or contact" aria-label="Search inquiries">
          </div>
        </div>
        <div class="col-6 col-md-auto">
          <select class="form-select" data-filter-attr="status" aria-label="Filter by inquiry status">
            <option value="">All statuses</option><?= options(INQUIRY_STATUSES, '') ?>
          </select>
        </div>
        <div class="col-6 col-md-auto"><span class="count-chip" data-filter-count aria-live="polite"><?= count($inquiries) ?> records</span></div>
      </div>
    </div>

    <div class="clay-card table-card">
      <div class="table-responsive">
        <table class="table clay-table">
          <thead>
            <tr>
              <th scope="col">Applicant</th>
              <th scope="col">Pet</th>
              <th scope="col">Living setup</th>
              <th scope="col">Status</th>
              <th scope="col">Date</th>
              <th scope="col" class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($inquiries as $q): ?>
            <tr data-row data-status="<?= e($q['inquiry_status']) ?>"
                data-search="<?= e(mb_strtolower($q['applicant_name'] . ' ' . $q['contact_info'] . ' ' . $q['name'] . ' ' . $q['pet_code'])) ?>">
              <td><strong><?= e($q['applicant_name']) ?></strong><span class="sub"><?= e($q['contact_info']) ?></span></td>
              <td>
                <div class="pet-cell">
                  <?= species_avatar($q['species'], 40) ?>
                  <div>
                    <a href="view.php?id=<?= (int) $q['pet_id'] ?>"><?= e($q['name']) ?></a>
                    <span class="sub"><?= e($q['pet_code']) ?></span>
                  </div>
                </div>
              </td>
              <td><?= e($q['living_setup']) ?></td>
              <td><?= status_pill($q['inquiry_status']) ?></td>
              <td class="text-nowrap"><?= fmt_date($q['inquiry_date']) ?></td>
              <td>
                <div class="actions">
                  <a class="icon-btn edit" href="adoptions.php?edit=<?= (int) $q['id'] ?>#form" aria-label="Edit inquiry from <?= e($q['applicant_name']) ?>" title="Edit"><?= icon('pencil', 18) ?></a>
                  <button type="button" class="icon-btn del" data-bs-toggle="modal" data-bs-target="#deleteModal"
                          data-delete-action="adoptions.php" data-delete-id="<?= (int) $q['id'] ?>"
                          data-delete-label="<?= e($q['applicant_name'] . "'s inquiry for " . $q['name']) ?>"
                          aria-label="Delete inquiry from <?= e($q['applicant_name']) ?>" title="Delete"><?= icon('trash', 18) ?></button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
            <tr data-filter-empty hidden><td colspan="6" class="empty-state">No inquiries match your search or filter.</td></tr>
          <?php if (!$inquiries): ?>
            <tr><td colspan="6" class="empty-state">No inquiries yet. Use the form to add the first one.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
