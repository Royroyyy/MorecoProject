<?php
require_once '../../config.php';
$admin = requireAdmin();

$d            = getJSON();
$title        = clean($d['title']        ?? '');
$category     = clean($d['category']     ?? '');
$desc         = clean($d['description']  ?? '');
$eligibility  = clean($d['eligibility']  ?? '');
$requirements = clean($d['requirements'] ?? '');
$howToApply   = clean($d['how_to_apply'] ?? '');
$emoji        = clean($d['emoji']        ?? '🎁');
$imageUrl     = clean($d['image_url']    ?? '');
$formUrl      = clean($d['form_url']     ?? '');
$isActive     = isset($d['is_active']) ? (int)(bool)$d['is_active'] : 1;

$validCategories = ['loan', 'savings', 'insurance', 'program', 'other'];
if (!$title || !$category) {
    respond(false, 'Title and category are required.', null, 400);
}
if (!in_array($category, $validCategories)) {
    respond(false, 'Invalid category. Must be: ' . implode(', ', $validCategories), null, 400);
}

$stmt = $pdo->prepare(
    'INSERT INTO benefits
     (title, category, description, eligibility, requirements, how_to_apply,
      emoji, image_url, form_url, is_active, created_by)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
);
$stmt->execute([
    $title, $category, $desc, $eligibility,
    $requirements, $howToApply, $emoji,
    $imageUrl, $formUrl, $isActive, $admin['id']
]);
$newId = (int)$pdo->lastInsertId();

logAction($pdo, $admin['id'], 'CREATE_BENEFIT', 'benefits', $newId, "Created benefit: $title");
respond(true, 'Benefit created successfully.', ['id' => $newId]);
?>
