<?php
// notifications/count.php - AJAX endpoint returning unread count
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/notify_helper.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) { echo json_encode(['count' => 0]); exit; }

echo json_encode(['count' => get_unread_count($pdo, $_SESSION['user_id'])]);
