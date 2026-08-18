<?php
require_once '../../config.php';
requireStaff();

$totalUsers    = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalMembers  = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='member'")->fetchColumn();
$totalApplicants=(int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='applicant'")->fetchColumn();
$activeUsers   = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE is_active=1")->fetchColumn();

$newThisMonth  = (int)$pdo->query(
    "SELECT COUNT(*) FROM users WHERE role='member'
     AND MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())"
)->fetchColumn();

$totalEvents   = (int)$pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
$upcomingEvents= (int)$pdo->query("SELECT COUNT(*) FROM events WHERE status='upcoming'")->fetchColumn();
$totalEventRegs= (int)$pdo->query("SELECT COUNT(*) FROM event_registrations")->fetchColumn();

$pendingApps   = (int)$pdo->query(
    "SELECT COUNT(*) FROM membership_applications WHERE status='pending'"
)->fetchColumn();
$approvedApps  = (int)$pdo->query(
    "SELECT COUNT(*) FROM membership_applications WHERE status='approved'"
)->fetchColumn();
$rejectedApps  = (int)$pdo->query(
    "SELECT COUNT(*) FROM membership_applications WHERE status='rejected'"
)->fetchColumn();

$totalLoans    = (int)$pdo->query("SELECT COUNT(*) FROM loans")->fetchColumn();
$pendingLoans  = (int)$pdo->query("SELECT COUNT(*) FROM loans WHERE status='pending'")->fetchColumn();
$approvedLoans = (int)$pdo->query("SELECT COUNT(*) FROM loans WHERE status='approved'")->fetchColumn();
$releasedLoans = (int)$pdo->query("SELECT COUNT(*) FROM loans WHERE status='released'")->fetchColumn();
$totalLoanAmt  = (float)$pdo->query(
    "SELECT COALESCE(SUM(amount),0) FROM loans WHERE status IN ('approved','released')"
)->fetchColumn();

$totalWithdrawals = (int)$pdo->query("SELECT COUNT(*) FROM withdrawals")->fetchColumn();
$pendingWithdrawals=(int)$pdo->query(
    "SELECT COUNT(*) FROM withdrawals WHERE status='pending'"
)->fetchColumn();
$releasedWithdrawals=(int)$pdo->query(
    "SELECT COUNT(*) FROM withdrawals WHERE status='released'"
)->fetchColumn();
$totalWithdrawalAmt=(float)$pdo->query(
    "SELECT COALESCE(SUM(amount),0) FROM withdrawals WHERE status='released'"
)->fetchColumn();

$loanTrend = $pdo->query(
    "SELECT DATE_FORMAT(created_at,'%Y-%m') AS month,
            COUNT(*) AS count,
            COALESCE(SUM(amount),0) AS total
     FROM loans
     WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
     GROUP BY month
     ORDER BY month ASC"
)->fetchAll();

$memberTrend = $pdo->query(
    "SELECT DATE_FORMAT(created_at,'%Y-%m') AS month,
            COUNT(*) AS count
     FROM users
     WHERE role='member'
       AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
     GROUP BY month
     ORDER BY month ASC"
)->fetchAll();

$calendarEvents = $pdo->query(
    "SELECT id, title, event_date, category, emoji, status, location
     FROM events
     WHERE event_date >= DATE_SUB(NOW(), INTERVAL 2 MONTH)
       AND event_date <= DATE_ADD(NOW(), INTERVAL 4 MONTH)
     ORDER BY event_date ASC
     LIMIT 60"
)->fetchAll();

$withdrawalTrend = $pdo->query(
    "SELECT DATE_FORMAT(created_at,'%Y-%m') AS month,
            COUNT(*) AS count,
            COALESCE(SUM(amount),0) AS total
     FROM withdrawals
     WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
     GROUP BY month
     ORDER BY month ASC"
)->fetchAll();

$recentAudit = $pdo->query(
    "SELECT al.*, u.first_name, u.last_name, u.username
     FROM audit_logs al
     LEFT JOIN users u ON al.user_id = u.id
     ORDER BY al.created_at DESC
     LIMIT 20"
)->fetchAll();

respond(true, 'Analytics retrieved.', [
    'members' => [
        'total'       => $totalUsers,
        'members'     => $totalMembers,
        'applicants'  => $totalApplicants,
        'active'      => $activeUsers,
        'new_month'   => $newThisMonth,
    ],
    'events' => [
        'total'        => $totalEvents,
        'upcoming'     => $upcomingEvents,
        'registrations'=> $totalEventRegs,
    ],
    'applications' => [
        'pending'  => $pendingApps,
        'approved' => $approvedApps,
        'rejected' => $rejectedApps,
    ],
    'loans' => [
        'total'    => $totalLoans,
        'pending'  => $pendingLoans,
        'approved' => $approvedLoans,
        'released' => $releasedLoans,
        'amount'   => $totalLoanAmt,
    ],
    'withdrawals' => [
        'total'    => $totalWithdrawals,
        'pending'  => $pendingWithdrawals,
        'released' => $releasedWithdrawals,
        'amount'   => $totalWithdrawalAmt,
    ],
    'trends' => [
        'loans'       => $loanTrend,
        'members'     => $memberTrend,
        'withdrawals' => $withdrawalTrend,
    ],
    'calendar_events' => $calendarEvents,
    'recent_audit' => $recentAudit,
]);
?>
