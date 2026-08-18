<?php
require_once '../../config.php';

$status   = clean($_GET['status']   ?? '');
$category = clean($_GET['category'] ?? '');
$search   = clean($_GET['search']   ?? '');
$limit    = isset($_GET['limit']) ? (int)$_GET['limit'] : 0;

$where  = [];
$params = [];

if ($status) {
    $where[]  = 'status = ?';
    $params[] = $status;
}
if ($category) {
    $where[]  = 'category = ?';
    $params[] = $category;
}
if ($search) {
    $where[]  = '(title LIKE ? OR description LIKE ? OR location LIKE ?)';
    $like     = '%' . $search . '%';
    $params   = array_merge($params, [$like, $like, $like]);
}

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$limitSQL = $limit > 0 ? 'LIMIT ' . $limit : '';

$stmt = $pdo->prepare(
    "SELECT e.*,
            u.first_name AS creator_first, u.last_name AS creator_last,
            (SELECT COUNT(*) FROM event_registrations er WHERE er.event_id = e.id) AS registration_count
     FROM events e
     LEFT JOIN users u ON e.created_by = u.id
     $whereSQL
     ORDER BY e.event_date ASC
     $limitSQL"
);
$stmt->execute($params);
$events = $stmt->fetchAll();

$cats  = $pdo->query('SELECT DISTINCT category FROM events ORDER BY category ASC')->fetchAll(PDO::FETCH_COLUMN);

respond(true, count($events) . ' event(s) found.', [
    'events'     => $events,
    'categories' => $cats
]);
?>
