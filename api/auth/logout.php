<?php
require_once '../../config.php';

$user = getSessionUser();
if ($user) {
    logAction($pdo, $user['id'], 'LOGOUT', 'users', $user['id'], 'User logged out');
}

$_SESSION = [];
session_destroy();

header('Content-Type: application/json');
echo json_encode(['success' => true, 'message' => 'Logged out successfully.', 'data' => null]);
?>
