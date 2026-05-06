<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_check.php';

$action = $_GET['action'] ?? null;
$target_id = (int)($_GET['user_id'] ?? 0);
if ($action && $target_id) {
    $user_stmt = $pdo->prepare('SELECT user_id, role, status FROM users WHERE user_id = ?');
    $user_stmt->execute([$target_id]);
    $target = $user_stmt->fetch();
    if ($target && $target['role'] !== 'admin') {
        if ($action === 'toggle') {
            $new_status = $target['status'] === 'active' ? 'suspended' : 'active';
            $pdo->prepare('UPDATE users SET status = ? WHERE user_id = ?')->execute([$new_status, $target_id]);
            flash('success', 'User status updated.');
        }
        if ($action === 'delete') {
            $pdo->prepare('DELETE FROM users WHERE user_id = ?')->execute([$target_id]);
            flash('success', 'User account removed.');
        }
    } else {
        flash('error', 'Cannot modify that user.');
    }
    redirect('/admin/users.php');
}

$users = $pdo->query(
    'SELECT u.*, c.name AS college_name FROM users u LEFT JOIN colleges c ON c.college_id = u.college_id ORDER BY u.created_at DESC'
)->fetchAll();

$page_title = 'Admin Users';
require_once __DIR__ . '/../includes/header.php';

$page_data = [
    'title' => 'Manage Users',
    'subtitle' => 'Activate, suspend, or remove accounts from the platform.',
    'columns' => ['Name', 'Email', 'College', 'Role', 'Status', 'Rating', 'Joined', 'Actions'],
    'rows' => array_map(static function ($user) {
        return [
            'id' => (int)$user['user_id'],
            'cells' => [
                $user['name'],
                $user['email'],
                $user['college_name'] ?? $user['college'] ?? '—',
                $user['role'],
                $user['status'],
                round((float)$user['avg_rating'], 1) . '/5 (' . (int)$user['total_reviews'] . ')',
                date('M j, Y', strtotime($user['created_at'])),
            ],
            'actions' => $user['role'] !== 'admin' ? [
                ['label' => 'Toggle', 'href' => '?action=toggle&user_id=' . $user['user_id'], 'variant' => 'btn-ghost', 'confirm' => "Change this user's status?"],
                ['label' => 'Delete', 'href' => '?action=delete&user_id=' . $user['user_id'], 'variant' => 'btn-danger', 'confirm' => 'Delete this user account?'],
            ] : [],
        ];
    }, $users),
];

echo render_react_page('admin_users', $page_data);
require_once __DIR__ . '/../includes/footer.php';