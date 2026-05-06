<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/location_helper.php';

$viewer_id = $_SESSION['user_id'] ?? null;
$profile_id = (int)($_GET['id'] ?? $viewer_id);
if (!$profile_id) {
    header('Location: /index.php');
    exit;
}

$stmt = $pdo->prepare(
    'SELECT u.*, c.name AS college_name FROM users u LEFT JOIN colleges c ON c.college_id = u.college_id WHERE u.user_id = ?'
);
$stmt->execute([$profile_id]);
$user = $stmt->fetch();
if (!$user) {
    header('Location: /index.php');
    exit;
}

$skills = $pdo->prepare(
    'SELECT s.*, sc.name AS category_name, sc.icon FROM skills s JOIN skill_categories sc ON sc.category_id = s.category_id WHERE s.user_id = ? AND s.status = ? ORDER BY s.created_at DESC'
);
$skills->execute([$profile_id, 'active']);
$skills = $skills->fetchAll();

$wants = $pdo->prepare(
    'SELECT sw.*, sc.name AS category_name, sc.icon FROM skill_wants sw JOIN skill_categories sc ON sc.category_id = sw.category_id WHERE sw.user_id = ? ORDER BY sw.created_at DESC'
);
$wants->execute([$profile_id]);
$wants = $wants->fetchAll();

$reviews = $pdo->prepare(
    'SELECT r.*, u.name AS reviewer_name FROM reviews r JOIN users u ON u.user_id = r.reviewer_id WHERE r.reviewee_id = ? ORDER BY r.created_at DESC LIMIT 10'
);
$reviews->execute([$profile_id]);
$reviews = $reviews->fetchAll();

$distance = null;
$distance_badge = '';
if ($viewer_id && $viewer_id !== $profile_id) {
    $current = $pdo->prepare('SELECT latitude, longitude, college_id FROM users WHERE user_id = ?');
    $current->execute([$viewer_id]);
    $me = $current->fetch();
    if ($me && $me['latitude'] && $me['longitude'] && $user['latitude'] && $user['longitude']) {
        $distance = haversine_distance((float)$me['latitude'], (float)$me['longitude'], (float)$user['latitude'], (float)$user['longitude']);
        $distance_badge = distance_badge($distance, $me['college_id'] == $user['college_id']);
    }
}

$distance_label = '';
if ($distance !== null) {
    if ($me && $me['college_id'] == $user['college_id']) {
        $distance_label = 'Same College';
    } elseif ($distance < 0.5) {
        $distance_label = 'Very Near';
    } else {
        $distance_label = $distance . ' km away';
    }
}

$page_title = $user['name'] . ' | Profile';
require_once __DIR__ . '/../includes/header.php';

$page_data = [
    'viewerId' => $viewer_id,
    'viewerIsSelf' => $viewer_id === $profile_id,
    'distanceBadge' => $distance_label,
    'user' => [
        'id' => (int)$user['user_id'],
        'name' => $user['name'],
        'avatar' => avatar_url($user['avatar'] ?? null, $user['name']),
        'avgRating' => (float)$user['avg_rating'],
        'totalReviews' => (int)$user['total_reviews'],
        'collegeName' => $user['college_name'] ?? '',
        'bio' => $user['bio'] ?? '',
        'availability' => array_values(json_decode($user['availability'] ?? '[]', true) ?: []),
    ],
    'skills' => array_map(static function ($skill) {
        return [
            'id' => (int)$skill['skill_id'],
            'title' => $skill['title'],
            'description' => $skill['description'] ?? '',
            'categoryName' => $skill['category_name'],
            'modeLabel' => match ($skill['mode']) {
                'online' => 'Online',
                'in-person' => 'In-Person',
                default => 'Flexible',
            },
        ];
    }, $skills),
    'wants' => array_map(static function ($want) {
        return [
            'id' => (int)$want['want_id'],
            'categoryName' => $want['category_name'],
            'description' => $want['description'] ?? '',
        ];
    }, $wants),
    'reviews' => array_map(static function ($review) {
        return [
            'id' => (int)$review['review_id'],
            'reviewerName' => $review['reviewer_name'],
            'rating' => (int)$review['rating'],
            'comment' => $review['comment'] ?? '',
            'timeAgo' => time_ago($review['created_at']),
        ];
    }, $reviews),
];

echo render_react_page('profile_view', $page_data);
require_once __DIR__ . '/../includes/footer.php';