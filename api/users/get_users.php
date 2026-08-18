<?php
require_once '../../config.php';
requireAdmin();

$role   = clean($_GET['role']   ?? '');
$search = clean($_GET['search'] ?? '');

$where  = [];
$params = [];

if ($role) {
    $where[]  = 'role = ?';
    $params[] = $role;
}
if ($search) {
    $where[]  = '(first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR username LIKE ?)';
    $like     = '%' . $search . '%';
    $params   = array_merge($params, [$like, $like, $like, $like]);
}

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = $pdo->prepare(
    "SELECT id, first_name, last_name, email, username, role,
            phone, address, is_active, created_at, updated_at
     FROM users
     $whereSQL
     ORDER BY created_at DESC"
);
$stmt->execute($params);
$users = $stmt->fetchAll();

foreach ($users as &$u) {
    $cnt = $pdo->prepare('SELECT COUNT(*) FROM event_registrations WHERE user_id = ?');
    $cnt->execute([$u['id']]);
    $u['event_reg_count'] = (int)$cnt->fetchColumn();

    $loanCnt = $pdo->prepare("SELECT COUNT(*) FROM loans WHERE user_id = ?");
    $loanCnt->execute([$u['id']]);
    $u['loan_count'] = (int)$loanCnt->fetchColumn();
}
unset($u);

respond(true, count($users) . ' user(s) found.', ['users' => $users]);
?>
