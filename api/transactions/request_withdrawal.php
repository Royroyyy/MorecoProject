<?php
require_once '../../config.php';
$user = requireMember();

$d             = getJSON();
$accountNumber = clean($d['account_number'] ?? '');
$accountName   = clean($d['account_name']   ?? '');
$amount        = (float)($d['amount']        ?? 0);
$notes         = clean($d['notes']           ?? '');

if (!$accountNumber) {
    respond(false, 'Account number is required.', null, 400);
}
if (!$accountName) {
    respond(false, 'Account name is required.', null, 400);
}
if ($amount <= 0) {
    respond(false, 'Withdrawal amount must be greater than zero.', null, 400);
}
if ($amount > 100000) {
    respond(false, 'Maximum single withdrawal is ₱100,000.', null, 400);
}

$existing = $pdo->prepare(
    "SELECT id FROM withdrawals
     WHERE user_id = ? AND status IN ('pending', 'approved')
     LIMIT 1"
);
$existing->execute([$user['id']]);
if ($existing->fetch()) {
    respond(false, 'You already have a pending withdrawal request. Please wait for it to be processed.', null, 409);
}

$stmt = $pdo->prepare(
    'INSERT INTO withdrawals (user_id, account_number, account_name, amount, notes, status)
     VALUES (?, ?, ?, ?, ?, "pending")'
);
$stmt->execute([$user['id'], $accountNumber, $accountName, $amount, $notes]);
$newId = (int)$pdo->lastInsertId();

createNotification($pdo, $user['id'],
    '💸 Withdrawal Request Received',
    'Your withdrawal request for ₱' . number_format($amount, 2) .
    ' from account "' . $accountName . '" (' . $accountNumber . ') has been received and is pending review. ' .
    'You will be notified once it is approved.',
    'withdrawal',
    'withdrawals.html'
);

logAction($pdo, $user['id'], 'REQUEST_WITHDRAWAL', 'withdrawals', $newId,
    "Withdrawal request: ₱$amount from account $accountNumber ($accountName)");

respond(true, 'Withdrawal request submitted successfully.', ['id' => $newId]);
?>
