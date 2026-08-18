<?php
require_once '../../config.php';
$user = requireLogin();

$unreadOnly = isset($_GET['unread_only']) && $_GET['unread_only'] == '1';
$limit      = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
$type       = clean($_GET['type'] ?? '');

$where  = ['n.user_id = ?'];
$params = [$user['id']];

if ($unreadOnly) {
    $where[] = 'n.is_read = 0';
}
if ($type) {
    $where[]  = 'n.type = ?';
    $params[] = $type;
}

$whereSQL = 'WHERE ' . implode(' AND ', $where);
$limitSQL  = $limit > 0 ? 'LIMIT ' . (int)$limit : '';

$stmt = $pdo->prepare(
    "SELECT * FROM notifications
     $whereSQL
     ORDER BY created_at DESC
     $limitSQL"
);
$stmt->execute($params);
$notifications = $stmt->fetchAll();

$countStmt = $pdo->prepare(
    'SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0'
);
$countStmt->execute([$user['id']]);
$unreadCount = (int)$countStmt->fetchColumn();

respond(true, count($notifications) . ' notification(s) found.', [
    'notifications' => $notifications,
    'unread_count'  => $unreadCount
]);
?>
