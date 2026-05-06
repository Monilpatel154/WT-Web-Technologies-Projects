<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_check.php';

$action = $_GET['action'] ?? null;
$report_id = (int)($_GET['report_id'] ?? 0);
if ($action && $report_id) {
    $valid = ['reviewed','resolved','dismissed'];
    if (in_array($action, $valid, true)) {
        $pdo->prepare('UPDATE reports SET status = ? WHERE report_id = ?')->execute([$action, $report_id]);
        flash('success', 'Report marked as ' . $action . '.');
    }
    redirect('/admin/reports.php');
}

$reports = $pdo->query(
    'SELECT r.*, a.name AS reporter_name, b.name AS reported_name FROM reports r
     JOIN users a ON a.user_id = r.reporter_id
     JOIN users b ON b.user_id = r.reported_user_id
     ORDER BY r.created_at DESC'
)->fetchAll();

$page_title = 'Admin Reports';
require_once __DIR__ . '/../includes/header.php';

$page_data = [
    'title' => 'Review Reports',
    'subtitle' => 'Resolve or dismiss reports from users and review the details.',
    'reports' => array_map(static function ($report) {
        return [
            'id' => (int)$report['report_id'],
            'reporterName' => $report['reporter_name'],
            'reportedName' => $report['reported_name'],
            'reason' => $report['reason'],
            'description' => $report['details'] ?? '',
            'status' => $report['status'],
            'timeAgo' => time_ago($report['created_at']),
        ];
    }, $reports),
];

echo render_react_page('admin_reports', $page_data);
require_once __DIR__ . '/../includes/footer.php';