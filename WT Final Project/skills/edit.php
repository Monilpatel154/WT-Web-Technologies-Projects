<?php
// skills/edit.php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

$skill_id   = (int)($_GET['id'] ?? 0);
$categories = $pdo->query("SELECT * FROM skill_categories ORDER BY name")->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM skills WHERE skill_id = ? AND user_id = ?");
$stmt->execute([$skill_id, $_SESSION['user_id']]);
$skill = $stmt->fetch();
if (!$skill) { header('Location: /profile/view.php?id=' . $_SESSION['user_id']); exit; }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $credit_val  = max(1, min(5, (int)($_POST['credit_value'] ?? 1)));
    $mode        = $_POST['mode'] ?? 'both';
    $status      = $_POST['status'] ?? 'active';

    if (strlen($title) < 3) $errors[] = 'Title too short.';
    if ($category_id < 1)   $errors[] = 'Select a category.';

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE skills SET category_id=?,title=?,description=?,credit_value=?,mode=?,status=? WHERE skill_id=? AND user_id=?");
        $stmt->execute([$category_id,$title,$description,$credit_val,$mode,$status,$skill_id,$_SESSION['user_id']]);
        flash('success', 'Skill updated!');
        header('Location: /skills/detail.php?id=' . $skill_id);
        exit;
    }
    $skill = array_merge($skill, compact('title','description','category_id','credit_val','mode','status'));
}

$page_title = 'Edit Skill';
require_once __DIR__ . '/../includes/header.php';

$page_data = [
    'mode' => 'edit',
    'csrfToken' => csrf_token(),
    'errors' => $errors,
    'action' => '',
    'cancelUrl' => '/skills/detail.php?id=' . $skill_id,
    'categories' => array_map(static function ($cat) {
        return [
            'id' => (int)$cat['category_id'],
            'name' => $cat['name'],
        ];
    }, $categories),
    'skill' => [
        'title' => $skill['title'],
        'description' => $skill['description'] ?? '',
        'categoryId' => (int)$skill['category_id'],
        'creditValue' => (int)$skill['credit_value'],
        'mode' => $skill['mode'],
        'status' => $skill['status'],
    ],
];

echo render_react_page('skill_form', $page_data);
require_once __DIR__ . '/../includes/footer.php';
