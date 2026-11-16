<?php
// Shared by create.php and edit.php. Expects $pet (field values), $errors, $submitLabel, $cancelUrl.
?>
<form method="post" class="needs-validation" novalidate>
  <?= csrf_field() ?>
  <div class="row g-3">
    <div class="col-sm-4">
      <label class="form-label" for="pet_code">Pet code <span class="req">*</span></label>
      <input class="form-control<?= inv($errors, 'pet_code') ?>" id="pet_code" name="pet_code" value="<?= e($pet['pet_code']) ?>"
             required pattern="[Pp][Ee][Tt]-[0-9]{3,6}" maxlength="10" autocomplete="off">
      <?= fb($errors, 'pet_code', 'Use the format PET-001.') ?>
    </div>
    <div class="col-sm-8">
      <label class="form-label" for="name">Name <span class="req">*</span></label>
      <input class="form-control<?= inv($errors, 'name') ?>" id="name" name="name" value="<?= e($pet['name']) ?>" required maxlength="60">
      <?= fb($errors, 'name', 'Name is required.') ?>
    </div>

    <div class="col-sm-4">
      <label class="form-label" for="species">Species <span class="req">*</span></label>
      <select class="form-select<?= inv($errors, 'species') ?>" id="species" name="species" required>
        <option value="">Choose…</option><?= options(SPECIES, $pet['species']) ?>
      </select>
      <?= fb($errors, 'species', 'Choose a species.') ?>
    </div>
    <div class="col-sm-8">
      <label class="form-label" for="breed">Breed</label>
      <input class="form-control<?= inv($errors, 'breed') ?>" id="breed" name="breed" value="<?= e($pet['breed']) ?>" maxlength="60" placeholder="e.g. Aspin, Puspin, Holland Lop">
      <?= fb($errors, 'breed', 'Breed is too long.') ?>
    </div>

    <div class="col-sm-4">
      <label class="form-label" for="age_label">Age</label>
      <input class="form-control<?= inv($errors, 'age_label') ?>" id="age_label" name="age_label" value="<?= e($pet['age_label']) ?>" maxlength="30" placeholder="e.g. 2 yrs, 8 mos">
      <?= fb($errors, 'age_label', 'Age is too long.') ?>
    </div>
    <div class="col-sm-4">
      <label class="form-label" for="sex">Sex <span class="req">*</span></label>
      <select class="form-select<?= inv($errors, 'sex') ?>" id="sex" name="sex" required><?= options(SEXES, $pet['sex']) ?></select>
      <?= fb($errors, 'sex', 'Choose a sex.') ?>
    </div>
    <div class="col-sm-4">
      <label class="form-label" for="intake_date">Intake date <span class="req">*</span></label>
      <input type="date" class="form-control<?= inv($errors, 'intake_date') ?>" id="intake_date" name="intake_date"
             value="<?= e($pet['intake_date']) ?>" max="<?= date('Y-m-d') ?>" required>
      <?= fb($errors, 'intake_date', 'Enter a valid date that is not in the future.') ?>
    </div>

    <div class="col-sm-6">
      <label class="form-label" for="vaccination_status">Vaccination status <span class="req">*</span></label>
      <select class="form-select<?= inv($errors, 'vaccination_status') ?>" id="vaccination_status" name="vaccination_status" required><?= options(VACC_STATUSES, $pet['vaccination_status']) ?></select>
      <?= fb($errors, 'vaccination_status', 'Choose a vaccination status.') ?>
    </div>
    <div class="col-sm-6">
      <label class="form-label" for="adoption_status">Adoption status <span class="req">*</span></label>
      <select class="form-select<?= inv($errors, 'adoption_status') ?>" id="adoption_status" name="adoption_status" required><?= options(ADOPTION_STATUSES, $pet['adoption_status']) ?></select>
      <?= fb($errors, 'adoption_status', 'Choose an adoption status.') ?>
    </div>

    <div class="col-12">
      <label class="form-label" for="notes">Notes</label>
      <textarea class="form-control<?= inv($errors, 'notes') ?>" id="notes" name="notes" rows="3" maxlength="1000" placeholder="Temperament, rescue story, special needs…"><?= e($pet['notes']) ?></textarea>
      <?= fb($errors, 'notes', 'Notes are too long.') ?>
    </div>
  </div>

  <div class="d-flex flex-wrap gap-2 mt-4">
    <button type="submit" class="btn btn-cta"><?= icon('check', 18) ?><?= e($submitLabel) ?></button>
    <a class="btn btn-ghost" href="<?= e($cancelUrl) ?>">Cancel</a>
  </div>
</form>
