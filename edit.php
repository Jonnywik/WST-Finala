<?php
require __DIR__ . '/includes/functions.php';
$pdo = db();

$id = (int) ($_GET['id'] ?? 0);
$pet = find_pet($pdo, $id);
if (!$pet) {
    flash('error', 'That pet record no longer exists.');
    redirect('index.php');
}
$originalName = $pet['name'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    [$pet, $errors] = validate_pet($pdo, $id);
    if (!$errors) {
        $pdo->prepare('UPDATE pets SET pet_code = :pet_code, name = :name, species = :species, breed = :breed, age_label = :age_label,
                       sex = :sex, vaccination_status = :vaccination_status, adoption_status = :adoption_status,
                       intake_date = :intake_date, notes = :notes WHERE id = :id')
            ->execute($pet + ['id' => $id]);
        flash('success', $pet['name'] . "'s profile was updated.");
        redirect('view.php?id=' . $id);
    }
}

$submitLabel = 'Save Changes';
$cancelUrl = 'view.php?id=' . $id;
$pageTitle = 'Edit ' . $originalName;
$activeNav = 'pets';
require __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
  <div class="col-lg-9 col-xl-8">
    <a class="btn btn-ghost btn-sm mb-3" href="view.php?id=<?= $id ?>"><?= icon('arrow-left', 16) ?>Back to profile</a>
    <div class="panel pop-in">
      <h1 data-split class="h2">Edit <?= e($originalName) ?></h1>
      <p class="text-muted-ink">Update the profile, vaccination status or adoption status.</p>
      <?php require __DIR__ . '/includes/pet_form.php'; ?>
    </div>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
