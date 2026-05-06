<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_check.php';

$stats = $pdo->query(
    'SELECT 
        (SELECT COUNT(*) FROM users) AS users,
        (SELECT COUNT(*) FROM skills) AS skills,
        (SELECT COUNT(*) FROM swap_requests WHERE status = "pending") AS pending_swaps,
        (SELECT COUNT(*) FROM sessions WHERE status = "scheduled") AS scheduled_sessions,
        (SELECT COUNT(*) FROM reports WHERE status = "pending") AS pending_reports,
        (SELECT COUNT(*) FROM notifications) AS notifications'
)->fetch();

$recent_reports = $pdo->query(
    'SELECT r.*, u.name AS reporter_name, v.name AS reported_name FROM reports r
     JOIN users u ON u.user_id = r.reporter_id
     JOIN users v ON v.user_id = r.reported_id
     ORDER BY r.created_at DESC LIMIT 6'
)->fetchAll();

$top_categories = $pdo->query(
    'SELECT sc.name, sc.icon, COUNT(s.skill_id) AS total FROM skill_categories sc
     LEFT JOIN skills s ON s.category_id = sc.category_id
     GROUP BY sc.category_id ORDER BY total DESC LIMIT 6'
)->fetchAll();

$page_title = 'Admin Dashboard';
require_once __DIR__ . '/../includes/header.php';

$page_data = [
    'stats' => [
        'users' => (int)$stats['users'],
        'skills' => (int)$stats['skills'],
        'pendingSwaps' => (int)$stats['pending_swaps'],
        'scheduledSessions' => (int)$stats['scheduled_sessions'],
        'pendingReports' => (int)$stats['pending_reports'],
        'notifications' => (int)$stats['notifications'],
    ],
    'topCategories' => array_map(static function ($cat) {
        return [
            'name' => $cat['name'],
            'icon' => $cat['icon'],
            'total' => (int)$cat['total'],
        ];
    }, $top_categories),
    'recentReports' => array_map(static function ($report) {
        return [
            'id' => (int)$report['report_id'],
            'reporterName' => $report['reporter_name'],
            'reportedName' => $report['reported_name'],
            'reason' => $report['reason'],
            'timeAgo' => time_ago($report['created_at']),
        ];
    }, $recent_reports),
];

echo render_react_page('admin_dashboard', $page_data);
require_once __DIR__ . '/../includes/footer.php';