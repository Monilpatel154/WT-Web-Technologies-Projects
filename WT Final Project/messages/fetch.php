<?php
// messages/fetch.php - AJAX GET endpoint for polling
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) { echo json_encode(['error' => 'Not authenticated']); exit; }

$swap_id  = (int)($_GET['swap_id'] ?? 0);
$after_id = (int)($_GET['after']   ?? 0);
$me       = $_SESSION['user_id'];

// Verify participant
$chk = $pdo->prepare("SELECT swap_id FROM swap_requests WHERE swap_id=? AND (requester_id=? OR receiver_id=?)");
$chk->execute([$swap_id, $me, $me]);
if (!$chk->fetch()) { echo json_encode(['error' => 'Not authorized']); exit; }

// Mark incoming as read
$pdo->prepare("UPDATE messages SET is_read=1 WHERE swap_id=? AND sender_id!=? AND is_read=0")->execute([$swap_id, $me]);

// Fetch new messages after last known ID
$stmt = $pdo->prepare("
    SELECT m.message_id, m.sender_id, m.message, m.created_at, u.name, u.avatar
    FROM messages m JOIN users u ON u.user_id=m.sender_id
    WHERE m.swap_id=? AND m.message_id>?
    ORDER BY m.message_id ASC LIMIT 50
");
$stmt->execute([$swap_id, $after_id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$result = [];
foreach ($rows as $r) {
    $result[] = [
        'message_id' => (int)$r['message_id'],
        'sender_id'  => (int)$r['sender_id'],
        'message'    => $r['message'],
        'name'       => $r['name'],
        'time_ago'   => time_ago($r['created_at']),
        'is_me'      => $r['sender_id'] == $me,
    ];
}

echo json_encode(['status' => 'ok', 'messages' => $result]);
