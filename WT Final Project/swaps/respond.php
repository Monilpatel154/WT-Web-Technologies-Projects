<?php
// swaps/respond.php - Accept or decline a swap
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/notify_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /swaps/my_requests.php'); exit; }
verify_csrf();

$swap_id = (int)($_POST['swap_id'] ?? 0);
$action  = $_POST['action'] ?? '';

if (!in_array($action, ['accept','decline','cancel'])) {
    flash('error', 'Invalid action.');
    header('Location: /swaps/my_requests.php');
    exit;
}

// Load swap
$stmt = $pdo->prepare("SELECT * FROM swap_requests WHERE swap_id = ?");
$stmt->execute([$swap_id]);
$swap = $stmt->fetch();
if (!$swap) { header('Location: /swaps/my_requests.php'); exit; }

$me = $_SESSION['user_id'];

if ($action === 'cancel') {
    if ($swap['requester_id'] != $me) { flash('error', 'Not authorized.'); header('Location: /swaps/my_requests.php'); exit; }
    $pdo->prepare("UPDATE swap_requests SET status='cancelled' WHERE swap_id=?")->execute([$swap_id]);
    flash('success', 'Swap request cancelled.');
} elseif ($action === 'accept') {
    if ($swap['receiver_id'] != $me) { flash('error', 'Not authorized.'); header('Location: /swaps/my_requests.php'); exit; }
    $pdo->prepare("UPDATE swap_requests SET status='accepted' WHERE swap_id=?")->execute([$swap_id]);
    // Create session record
    $pdo->prepare("INSERT INTO sessions (swap_id,status) VALUES (?,?)")->execute([$swap_id,'scheduled']);
    add_notification($pdo, $swap['requester_id'], 'swap_accepted', $swap_id, $_SESSION['user_name'] . ' accepted your swap request! Schedule your session now.');
    flash('success', 'Swap accepted! Head to the chat to schedule your session.');
    header('Location: /messages/chat.php?swap_id=' . $swap_id);
    exit;
} elseif ($action === 'decline') {
    if ($swap['receiver_id'] != $me) { flash('error', 'Not authorized.'); header('Location: /swaps/my_requests.php'); exit; }
    $pdo->prepare("UPDATE swap_requests SET status='declined' WHERE swap_id=?")->execute([$swap_id]);
    add_notification($pdo, $swap['requester_id'], 'swap_declined', $swap_id, $_SESSION['user_name'] . ' declined your swap request.');
    flash('info', 'Swap declined.');
}

header('Location: /swaps/my_requests.php');
exit;
