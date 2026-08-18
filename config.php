<?php

define('DB_HOST', 'sql211.infinityfree.com');
define('DB_USER', 'if0_42604633');
define('DB_PASS', 'Pw7HhOkNZ9RMMd');
define('DB_NAME', 'if0_42604633_moreco_db');

define('UPLOAD_DIR', __DIR__ . '/assets/uploads/');
define('BASE_URL',   'https://moreco.rf.gd/');

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed. Please try again later.',
        'data'    => null
    ]);
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function respond($success, $message, $data = null, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data'    => $data
    ]);
    exit;
}

function getSessionUser() {
    return $_SESSION['user'] ?? null;
}

function requireLogin() {
    if (!isset($_SESSION['user'])) {
        respond(false, 'You must be logged in.', null, 401);
    }
    return $_SESSION['user'];
}

function requireRole($roles) {
    $user = requireLogin();
    if (is_string($roles)) $roles = [$roles];
    if (!in_array($user['role'], $roles)) {
        respond(false, 'You do not have permission to do this.', null, 403);
    }
    return $user;
}

function requireAdmin()       { return requireRole('admin'); }
function requireClerk()       { return requireRole(['admin', 'clerk']); }
function requireLoanOfficer() { return requireRole(['admin', 'loan_officer']); }
function requireMember()      { return requireRole(['admin', 'member']); }
function requireStaff()       { return requireRole(['admin', 'clerk', 'loan_officer']); }

function logAction($pdo, $userId, $action, $targetType = null, $targetId = null, $details = null) {
    try {
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $stmt = $pdo->prepare(
            'INSERT INTO audit_logs (user_id, action, target_type, target_id, details, ip_address)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$userId, $action, $targetType, $targetId, $details, $ip]);
    } catch (Exception $e) {
        
    }
}

function createNotification($pdo, $userId, $title, $message, $type = 'general', $link = null) {
    try {
        $stmt = $pdo->prepare(
            'INSERT INTO notifications (user_id, title, message, type, link)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$userId, $title, $message, $type, $link]);
    } catch (Exception $e) {
        
    }
}

function clean($value) {
    return htmlspecialchars(trim($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function getJSON() {
    return json_decode(file_get_contents('php://input'), true) ?? [];
}
?>
