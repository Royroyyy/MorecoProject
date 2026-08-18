<?php
require_once '../../config.php';

$category = clean($_GET['category'] ?? '');
$search   = clean($_GET['search']   ?? '');

$where  = ['b.is_active = 1'];
$params = [];

if ($category) {
    $where[]  = 'b.category = ?';
    $params[] = $category;
}
if ($search) {
    $where[]  = '(b.title LIKE ? OR b.description LIKE ?)';
    $like     = '%' . $search . '%';
    $params   = array_merge($params, [$like, $like]);
}

$whereSQL = 'WHERE ' . implode(' AND ', $where);

$stmt = $pdo->prepare(
    "SELECT b.*,
            u.first_name AS creator_first, u.last_name AS creator_last
     FROM benefits b
     LEFT JOIN users u ON b.created_by = u.id
     $whereSQL
     ORDER BY b.category ASC, b.title ASC"
);
$stmt->execute($params);
$benefits = $stmt->fetchAll();

$cats = $pdo->query(
    "SELECT DISTINCT category FROM benefits WHERE is_active = 1 ORDER BY category ASC"
)->fetchAll(PDO::FETCH_COLUMN);

respond(true, count($benefits) . ' benefit(s) found.', [
    'benefits'   => $benefits,
    'categories' => $cats
]);
?>
