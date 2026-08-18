<?php
require_once '../../config.php';
$user = requireMember(); 

$d       = getJSON();
$eventId = (int)($d['event_id'] ?? 0);
if (!$eventId) respond(false, 'event_id is required.', null, 400);

$ev = $pdo->prepare('SELECT * FROM events WHERE id = ? LIMIT 1');
$ev->execute([$eventId]);
$event = $ev->fetch();

if (!$event) respond(false, 'Event not found.', null, 404);
if ($event['status'] !== 'upcoming') {
    respond(false, 'This event is already completed.', null, 400);
}

$cnt = $pdo->prepare('SELECT COUNT(*) FROM event_registrations WHERE event_id = ?');
$cnt->execute([$eventId]);
if ((int)$cnt->fetchColumn() >= (int)$event['slots']) {
    respond(false, 'No slots available for this event.', null, 409);
}

try {
    $ins = $pdo->prepare(
        'INSERT INTO event_registrations (user_id, event_id) VALUES (?, ?)'
    );
    $ins->execute([$user['id'], $eventId]);

    createNotification($pdo, $user['id'],
        'Event Registration Confirmed',
        'You are registered for: ' . $event['title'] . ' on ' . $event['event_date'] . '.',
        'event',
        'events.html'
    );

    logAction($pdo, $user['id'], 'REGISTER_EVENT', 'event_registrations', (int)$pdo->lastInsertId(),
        "Registered for event: {$event['title']}");

    respond(true, 'Successfully registered for this event.', ['event_title' => $event['title']]);

} catch (PDOException $e) {
    respond(false, 'You are already registered for this event.', null, 409);
}
?>
