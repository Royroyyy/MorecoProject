<?php
require_once '../../config.php';
$admin = requireAdmin();

$d        = getJSON();
$title    = clean($d['title']    ?? '');
$body     = clean($d['body']     ?? '');
$icon     = clean($d['icon']     ?? '📢');
$priority = in_array($d['priority'] ?? '', ['normal','high']) ? $d['priority'] : 'normal';
$date     = clean($d['date']     ?? date('Y-m-d'));
$validBranches = ['all','morong','teresa','antipolo','tanay','siniloan','taytay','masinag'];
$branch   = in_array($d['branch'] ?? '', $validBranches) ? $d['branch'] : 'all';

if (!$title || !$body) {
    respond(false, 'Title and body are required.', null, 400);
}

$stmt = $pdo->prepare(
    'INSERT INTO announcements (title, body, icon, priority, branch, posted_at, created_by)
     VALUES (?, ?, ?, ?, ?, ?, ?)'
);
$stmt->execute([$title, $body, $icon, $priority, $branch, $date, $admin['id']]);
$newId = (int)$pdo->lastInsertId();

logAction($pdo, $admin['id'], 'CREATE_ANNOUNCEMENT', 'announcements', $newId, "Posted: $title (branch: $branch)");
respond(true, 'Announcement posted successfully.', ['id' => $newId]);
?>
