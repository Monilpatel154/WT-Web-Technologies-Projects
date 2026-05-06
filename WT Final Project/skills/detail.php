<?php
// skills/detail.php - Single skill detail page
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/location_helper.php';
require_once __DIR__ . '/../includes/notify_helper.php';

$skill_id = (int)($_GET['id'] ?? 0);
if (!$skill_id) { header('Location: /skills/explore.php'); exit; }

$stmt = $pdo->prepare("
    SELECT s.*, u.user_id AS owner_id, u.name AS user_name, u.bio AS user_bio,
           u.avg_rating, u.avatar, u.college, u.latitude AS u_lat, u.longitude AS u_lon,
           u.availability,
           c.name AS category_name, c.icon AS category_icon
    FROM skills s
    JOIN users u ON s.user_id = u.user_id
    JOIN skill_categories c ON s.category_id = c.category_id
    WHERE s.skill_id = ? AND s.status = 'active'
");
$stmt->execute([$skill_id]);
$skill = $stmt->fetch();
if (!$skill) { header('Location: /skills/explore.php'); exit; }

// Owner's other skills
$other_skills = $pdo->prepare("
    SELECT s.*, c.name AS category_name, c.icon AS category_icon
    FROM skills s JOIN skill_categories c ON s.category_id = c.category_id
    WHERE s.user_id = ? AND s.skill_id != ? AND s.status = 'active' LIMIT 4
");
$other_skills->execute([$skill['owner_id'], $skill_id]);
$other = $other_skills->fetchAll();

// Reviews for this owner
$reviews = $pdo->prepare("
    SELECT r.*, u.name AS reviewer_name, u.avatar AS reviewer_avatar
    FROM reviews r JOIN users u ON r.reviewer_id = u.user_id
    WHERE r.reviewee_id = ? ORDER BY r.created_at DESC LIMIT 5
");
$reviews->execute([$skill['owner_id']]);
$review_list = $reviews->fetchAll();

// Distance
$dist = null;
if (isset($_SESSION['user_lat']) && $_SESSION['user_lat'] && $skill['u_lat']) {
    $dist = haversine_distance((float)$_SESSION['user_lat'], (float)$_SESSION['user_lon'], (float)$skill['u_lat'], (float)$skill['u_lon']);
}

// My skills (for swap request)
$my_skills = [];
if (isset($_SESSION['user_id'])) {
    $ms = $pdo->prepare("SELECT skill_id, title FROM skills WHERE user_id = ? AND status='active'");
    $ms->execute([$_SESSION['user_id']]);
    $my_skills = $ms->fetchAll();
}

$page_title = $skill['title'];
$is_owner   = (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $skill['owner_id']);

$distance_label = '';
if ($dist !== null) {
    if (($skill['college'] ?? null) && (($_SESSION['user_college'] ?? null) === $skill['college'])) {
        $distance_label = 'Same College';
    } elseif ($dist < 0.5) {
        $distance_label = 'Very Near';
    } else {
        $distance_label = $dist . ' km away';
    }
}

require_once __DIR__ . '/../includes/header.php';

$page_data = [
    'loggedIn' => isset($_SESSION['user_id']),
    'isOwner' => $is_owner,
    'csrfToken' => csrf_token(),
    'skill' => [
        'id' => (int)$skill['skill_id'],
        'title' => $skill['title'],
        'description' => $skill['description'] ?? '',
        'creditValue' => (int)$skill['credit_value'],
        'mode' => $skill['mode'],
        'modeLabel' => match ($skill['mode']) {
            'online' => 'Online',
            'in-person' => 'In-Person',
            default => 'Flexible',
        },
        'categoryName' => $skill['category_name'],
        'ownerId' => (int)$skill['owner_id'],
        'ownerAvatar' => 'https://ui-avatars.com/api/?name=' . urlencode($skill['user_name']) . '&background=2563EB&color=fff&size=128',
        'userName' => $skill['user_name'],
        'college' => $skill['college'] ?? '',
        'bio' => $skill['user_bio'] ?? '',
        'avgRating' => (float)$skill['avg_rating'],
        'reviewCount' => (int)($skill['total_reviews_x'] ?? count($review_list)),
        'distanceBadge' => $distance_label,
        'userFirstName' => explode(' ', $skill['user_name'])[0],
    ],
    'mySkills' => array_map(static function ($ms) {
        return [
            'id' => (int)$ms['skill_id'],
            'title' => $ms['title'],
        ];
    }, $my_skills),
    'reviews' => array_map(static function ($rev) {
        return [
            'id' => (int)$rev['review_id'],
            'reviewerName' => $rev['reviewer_name'],
            'reviewerAvatar' => 'https://ui-avatars.com/api/?name=' . urlencode($rev['reviewer_name']) . '&background=94A3B8&color=fff&size=56',
            'rating' => (int)$rev['rating'],
            'comment' => $rev['comment'] ?? '',
            'timeAgo' => time_ago($rev['created_at']),
        ];
    }, $review_list),
    'otherSkills' => array_map(static function ($os) {
        return [
            'id' => (int)$os['skill_id'],
            'title' => $os['title'],
            'categoryName' => $os['category_name'],
        ];
    }, $other),
];

echo render_react_page('skill_detail', $page_data);
require_once __DIR__ . '/../includes/footer.php';
