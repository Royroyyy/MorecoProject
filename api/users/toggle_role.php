<?php
require_once '../../config.php';
$admin = requireAdmin();

$d       = getJSON();
$id      = (int)($d['id']   ?? 0);
$newRole = clean($d['role'] ?? '');

if (!$id) respond(false, 'User ID is required.', null, 400);
if ($id === (int)$admin['id']) {
    respond(false, 'You cannot change your own role.', null, 400);
}

$validRoles = ['applicant', 'member', 'admin', 'clerk', 'loan_officer'];
if (!in_array($newRole, $validRoles)) {
    respond(false, 'Invalid role. Valid roles: ' . implode(', ', $validRoles), null, 400);
}

$check = $pdo->prepare('SELECT id, first_name, last_name, role FROM users WHERE id = ? LIMIT 1');
$check->execute([$id]);
$user = $check->fetch();
if (!$user) respond(false, 'User not found.', null, 404);

$pdo->prepare('UPDATE users SET role = ?, updated_at = NOW() WHERE id = ?')
    ->execute([$newRole, $id]);

logAction($pdo, $admin['id'], 'CHANGE_USER_ROLE', 'users', $id,
    "Changed role of {$user['first_name']} {$user['last_name']} from {$user['role']} to $newRole");

respond(true, "Role updated to $newRole.", ['new_role' => $newRole]);
?>
