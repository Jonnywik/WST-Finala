<?php
// Shared bootstrap: session, DB connection, option lists and small helpers.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/svg_helpers.php';

const SPECIES           = ['Dog', 'Cat', 'Rabbit', 'Bird', 'Other'];
const SEXES             = ['Male', 'Female', 'Unknown'];
const VACC_STATUSES     = ['Up to Date', 'Partial', 'Pending'];
const ADOPTION_STATUSES = ['Available', 'Pending Adoption', 'Under Vet Care', 'Adopted'];
const CARE_TYPES        = ['Vaccination', 'Checkup', 'Deworming', 'Medication', 'Grooming'];
const INQUIRY_STATUSES  = ['Pending Review', 'Meet & Greet', 'Approved', 'Declined'];
const LIVING_SETUPS     = ['House with yard', 'House', 'Apartment', 'Condo', 'Other'];

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function post(string $key): string
{
    $v = $_POST[$key] ?? '';
    return is_string($v) ? trim($v) : '';
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function take_flash(): ?array
{
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

function csrf_check(): void
{
    if (!hash_equals(csrf_token(), post('csrf'))) {
        http_response_code(400);
        exit('Invalid form token. Go back, refresh the page and try again.');
    }
}

function valid_date(string $date): bool
{
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

function fmt_date(?string $date): string
{
    return $date ? date('M j, Y', strtotime($date)) : '—';
}

function options(array $list, $selected): string
{
    $html = '';
    foreach ($list as $item) {
        $html .= '<option' . ((string) $item === (string) $selected ? ' selected' : '') . '>' . e($item) . '</option>';
    }
    return $html;
}

// Form helpers: " is-invalid" class and the feedback line (server error or the default hint).
function inv(array $errors, string $key): string
{
    return isset($errors[$key]) ? ' is-invalid' : '';
}

function fb(array $errors, string $key, string $default): string
{
    return '<div class="invalid-feedback">' . e($errors[$key] ?? $default) . '</div>';
}

function next_pet_code(PDO $pdo): string
{
    $max = (int) $pdo->query("SELECT MAX(CAST(SUBSTRING(pet_code, 5) AS UNSIGNED)) FROM pets WHERE pet_code LIKE 'PET-%'")->fetchColumn();
    return sprintf('PET-%03d', $max + 1);
}

function find_pet(PDO $pdo, int $id): ?array
{
    $st = $pdo->prepare('SELECT * FROM pets WHERE id = ?');
    $st->execute([$id]);
    return $st->fetch() ?: null;
}

// Reads the pet form from $_POST. Returns [clean data, errors keyed by field].
function validate_pet(PDO $pdo, ?int $ignoreId = null): array
{
    $d = [];
    foreach (['pet_code', 'name', 'species', 'breed', 'age_label', 'sex', 'vaccination_status', 'adoption_status', 'intake_date', 'notes'] as $k) {
        $d[$k] = post($k);
    }
    $d['pet_code'] = strtoupper($d['pet_code']);
    $err = [];

    if (!preg_match('/^PET-\d{3,6}$/', $d['pet_code'])) {
        $err['pet_code'] = 'Use the format PET-001.';
    } else {
        $st = $pdo->prepare('SELECT id FROM pets WHERE pet_code = ? AND id <> ?');
        $st->execute([$d['pet_code'], (int) $ignoreId]);
        if ($st->fetch()) {
            $err['pet_code'] = 'This pet code is already taken.';
        }
    }
    if ($d['name'] === '' || mb_strlen($d['name']) > 60) {
        $err['name'] = 'Name is required (up to 60 characters).';
    }
    if (!in_array($d['species'], SPECIES, true)) {
        $err['species'] = 'Choose a species.';
    }
    if (mb_strlen($d['breed']) > 60) {
        $err['breed'] = 'Breed is too long (60 characters max).';
    }
    if (mb_strlen($d['age_label']) > 30) {
        $err['age_label'] = 'Age is too long (30 characters max).';
    }
    if (!in_array($d['sex'], SEXES, true)) {
        $err['sex'] = 'Choose a sex.';
    }
    if (!in_array($d['vaccination_status'], VACC_STATUSES, true)) {
        $err['vaccination_status'] = 'Choose a vaccination status.';
    }
    if (!in_array($d['adoption_status'], ADOPTION_STATUSES, true)) {
        $err['adoption_status'] = 'Choose an adoption status.';
    }
    if (!valid_date($d['intake_date']) || $d['intake_date'] > date('Y-m-d')) {
        $err['intake_date'] = 'Enter a valid intake date that is not in the future.';
    }
    if (mb_strlen($d['notes']) > 1000) {
        $err['notes'] = 'Notes are too long (1000 characters max).';
    }
    return [$d, $err];
}

// Keeps a pet's adoption status in step with its inquiries:
// any Approved -> Adopted, else any Meet & Greet -> Pending Adoption,
// else a pet that was Pending Adoption / Adopted goes back to Available.
function sync_pet_adoption(PDO $pdo, int $petId): void
{
    $st = $pdo->prepare('SELECT inquiry_status FROM adoption_inquiries WHERE pet_id = ?');
    $st->execute([$petId]);
    $statuses = $st->fetchAll(PDO::FETCH_COLUMN);

    if (in_array('Approved', $statuses, true)) {
        $new = 'Adopted';
    } elseif (in_array('Meet & Greet', $statuses, true)) {
        $new = 'Pending Adoption';
    } else {
        $pet = find_pet($pdo, $petId);
        if (!$pet || !in_array($pet['adoption_status'], ['Pending Adoption', 'Adopted'], true)) {
            return;
        }
        $new = 'Available';
    }
    $pdo->prepare('UPDATE pets SET adoption_status = ? WHERE id = ?')->execute([$new, $petId]);
}
