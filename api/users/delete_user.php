<?php
require_once '../../config.php';
$admin = requireAdmin();

$d  = getJSON();
$id = (int)($d['id'] ?? 0);

if (!$id) respond(false, 'User ID is required.', null, 400);
if ($id === (int)$admin['id']) {
    respond(false, 'You cannot delete your own account.', null, 400);
}

$check = $pdo->prepare('SELECT id, first_name, last_name, role FROM users WHERE id = ? LIMIT 1');
$check->execute([$id]);
$user = $check->fetch();
if (!$user) respond(false, 'User not found.', null, 404);

if ($user['role'] === 'admin') {
    respond(false, 'Admin accounts cannot be deleted. Demote them first.', null, 403);
}

$pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);

logAction($pdo, $admin['id'], 'DELETE_USER', 'users', $id,
    "Deleted user: {$user['first_name']} {$user['last_name']} ({$user['role']})");

respond(true, 'User deleted successfully.');
?>
