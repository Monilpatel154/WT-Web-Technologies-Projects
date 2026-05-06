<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

$reporter_id = $_SESSION['user_id'];
$reported_id = (int)($_GET['reported_id'] ?? ($_POST['reported_id'] ?? 0));
$swap_id = (int)($_GET['swap_id'] ?? ($_POST['swap_id'] ?? 0));

$reason_options = [
    'no_show' => 'No-show or last-minute cancellation',
    'harassment' => 'Harassment or inappropriate behavior',
    'fake_skill' => 'Listed skill is not real or misleading',
    'spam' => 'Spam or promotional content',
    'other' => 'Other concern',
];

if ($swap_id && !$reported_id) {
    $swap_stmt = $pdo->prepare('SELECT requester_id, receiver_id FROM swap_requests WHERE swap_id = ? AND (requester_id = ? OR receiver_id = ?)');
    $swap_stmt->execute([$swap_id, $reporter_id, $reporter_id]);
    $swap = $swap_stmt->fetch();
    if ($swap) {
        $reported_id = ((int)$swap['requester_id'] === $reporter_id) ? (int)$swap['receiver_id'] : (int)$swap['requester_id'];
    }
}

if (!$reported_id) {
    flash('error', 'No user was selected to report.');
    redirect('/index.php');
}

$user_stmt = $pdo->prepare('SELECT user_id, name FROM users WHERE user_id = ?');
$user_stmt->execute([$reported_id]);
$reported_user = $user_stmt->fetch();
if (!$reported_user) {
    flash('error', 'That user could not be found.');
    redirect('/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $reason = $_POST['reason'] ?? 'other';
    $description = trim($_POST['description'] ?? '');

    if (!array_key_exists($reason, $reason_options)) {
        flash('error', 'Please select a valid reason for the report.');
        redirect($_SERVER['REQUEST_URI']);
    }

    $swap_stmt = null;
    if ($swap_id) {
        $swap_stmt = $pdo->prepare('SELECT requester_skill_id, receiver_skill_id FROM swap_requests WHERE swap_id = ? AND (requester_id = ? OR receiver_id = ?)');
        $swap_stmt->execute([$swap_id, $reporter_id, $reporter_id]);
    }
    $swap_data = $swap_stmt ? $swap_stmt->fetch() : null;
    $skill_id = $swap_data ? (int)($swap_data['receiver_skill_id'] ?? $swap_data['requester_skill_id'] ?? 0) : null;

    $insert = $pdo->prepare('INSERT INTO reports (reporter_id, reported_user_id, skill_id, reason, status) VALUES (?, ?, ?, ?, ?)');
    $insert->execute([$reporter_id, $reported_id, $skill_id ?: null, $reason, 'open']);

    flash('success', 'Report submitted. Our team will review it shortly.');
    redirect('/profile/view.php?id=' . $reported_id);
}

$page_title = 'Submit a Report';
require_once __DIR__ . '/../includes/header.php';
?>
<?php
$page_data = [
    'csrfToken' => csrf_token(),
    'action' => '/reports/submit.php',
    'reportedId' => $reported_id,
    'reportedName' => $reported_user['name'],
    'swapId' => $swap_id,
    'defaultReason' => 'no_show',
    'reasonOptions' => array_map(static function ($key, $label) {
        return ['value' => $key, 'label' => $label];
    }, array_keys($reason_options), array_values($reason_options)),
    'cancelUrl' => '/profile/view.php?id=' . $reported_id,
];

echo render_react_page('report_submit', $page_data);
require_once __DIR__ . '/../includes/footer.php';