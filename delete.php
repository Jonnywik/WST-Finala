<?php
// Deletes a pet. POST only (sent by the confirmation modal), so a plain link can never delete a record.
require __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}
csrf_check();

$pdo = db();
$pet = find_pet($pdo, (int) post('id'));
if ($pet) {
    // vet_logs and adoption_inquiries rows go with it (ON DELETE CASCADE).
    $pdo->prepare('DELETE FROM pets WHERE id = ?')->execute([$pet['id']]);
    flash('success', $pet['name'] . ' (' . $pet['pet_code'] . ') was deleted.');
} else {
    flash('error', 'That pet record no longer exists.');
}
redirect('index.php');
