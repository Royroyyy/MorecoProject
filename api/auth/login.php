<?php
require_once '../../config.php';

$d        = getJSON();
$username = clean($d['username'] ?? '');
$password = $d['password'] ?? '';

if (!$username || !$password) {
    respond(false, 'Please fill in all fields.', null, 400);
}

$stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? AND is_active = 1 LIMIT 1');
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    respond(false, 'Invalid username or password.', null, 401);
}

unset($user['password']);
$_SESSION['user'] = $user;

logAction($pdo, $user['id'], 'LOGIN', 'users', $user['id'], 'User logged in');

respond(true, 'Login successful.', $user);
?>
