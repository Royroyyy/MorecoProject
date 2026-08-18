<?php
require_once '../../config.php';
$admin = requireAdmin();

$d         = getJSON();
$title     = clean($d['title']       ?? '');
$category  = clean($d['category']    ?? '');
$desc      = clean($d['description'] ?? '');
$date      = clean($d['date']        ?? '');
$location  = clean($d['location']    ?? '');
$organizer = clean($d['organizer']   ?? '');
$slots     = max(1, (int)($d['slots'] ?? 100));
$emoji     = clean($d['emoji']       ?? '📅');
$image     = clean($d['image_url']   ?? '');
$status    = in_array($d['status'] ?? '', ['upcoming','completed'])
             ? $d['status'] : 'upcoming';

if (!$title || !$category || !$date) {
    respond(false, 'Title, category, and date are required.', null, 400);
}

$stmt = $pdo->prepare(
    'INSERT INTO events
     (title, category, description, event_date, location, organizer, slots, emoji, image_url, status, created_by)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
);
$stmt->execute([
    $title, $category, $desc, $date,
    $location, $organizer, $slots,
    $emoji, $image, $status, $admin['id']
]);
$newId = (int)$pdo->lastInsertId();

logAction($pdo, $admin['id'], 'CREATE_EVENT', 'events', $newId, "Created event: $title");

respond(true, 'Event created successfully.', ['id' => $newId]);
?>
