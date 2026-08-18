<?php
require_once '../../config.php';
$admin = requireAdmin();

$d        = getJSON();
$appId    = (int)($d['application_id'] ?? 0);
$decision = clean($d['decision'] ?? '');   
$reason   = clean($d['reason']   ?? '');

if (!$appId || !in_array($decision, ['approved', 'rejected'])) {
    respond(false, 'application_id and a valid decision (approved/rejected) are required.', null, 400);
}

$stmt = $pdo->prepare(
    'SELECT ma.*, u.first_name, u.last_name, u.email
     FROM membership_applications ma
     JOIN users u ON ma.user_id = u.id
     WHERE ma.id = ? LIMIT 1'
);
$stmt->execute([$appId]);
$app = $stmt->fetch();

if (!$app) {
    respond(false, 'Application not found.', null, 404);
}
if ($app['status'] !== 'pending' && $app['status'] !== 'under_review') {
    respond(false, 'This application has already been decided.', null, 409);
}

$pdo->prepare(
    'UPDATE membership_applications
     SET status=?, reviewed_by=?, reviewed_at=NOW(), rejection_reason=?, updated_at=NOW()
     WHERE id=?'
)->execute([$decision, $admin['id'], $decision === 'rejected' ? $reason : null, $appId]);

$firstName = $app['first_name'];

if ($decision === 'approved') {

    
    createNotification($pdo, $app['user_id'],
        '🎉 Application Approved — Schedule Your Orientation',
        "Congratulations, {$firstName}! Your MORECO membership application has been approved by the committee. " .
        "Your next step is to select an orientation schedule. " .
        "Please visit the Orientation page to choose a date and time that works for you. " .
        "Orientation is required to complete your membership and unlock all MORECO benefits.",
        'membership',
        'orientation.html'
    );

    
    createNotification($pdo, $app['user_id'],
        '📅 Action Required: Choose Your Orientation Schedule',
        "Hi {$firstName}, this is a reminder to select your orientation schedule. " .
        "Click this notification or visit the Orientation page to pick your preferred date. " .
        "Orientation slots are limited — please register as soon as possible.",
        'orientation',
        'orientation.html'
    );

    logAction($pdo, $admin['id'], 'APPROVE_APPLICATION', 'membership_applications', $appId,
        "Approved application for {$firstName} {$app['last_name']} (user #{$app['user_id']})");

    respond(true, "Application approved. {$firstName} has been notified to schedule their orientation.", [
        'user_id'    => $app['user_id'],
        'applicant'  => $firstName . ' ' . $app['last_name'],
        'next_step'  => 'orientation'
    ]);

} else {

    createNotification($pdo, $app['user_id'],
        '📋 Membership Application Update',
        "Dear {$firstName}, we regret to inform you that your membership application was not approved at this time. " .
        "Reason: " . ($reason ?: 'Not specified') . ". " .
        "You are welcome to re-apply after addressing the concerns noted. " .
        "For questions, please visit any MORECO branch.",
        'membership'
    );

    logAction($pdo, $admin['id'], 'REJECT_APPLICATION', 'membership_applications', $appId,
        "Rejected application for {$firstName} {$app['last_name']}. Reason: {$reason}");

    respond(true, "Application rejected. {$firstName} has been notified.", [
        'user_id'   => $app['user_id'],
        'applicant' => $firstName . ' ' . $app['last_name'],
    ]);
}
?>
