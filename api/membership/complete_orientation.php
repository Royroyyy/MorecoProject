<?php
require_once '../../config.php';
$admin = requireAdmin();

$d      = getJSON();
$regId  = (int)($d['registration_id'] ?? 0);
$status = clean($d['status'] ?? 'completed'); 

if (!$regId || !in_array($status, ['completed', 'missed'])) {
    respond(false, 'registration_id and valid status (completed/missed) are required.', null, 400);
}

$stmt = $pdo->prepare(
    'SELECT oreg.*, u.first_name, u.last_name
     FROM orientation_registrations oreg
     JOIN users u ON oreg.user_id = u.id
     WHERE oreg.id = ? LIMIT 1'
);
$stmt->execute([$regId]);
$reg = $stmt->fetch();

if (!$reg) {
    respond(false, 'Registration not found.', null, 404);
}
if ($reg['status'] !== 'scheduled') {
    respond(false, 'This orientation has already been processed.', null, 409);
}

$pdo->prepare(
    'UPDATE orientation_registrations
     SET status=?, completed_at=NOW(), completed_by=?
     WHERE id=?'
)->execute([$status, $admin['id'], $regId]);

if ($status === 'completed') {
    
    $pdo->prepare("UPDATE users SET role='member', updated_at=NOW() WHERE id=?")
        ->execute([$reg['user_id']]);

    createNotification($pdo, $reg['user_id'],
        'Welcome to MORECO! You are now a Member.',
        'Congratulations! You have completed your orientation and are now an official MORECO member. You can now access all member benefits, apply for loans, and more.',
        'membership',
        'dashboard.html'
    );

    logAction($pdo, $admin['id'], 'COMPLETE_ORIENTATION', 'orientation_registrations', $regId,
        'Orientation completed for user #' . $reg['user_id'] . ' — promoted to member');

    respond(true, $reg['first_name'] . ' ' . $reg['last_name'] . ' has been promoted to Member.', null);

} else {
    
    
    $pdo->prepare('DELETE FROM orientation_registrations WHERE id=?')->execute([$regId]);

    createNotification($pdo, $reg['user_id'],
        'Orientation Missed',
        'You missed your scheduled orientation. Please log in and select a new orientation schedule to complete your membership.',
        'orientation',
        'orientation.html'
    );

    logAction($pdo, $admin['id'], 'MISSED_ORIENTATION', 'orientation_registrations', $regId,
        'Orientation missed for user #' . $reg['user_id']);

    respond(true, 'Orientation marked as missed. User can reschedule.', null);
}
?>
