<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_check.php';

$top_users = $pdo->query(
    'SELECT u.user_id, u.name, u.avg_rating, u.total_reviews, COUNT(s.skill_id) AS skill_count
     FROM users u
     LEFT JOIN skills s ON s.user_id = u.user_id
     WHERE u.role = "user"
     GROUP BY u.user_id
     ORDER BY skill_count DESC, u.avg_rating DESC
     LIMIT 8'
)->fetchAll();

$top_reported = $pdo->query(
    'SELECT u.user_id, u.name, COUNT(r.report_id) AS reports FROM users u
     JOIN reports r ON r.reported_id = u.user_id
     GROUP BY u.user_id ORDER BY reports DESC LIMIT 6'
)->fetchAll();

$category_counts = $pdo->query(
    'SELECT sc.name, COUNT(s.skill_id) AS total FROM skill_categories sc
     LEFT JOIN skills s ON s.category_id = sc.category_id
     GROUP BY sc.category_id ORDER BY total DESC LIMIT 8'
)->fetchAll();

$page_title = 'Admin Analytics';
require_once __DIR__ . '/../includes/header.php';

$page_data = [
    'title' => 'Analytics',
    'subtitle' => 'Insights into platform usage and engagement.',
    'topUsers' => array_map(static function ($user) {
        return [
            'id' => (int)$user['user_id'],
            'name' => $user['name'],
            'skillCount' => (int)$user['skill_count'],
            'ratingStars' => round((float)$user['avg_rating'], 1) . '/5',
            'totalReviews' => (int)$user['total_reviews'],
        ];
    }, $top_users),
    'topReported' => array_map(static function ($user) {
        return [
            'id' => (int)$user['user_id'],
            'name' => $user['name'],
            'reports' => (int)$user['reports'],
        ];
    }, $top_reported),
    'categoryCounts' => array_map(static function ($category) {
        return [
            'name' => $category['name'],
            'total' => (int)$category['total'],
        ];
    }, $category_counts),
];

echo render_react_page('admin_analytics', $page_data);
require_once __DIR__ . '/../includes/footer.php';