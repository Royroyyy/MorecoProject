<?php
require_once '../../config.php';
requireAdmin();

$d  = getJSON();
$id = (int)($d['id'] ?? 0);
if (!$id) respond(false, 'Event ID is required.', null, 400);

$check = $pdo->prepare('SELECT id, title FROM events WHERE id = ? LIMIT 1');
$check->execute([$id]);
if (!$check->fetch()) respond(false, 'Event not found.', null, 404);

$stmt = $pdo->prepare(
    'UPDATE events
     SET title=?, category=?, description=?, event_date=?,
         location=?, organizer=?, slots=?, emoji=?, image_url=?, status=?, updated_at=NOW()
     WHERE id=?'
);
$stmt->execute([
    clean($d['title']       ?? ''),
    clean($d['category']    ?? ''),
    clean($d['description'] ?? ''),
    clean($d['date']        ?? ''),
    clean($d['location']    ?? ''),
    clean($d['organizer']   ?? ''),
    max(1, (int)($d['slots'] ?? 100)),
    clean($d['emoji']       ?? '📅'),
    clean($d['image_url']   ?? ''),
    in_array($d['status'] ?? '', ['upcoming','completed']) ? $d['status'] : 'upcoming',
    $id
]);

logAction($pdo, $_SESSION['user']['id'], 'UPDATE_EVENT', 'events', $id, "Updated event #$id");
respond(true, 'Event updated successfully.');
?>
