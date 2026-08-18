<?php
require_once '../../config.php';
$user = requireLogin();

$targetUserId = $user['id'];
if (in_array($user['role'], ['admin', 'clerk']) && isset($_GET['user_id'])) {
    $targetUserId = (int)$_GET['user_id'];
}

$stmt = $pdo->prepare(
    'SELECT ma.*,
            u.first_name, u.last_name, u.email, u.username, u.phone,
            r.first_name AS reviewer_first, r.last_name AS reviewer_last
     FROM membership_applications ma
     JOIN users u ON ma.user_id = u.id
     LEFT JOIN users r ON ma.reviewed_by = r.id
     WHERE ma.user_id = ?
     ORDER BY ma.created_at DESC
     LIMIT 1'
);
$stmt->execute([$targetUserId]);
$application = $stmt->fetch();

if (!$application) {
    respond(true, 'No application found.', ['application' => null]);
}

respond(true, 'Application retrieved.', ['application' => $application]);
?>
