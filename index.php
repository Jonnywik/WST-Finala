<?php
require __DIR__ . '/includes/functions.php';
$pdo = db();

$n = array_map('intval', $pdo->query("SELECT
    (SELECT COUNT(*) FROM pets) AS total,
    (SELECT COUNT(*) FROM pets WHERE adoption_status = 'Available') AS ready,
    (SELECT COUNT(*) FROM pets WHERE adoption_status = 'Under Vet Care') AS vetcare,
    (SELECT COUNT(*) FROM vet_logs) AS visits,
    (SELECT COUNT(*) FROM adoption_inquiries WHERE inquiry_status = 'Pending Review') AS pending")->fetch());

$pets = $pdo->query('SELECT * FROM pets ORDER BY intake_date DESC, id DESC')->fetchAll();

// Pets ready to adopt, oldest record first. Pet of the Day rotates through them, one per calendar day.
$available = array_values(array_filter($pets, fn($p) => $p['adoption_status'] === 'Available'));
usort($available, fn($a, $b) => $a['id'] <=> $b['id']);
$spot = $available ? $available[(int) date('z') % count($available)] : null;

// The statement under the hero, written from live counts. app.js lights the words up one by one on scroll.
$statement = sprintf(
    'Right now %d %s AdopIT! home. %d %s ready to leave today, %d %s healing with our vets, and %d %s waiting to hear back.',
    $n['total'], $n['total'] === 1 ? 'animal calls' : 'animals call',
    $n['ready'], $n['ready'] === 1 ? 'is' : 'are',
    $n['vetcare'], $n['vetcare'] === 1 ? 'is' : 'are',
    $n['pending'], $n['pending'] === 1 ? 'family is' : 'families are'
);

$tiles = [
    ['Total pets', $n['total'], '#registry'],
    ['Ready to adopt', $n['ready'], '#ready'],
    ['Vet visits logged', $n['visits'], 'vet_care.php'],
    ['Pending inquiries', $n['pending'], 'adoptions.php'],
];
$tileMax = max(1, max(array_column($tiles, 1)));

// Decorative avatars floating in the hero: species, left %, top %, size px, depth (how far they drift).
$floaters = [
    ['Dog', 72, 24, 128, 1.0],
    ['Cat', 88, 36, 90, 0.6],
    ['Rabbit', 77, 55, 80, 1.4],
    ['Bird', 91, 64, 64, 0.8],
    ['Other', 63, 38, 56, 1.7],
];

$pageTitle = 'Welcome';
$activeNav = 'pets';
$showIntro = true;
require __DIR__ . '/includes/header.php';
?>

<!-- 01 HERO: pinned while you scroll past it; it shrinks, the title lifts and the avatars drift -->
<section class="stage" data-stage data-bg="#0F0E13" data-fg="#F4EFE6" aria-label="Welcome">
  <div class="stage-pin">
    <div class="hero spot" data-hero>
      <div class="hero-glow" aria-hidden="true"></div>
      <div class="hero-floaters" aria-hidden="true">
        <?php foreach ($floaters as $i => [$sp, $x, $y, $size, $depth]): ?>
          <span class="floater" data-depth="<?= $depth ?>" style="left:<?= $x ?>%;top:<?= $y ?>%;--i:<?= $i ?>">
            <span class="floater-pop"><span class="floater-bob"><?= species_avatar($sp, $size) ?></span></span>
          </span>
        <?php endforeach; ?>
      </div>

      <div class="hero-top hero-fade" style="--i:0">
        <span class="db-pill"><span class="db-dot"></span>MySQL live · <?= e(DB_NAME) ?></span>
        <span class="hero-date"><span data-greeting>Welcome</span> · <?= date('l, F j') ?></span>
      </div>

      <h1 class="hero-title" data-hero-title>
        <span class="line"><span class="li" style="--i:0">Find</span> <span class="li outline" style="--i:1">them</span></span>
        <span class="line"><span class="li" style="--i:2">a</span> <span class="li sun" style="--i:3">home.</span></span>
      </h1>

      <div class="hero-bottom">
        <div class="hero-fade" style="--i:2">
          <p class="hero-lead">The shelter registry for every arrival, vet visit and adoption. <strong><?= $n['ready'] ?> ready to go home today.</strong></p>
          <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-cta" href="#ready" data-burst><?= icon('paw', 18) ?>Meet them</a>
            <a class="btn btn-ghost" href="vet_care.php#form"><?= icon('stethoscope', 18) ?>Log Vet Care</a>
            <a class="btn btn-ghost" href="adoptions.php#form"><?= icon('heart', 18) ?>New Inquiry</a>
          </div>
        </div>
        <a class="badge-spin hero-fade" style="--i:4" href="#ready" data-cursor="Scroll" data-burst aria-label="Jump to pets ready for adoption">
          <svg class="badge-ring" viewBox="0 0 120 120" data-spin aria-hidden="true">
            <defs><path id="badgeCircle" d="M60,60 m-47,0 a47,47 0 1,1 94,0 a47,47 0 1,1 -94,0"/></defs>
            <text><textPath href="#badgeCircle" textLength="292" lengthAdjust="spacing">ADOPT · DON'T SHOP · ADOPT · DON'T SHOP ·</textPath></text>
          </svg>
          <span class="badge-core"><?= icon('paw', 28) ?></span>
        </a>
      </div>
    </div>
  </div>
</section>

<!-- 02 STATEMENT: the words light up as you scroll through them -->
<section class="statement-wrap" data-bg="#1A1428" data-fg="#F4EFE6" aria-label="Today at the shelter">
  <span class="eyebrow" data-scramble>(02) Today at the shelter</span>
  <p class="statement" data-statement>
    <?php foreach (explode(' ', $statement) as $w): ?><span class="sw<?= ctype_digit($w) ? ' num' : '' ?>"><?= e($w) ?></span> <?php endforeach; ?>
  </p>
</section>

<!-- 03 READY TO ADOPT: vertical scrolling moves this row of cards sideways -->
<section class="hs" id="ready" data-hs data-bg="#FFD23F" data-fg="#0F0E13" aria-labelledby="readyTitle">
  <div class="hs-pin">
    <div class="hs-head">
      <div>
        <span class="eyebrow" data-scramble>(03) Ready to adopt</span>
        <h2 class="section-title" id="readyTitle" data-chars data-skew>Meet the crew</h2>
      </div>
      <?php if ($available): ?>
        <div class="hs-meta" aria-hidden="true">
          <span class="hs-count"><span data-hs-index>01</span> / <?= sprintf('%02d', count($available)) ?></span>
          <span class="hs-bar"><span data-hs-bar></span></span>
        </div>
      <?php endif; ?>
    </div>
    <div class="hs-track" data-hs-track>
      <?php foreach ($available as $i => $p): ?>
        <article class="pet-card spot" data-tilt>
          <div class="card-top">
            <?php if ($spot && (int) $spot['id'] === (int) $p['id']): ?><span class="card-tag"><?= icon('star', 14) ?>Pet of the day</span><?php else: ?><span></span><?php endif; ?>
            <span class="card-num"><?= sprintf('%02d', $i + 1) ?></span>
          </div>
          <div class="card-avatar"><?= species_avatar($p['species'], 150) ?></div>
          <h3 class="card-name"><a href="view.php?id=<?= (int) $p['id'] ?>" data-cursor="View"><?= e($p['name']) ?></a></h3>
          <p class="card-meta"><?= e(trim($p['breed'] . ' · ' . $p['age_label'] . ' · ' . $p['sex'], ' ·')) ?></p>
          <div class="d-flex flex-wrap gap-2 mb-3"><span class="code-pill"><?= e($p['pet_code']) ?></span><?= status_pill($p['vaccination_status']) ?></div>
          <div class="card-actions">
            <a class="btn btn-ghost btn-sm" href="view.php?id=<?= (int) $p['id'] ?>"><?= icon('eye', 16) ?>Profile</a>
            <a class="btn btn-cta btn-sm" href="adoptions.php?pet_id=<?= (int) $p['id'] ?>#form" data-burst><?= icon('heart', 16) ?>Inquire</a>
          </div>
        </article>
      <?php endforeach; ?>
      <a class="pet-card card-new" href="create.php" data-cursor="Add">
        <span class="card-plus"><?= icon('plus', 44) ?></span>
        <span class="card-name"><?= $available ? 'Register a new arrival' : 'No pet is available right now. Register a new arrival' ?></span>
      </a>
    </div>
  </div>
</section>

<!-- 04 NUMBERS -->
<section class="stats-wrap" data-bg="#0F0E13" data-fg="#F4EFE6" aria-label="Live counts">
  <div class="stats">
    <?php foreach ($tiles as $i => [$label, $num, $href]): ?>
      <a class="stat" href="<?= $href ?>" data-reveal style="--i:<?= $i ?>">
        <span class="stat-head"><span class="stat-label"><?= e($label) ?></span><span class="stat-go"><?= icon('arrow-up-right', 22) ?></span></span>
        <span class="stat-num" data-count="<?= $num ?>"><?= $num ?></span>
        <span class="stat-bar"><span style="--w:<?= round($num / $tileMax, 3) ?>"></span></span>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<?php if ($pets): ?>
<div class="tapes" aria-hidden="true">
  <div class="tape" data-marquee="-1">
    <div class="marquee-track">
      <?php foreach ($pets as $p): ?>
        <span class="marquee-item"><?= species_avatar($p['species'], 40) ?><?= e($p['name']) ?></span><span class="marquee-sep"><?= icon('star', 22) ?></span>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="tape tape-alt" data-marquee="1">
    <div class="marquee-track">
      <?php foreach (['Adopt', 'Foster', 'Rescue', 'Vaccinate', 'Love', 'Repeat'] as $word): ?>
        <span class="marquee-item"><?= $word ?></span><span class="marquee-sep"><?= icon('heart', 22) ?></span>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- 05 REGISTRY -->
<section id="registry" class="registry" data-bg="#0F0E13" data-fg="#F4EFE6" data-filter-scope aria-labelledby="registryTitle">
  <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
    <div>
      <span class="eyebrow" data-scramble>(05) The registry</span>
      <h2 class="section-title" id="registryTitle" data-chars data-skew>Every pet</h2>
    </div>
    <span class="text-muted-ink">Live records from the <code>pets</code> table. Hover a row for a preview, click it to open.</span>
  </div>

  <div class="panel filter-bar mb-3" data-reveal>
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

  <div class="panel table-card" data-reveal>
    <div class="table-responsive">
      <table class="table data-table">
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
                <a class="icon-btn view" href="view.php?id=<?= (int) $p['id'] ?>" aria-label="View <?= e($p['name']) ?>" title="View"><?= icon('eye', 18) ?></a>
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
