<?php
require_once '../../config.php';

$d         = getJSON();
$firstName = clean($d['firstName'] ?? '');
$lastName  = clean($d['lastName']  ?? '');
$email     = clean($d['email']     ?? '');
$username  = clean($d['username']  ?? '');
$password  = $d['password'] ?? '';
$phone     = clean($d['phone']     ?? '');
$address   = clean($d['address']   ?? '');

if (!$firstName || !$lastName || !$email || !$username || !$password) {
    respond(false, 'Please fill in all required fields.', null, 400);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(false, 'Please enter a valid email address.', null, 400);
}
if (strlen($username) < 3) {
    respond(false, 'Username must be at least 3 characters.', null, 400);
}
if (strlen($password) < 6) {
    respond(false, 'Password must be at least 6 characters.', null, 400);
}

$check = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
$check->execute([$username, $email]);
if ($check->fetch()) {
    respond(false, 'Username or email is already taken.', null, 409);
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $pdo->prepare(
    "INSERT INTO users (first_name, last_name, email, username, password, role, phone, address)
     VALUES (?, ?, ?, ?, ?, 'applicant', ?, ?)"
);
$stmt->execute([$firstName, $lastName, $email, $username, $hash, $phone, $address]);
$newId = (int)$pdo->lastInsertId();

logAction($pdo, $newId, 'REGISTER', 'users', $newId, 'New account registered');

respond(true, 'Account created successfully. You can now log in.', ['id' => $newId]);
?>
