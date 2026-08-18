<?php
require_once '../../config.php';
$user = requireLogin();

$d  = getJSON();
$id = (int)($d['id'] ?? 0);

if (!$id) {
    respond(false, 'Notification ID is required.', null, 400);
}

$stmt = $pdo->prepare(
    'UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?'
);
$stmt->execute([$id, $user['id']]);

if ($stmt->rowCount() === 0) {
    respond(false, 'Notification not found or you do not have permission.', null, 404);
}

respond(true, 'Notification marked as read.');
?>
