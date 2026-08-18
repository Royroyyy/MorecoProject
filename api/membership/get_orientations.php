<?php
require_once '../../config.php';
$user = requireLogin();

$stmt = $pdo->query(
    'SELECT os.*,
            COUNT(oreg.id) AS registered_count,
            (os.max_slots - COUNT(oreg.id)) AS slots_remaining
     FROM orientation_schedules os
     LEFT JOIN orientation_registrations oreg
           ON os.id = oreg.schedule_id AND oreg.status != "missed"
     WHERE os.is_active = 1
     GROUP BY os.id
     HAVING slots_remaining > 0
     ORDER BY os.scheduled_date ASC, os.scheduled_time ASC'
);
$schedules = $stmt->fetchAll();

$existing = $pdo->prepare(
    'SELECT oreg.*, os.title, os.scheduled_date, os.scheduled_time, os.location
     FROM orientation_registrations oreg
     JOIN orientation_schedules os ON oreg.schedule_id = os.id
     WHERE oreg.user_id = ?
     LIMIT 1'
);
$existing->execute([$user['id']]);
$myReg = $existing->fetch() ?: null;

respond(true, 'Orientation schedules retrieved.', [
    'schedules'       => $schedules,
    'my_registration' => $myReg
]);
?>
