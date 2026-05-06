<?php
// match/smart_match.php - Smart mutual match algorithm
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/location_helper.php';

$me = $_SESSION['user_id'];

// Fetch the current user's data for location
$me_data = $pdo->prepare("SELECT * FROM users WHERE user_id=?");
$me_data->execute([$me]);
$me_user = $me_data->fetch();

// Smart Match SQL:
// Find users who:
// (A) offer skills I WANT (matching my skill_wants categories)
// (B) want skills I OFFER (matching my skills categories)
// → That's a true mutual match!
$matches = $pdo->prepare("
    SELECT DISTINCT
        u.user_id, u.name, u.avatar, u.avg_rating, u.total_reviews,
        u.college_id, u.latitude, u.longitude,
        c.name AS college_name,
        GROUP_CONCAT(DISTINCT sk_offer.title ORDER BY sk_offer.title SEPARATOR ', ') AS they_offer,
        GROUP_CONCAT(DISTINCT sc_offer.name  ORDER BY sc_offer.name  SEPARATOR ', ') AS they_offer_cats
    FROM users u
    JOIN colleges c ON c.college_id = u.college_id
    -- They must offer something in a category I WANT
    JOIN skills sk_offer ON sk_offer.user_id = u.user_id AND sk_offer.status = 'active'
    JOIN skill_wants sw_me ON sw_me.user_id = :me AND sw_me.category_id = sk_offer.category_id
    -- They must WANT something in a category I OFFER
    JOIN skill_wants sw_them ON sw_them.user_id = u.user_id
    JOIN skills sk_me ON sk_me.user_id = :me2 AND sk_me.status = 'active'
        AND sk_me.category_id = sw_them.category_id
    -- Category info for display
    JOIN skill_categories sc_offer ON sc_offer.category_id = sk_offer.category_id
    WHERE u.user_id != :me3 AND u.status = 'active'
    GROUP BY u.user_id
    ORDER BY u.avg_rating DESC
    LIMIT 30
");
$matches->execute([':me' => $me, ':me2' => $me, ':me3' => $me]);
$results = $matches->fetchAll();

// My skills and wants count for empty-state context
$my_skills_count = (int)$pdo->prepare("SELECT COUNT(*) FROM skills WHERE user_id=? AND status='active'")->execute([$me]) ?: 0;
$my_wants_count  = (int)$pdo->prepare("SELECT COUNT(*) FROM skill_wants WHERE user_id=?")->execute([$me]) ?: 0;

$stmt2 = $pdo->prepare("SELECT COUNT(*) FROM skills WHERE user_id=? AND status='active'");
$stmt2->execute([$me]);
$my_skills_count = (int)$stmt2->fetchColumn();

$stmt3 = $pdo->prepare("SELECT COUNT(*) FROM skill_wants WHERE user_id=?");
$stmt3->execute([$me]);
$my_wants_count = (int)$stmt3->fetchColumn();

// Attach distance info
foreach ($results as &$r) {
    $r['distance'] = null;
    $r['distance_badge'] = '';
    if ($me_user['latitude'] && $r['latitude']) {
        $dist = haversine_distance(
            (float)$me_user['latitude'], (float)$me_user['longitude'],
            (float)$r['latitude'],        (float)$r['longitude']
        );
        $r['distance'] = $dist;
        $r['distance_badge'] = distance_badge($dist, $me_user['college_name'] ?? null, $r['college_name'] ?? null);
    }
}
unset($r);

$page_title = 'Smart Match';
require_once __DIR__ . '/../includes/header.php';

$page_data = [
    'results' => array_map(static function ($r) {
        return [
            'id' => (int)$r['user_id'],
            'name' => $r['name'],
            'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($r['name']) . '&background=2563EB&color=fff&size=128',
            'collegeName' => $r['college_name'] ?? '',
            'ratingStars' => stars((float)$r['avg_rating']),
            'totalReviews' => (int)$r['total_reviews'],
            'theyOffer' => $r['they_offer'],
            'distanceBadge' => strip_tags($r['distance_badge'] ?? ''),
            'distanceKm' => $r['distance'] !== null ? number_format((float)$r['distance'], 1) : '',
        ];
    }, $results),
];

echo render_react_page('smart_match', $page_data);
require_once __DIR__ . '/../includes/footer.php';
