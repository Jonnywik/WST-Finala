<?php
require __DIR__ . '/includes/functions.php';
$pdo = db();

$stats = $pdo->query("SELECT
    (SELECT COUNT(*) FROM pets) AS total,
    (SELECT COUNT(*) FROM pets WHERE adoption_status = 'Available') AS ready,
    (SELECT COUNT(*) FROM vet_logs) AS visits,
    (SELECT COUNT(*) FROM adoption_inquiries WHERE inquiry_status = 'Pending Review') AS pending")->fetch();

$pets = $pdo->query('SELECT * FROM pets ORDER BY intake_date DESC, id DESC')->fetchAll();

// Pet of the Day: rotates through the Available pets, one per calendar day.
$available = array_values(array_filter($pets, fn($p) => $p['adoption_status'] === 'Available'));
usort($available, fn($a, $b) => $a['id'] <=> $b['id']);
$spot = $available ? $available[(int) date('z') % count($available)] : null;

$tiles = [
    ['Total Pets', $stats['total'], 'clay-mint', '#registry'],
    ['Ready to Adopt', $stats['ready'], 'clay-peach', '#registry'],
    ['Vet Visits Logged', $stats['visits'], 'clay-lav', 'vet_care.php'],
    ['Pending Inquiries', $stats['pending'], 'clay-pink', 'adoptions.php'],
];

$pageTitle = 'Welcome';
$activeNav = 'pets';
require __DIR__ . '/includes/header.php';
?>

<section class="row g-4 mb-4" aria-label="Shelter overview">
  <div class="col-lg-5">
    <div class="clay-card hero-welcome pop-in" style="--i:0">
      <span class="db-pill"><span class="db-dot"></span>MySQL live · <?= e(DB_NAME) ?></span>
      <h1 class="mt-3"><span data-greeting>Welcome</span>, shelter team!</h1>
      <p class="fs-5 mb-4">Every pet here is waiting for a home. Track who arrived, who needs the vet, and who is about to be adopted, all in one place.</p>
      <div class="d-flex flex-wrap gap-2 mt-auto">
        <a class="btn btn-clay" href="vet_care.php#form"><?= icon('stethoscope', 18) ?>Log Vet Care</a>
        <a class="btn btn-clay" href="adoptions.php#form"><?= icon('heart', 18) ?>New Inquiry</a>
      </div>
    </div>
  </div>

  <div class="col-md-7 col-lg-4">
    <div class="clay-card clay-peach spotlight clay-lift pop-in" style="--i:1">
      <span class="eyebrow"><?= icon('star', 14) ?>Pet of the Day</span>
      <?php if ($spot): ?>
        <div class="d-flex align-items-center gap-3 mt-3">
          <?= species_avatar($spot['species'], 84) ?>
          <div>
            <h2 class="h3 mb-1"><?= e($spot['name']) ?></h2>
            <span class="code-pill"><?= e($spot['pet_code']) ?></span>
          </div>
        </div>
        <p class="mt-3 mb-2"><?= e(trim($spot['breed'] . ' · ' . $spot['age_label'] . ' · ' . $spot['sex'], ' ·')) ?></p>
        <div class="mb-3"><?= status_pill($spot['vaccination_status']) ?></div>
        <div class="d-flex flex-wrap gap-2 mt-auto">
          <a class="btn btn-clay btn-sm" href="view.php?id=<?= (int) $spot['id'] ?>"><?= icon('eye', 16) ?>View Profile</a>
          <a class="btn btn-cta btn-sm" href="adoptions.php?pet_id=<?= (int) $spot['id'] ?>#form"><?= icon('heart', 16) ?>Inquire</a>
        </div>
      <?php else: ?>
        <p class="mt-3 mb-3">No pet is marked Available right now. Register a new arrival to feature them here.</p>
        <a class="btn btn-cta btn-sm mt-auto align-self-start" href="create.php"><?= icon('plus', 16) ?>Register Pet</a>
      <?php endif; ?>
    </div>
  </div>

  <div class="col-md-5 col-lg-3">
    <div class="stat-grid">
      <?php foreach ($tiles as $i => [$label, $num, $tone, $href]): ?>
        <a class="clay-card <?= $tone ?> stat-tile clay-lift pop-in" style="--i:<?= $i + 2 ?>" href="<?= $href ?>">
          <span class="stat-num"><?= (int) $num ?></span>
          <span class="stat-label"><?= e($label) ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section id="registry" data-filter-scope aria-labelledby="registryTitle">
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <h2 class="h3 mb-0" id="registryTitle">Pets Registry</h2>
    <span class="text-muted-ink">Live records from the <code>pets</code> table</span>
  </div>

  <div class="clay-card filter-bar mb-3">
    <div class="row g-2 align-items-center">
      <div class="col-lg">
        <div class="search-box">
          <?= icon('search', 18) ?>
          <input type="search" class="form-control" data-filter-search placeholder="Search by code, name or breed" aria-label="Search pets">
        </div>
      </div>
      <div class="col-6 col-lg-auto">
        <select class="form-select" data-filter-attr="species" aria-label="Filter by species">
          <option value="">All species</option><?= options(SPECIES, '') ?>
        </select>
      </div>
      <div class="col-6 col-lg-auto">
        <select class="form-select" data-filter-attr="status" aria-label="Filter by adoption status">
          <option value="">All statuses</option><?= options(ADOPTION_STATUSES, '') ?>
        </select>
      </div>
      <div class="col-lg-auto"><span class="count-chip" data-filter-count aria-live="polite"><?= count($pets) ?> records</span></div>
    </div>
  </div>

  <div class="clay-card table-card">
    <div class="table-responsive">
      <table class="table clay-table">
        <thead>
          <tr>
            <th scope="col">Pet</th>
            <th scope="col">Code</th>
            <th scope="col">Details</th>
            <th scope="col">Vaccination</th>
            <th scope="col">Status</th>
            <th scope="col">Intake</th>
            <th scope="col" class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($pets as $p): ?>
          <tr data-row data-species="<?= e($p['species']) ?>" data-status="<?= e($p['adoption_status']) ?>"
              data-search="<?= e(mb_strtolower($p['pet_code'] . ' ' . $p['name'] . ' ' . $p['breed'])) ?>">
            <td>
              <div class="pet-cell">
                <?= species_avatar($p['species']) ?>
                <div>
                  <a href="view.php?id=<?= (int) $p['id'] ?>"><?= e($p['name']) ?></a>
                  <span class="sub"><?= e($p['breed'] ?: $p['species']) ?></span>
                </div>
              </div>
            </td>
            <td><span class="code-pill"><?= e($p['pet_code']) ?></span></td>
            <td class="text-nowrap"><?= e($p['species']) ?> · <?= e($p['sex']) ?><span class="sub"><?= e($p['age_label'] ?: 'Age unknown') ?></span></td>
            <td><?= status_pill($p['vaccination_status']) ?></td>
            <td><?= status_pill($p['adoption_status']) ?></td>
            <td class="text-nowrap"><?= fmt_date($p['intake_date']) ?></td>
            <td>
              <div class="actions">
                <a class="icon-btn" href="view.php?id=<?= (int) $p['id'] ?>" aria-label="View <?= e($p['name']) ?>" title="View"><?= icon('eye', 18) ?></a>
                <a class="icon-btn edit" href="edit.php?id=<?= (int) $p['id'] ?>" aria-label="Edit <?= e($p['name']) ?>" title="Edit"><?= icon('pencil', 18) ?></a>
                <button type="button" class="icon-btn del" data-bs-toggle="modal" data-bs-target="#deleteModal"
                        data-delete-action="delete.php" data-delete-id="<?= (int) $p['id'] ?>"
                        data-delete-label="<?= e($p['name'] . ' (' . $p['pet_code'] . ')') ?>"
                        data-delete-note="Their vet logs and adoption inquiries will be deleted too. This cannot be undone."
                        aria-label="Delete <?= e($p['name']) ?>" title="Delete"><?= icon('trash', 18) ?></button>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
          <tr data-filter-empty hidden><td colspan="7" class="empty-state">No pets match your search or filters.</td></tr>
        <?php if (!$pets): ?>
          <tr><td colspan="7" class="empty-state">No pets yet. <a href="create.php">Register the first one</a>.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
