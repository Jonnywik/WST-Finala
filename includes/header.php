<?php
// Expects: $pageTitle (string), $activeNav ('pets' | 'vet' | 'adoptions' | '').
$activeNav = $activeNav ?? '';
$navItems = [
    'pets'      => ['index.php', 'Welcome & Pets', 'home'],
    'vet'       => ['vet_care.php', 'Vet Care Logs', 'stethoscope'],
    'adoptions' => ['adoptions.php', 'Adoption Inquiries', 'heart'],
];
$flash = take_flash();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle ?? 'Welcome') ?> · AdopIT!</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600;700&family=Varela+Round&display=swap" rel="stylesheet">
  <link href="assets/vendor/bootstrap/bootstrap.min.css" rel="stylesheet">
  <link href="assets/app.css" rel="stylesheet">
</head>
<body>
<a class="skip-link visually-hidden-focusable" href="#main">Skip to content</a>

<header class="nav-wrap container-xl">
  <nav class="navbar navbar-expand-lg pill-nav" aria-label="Main">
    <a class="navbar-brand brand" href="index.php"><?= brand_logo() ?><span>AdopIT!</span></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
            aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav mx-auto">
        <?php foreach ($navItems as $key => [$href, $label, $ico]): ?>
          <li class="nav-item">
            <a class="nav-link<?= $activeNav === $key ? ' active' : '' ?>" href="<?= $href ?>"<?= $activeNav === $key ? ' aria-current="page"' : '' ?>>
              <?= icon($ico, 18) ?><?= e($label) ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
      <a class="btn btn-cta" href="create.php"><?= icon('plus', 18) ?>Register Pet</a>
    </div>
  </nav>
</header>

<main id="main" class="container-xl py-4">
<?php if ($flash): ?>
  <div class="flash flash-<?= e($flash['type']) ?> alert alert-dismissible fade show" role="alert">
    <?= icon($flash['type'] === 'success' ? 'check' : 'alert') ?>
    <span><?= e($flash['message']) ?></span>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>
