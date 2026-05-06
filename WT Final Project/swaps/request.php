<?php
// swaps/request.php - Send a swap request (POST only)
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/notify_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /skills/explore.php'); exit; }
verify_csrf();

$requester_skill_id = (int)($_POST['requester_skill_id'] ?? 0);
$receiver_skill_id  = (int)($_POST['receiver_skill_id'] ?? 0);
$receiver_id        = (int)($_POST['receiver_id'] ?? 0);
$message            = trim(substr($_POST['message'] ?? '', 0, 1000));

// Validate ownership
$chk = $pdo->prepare("SELECT skill_id FROM skills WHERE skill_id=? AND user_id=? AND status='active'");
$chk->execute([$requester_skill_id, $_SESSION['user_id']]);
if (!$chk->fetch()) { flash('error', 'Invalid skill selected.'); header('Location: /skills/explore.php'); exit; }

// Validate receiver skill exists
$chk2 = $pdo->prepare("SELECT skill_id, credit_value FROM skills WHERE skill_id=? AND status='active'");
$chk2->execute([$receiver_skill_id]);
$rec_skill = $chk2->fetch();
if (!$rec_skill) { flash('error', 'Skill not found.'); header('Location: /skills/explore.php'); exit; }

// Get my skill credit value
$mySkill = $pdo->prepare("SELECT credit_value FROM skills WHERE skill_id=?");
$mySkill->execute([$requester_skill_id]);
$my_credits = (int)$mySkill->fetchColumn();

// Check for existing pending request
$dup = $pdo->prepare("SELECT swap_id FROM swap_requests WHERE requester_id=? AND receiver_id=? AND status='pending'");
$dup->execute([$_SESSION['user_id'], $receiver_id]);
if ($dup->fetch()) {
    flash('error', 'You already have a pending swap request with this user.');
    header('Location: /skills/detail.php?id=' . $receiver_skill_id);
    exit;
}

$stmt = $pdo->prepare("INSERT INTO swap_requests (requester_id,receiver_id,requester_skill_id,receiver_skill_id,requester_credits,receiver_credits,message) VALUES (?,?,?,?,?,?,?)");
$stmt->execute([$_SESSION['user_id'],$receiver_id,$requester_skill_id,$receiver_skill_id,$my_credits,$rec_skill['credit_value'],$message]);
$swap_id = $pdo->lastInsertId();

// Notify receiver
add_notification($pdo, $receiver_id, 'swap_request', $swap_id, $_SESSION['user_name'] . ' sent you a skill swap request!');

flash('success', 'Swap request sent! You\'ll be notified when they respond.');
header('Location: /swaps/my_requests.php');
exit;
