<?php
require_once '../../config.php';
$user = requireLogin();

if ($user['role'] === 'member') {
    respond(false, 'You are already a member.', null, 400);
}

$existing = $pdo->prepare('SELECT id, status FROM membership_applications WHERE user_id = ? LIMIT 1');
$existing->execute([$user['id']]);
$app = $existing->fetch();

if ($app) {
    respond(false, 'You already have a membership application on file. Status: ' . $app['status'], null, 409);
}

if (empty($_FILES['valid_id']) || $_FILES['valid_id']['error'] !== UPLOAD_ERR_OK) {
    respond(false, 'Valid ID is required.', null, 400);
}
if (empty($_FILES['proof_of_residence']) || $_FILES['proof_of_residence']['error'] !== UPLOAD_ERR_OK) {
    respond(false, 'Proof of residence is required.', null, 400);
}

$allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
$maxSize      = 5 * 1024 * 1024; 

function validateAndMove($file, $subDir) {
    global $allowedTypes, $maxSize;

    if ($file['size'] > $maxSize) {
        return ['error' => 'File is too large. Maximum size is 5MB.'];
    }
    if (!in_array($file['type'], $allowedTypes)) {
        return ['error' => 'Invalid file type. Only JPG, PNG, WEBP, or PDF allowed.'];
    }

    $uploadDir = UPLOAD_DIR . $subDir . '/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('file_', true) . '.' . strtolower($ext);
    $destPath = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        return ['error' => 'Failed to save file. Check server permissions.'];
    }

    return ['path' => 'assets/uploads/' . $subDir . '/' . $filename];
}

$validIdResult = validateAndMove($_FILES['valid_id'], 'valid_ids');
if (isset($validIdResult['error'])) {
    respond(false, $validIdResult['error'], null, 400);
}

$proofResult = validateAndMove($_FILES['proof_of_residence'], 'proof_of_residence');
if (isset($proofResult['error'])) {
    respond(false, $proofResult['error'], null, 400);
}

$photoPath = null;
if (!empty($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $photoResult = validateAndMove($_FILES['photo'], 'photos');
    if (!isset($photoResult['error'])) {
        $photoPath = $photoResult['path'];
    }
}

$notes = clean($_POST['notes'] ?? '');

$stmt = $pdo->prepare(
    'INSERT INTO membership_applications
     (user_id, valid_id_path, proof_of_residence_path, photo_path, notes)
     VALUES (?, ?, ?, ?, ?)'
);
$stmt->execute([
    $user['id'],
    $validIdResult['path'],
    $proofResult['path'],
    $photoPath,
    $notes
]);
$appId = (int)$pdo->lastInsertId();

logAction($pdo, $user['id'], 'SUBMIT_MEMBERSHIP_APPLICATION', 'membership_applications', $appId, 'Membership application submitted');

createNotification($pdo, $user['id'],
    'Application Received',
    'Your membership application has been received and is under review. We will notify you once a decision has been made.',
    'membership'
);

respond(true, 'Your membership application has been submitted successfully.', ['application_id' => $appId]);
?>
