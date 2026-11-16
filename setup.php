<?php
// One-click installer: creates adopit_db, the three tables and the sample data from sql/database.sql.
session_start();
require __DIR__ . '/config/db.php';
require __DIR__ . '/includes/svg_helpers.php';

function e($v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

$error = '';
$done = false;
$installed = false;

try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';charset=utf8mb4', DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $st = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ? AND table_name = 'pets'");
    $st->execute([DB_NAME]);
    $installed = (bool) $st->fetchColumn();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (empty($_SESSION['setup_token']) || !hash_equals($_SESSION['setup_token'], (string) ($_POST['token'] ?? ''))) {
            throw new RuntimeException('The form expired. Reload this page and try again.');
        }
        $sql = file_get_contents(__DIR__ . '/sql/database.sql');
        $sql = preg_replace('/^--.*$/m', '', $sql);                   // drop comment lines
        foreach (preg_split('/;\s*(?:\r?\n|$)/', $sql) as $statement) {
            if (trim($statement) !== '') {
                $pdo->exec($statement);
            }
        }
        $done = $installed = true;
    }
} catch (Throwable $ex) {
    $error = $ex->getMessage();
}
$_SESSION['setup_token'] = bin2hex(random_bytes(16));
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Setup · AdopIT!</title>
  <link href="assets/vendor/bootstrap/bootstrap.min.css" rel="stylesheet">
  <link href="assets/app.css" rel="stylesheet">
</head>
<body>
<main class="container py-5" style="max-width: 620px">
  <div class="panel pop-in">
    <div class="brand mb-3"><?= brand_logo() ?><span>AdopIT! setup</span></div>

    <?php if ($error): ?>
      <div class="flash flash-error p-3 mb-3"><?= icon('alert') ?><span><?= e($error) ?></span></div>
      <p>Make sure <strong>MySQL</strong> is started in the XAMPP Control Panel and that the settings in <code>config/db.php</code> are right.</p>
    <?php endif; ?>

    <?php if ($done): ?>
      <div class="flash flash-success p-3 mb-3"><?= icon('check') ?><span>Database <?= e(DB_NAME) ?> is installed with sample data.</span></div>
      <a class="btn btn-cta" href="index.php"><?= icon('home', 18) ?>Open AdopIT!</a>
    <?php else: ?>
      <p>This creates the <code><?= e(DB_NAME) ?></code> database with the <code>pets</code>, <code>vet_logs</code> and <code>adoption_inquiries</code> tables, then loads 10 sample pets.</p>
      <?php if ($installed): ?>
        <p><strong>The database is already installed.</strong> Running setup again erases every pet, vet log and inquiry and restores the sample data.</p>
      <?php endif; ?>
      <form method="post" class="d-flex flex-wrap gap-2"<?= $installed ? ' onsubmit="return confirm(\'Erase all AdopIT! records and restore the sample data?\')"' : '' ?>>
        <input type="hidden" name="token" value="<?= e($_SESSION['setup_token']) ?>">
        <button type="submit" class="btn <?= $installed ? 'btn-danger-solid' : 'btn-cta' ?>"><?= icon('database', 18) ?><?= $installed ? 'Reset to sample data' : 'Install database' ?></button>
        <?php if ($installed): ?><a class="btn btn-ghost" href="index.php">Back to AdopIT!</a><?php endif; ?>
      </form>
    <?php endif; ?>
  </div>
</main>
</body>
</html>
