<?php
// messages/send.php - AJAX POST endpoint
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/notify_helper.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) { echo json_encode(['error' => 'Not authenticated']); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['error' => 'Method not allowed']); exit; }
verify_csrf();

$swap_id = (int)($_POST['swap_id'] ?? 0);
$me      = $_SESSION['user_id'];
$text    = trim(substr($_POST['message'] ?? '', 0, 2000));

if (!$swap_id || !$text) { echo json_encode(['error' => 'Invalid data']); exit; }

// Verify user is participant
$chk = $pdo->prepare("SELECT * FROM swap_requests WHERE swap_id=? AND (requester_id=? OR receiver_id=?) AND status IN ('accepted','pending')");
$chk->execute([$swap_id, $me, $me]);
$swap = $chk->fetch();
if (!$swap) { echo json_encode(['error' => 'Not authorized']); exit; }

$ins = $pdo->prepare("INSERT INTO messages (swap_id, sender_id, message) VALUES (?,?,?)");
$ins->execute([$swap_id, $me, $text]);
$msg_id = $pdo->lastInsertId();

// Notify the other person once per unread batch
$other_id = ($swap['requester_id'] == $me) ? $swap['receiver_id'] : $swap['requester_id'];
$unread_stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE swap_id=? AND sender_id=? AND is_read=0");
$unread_stmt->execute([$swap_id, $me]);
if ((int)$unread_stmt->fetchColumn() === 1) {
    add_notification($pdo, $other_id, 'new_message', $swap_id, $_SESSION['user_name'] . ' sent you a message.');
}

echo json_encode([
    'status'  => 'ok',
    'message' => [
        'message_id' => $msg_id,
        'sender_id'  => $me,
        'message'    => $text,
        'name'       => $_SESSION['user_name'],
        'time_ago'   => 'Just now',
    ]
]);
