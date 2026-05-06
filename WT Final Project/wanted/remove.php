<?php
// wanted/remove.php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

$want_id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("DELETE FROM skill_wants WHERE want_id = ? AND user_id = ?");
$stmt->execute([$want_id, $_SESSION['user_id']]);
flash('success', 'Removed from your want list.');
header('Location: /wanted/add.php');
exit;
