<?php
// skills/add.php - Add a new skill
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/notify_helper.php';

$page_title  = 'Add Skill';
$categories  = $pdo->query("SELECT * FROM skill_categories ORDER BY name")->fetchAll();
$errors      = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $credit_val  = max(1, min(5, (int)($_POST['credit_value'] ?? 1)));
    $mode        = $_POST['mode'] ?? 'both';

    if (strlen($title) < 3)  $errors[] = 'Title must be at least 3 characters.';
    if ($category_id < 1)    $errors[] = 'Please select a category.';
    if (!in_array($mode, ['online','in-person','both'])) $errors[] = 'Invalid mode.';

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO skills (user_id, category_id, title, description, credit_value, mode) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $category_id, $title, $description, $credit_val, $mode]);
        
        // If this is a new user (0 wants), send them to add a want to complete onboarding
        $wants_count = (int)$pdo->query("SELECT COUNT(*) FROM skill_wants WHERE user_id = " . (int)$_SESSION['user_id'])->fetchColumn();
        
        if ($wants_count === 0) {
            flash('success', 'Skill added successfully! Now, add a skill you want to learn.');
            header('Location: /wanted/add.php');
        } else {
            flash('success', 'Skill added successfully! Others can now find and swap with you.');
            header('Location: /profile/view.php?id=' . $_SESSION['user_id']);
        }
        exit;
    }
}

require_once __DIR__ . '/../includes/header.php';

$page_data = [
    'mode' => 'add',
    'csrfToken' => csrf_token(),
    'errors' => $errors,
    'action' => '',
    'cancelUrl' => '/skills/explore.php',
    'categories' => array_map(static function ($cat) {
        return [
            'id' => (int)$cat['category_id'],
            'name' => $cat['name'],
        ];
    }, $categories),
    'skill' => [
        'title' => $_POST['title'] ?? '',
        'description' => $_POST['description'] ?? '',
        'categoryId' => (int)($_POST['category_id'] ?? 0),
        'creditValue' => (int)($_POST['credit_value'] ?? 1),
        'mode' => $_POST['mode'] ?? 'both',
    ],
];

echo render_react_page('skill_form', $page_data);
require_once __DIR__ . '/../includes/footer.php';
