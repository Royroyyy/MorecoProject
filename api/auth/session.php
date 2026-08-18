<?php
require_once '../../config.php';

$user = getSessionUser();

if ($user) {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? AND is_active = 1 LIMIT 1');
    $stmt->execute([$user['id']]);
    $fresh = $stmt->fetch();
    if ($fresh) {
        unset($fresh['password']);
        $_SESSION['user'] = $fresh;
        $user = $fresh;
    } else {
        
        $_SESSION = [];
        session_destroy();
        $user = null;
    }
}

respond(true, 'Session check complete.', ['user' => $user]);
?>
