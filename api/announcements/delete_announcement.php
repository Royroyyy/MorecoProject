<?php
require_once '../../config.php';
requireAdmin();

$d  = getJSON();
$id = (int)($d['id'] ?? 0);
if (!$id) respond(false, 'ID is required.', null, 400);

$check = $pdo->prepare('SELECT id, title FROM announcements WHERE id = ? LIMIT 1');
$check->execute([$id]);
$ann = $check->fetch();
if (!$ann) respond(false, 'Announcement not found.', null, 404);

$pdo->prepare('DELETE FROM announcements WHERE id = ?')->execute([$id]);
logAction($pdo, $_SESSION['user']['id'], 'DELETE_ANNOUNCEMENT', 'announcements', $id,
    "Deleted: {$ann['title']}");

respond(true, 'Announcement deleted.');
?>
