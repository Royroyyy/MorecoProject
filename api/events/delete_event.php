<?php
require_once '../../config.php';
requireAdmin();

$d  = getJSON();
$id = (int)($d['id'] ?? 0);
if (!$id) respond(false, 'Event ID is required.', null, 400);

$check = $pdo->prepare('SELECT id, title FROM events WHERE id = ? LIMIT 1');
$check->execute([$id]);
$event = $check->fetch();
if (!$event) respond(false, 'Event not found.', null, 404);

$pdo->prepare('DELETE FROM events WHERE id = ?')->execute([$id]);

logAction($pdo, $_SESSION['user']['id'], 'DELETE_EVENT', 'events', $id, "Deleted event: {$event['title']}");
respond(true, 'Event deleted successfully.');
?>
