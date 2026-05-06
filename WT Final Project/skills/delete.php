<?php
// skills/delete.php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

$skill_id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("DELETE FROM skills WHERE skill_id = ? AND user_id = ?");
$stmt->execute([$skill_id, $_SESSION['user_id']]);
flash('success', 'Skill removed.');
header('Location: /profile/view.php?id=' . $_SESSION['user_id']);
exit;
