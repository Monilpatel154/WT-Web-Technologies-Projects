<?php
// index.php - SkillSwap Landing Page
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/notify_helper.php';

$page_title = 'Home';

// Stats
$stats = $pdo->query("
    SELECT 
        (SELECT COUNT(*) FROM users WHERE role='user') AS total_users,
        (SELECT COUNT(*) FROM skills WHERE status='active') AS total_skills,
        (SELECT COUNT(*) FROM swap_requests WHERE status='completed') AS total_swaps,
        (SELECT COUNT(*) FROM skill_categories) AS total_categories
")->fetch();

// Featured skills (latest 6)
$featured = $pdo->query("
    SELECT s.*, u.name AS user_name, u.avg_rating, u.avatar, u.college,
    c.name AS category_name, c.icon AS category_icon
    FROM skills s
    JOIN users u ON s.user_id = u.user_id
    JOIN skill_categories c ON s.category_id = c.category_id
    WHERE s.status = 'active' AND u.status = 'active'
    ORDER BY s.created_at DESC LIMIT 6
")->fetchAll();

// Categories with skill count
$categories = $pdo->query("
    SELECT c.*, COUNT(s.skill_id) AS skill_count
    FROM skill_categories c
    LEFT JOIN skills s ON s.category_id = c.category_id AND s.status='active'
    GROUP BY c.category_id
    ORDER BY skill_count DESC
")->fetchAll();

// Unread count for nav
$unread_count = 0;
if (isset($_SESSION['user_id'])) {
    $unread_count = get_unread_count($pdo, $_SESSION['user_id']);
}

$logged_in = isset($_SESSION['user_id']);
$is_admin  = ($_SESSION['user_role'] ?? '') === 'admin';

require_once __DIR__ . '/includes/header.php';
?>
<?php
$home_data = [
    'loggedIn' => $logged_in,
    'isAdmin' => $is_admin,
    'stats' => [
        'totalUsers' => (int)($stats['total_users'] ?? 0),
        'totalSkills' => (int)($stats['total_skills'] ?? 0),
        'totalSwaps' => (int)($stats['total_swaps'] ?? 0),
        'totalCategories' => (int)($stats['total_categories'] ?? 0),
    ],
    'categories' => array_map(static function ($cat) {
        return [
            'id' => (int)$cat['category_id'],
            'name' => $cat['name'],
            'icon' => $cat['icon'],
            'count' => (int)$cat['skill_count'],
        ];
    }, $categories),
    'featured' => array_map(static function ($skill) {
        $modeLabels = [
            'online' => 'Online',
            'in-person' => 'In-Person',
            'both' => 'Flexible',
        ];

        return [
            'id' => (int)$skill['skill_id'],
            'title' => $skill['title'],
            'description' => $skill['description'],
            'creditValue' => (int)$skill['credit_value'],
            'mode' => $skill['mode'],
            'modeLabel' => $modeLabels[$skill['mode']] ?? ucfirst((string)$skill['mode']),
            'category' => $skill['category_name'],
            'categoryIcon' => $skill['category_icon'],
            'userName' => $skill['user_name'],
            'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($skill['user_name']) . '&background=2563EB&color=fff&size=96',
            'rating' => (float)$skill['avg_rating'],
        ];
    }, $featured),
];
?>
<?php echo render_react_page('home', $home_data); ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
