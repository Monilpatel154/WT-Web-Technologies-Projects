<?php
// sessions/detail.php - Session detail + schedule
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/location_helper.php';
require_once __DIR__ . '/../includes/notify_helper.php';

$swap_id = (int)($_GET['swap_id'] ?? 0);
$me      = $_SESSION['user_id'];

$swap = $pdo->prepare("
    SELECT sr.*, 
           u1.name AS req_name, u1.latitude AS req_lat, u1.longitude AS req_lon,
           u2.name AS rec_name, u2.latitude AS rec_lat, u2.longitude AS rec_lon,
           sk1.title AS req_skill, sk2.title AS rec_skill
    FROM swap_requests sr
    JOIN users u1 ON u1.user_id = sr.requester_id
    JOIN users u2 ON u2.user_id = sr.receiver_id
    JOIN skills sk1 ON sk1.skill_id = sr.requester_skill_id
    JOIN skills sk2 ON sk2.skill_id = sr.receiver_skill_id
    WHERE sr.swap_id = ? AND (sr.requester_id=? OR sr.receiver_id=?)
");
$swap->execute([$swap_id,$me,$me]);
$swap = $swap->fetch();
if (!$swap) { header('Location: /sessions/my_sessions.php'); exit; }

$session = $pdo->prepare("SELECT * FROM sessions WHERE swap_id=?");
$session->execute([$swap_id]);
$session = $session->fetch();

// Handle schedule form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['schedule'])) {
    verify_csrf();
    $date      = $_POST['session_date'] ?? '';
    $time      = $_POST['session_time'] ?? '';
    $meet_link = trim(strip_tags($_POST['meet_link'] ?? ''));
    $location  = trim(strip_tags($_POST['meet_location'] ?? ''));
    $dt        = $date && $time ? date('Y-m-d H:i:s', strtotime("$date $time")) : null;

    $pdo->prepare("UPDATE sessions SET scheduled_at=?,meet_link=?,meet_location=?,status='scheduled' WHERE swap_id=?")
        ->execute([$dt, $meet_link, $location, $swap_id]);

    // Notify other person
    $other_id = ($swap['requester_id'] == $me) ? $swap['receiver_id'] : $swap['requester_id'];
    add_notification($pdo, $other_id, 'session_scheduled', $swap_id, $_SESSION['user_name'] . ' scheduled your skill session.');
    flash('success', 'Session scheduled!');
    header('Location: /sessions/detail.php?swap_id=' . $swap_id);
    exit;
}

// Handle complete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete'])) {
    verify_csrf();
    $pdo->prepare("UPDATE sessions SET status='completed',completed_at=NOW() WHERE swap_id=?")->execute([$swap_id]);
    $pdo->prepare("UPDATE swap_requests SET status='completed' WHERE swap_id=?")->execute([$swap_id]);
    $other_id = ($swap['requester_id'] == $me) ? $swap['receiver_id'] : $swap['requester_id'];
    add_notification($pdo, $other_id, 'session_completed', $swap_id, 'Session marked completed! Leave a review for ' . $_SESSION['user_name'] . '.');
    flash('success', 'Session marked as complete! Please leave a review for your partner.');
    header('Location: /reviews/submit.php?swap_id=' . $swap_id . '&reviewee=' . $other_id);
    exit;
}

// Meetup midpoint
$meetup_link = null;
$distance    = null;
if ($swap['req_lat'] && $swap['rec_lat']) {
    $mid     = midpoint($swap['req_lat'],$swap['req_lon'],$swap['rec_lat'],$swap['rec_lon']);
    $meetup_link = maps_link($mid['lat'],$mid['lng']);
    $distance = haversine_distance($swap['req_lat'],$swap['req_lon'],$swap['rec_lat'],$swap['rec_lon']);
}

$other_name = ($swap['requester_id'] == $me) ? $swap['rec_name'] : $swap['req_name'];
$page_title = 'Session Details';
require_once __DIR__ . '/../includes/header.php';
?>
<?php
$page_data = [
    'csrfToken' => csrf_token(),
    'action' => '/sessions/detail.php?swap_id=' . $swap_id,
    'swapId' => $swap_id,
    'otherName' => $other_name,
    'reqSkill' => $swap['req_skill'],
    'recSkill' => $swap['rec_skill'],
    'meetupLink' => $meetup_link,
    'distance' => $distance,
    'minDate' => date('Y-m-d'),
    'session' => [
        'status' => $session['status'] ?? 'scheduled',
        'statusLabel' => ucfirst(str_replace('_', '-', $session['status'] ?? 'scheduled')),
        'scheduledDate' => $session && !empty($session['scheduled_at']) ? date('Y-m-d', strtotime($session['scheduled_at'])) : '',
        'scheduledTime' => $session && !empty($session['scheduled_at']) ? date('H:i', strtotime($session['scheduled_at'])) : '',
        'scheduledDateTime' => $session && !empty($session['scheduled_at']) ? date('D, d M Y · h:i A', strtotime($session['scheduled_at'])) : '',
        'meetLink' => $session['meet_link'] ?? '',
        'meetLocation' => $session['meet_location'] ?? '',
    ],
    'reportUrl' => '/reports/submit.php?swap_id=' . $swap_id . '&reason=no_show',
    'chatUrl' => '/messages/chat.php?swap_id=' . $swap_id,
];

echo render_react_page('session_detail', $page_data);
require_once __DIR__ . '/../includes/footer.php';
