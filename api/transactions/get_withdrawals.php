<?php
require_once '../../config.php';
$user = requireLogin();

if (in_array($user['role'], ['admin', 'clerk'])) {
    $status = clean($_GET['status'] ?? '');
    $search = clean($_GET['search'] ?? '');

    $where  = [];
    $params = [];

    if ($status) {
        $where[]  = 'w.status = ?';
        $params[] = $status;
    }
    if ($search) {
        $where[]  = '(u.first_name LIKE ? OR u.last_name LIKE ? OR u.username LIKE ? OR w.account_number LIKE ?)';
        $like     = '%' . $search . '%';
        $params   = array_merge($params, [$like, $like, $like, $like]);
    }

    $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $stmt = $pdo->prepare(
        "SELECT w.*,
                u.first_name, u.last_name, u.email, u.username, u.phone,
                r.first_name AS reviewer_first, r.last_name AS reviewer_last,
                rb.first_name AS releaser_first, rb.last_name AS releaser_last
         FROM withdrawals w
         JOIN users u  ON w.user_id     = u.id
         LEFT JOIN users r  ON w.reviewed_by = r.id
         LEFT JOIN users rb ON w.released_by = rb.id
         $whereSQL
         ORDER BY w.created_at DESC"
    );
    $stmt->execute($params);
} else {
    $stmt = $pdo->prepare(
        'SELECT w.*,
                r.first_name AS reviewer_first, r.last_name AS reviewer_last
         FROM withdrawals w
         LEFT JOIN users r ON w.reviewed_by = r.id
         WHERE w.user_id = ?
         ORDER BY w.created_at DESC'
    );
    $stmt->execute([$user['id']]);
}

$withdrawals = $stmt->fetchAll();

foreach ($withdrawals as &$w) {
    if (in_array($w['status'], ['approved', 'released'])) {
        $qr = $pdo->prepare(
            "SELECT qr_token, status AS qr_status, generated_at, scanned_at
             FROM qr_transactions
             WHERE transaction_type = 'withdrawal' AND reference_id = ?
             ORDER BY generated_at DESC LIMIT 1"
        );
        $qr->execute([$w['id']]);
        $w['qr'] = $qr->fetch() ?: null;
    }
}
unset($w);

respond(true, count($withdrawals) . ' withdrawal(s) found.', ['withdrawals' => $withdrawals]);
?>
