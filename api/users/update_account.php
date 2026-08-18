<?php
require_once '../../config.php';
$me = requireLogin();

$d         = getJSON();
$firstName = clean($d['firstName'] ?? '');
$lastName  = clean($d['lastName']  ?? '');
$email     = clean($d['email']     ?? '');
$username  = clean($d['username']  ?? '');
$phone     = clean($d['phone']     ?? '');
$address   = clean($d['address']   ?? '');
$currPass  = $d['currentPass'] ?? '';
$newPass   = $d['newPass']     ?? '';

if (!$firstName || !$lastName || !$email || !$username) {
    respond(false, 'Name, email, and username cannot be empty.', null, 400);
}

$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
$stmt->execute([$me['id']]);
$dbUser = $stmt->fetch();

if ($newPass !== '') {
    if (!password_verify($currPass, $dbUser['password'])) {
        respond(false, 'Current password is incorrect.', null, 400);
    }
    if (strlen($newPass) < 6) {
        respond(false, 'New password must be at least 6 characters.', null, 400);
    }
    $hash = password_hash($newPass, PASSWORD_DEFAULT);
    $pdo->prepare('UPDATE users SET password = ? WHERE id = ?')
        ->execute([$hash, $me['id']]);
}

$pdo->prepare(
    'UPDATE users SET first_name=?, last_name=?, email=?, username=?, phone=?, address=?, updated_at=NOW()
     WHERE id=?'
)->execute([$firstName, $lastName, $email, $username, $phone, $address, $me['id']]);

$_SESSION['user']['first_name'] = $firstName;
$_SESSION['user']['last_name']  = $lastName;
$_SESSION['user']['email']      = $email;
$_SESSION['user']['username']   = $username;
$_SESSION['user']['phone']      = $phone;
$_SESSION['user']['address']    = $address;

logAction($pdo, $me['id'], 'UPDATE_ACCOUNT', 'users', $me['id'], 'User updated their account');

respond(true, 'Account updated successfully.', $_SESSION['user']);
?>
