<?php
require_once '../../config.php';
$user = requireLogin();

$pdo->prepare(
    'UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0'
)->execute([$user['id']]);

respond(true, 'All notifications marked as read.');
?>
