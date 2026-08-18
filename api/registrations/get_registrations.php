<?php
require_once '../../config.php';
$user = requireLogin();

if ($user['role'] === 'admin') {
    
    $eventId = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;

    if ($eventId) {
        $stmt = $pdo->prepare(
            'SELECT er.*,
                    u.first_name, u.last_name, u.email, u.username,
                    e.title AS event_title, e.event_date
             FROM event_registrations er
             JOIN users  u ON er.user_id  = u.id
             JOIN events e ON er.event_id = e.id
             WHERE er.event_id = ?
             ORDER BY er.registered_at DESC'
        );
        $stmt->execute([$eventId]);
    } else {
        $stmt = $pdo->query(
            'SELECT er.*,
                    u.first_name, u.last_name, u.email, u.username,
                    e.title AS event_title, e.event_date, e.emoji
             FROM event_registrations er
             JOIN users  u ON er.user_id  = u.id
             JOIN events e ON er.event_id = e.id
             ORDER BY er.registered_at DESC'
        );
    }
} else {
    
    $stmt = $pdo->prepare(
        'SELECT er.*,
                e.title, e.event_date, e.location, e.emoji, e.status AS event_status
         FROM event_registrations er
         JOIN events e ON er.event_id = e.id
         WHERE er.user_id = ?
         ORDER BY er.registered_at DESC'
    );
    $stmt->execute([$user['id']]);
}

$registrations = $stmt->fetchAll();
respond(true, count($registrations) . ' registration(s) found.', ['registrations' => $registrations]);
?>
