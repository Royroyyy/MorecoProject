<?php
require_once '../../config.php';
$user = requireLogin();

$d  = getJSON();
$id = (int)($d['id'] ?? 0);
if (!$id) respond(false, 'Registration ID is required.', null, 400);

if ($user['role'] === 'admin') {
    $pdo->prepare('DELETE FROM event_registrations WHERE id = ?')->execute([$id]);
} else {
    $affected = $pdo->prepare(
        'DELETE FROM event_registrations WHERE id = ? AND user_id = ?'
    );
    $affected->execute([$id, $user['id']]);
    if ($affected->rowCount() === 0) {
        respond(false, 'Registration not found or you do not have permission.', null, 403);
    }
}

logAction($pdo, $user['id'], 'CANCEL_EVENT_REGISTRATION', 'event_registrations', $id, 'Registration cancelled');
respond(true, 'Registration cancelled successfully.');
?>
