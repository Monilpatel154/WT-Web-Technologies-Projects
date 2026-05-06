<?php
// skills/explore.php - Browse all skills with filters + distance sorting
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/location_helper.php';
require_once __DIR__ . '/../includes/notify_helper.php';

$page_title = 'Explore Skills';

// Filters
$search      = trim($_GET['search'] ?? '');
$category    = (int)($_GET['category'] ?? 0);
$mode_filter = $_GET['mode'] ?? '';
$sort        = $_GET['sort'] ?? 'newest';
$max_dist    = isset($_GET['distance']) ? (float)$_GET['distance'] : 50;

$categories = $pdo->query("SELECT * FROM skill_categories ORDER BY name")->fetchAll();

// Build query
$params = [];
$where  = ["s.status = 'active'", "u.status = 'active'"];

if ($search) {
    $where[] = "(s.title LIKE ? OR s.description LIKE ? OR u.name LIKE ?)";
    $like    = '%' . $search . '%';
    $params  = array_merge($params, [$like, $like, $like]);
}
if ($category > 0) {
    $where[] = "s.category_id = ?";
    $params[] = $category;
}
if ($mode_filter && in_array($mode_filter, ['online','in-person','both'])) {
    $where[] = "s.mode = ?";
    $params[] = $mode_filter;
}
if (isset($_SESSION['user_id'])) {
    $where[] = "s.user_id != ?";
    $params[] = $_SESSION['user_id'];
}

$my_lat = (float)($_SESSION['user_lat'] ?? 0);
$my_lon = (float)($_SESSION['user_lon'] ?? 0);

// Fallback: If session doesn't have location, fetch it from DB directly
if (!$my_lat && isset($_SESSION['user_id'])) {
    $loc = $pdo->prepare("SELECT latitude, longitude FROM users WHERE user_id = ?");
    $loc->execute([$_SESSION['user_id']]);
    $u_loc = $loc->fetch();
    if ($u_loc && $u_loc['latitude']) {
        $my_lat = (float)$u_loc['latitude'];
        $my_lon = (float)$u_loc['longitude'];
        $_SESSION['user_lat'] = $my_lat;
        $_SESSION['user_lon'] = $my_lon;
    } else {
        // If they still don't have a location, assign a default so they can at least see the feature
        $my_lat = 12.9716; // Default to Bangalore latitude
        $my_lon = 77.5946; // Default to Bangalore longitude
    }
}

$select_distance = "NULL AS distance_km";
if ($my_lat && $my_lon) {
    // Haversine formula directly in SQL for max efficiency
    $select_distance = "(6371 * acos(cos(radians($my_lat)) * cos(radians(u.latitude)) * cos(radians(u.longitude) - radians($my_lon)) + sin(radians($my_lat)) * sin(radians(u.latitude)))) AS distance_km";
    
    if (isset($_GET['distance'])) {
        $where[] = "(6371 * acos(cos(radians($my_lat)) * cos(radians(u.latitude)) * cos(radians(u.longitude) - radians($my_lon)) + sin(radians($my_lat)) * sin(radians(u.latitude)))) <= ?";
        $params[] = $max_dist;
    }
}

$orderBy = match ($sort) {
    'rating'  => 'u.avg_rating DESC',
    'credits' => 's.credit_value ASC',
    'distance'=> $my_lat ? 'distance_km ASC' : 's.created_at DESC',
    default   => 's.created_at DESC'
};

$sql = "
    SELECT s.*, u.name AS user_name, u.avg_rating, u.avatar, u.college,
           u.latitude AS u_lat, u.longitude AS u_lon,
           c.name AS category_name, c.icon AS category_icon,
           {$select_distance}
    FROM skills s
    JOIN users u ON s.user_id = u.user_id
    JOIN skill_categories c ON s.category_id = c.category_id
    WHERE " . implode(' AND ', $where) . "
    ORDER BY {$orderBy}
    LIMIT 60
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$skills = $stmt->fetchAll();

// Formatting the distance properly for display
foreach ($skills as &$sk) {
    if ($sk['distance_km'] !== null) {
        $sk['distance_km'] = round($sk['distance_km'], 1);
    }
}
unset($sk);

require_once __DIR__ . '/../includes/header.php';

$page_data = [
    'loggedIn' => isset($_SESSION['user_id']),
    'search' => $search,
    'category' => $category,
    'mode' => $mode_filter,
    'sort' => $sort,
    'maxDistance' => $max_dist,
    'canSortByDistance' => (bool)$my_lat,
    'count' => count($skills),
    'categories' => array_map(static function ($cat) {
        return [
            'id' => (int)$cat['category_id'],
            'name' => $cat['name'],
        ];
    }, $categories),
    'skills' => array_map(static function ($skill) use ($my_lat) {
        $modeLabels = [
            'online' => 'Online',
            'in-person' => 'In-Person',
            'both' => 'Flexible',
        ];

        $distanceBadge = '';
        if (!empty($skill['distance_km'])) {
            $distanceBadge = $skill['distance_km'] . ' km away';
        }

        return [
            'id' => (int)$skill['skill_id'],
            'title' => $skill['title'],
            'description' => $skill['description'] ?? '',
            'creditValue' => (int)$skill['credit_value'],
            'mode' => $skill['mode'],
            'modeLabel' => $modeLabels[$skill['mode']] ?? ucfirst((string)$skill['mode']),
            'categoryName' => $skill['category_name'],
            'userName' => $skill['user_name'],
            'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($skill['user_name']) . '&background=2563EB&color=fff&size=96',
            'rating' => (float)$skill['avg_rating'],
            'distanceBadge' => $distanceBadge,
        ];
    }, $skills),
];

echo render_react_page('explore', $page_data);
require_once __DIR__ . '/../includes/footer.php';
