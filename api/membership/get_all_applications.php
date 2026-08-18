<?php
require_once '../../config.php';
requireAdmin();

$status = clean($_GET['status'] ?? '');
$search = clean($_GET['search'] ?? '');

$where  = [];
$params = [];

if ($status) {
    $where[]  = 'ma.status = ?';
    $params[] = $status;
}
if ($search) {
    $where[]  = '(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR u.username LIKE ?)';
    $like     = '%' . $search . '%';
    $params   = array_merge($params, [$like, $like, $like, $like]);
}

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = $pdo->prepare(
    "SELECT ma.*,
            u.first_name, u.last_name, u.email, u.username, u.phone,
            r.first_name AS reviewer_first, r.last_name AS reviewer_last
     FROM membership_applications ma
     JOIN users u  ON ma.user_id     = u.id
     LEFT JOIN users r ON ma.reviewed_by = r.id
     $whereSQL
     ORDER BY ma.created_at DESC"
);
$stmt->execute($params);
$applications = $stmt->fetchAll();

respond(true, count($applications) . ' application(s) found.', ['applications' => $applications]);
?>
