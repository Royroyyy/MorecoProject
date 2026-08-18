<?php
require_once '../../config.php';
$user = requireLogin();

$d          = getJSON();
$scheduleId = (int)($d['schedule_id'] ?? 0);

if (!$scheduleId) {
    respond(false, 'schedule_id is required.', null, 400);
}

$appStmt = $pdo->prepare(
    'SELECT id FROM membership_applications
     WHERE user_id = ? AND status = "approved"
     LIMIT 1'
);
$appStmt->execute([$user['id']]);
if (!$appStmt->fetch()) {
    respond(false, 'Your membership application must be approved before selecting an orientation.', null, 403);
}

$dupCheck = $pdo->prepare('SELECT id FROM orientation_registrations WHERE user_id = ? LIMIT 1');
$dupCheck->execute([$user['id']]);
if ($dupCheck->fetch()) {
    respond(false, 'You have already selected an orientation schedule.', null, 409);
}

$schStmt = $pdo->prepare(
    'SELECT os.*,
            (os.max_slots - COUNT(oreg.id)) AS slots_remaining
     FROM orientation_schedules os
     LEFT JOIN orientation_registrations oreg
           ON os.id = oreg.schedule_id AND oreg.status != "missed"
     WHERE os.id = ? AND os.is_active = 1
     GROUP BY os.id
     LIMIT 1'
);
$schStmt->execute([$scheduleId]);
$schedule = $schStmt->fetch();

if (!$schedule) {
    respond(false, 'Schedule not found or is no longer available.', null, 404);
}
if ((int)$schedule['slots_remaining'] <= 0) {
    respond(false, 'This schedule is fully booked. Please choose another.', null, 409);
}

$stmt = $pdo->prepare(
    'INSERT INTO orientation_registrations (user_id, schedule_id, status)
     VALUES (?, ?, "scheduled")'
);
$stmt->execute([$user['id'], $scheduleId]);
$regId = (int)$pdo->lastInsertId();

logAction($pdo, $user['id'], 'SELECT_ORIENTATION', 'orientation_registrations', $regId,
    'Selected schedule #' . $scheduleId);

createNotification($pdo, $user['id'],
    'Orientation Schedule Confirmed',
    'You are registered for orientation on ' . $schedule['scheduled_date'] .
    ' at ' . $schedule['scheduled_time'] . ', ' . $schedule['location'] .
    '. Please attend to complete your MORECO membership.',
    'orientation',
    'orientation.html'
);

respond(true, 'Orientation schedule selected successfully.', [
    'registration_id' => $regId,
    'schedule'        => $schedule
]);
?>
