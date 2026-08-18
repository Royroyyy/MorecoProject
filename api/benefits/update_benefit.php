<?php
require_once '../../config.php';
requireAdmin();

$d  = getJSON();
$id = (int)($d['id'] ?? 0);
if (!$id) respond(false, 'Benefit ID is required.', null, 400);

$check = $pdo->prepare('SELECT id FROM benefits WHERE id = ? LIMIT 1');
$check->execute([$id]);
if (!$check->fetch()) respond(false, 'Benefit not found.', null, 404);

$validCategories = ['loan', 'savings', 'insurance', 'program', 'other'];
$category = clean($d['category'] ?? '');
if ($category && !in_array($category, $validCategories)) {
    respond(false, 'Invalid category.', null, 400);
}

$pdo->prepare(
    'UPDATE benefits
     SET title=?, category=?, description=?, eligibility=?,
         requirements=?, how_to_apply=?, emoji=?, image_url=?, form_url=?, is_active=?
     WHERE id=?'
)->execute([
    clean($d['title']        ?? ''),
    $category,
    clean($d['description']  ?? ''),
    clean($d['eligibility']  ?? ''),
    clean($d['requirements'] ?? ''),
    clean($d['how_to_apply'] ?? ''),
    clean($d['emoji']        ?? '🎁'),
    clean($d['image_url']    ?? ''),
    clean($d['form_url']     ?? ''),
    isset($d['is_active']) ? (int)(bool)$d['is_active'] : 1,
    $id
]);

logAction($pdo, $_SESSION['user']['id'], 'UPDATE_BENEFIT', 'benefits', $id, "Updated benefit #$id");
respond(true, 'Benefit updated successfully.');
?>
