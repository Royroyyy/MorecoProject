<?php
require_once '../../config.php';
requireAdmin();

if (!isset($_FILES['image'])) {
    respond(false, 'No image file received.', null, 400);
}

$file      = $_FILES['image'];
$maxSize   = 3 * 1024 * 1024;
$allowed   = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
$uploadDir = '../../assets/images/events/';

if ($file['error'] !== UPLOAD_ERR_OK)   respond(false, 'Upload failed. Please try again.', null, 400);
if ($file['size'] > $maxSize)           respond(false, 'Image too large. Maximum size is 3MB.', null, 400);
if (!in_array($file['type'], $allowed)) respond(false, 'Invalid file type. Use JPG, PNG, or WebP.', null, 400);

if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'event_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . strtolower($ext);
$destPath = $uploadDir . $filename;

if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    respond(false, 'Failed to save image. Check folder permissions.', null, 500);
}

respond(true, 'Image uploaded successfully.', ['url' => 'assets/images/events/' . $filename]);
?>
