<?php
// reviews/submit.php - Bidirectional review submission
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/notify_helper.php';

$swap_id     = (int)($_GET['swap_id']   ?? ($_POST['swap_id']   ?? 0));
$reviewee_id = (int)($_GET['reviewee']  ?? ($_POST['reviewee_id'] ?? 0));
$me          = $_SESSION['user_id'];

// Load swap – must be completed and user must be participant
$stmt = $pdo->prepare("
    SELECT sr.*, 
           u1.name AS req_name, u2.name AS rec_name,
           sk1.title AS req_skill, sk2.title AS rec_skill
    FROM swap_requests sr
    JOIN users u1 ON u1.user_id=sr.requester_id
    JOIN users u2 ON u2.user_id=sr.receiver_id
    JOIN skills sk1 ON sk1.skill_id=sr.requester_skill_id
    JOIN skills sk2 ON sk2.skill_id=sr.receiver_skill_id
    WHERE sr.swap_id=? AND sr.status='completed' AND (sr.requester_id=? OR sr.receiver_id=?)
");
$stmt->execute([$swap_id, $me, $me]);
$swap = $stmt->fetch();
if (!$swap) { flash('error','This swap is not available for review.'); redirect('/swaps/my_requests.php'); }

$session_stmt = $pdo->prepare('SELECT session_id FROM sessions WHERE swap_id = ?');
$session_stmt->execute([$swap_id]);
$session_id = (int)($session_stmt->fetchColumn() ?: 0);
if (!$session_id) { flash('error','This session is not available for review.'); redirect('/swaps/my_requests.php'); }

// Verify reviewee is the other participant
if ($reviewee_id != $swap['requester_id'] && $reviewee_id != $swap['receiver_id']) {
    flash('error', 'Invalid review target.'); redirect('/swaps/my_requests.php');
}
if ($reviewee_id == $me) { flash('error','You cannot review yourself.'); redirect('/swaps/my_requests.php'); }

// Check if already reviewed
$already = $pdo->prepare("SELECT review_id FROM reviews WHERE session_id=? AND reviewer_id=? AND reviewed_id=?");
$already->execute([$session_id, $me, $reviewee_id]);
$existing = $already->fetch();

// Reviewee name
$rstmt = $pdo->prepare("SELECT name, avg_rating, total_reviews FROM users WHERE user_id=?");
$rstmt->execute([$reviewee_id]);
$reviewee = $rstmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $rating  = (int)($_POST['rating'] ?? 0);
    $comment = trim(substr($_POST['comment'] ?? '', 0, 800));

    if ($rating < 1 || $rating > 5) { flash('error','Please select a rating (1–5 stars).'); redirect($_SERVER['REQUEST_URI']); }
    if ($existing) { flash('error','You have already submitted a review for this swap.'); redirect('/swaps/my_requests.php'); }

    // Insert review
    $pdo->prepare("INSERT INTO reviews (session_id, reviewer_id, reviewed_id, rating, comment) VALUES (?,?,?,?,?)")
        ->execute([$session_id, $me, $reviewee_id, $rating, $comment]);

    // Update reviewee's avg_rating and total_reviews
    $pdo->prepare("
        UPDATE users SET 
            total_reviews = total_reviews + 1,
            avg_rating = (avg_rating * total_reviews + ?) / (total_reviews + 1)
        WHERE user_id = ?
    ")->execute([$rating, $reviewee_id]);

    add_notification($pdo, $reviewee_id, 'review', $swap_id, $_SESSION['user_name'] . ' left you a ' . $rating . '-star review!');
    flash('success','Review submitted! Thank you for your feedback.');
    redirect('/swaps/my_requests.php');
}

$page_title = 'Review ' . ($reviewee['name'] ?? 'User');
require_once __DIR__ . '/../includes/header.php';
?>
<?php
$page_data = [
    'csrfToken' => csrf_token(),
    'action' => '/reviews/submit.php',
    'swapId' => $swap_id,
    'sessionId' => $session_id,
    'revieweeId' => $reviewee_id,
    'revieweeName' => $reviewee['name'] ?? 'User',
    'revieweeAvatar' => 'https://ui-avatars.com/api/?name=' . urlencode($reviewee['name'] ?? 'User') . '&background=6C63FF&color=fff&size=128',
    'revieweeRating' => (float)($reviewee['avg_rating'] ?? 0),
    'revieweeTotalReviews' => (int)($reviewee['total_reviews'] ?? 0),
    'reqSkill' => $swap['req_skill'],
    'recSkill' => $swap['rec_skill'],
    'existing' => (bool)$existing,
    'backUrl' => '/swaps/my_requests.php',
    'ratingLabel' => 'Click to rate',
];

echo render_react_page('review_submit', $page_data);
require_once __DIR__ . '/../includes/footer.php';
