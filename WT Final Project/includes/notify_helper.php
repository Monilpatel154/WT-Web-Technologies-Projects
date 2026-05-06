<?php
// includes/notify_helper.php - Insert notifications into DB

function add_notification(PDO $pdo, int $user_id, string $type, ?int $ref_id, string $message): void {
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, type, ref_id, message) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $type, $ref_id, $message]);
}

function get_unread_count(PDO $pdo, int $user_id): int {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$user_id]);
    return (int) $stmt->fetchColumn();
}
