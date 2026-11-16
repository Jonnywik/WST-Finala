<?php
// Expects: $pageTitle (string), $activeNav ('pets' | 'vet' | 'adoptions' | ''), optional $showIntro (index only).
$activeNav = $activeNav ?? '';
$showIntro = !empty($showIntro);
$navItems = [
    'pets'      => ['index.php', 'Welcome & Pets', 'home'],
    'vet'       => ['vet_care.php', 'Vet Care Logs', 'stethoscope'],
    'adoptions' => ['adoptions.php', 'Adoption Inquiries', 'heart'],
];
$flash = take_flash();
?>
<!doctype html>
<html lang="en" data-bs-theme="dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle ?? 'Welcome') ?> · AdopIT!</title>
  <script>
    // Runs before first paint: "motion" switches on every animation in app.css,
    // "intro-seen" skips the loading screen after the first visit in this tab.
    (function (r) {
      r.classList.add('js');
      try { if (!matchMedia('(prefers-reduced-motion: reduce)').matches) r.classList.add('motion'); } catch (e) {}
      var seen = <?= $showIntro ? 'false' : 'true' ?>;
      try { seen = seen || !!sessionStorage.getItem('adopit-intro'); } catch (e) { seen = true; }
      if (seen) r.classList.add('intro-seen');
    })(document.documentElement);
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@300..700&family=DM+Sans:opsz,wght@9..40,400..700&display=swap" rel="stylesheet">
  <link href="assets/vendor/bootstrap/bootstrap.min.css" rel="stylesheet">
  <link href="assets/app.css" rel="stylesheet">
</head>
<body>
<a class="skip-link visually-hidden-focusable" href="#main">Skip to content</a>
<div class="progress-line" aria-hidden="true"></div>

<?php if ($showIntro): ?>
<div class="intro" aria-hidden="true">
  <div class="intro-top"><span>Pet adoption &amp; vet care registry</span><span>Fetching pets</span></div>
  <div class="intro-word"><?php foreach (mb_str_split('AdopIT!') as $i => $ch): ?><span style="--i:<?= $i ?>"><?= e($ch) ?></span><?php endforeach; ?></div>
  <div class="intro-count"><span data-intro-count>0</span>%</div>
</div>
<?php endif; ?>

<header class="nav-wrap container-xl">
  <nav class="navbar navbar-expand-lg bar" aria-label="Main">
    <a class="navbar-brand brand" href="index.php"><?= brand_logo(30) ?><span>AdopIT!</span></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
            aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav mx-auto">
        <?php foreach ($navItems as $key => [$href, $label, $ico]): ?>
          <li class="nav-item">
            <a class="nav-link<?= $activeNav === $key ? ' active' : '' ?>" href="<?= $href ?>"<?= $activeNav === $key ? ' aria-current="page"' : '' ?>>
              <?= icon($ico, 18) ?><?= roll_label($label) ?>
              <?php if ($activeNav === $key): ?><span class="nav-ind"></span><?php endif; ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
      <a class="btn btn-cta" href="create.php"><?= icon('plus', 18) ?>Register Pet</a>
    </div>
  </nav>
</header>

<?php if ($flash): ?>
<div class="flash-wrap">
  <div class="flash flash-<?= e($flash['type']) ?> alert alert-dismissible fade show" role="alert">
    <?= icon($flash['type'] === 'success' ? 'check' : 'alert') ?>
    <span><?= e($flash['message']) ?></span>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    <?php if ($flash['type'] === 'success'): ?><span class="flash-timer" aria-hidden="true"></span><?php endif; ?>
  </div>
</div>
<?php endif; ?>

<main id="main" class="container-xl py-4">
