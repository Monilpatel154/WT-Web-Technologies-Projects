<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_check.php';

$action = $_GET['action'] ?? null;
$skill_id = (int)($_GET['skill_id'] ?? 0);
if ($action && $skill_id) {
    $skill_stmt = $pdo->prepare('SELECT skill_id, status FROM skills WHERE skill_id = ?');
    $skill_stmt->execute([$skill_id]);
    $skill = $skill_stmt->fetch();
    if ($skill) {
        if ($action === 'toggle') {
            $new_status = $skill['status'] === 'active' ? 'inactive' : 'active';
            $pdo->prepare('UPDATE skills SET status = ? WHERE skill_id = ?')->execute([$new_status, $skill_id]);
            flash('success', 'Skill status updated.');
        }
        if ($action === 'delete') {
            $pdo->prepare('DELETE FROM skills WHERE skill_id = ?')->execute([$skill_id]);
            flash('success', 'Skill removed from platform.');
        }
    }
    redirect('/admin/skills.php');
}

$skills = $pdo->query(
    'SELECT s.*, u.name AS user_name, sc.name AS category_name FROM skills s
     JOIN users u ON u.user_id = s.user_id
     JOIN skill_categories sc ON sc.category_id = s.category_id
     ORDER BY s.created_at DESC'
)->fetchAll();

$page_title = 'Admin Skills';
require_once __DIR__ . '/../includes/header.php';

$page_data = [
    'title' => 'Skill Moderation',
    'subtitle' => 'Review skill listings and toggle visibility when necessary.',
    'columns' => ['Title', 'Owner', 'Category', 'Mode', 'Status', 'Credits', 'Created', 'Actions'],
    'rows' => array_map(static function ($skill) {
        return [
            'id' => (int)$skill['skill_id'],
            'cells' => [
                $skill['title'],
                $skill['user_name'],
                $skill['category_name'],
                ucfirst($skill['mode']),
                $skill['status'],
                (string)$skill['credit_value'],
                date('M j, Y', strtotime($skill['created_at'])),
            ],
            'actions' => [
                ['label' => 'Toggle', 'href' => '?action=toggle&skill_id=' . $skill['skill_id'], 'variant' => 'btn-ghost', 'confirm' => 'Toggle this skill status?'],
                ['label' => 'Delete', 'href' => '?action=delete&skill_id=' . $skill['skill_id'], 'variant' => 'btn-danger', 'confirm' => 'Delete this skill?'],
            ],
        ];
    }, $skills),
];

echo render_react_page('admin_skills', $page_data);
require_once __DIR__ . '/../includes/footer.php';