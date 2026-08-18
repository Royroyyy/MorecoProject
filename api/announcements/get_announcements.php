<?php
require_once '../../config.php';

$priority = clean($_GET['priority'] ?? '');
$branch   = clean($_GET['branch']   ?? '');
$search   = clean($_GET['search']   ?? '');
$limit    = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;

$where  = [];
$params = [];

if ($priority) {
    $where[]  = 'priority = ?';
    $params[] = $priority;
}

if ($branch && $branch !== 'all') {
    $where[]  = "(branch = ? OR branch = 'all')";
    $params[] = $branch;
}

if ($search) {
    $where[]  = "(title LIKE ? OR body LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$limitSQL  = $limit > 0 ? 'LIMIT ' . (int)$limit : '';

$stmt = $pdo->prepare(
    "SELECT * FROM announcements
     $whereSQL
     ORDER BY posted_at DESC, created_at DESC
     $limitSQL"
);
$stmt->execute($params);
$announcements = $stmt->fetchAll();

respond(true, count($announcements) . ' announcement(s) found.', [
    'announcements' => $announcements
]);
?>
