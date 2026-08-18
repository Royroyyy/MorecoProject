<?php
require_once '../../config.php';
$user = requireMember();

$d          = getJSON();
$amount     = (float)($d['amount']      ?? 0);
$purpose    = clean($d['purpose']       ?? '');
$termMonths = (int)($d['term_months']   ?? 12);
$interest   = (float)($d['interest_rate'] ?? 2.00);

if ($amount <= 0) {
    respond(false, 'Loan amount must be greater than zero.', null, 400);
}
if ($amount > 500000) {
    respond(false, 'Maximum loan amount is ₱500,000.', null, 400);
}
if (!$purpose) {
    respond(false, 'Loan purpose is required.', null, 400);
}
if ($termMonths < 1 || $termMonths > 60) {
    respond(false, 'Loan term must be between 1 and 60 months.', null, 400);
}

$existing = $pdo->prepare(
    "SELECT id FROM loans
     WHERE user_id = ? AND status IN ('pending','under_review','approved','released')
     LIMIT 1"
);
$existing->execute([$user['id']]);
if ($existing->fetch()) {
    respond(false, 'You already have an active loan application. Please wait for it to be resolved before applying again.', null, 409);
}

$stmt = $pdo->prepare(
    'INSERT INTO loans (user_id, amount, purpose, term_months, interest_rate, status)
     VALUES (?, ?, ?, ?, ?, "pending")'
);
$stmt->execute([$user['id'], $amount, $purpose, $termMonths, $interest]);
$newId = (int)$pdo->lastInsertId();

createNotification($pdo, $user['id'],
    'Loan Application Received',
    'Your loan application for ' . formatPhpMoney($amount) . ' has been received and is pending review.',
    'loan',
    'transactions.html'
);

logAction($pdo, $user['id'], 'APPLY_LOAN', 'loans', $newId,
    "Applied for loan: ₱$amount for $termMonths months");

respond(true, 'Loan application submitted successfully.', ['id' => $newId]);

function formatPhpMoney($amount) {
    return '₱' . number_format($amount, 2);
}
?>
