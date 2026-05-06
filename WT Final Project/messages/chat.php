<?php
// messages/chat.php - Per-swap chat with AJAX polling
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/location_helper.php';
require_once __DIR__ . '/../includes/notify_helper.php';

$swap_id = (int)($_GET['swap_id'] ?? 0);
$me      = $_SESSION['user_id'];

// Load swap with full info
$stmt = $pdo->prepare("
    SELECT sr.*, 
           u1.name AS req_name, u1.avatar AS req_avatar, u1.latitude AS req_lat, u1.longitude AS req_lon,
           u2.name AS rec_name, u2.avatar AS rec_avatar, u2.latitude AS rec_lat, u2.longitude AS rec_lon,
           sk1.title AS req_skill, sk2.title AS rec_skill
    FROM swap_requests sr
    JOIN users u1 ON u1.user_id = sr.requester_id
    JOIN users u2 ON u2.user_id = sr.receiver_id
    JOIN skills sk1 ON sk1.skill_id = sr.requester_skill_id
    JOIN skills sk2 ON sk2.skill_id = sr.receiver_skill_id
    WHERE sr.swap_id = ? AND (sr.requester_id=? OR sr.receiver_id=?)
");
$stmt->execute([$swap_id, $me, $me]);
$swap = $stmt->fetch();
if (!$swap) { header('Location: /swaps/my_requests.php'); exit; }

// Handle message send (AJAX or form POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $msg_text = trim(substr($_POST['message'] ?? '', 0, 2000));
    if ($msg_text) {
        $ins = $pdo->prepare("INSERT INTO messages (swap_id, sender_id, message) VALUES (?,?,?)");
        $ins->execute([$swap_id, $me, $msg_text]);
        $msg_id = $pdo->lastInsertId();

        // Notify the other person
        $other_id = ($swap['requester_id'] == $me) ? $swap['receiver_id'] : $swap['requester_id'];
        // Only notify if it's their first unread (avoid spam)
        $unread_check = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE swap_id=? AND sender_id=? AND is_read=0");
        $unread_check->execute([$swap_id, $me]);
        if ((int)$unread_check->fetchColumn() === 1) {
            add_notification($pdo, $other_id, 'new_message', $swap_id, $_SESSION['user_name'] . ' sent you a message.');
        }

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) || isset($_POST['ajax'])) {
            header('Content-Type: application/json');
            echo json_encode([
                'status'     => 'ok',
                'message'    => [
                    'message_id' => $msg_id,
                    'sender_id'  => $me,
                    'message'    => $msg_text,
                    'name'       => $_SESSION['user_name'],
                    'avatar'     => 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['user_name']) . '&background=6C63FF&color=fff&size=56',
                    'time_ago'   => 'Just now',
                ]
            ]);
            exit;
        }
    }
    header('Location: /messages/chat.php?swap_id=' . $swap_id);
    exit;
}

// Mark messages as read
$pdo->prepare("UPDATE messages SET is_read=1 WHERE swap_id=? AND sender_id != ?")->execute([$swap_id, $me]);

// Load last 50 messages
$msgs = $pdo->prepare("
    SELECT m.*, u.name, u.avatar 
    FROM messages m JOIN users u ON u.user_id = m.sender_id
    WHERE m.swap_id=? ORDER BY m.created_at ASC LIMIT 50
");
$msgs->execute([$swap_id]);
$messages = $msgs->fetchAll();
$last_id  = !empty($messages) ? end($messages)['message_id'] : 0;

$other_id   = ($swap['requester_id'] == $me) ? $swap['receiver_id'] : $swap['requester_id'];
$other_name = ($swap['requester_id'] == $me) ? $swap['rec_name']   : $swap['req_name'];

// Meetup midpoint
$meetup_link = $dist = null;
if ($swap['req_lat'] && $swap['rec_lat']) {
    $mid  = midpoint((float)$swap['req_lat'],(float)$swap['req_lon'],(float)$swap['rec_lat'],(float)$swap['rec_lon']);
    $meetup_link = maps_link($mid['lat'], $mid['lng']);
    $dist = haversine_distance((float)$swap['req_lat'],(float)$swap['req_lon'],(float)$swap['rec_lat'],(float)$swap['rec_lon']);
}

$distance_label = $dist !== null ? $dist . ' km apart' : '';

$page_title = 'Chat with ' . $other_name;
require_once __DIR__ . '/../includes/header.php';

$page_data = [
    'csrfToken' => csrf_token(),
    'swapId' => $swap_id,
    'me' => [
        'id' => $me,
        'name' => $_SESSION['user_name'],
        'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['user_name']) . '&background=2563EB&color=fff&size=56',
    ],
    'other' => [
        'id' => (int)$other_id,
        'name' => $other_name,
        'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($other_name) . '&background=94A3B8&color=fff&size=56',
    ],
    'swap' => [
        'requesterSkill' => $swap['req_skill'],
        'receiverSkill' => $swap['rec_skill'],
        'status' => $swap['status'],
    ],
    'messages' => array_map(static function ($msg) use ($me) {
        return [
            'id' => (int)$msg['message_id'],
            'senderId' => (int)$msg['sender_id'],
            'name' => $msg['name'],
            'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($msg['name']) . '&background=' . (($msg['sender_id'] == $me) ? '2563EB' : '94A3B8') . '&color=fff&size=56',
            'message' => $msg['message'],
            'timeAgo' => time_ago($msg['created_at']),
        ];
    }, $messages),
    'meetupLink' => $meetup_link,
    'distanceKm' => $distance_label,
];

echo render_react_page('chat', $page_data);
require_once __DIR__ . '/../includes/footer.php';
