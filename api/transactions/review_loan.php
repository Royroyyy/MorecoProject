<?php
require_once '../../config.php';
$reviewer = requireLoanOfficer();

$d        = getJSON();
$loanId   = (int)($d['loan_id']  ?? 0);
$decision = clean($d['decision'] ?? '');   
$notes    = clean($d['notes']    ?? '');
$dueDate  = clean($d['due_date'] ?? '');

if (!$loanId || !in_array($decision, ['approved', 'rejected'])) {
    respond(false, 'loan_id and a valid decision (approved/rejected) are required.', null, 400);
}

$stmt = $pdo->prepare('SELECT * FROM loans WHERE id = ? LIMIT 1');
$stmt->execute([$loanId]);
$loan = $stmt->fetch();

if (!$loan) respond(false, 'Loan not found.', null, 404);
if (!in_array($loan['status'], ['pending', 'under_review'])) {
    respond(false, 'This loan has already been decided.', null, 409);
}

if ($decision === 'approved') {
    if (!$dueDate) {
        
        $dueDate = date('Y-m-d', strtotime('+' . $loan['term_months'] . ' months'));
    }

    $pdo->prepare(
        'UPDATE loans
         SET status="approved", reviewed_by=?, reviewed_at=NOW(),
             notes=?, due_date=?, updated_at=NOW()
         WHERE id=?'
    )->execute([$reviewer['id'], $notes, $dueDate, $loanId]);

    
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+7 days'));

    $pdo->prepare(
        "INSERT INTO qr_transactions
         (transaction_type, reference_id, qr_token, status, expires_at)
         VALUES ('loan', ?, ?, 'active', ?)"
    )->execute([$loanId, $token, $expires]);

    createNotification($pdo, $loan['user_id'],
        'Loan Approved! Present Your QR Code',
        'Your loan application for ₱' . number_format($loan['amount'], 2) .
        ' has been approved. Log in to view your QR code and present it to a clerk to receive your funds.',
        'loan',
        'transactions.html'
    );

    logAction($pdo, $reviewer['id'], 'APPROVE_LOAN', 'loans', $loanId,
        "Approved loan #$loanId for user #{$loan['user_id']}");

    respond(true, 'Loan approved. QR code generated and user notified.', ['qr_token' => $token]);

} else {
    $pdo->prepare(
        'UPDATE loans
         SET status="rejected", reviewed_by=?, reviewed_at=NOW(),
             rejection_reason=?, notes=?, updated_at=NOW()
         WHERE id=?'
    )->execute([$reviewer['id'], $notes, $notes, $loanId]);

    createNotification($pdo, $loan['user_id'],
        'Loan Application Not Approved',
        'Your loan application for ₱' . number_format($loan['amount'], 2) .
        ' was not approved. Reason: ' . ($notes ?: 'Not specified') .
        '. Please contact the MORECO office for more information.',
        'loan',
        'transactions.html'
    );

    logAction($pdo, $reviewer['id'], 'REJECT_LOAN', 'loans', $loanId,
        "Rejected loan #$loanId. Reason: $notes");

    respond(true, 'Loan rejected. User has been notified.', null);
}
?>
