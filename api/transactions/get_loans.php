<?php
require_once '../../config.php';
$user = requireLogin();

if (in_array($user['role'], ['admin', 'loan_officer'])) {
    
    $status = clean($_GET['status'] ?? '');
    $search = clean($_GET['search'] ?? '');

    $where  = [];
    $params = [];

    if ($status) {
        $where[]  = 'l.status = ?';
        $params[] = $status;
    }
    if ($search) {
        $where[]  = '(u.first_name LIKE ? OR u.last_name LIKE ? OR u.username LIKE ?)';
        $like     = '%' . $search . '%';
        $params   = array_merge($params, [$like, $like, $like]);
    }

    $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $stmt = $pdo->prepare(
        "SELECT l.*,
                u.first_name, u.last_name, u.email, u.username, u.phone,
                r.first_name AS reviewer_first, r.last_name AS reviewer_last,
                rb.first_name AS releaser_first, rb.last_name AS releaser_last
         FROM loans l
         JOIN users u  ON l.user_id     = u.id
         LEFT JOIN users r  ON l.reviewed_by = r.id
         LEFT JOIN users rb ON l.released_by = rb.id
         $whereSQL
         ORDER BY l.created_at DESC"
    );
    $stmt->execute($params);
} else {
    
    $stmt = $pdo->prepare(
        'SELECT l.*,
                r.first_name AS reviewer_first, r.last_name AS reviewer_last
         FROM loans l
         LEFT JOIN users r ON l.reviewed_by = r.id
         WHERE l.user_id = ?
         ORDER BY l.created_at DESC'
    );
    $stmt->execute([$user['id']]);
}

$loans = $stmt->fetchAll();

foreach ($loans as &$loan) {
    if (in_array($loan['status'], ['approved', 'released'])) {
        $qr = $pdo->prepare(
            "SELECT qr_token, status AS qr_status, generated_at, scanned_at
             FROM qr_transactions
             WHERE transaction_type = 'loan' AND reference_id = ?
             ORDER BY generated_at DESC LIMIT 1"
        );
        $qr->execute([$loan['id']]);
        $loan['qr'] = $qr->fetch() ?: null;
    }
}
unset($loan);

respond(true, count($loans) . ' loan(s) found.', ['loans' => $loans]);
?>
