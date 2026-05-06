<?php
// notifications/index.php - Notification list page
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

$me = $_SESSION['user_id'];

// Mark all as read when user opens this page
$pdo->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?")->execute([$me]);

// Load last 60 notifications
$stmt = $pdo->prepare("
    SELECT * FROM notifications WHERE user_id=? 
    ORDER BY created_at DESC LIMIT 60
");
$stmt->execute([$me]);
$notifs = $stmt->fetchAll();

// Icons per type
$type_icons = [
    'swap_request'      => '🔄 Swap',
    'swap_accepted'     => '✅ Accepted',
    'swap_declined'     => '❌ Declined',
    'swap_cancelled'    => '🚫 Cancelled',
    'new_message'       => '💬 Message',
    'review'            => '⭐ Review',
    'review_received'   => '⭐ Review',
    'session_scheduled' => '📅 Session',
    'session_complete'  => '✔️ Done',
    'report'            => '🚩 Report',
];

// Link per type/ref_id
function notif_link(string $type, $ref_id): string {
    return match(true) {
        in_array($type, ['swap_request','swap_accepted','swap_declined','swap_cancelled']) => '/swaps/my_requests.php',
        $type === 'new_message'   => '/messages/chat.php?swap_id=' . (int)$ref_id,
        $type === 'review'        => '/swaps/my_requests.php',
        str_starts_with($type, 'session') => '/sessions/detail.php?swap_id=' . (int)$ref_id,
        default => '#',
    };
}

$page_title = 'Notifications';
require_once __DIR__ . '/../includes/header.php';

$page_data = [
    'notifications' => array_map(static function ($n) use ($type_icons) {
        return [
            'id' => (int)($n['notif_id'] ?? $n['notification_id'] ?? 0),
            'icon' => $type_icons[$n['type']] ?? 'Message',
            'message' => $n['message'],
            'timeAgo' => time_ago($n['created_at']),
            'link' => notif_link($n['type'], $n['ref_id']),
            'isRead' => (bool)$n['is_read'],
        ];
    }, $notifs),
];

echo render_react_page('notifications', $page_data);
require_once __DIR__ . '/../includes/footer.php';
