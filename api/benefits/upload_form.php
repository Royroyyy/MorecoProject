<?php
require_once '../../config.php';
requireAdmin();

if (!isset($_FILES['form'])) {
    respond(false, 'No file received.', null, 400);
}

$file      = $_FILES['form'];
$maxSize   = 5 * 1024 * 1024; 
$allowed   = ['application/pdf'];
$uploadDir = '../../assets/forms/';

if ($file['error'] !== UPLOAD_ERR_OK) {
    respond(false, 'Upload failed. Please try again.', null, 400);
}
if ($file['size'] > $maxSize) {
    respond(false, 'File too large. Maximum size is 5MB.', null, 400);
}
if ($file['type'] !== 'application/pdf') {
    respond(false, 'Invalid file type. Please upload a PDF file.', null, 400);
}

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$filename = 'form_' . time() . '_' . bin2hex(random_bytes(4)) . '.pdf';
$destPath = $uploadDir . $filename;

if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    respond(false, 'Failed to save file. Check folder permissions.', null, 500);
}

$publicUrl = 'assets/forms/' . $filename;
respond(true, 'Form uploaded successfully.', ['url' => $publicUrl]);
?>
