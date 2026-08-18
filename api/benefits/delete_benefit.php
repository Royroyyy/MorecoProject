<?php
require_once '../../config.php';
requireAdmin();

$d  = getJSON();
$id = (int)($d['id'] ?? 0);
if (!$id) respond(false, 'Benefit ID is required.', null, 400);

$check = $pdo->prepare('SELECT id, title FROM benefits WHERE id = ? LIMIT 1');
$check->execute([$id]);
$benefit = $check->fetch();
if (!$benefit) respond(false, 'Benefit not found.', null, 404);

if (isset($_GET['hard']) && $_GET['hard'] == '1') {
    $pdo->prepare('DELETE FROM benefits WHERE id = ?')->execute([$id]);
    logAction($pdo, $_SESSION['user']['id'], 'DELETE_BENEFIT', 'benefits', $id,
        "Hard deleted benefit: {$benefit['title']}");
    respond(true, 'Benefit permanently deleted.');
} else {
    $pdo->prepare('UPDATE benefits SET is_active = 0 WHERE id = ?')->execute([$id]);
    logAction($pdo, $_SESSION['user']['id'], 'DEACTIVATE_BENEFIT', 'benefits', $id,
        "Deactivated benefit: {$benefit['title']}");
    respond(true, 'Benefit deactivated successfully.');
}
?>
