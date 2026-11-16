<?php
require __DIR__ . '/includes/functions.php';
$pdo = db();

$errors = [];
$pet = [
    'pet_code' => next_pet_code($pdo), 'name' => '', 'species' => '', 'breed' => '', 'age_label' => '',
    'sex' => 'Unknown', 'vaccination_status' => 'Pending', 'adoption_status' => 'Available',
    'intake_date' => date('Y-m-d'), 'notes' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    [$pet, $errors] = validate_pet($pdo);
    if (!$errors) {
        $pdo->prepare('INSERT INTO pets (pet_code, name, species, breed, age_label, sex, vaccination_status, adoption_status, intake_date, notes)
                       VALUES (:pet_code, :name, :species, :breed, :age_label, :sex, :vaccination_status, :adoption_status, :intake_date, :notes)')
            ->execute($pet);
        flash('success', $pet['name'] . ' was registered as ' . $pet['pet_code'] . '.');
        redirect('view.php?id=' . $pdo->lastInsertId());
    }
}

$submitLabel = 'Register Pet';
$cancelUrl = 'index.php';
$pageTitle = 'Register Pet';
$activeNav = 'pets';
require __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
  <div class="col-lg-9 col-xl-8">
    <a class="btn btn-clay btn-sm mb-3" href="index.php"><?= icon('arrow-left', 16) ?>Back to registry</a>
    <div class="clay-card pop-in">
      <h1 class="h2">Register a new pet</h1>
      <p class="text-muted-ink">The next free pet code is suggested for you. Fields marked <span class="req">*</span> are required.</p>
      <?php require __DIR__ . '/includes/pet_form.php'; ?>
    </div>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
