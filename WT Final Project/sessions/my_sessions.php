<?php
// sessions/my_sessions.php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/notify_helper.php';

$page_title = 'My Sessions';
$me = $_SESSION['user_id'];

$sessions = $pdo->prepare("
    SELECT s.*, sr.requester_id, sr.receiver_id, sr.status AS swap_status,
           sk1.title AS req_skill, sk2.title AS rec_skill,
           u1.name AS req_name, u2.name AS rec_name
    FROM sessions s
    JOIN swap_requests sr ON sr.swap_id = s.swap_id
    JOIN skills sk1 ON sk1.skill_id = sr.requester_skill_id
    JOIN skills sk2 ON sk2.skill_id = sr.receiver_skill_id
    JOIN users u1 ON u1.user_id = sr.requester_id
    JOIN users u2 ON u2.user_id = sr.receiver_id
    WHERE sr.requester_id = ? OR sr.receiver_id = ?
    ORDER BY s.scheduled_at DESC
");
$sessions->execute([$me, $me]);
$session_list = $sessions->fetchAll();

require_once __DIR__ . '/../includes/header.php';

$page_data = [
    'sessions' => array_map(static function ($sess) use ($me) {
        $other_id = ($sess['requester_id'] == $me) ? $sess['receiver_id'] : $sess['requester_id'];
        $other_name = ($sess['requester_id'] == $me) ? $sess['rec_name'] : $sess['req_name'];
        $my_skill = ($sess['requester_id'] == $me) ? $sess['req_skill'] : $sess['rec_skill'];
        $their_skill = ($sess['requester_id'] == $me) ? $sess['rec_skill'] : $sess['req_skill'];
        $statusMap = ['scheduled' => 'Scheduled', 'completed' => 'Completed', 'cancelled' => 'Cancelled', 'no_show' => 'No show'];
        $iconMap = ['scheduled' => 'Scheduled', 'completed' => 'Completed', 'cancelled' => 'Cancelled', 'no_show' => 'No show'];

        return [
            'id' => (int)$sess['session_id'],
            'swapId' => (int)$sess['swap_id'],
            'otherId' => (int)$other_id,
            'otherName' => $other_name,
            'mySkill' => $my_skill,
            'theirSkill' => $their_skill,
            'icon' => $iconMap[$sess['status']] ?? 'Session',
            'status' => $sess['status'],
            'scheduledAt' => $sess['scheduled_at'] ? date('D, d M Y · h:i A', strtotime($sess['scheduled_at'])) : '',
            'meetLink' => $sess['meet_link'] ?? '',
            'canReview' => ($sess['status'] === 'completed' && $sess['swap_status'] === 'completed'),
        ];
    }, $session_list),
];

echo render_react_page('sessions', $page_data);
require_once __DIR__ . '/../includes/footer.php';
