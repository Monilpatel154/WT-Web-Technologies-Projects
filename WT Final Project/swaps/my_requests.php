<?php
// swaps/my_requests.php - View all my swap requests
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/notify_helper.php';

$page_title = 'My Swaps';
$me         = $_SESSION['user_id'];
$tab        = $_GET['tab'] ?? 'incoming';

// Incoming requests (I am receiver)
$incoming = $pdo->prepare("
    SELECT sr.*, 
           u.name AS requester_name, u.avatar AS requester_avatar, u.college AS requester_college,
           sk1.title AS my_skill_title, sk2.title AS their_skill_title
    FROM swap_requests sr
    JOIN users u ON u.user_id = sr.requester_id
    JOIN skills sk1 ON sk1.skill_id = sr.receiver_skill_id
    JOIN skills sk2 ON sk2.skill_id = sr.requester_skill_id
    WHERE sr.receiver_id = ? AND sr.status = 'pending'
    ORDER BY sr.created_at DESC
");
$incoming->execute([$me]);
$incoming_list = $incoming->fetchAll();

// Outgoing (I sent)
$outgoing = $pdo->prepare("
    SELECT sr.*,
           u.name AS receiver_name, u.avatar AS receiver_avatar,
           sk1.title AS my_skill_title, sk2.title AS their_skill_title
    FROM swap_requests sr
    JOIN users u ON u.user_id = sr.receiver_id
    JOIN skills sk1 ON sk1.skill_id = sr.requester_skill_id
    JOIN skills sk2 ON sk2.skill_id = sr.receiver_skill_id
    WHERE sr.requester_id = ? AND sr.status IN ('pending','accepted')
    ORDER BY sr.created_at DESC
");
$outgoing->execute([$me]);
$outgoing_list = $outgoing->fetchAll();

// Active / completed
$active = $pdo->prepare("
    SELECT sr.*,
           u1.name AS req_name, u2.name AS rec_name,
           sk1.title AS req_skill, sk2.title AS rec_skill
    FROM swap_requests sr
    JOIN users u1 ON u1.user_id = sr.requester_id
    JOIN users u2 ON u2.user_id = sr.receiver_id
    JOIN skills sk1 ON sk1.skill_id = sr.requester_skill_id
    JOIN skills sk2 ON sk2.skill_id = sr.receiver_skill_id
    WHERE (sr.requester_id = ? OR sr.receiver_id = ?) AND sr.status IN ('accepted','completed')
    ORDER BY sr.updated_at DESC
");
$active->execute([$me, $me]);
$active_list = $active->fetchAll();

require_once __DIR__ . '/../includes/header.php';

$tabs = [
    'incoming' => ['Incoming', count($incoming_list)],
    'outgoing' => ['Sent', count($outgoing_list)],
    'active' => ['Active / Done', count($active_list)],
];

$page_data = [
    'tab' => $tab,
    'csrfToken' => csrf_token(),
    'tabs' => $tabs,
    'incoming' => array_map(static function ($sw) {
        return [
            'id' => (int)$sw['swap_id'],
            'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($sw['requester_name']) . '&background=2563EB&color=fff&size=128',
            'name' => $sw['requester_name'],
            'mySkill' => $sw['my_skill_title'],
            'theirSkill' => $sw['their_skill_title'],
            'message' => $sw['message'] ?? '',
            'timeAgo' => time_ago($sw['created_at']),
            'college' => $sw['requester_college'] ?? '',
        ];
    }, $incoming_list),
    'outgoing' => array_map(static function ($sw) {
        return [
            'id' => (int)$sw['swap_id'],
            'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($sw['receiver_name']) . '&background=94A3B8&color=fff&size=128',
            'name' => $sw['receiver_name'],
            'mySkill' => $sw['my_skill_title'],
            'theirSkill' => $sw['their_skill_title'],
            'status' => $sw['status'],
            'statusLabel' => ucfirst($sw['status']),
            'timeAgo' => time_ago($sw['created_at']),
        ];
    }, $outgoing_list),
    'active' => array_map(static function ($sw) use ($me) {
        $other_id = ($sw['requester_id'] == $me) ? $sw['receiver_id'] : $sw['requester_id'];
        $other_name = ($sw['requester_id'] == $me) ? $sw['rec_name'] : $sw['req_name'];
        $other_skill = ($sw['requester_id'] == $me) ? $sw['rec_skill'] : $sw['req_skill'];
        $my_skill = ($sw['requester_id'] == $me) ? $sw['req_skill'] : $sw['rec_skill'];

        return [
            'id' => (int)$sw['swap_id'],
            'otherId' => (int)$other_id,
            'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($other_name) . '&background=2563EB&color=fff&size=128',
            'name' => $other_name,
            'mySkill' => $my_skill,
            'theirSkill' => $other_skill,
            'status' => $sw['status'],
            'statusLabel' => ucfirst($sw['status']),
        ];
    }, $active_list),
];

echo render_react_page('swaps', $page_data);
require_once __DIR__ . '/../includes/footer.php';
