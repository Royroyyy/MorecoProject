<?php
require_once '../../config.php';
requireAdmin();

$scheduleId = isset($_GET['schedule_id']) ? (int)$_GET['schedule_id'] : null;
$status     = clean($_GET['status'] ?? '');

$where  = [];
$params = [];

if ($scheduleId) {
    $where[]  = 'oreg.schedule_id = ?';
    $params[] = $scheduleId;
}
if ($status) {
    $where[]  = 'oreg.status = ?';
    $params[] = $status;
}

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = $pdo->prepare(
    "SELECT oreg.*,
            u.first_name, u.last_name, u.email, u.username, u.phone,
            os.title AS schedule_title, os.scheduled_date, os.scheduled_time, os.location,
            c.first_name AS completer_first, c.last_name AS completer_last
     FROM orientation_registrations oreg
     JOIN users u  ON oreg.user_id    = u.id
     JOIN orientation_schedules os ON oreg.schedule_id = os.id
     LEFT JOIN users c ON oreg.completed_by = c.id
     $whereSQL
     ORDER BY os.scheduled_date ASC, oreg.created_at ASC"
);
$stmt->execute($params);
$registrations = $stmt->fetchAll();

respond(true, count($registrations) . ' registration(s) found.', ['registrations' => $registrations]);
?>
