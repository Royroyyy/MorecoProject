<?php
require_once '../../config.php';
$admin = requireAdmin();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->query(
        'SELECT os.*,
                COUNT(oreg.id) AS registered_count,
                u.first_name AS creator_first, u.last_name AS creator_last
         FROM orientation_schedules os
         LEFT JOIN orientation_registrations oreg ON os.id = oreg.schedule_id
         LEFT JOIN users u ON os.created_by = u.id
         GROUP BY os.id
         ORDER BY os.scheduled_date ASC, os.scheduled_time ASC'
    );
    respond(true, 'Schedules retrieved.', ['schedules' => $stmt->fetchAll()]);
}

if ($method === 'POST') {
    $d        = getJSON();
    $title    = clean($d['title']    ?? '');
    $date     = clean($d['date']     ?? '');
    $time     = clean($d['time']     ?? '');
    $location = clean($d['location'] ?? '');
    $maxSlots = max(1, (int)($d['max_slots'] ?? 20));

    if (!$title || !$date || !$time || !$location) {
        respond(false, 'Title, date, time, and location are required.', null, 400);
    }

    $stmt = $pdo->prepare(
        'INSERT INTO orientation_schedules (title, scheduled_date, scheduled_time, location, max_slots, created_by)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([$title, $date, $time, $location, $maxSlots, $admin['id']]);
    $newId = (int)$pdo->lastInsertId();

    logAction($pdo, $admin['id'], 'CREATE_ORIENTATION_SCHEDULE', 'orientation_schedules', $newId, 'Schedule created');
    respond(true, 'Orientation schedule created.', ['id' => $newId]);
}

if ($method === 'DELETE') {
    $d  = getJSON();
    $id = (int)($d['id'] ?? 0);
    if (!$id) respond(false, 'ID is required.', null, 400);

    $pdo->prepare('UPDATE orientation_schedules SET is_active=0 WHERE id=?')->execute([$id]);
    logAction($pdo, $admin['id'], 'DEACTIVATE_ORIENTATION_SCHEDULE', 'orientation_schedules', $id, 'Schedule deactivated');
    respond(true, 'Schedule deactivated.');
}

respond(false, 'Method not allowed.', null, 405);
?>
