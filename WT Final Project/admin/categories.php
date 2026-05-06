<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_check.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['name'] ?? '');
    $icon = trim($_POST['icon'] ?? 'Book');
    $color = trim($_POST['color'] ?? '#6C63FF');
    if ($name !== '') {
        $stmt = $pdo->prepare('INSERT INTO skill_categories (name, icon, color) VALUES (?, ?, ?)');
        $stmt->execute([$name, $icon, $color]);
        flash('success', 'Category added successfully.');
    } else {
        flash('error', 'Category name cannot be empty.');
    }
    redirect('/admin/categories.php');
}

if (!empty($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    $pdo->prepare('DELETE FROM skill_categories WHERE category_id = ?')->execute([$delete_id]);
    flash('success', 'Category removed.');
    redirect('/admin/categories.php');
}

$categories = $pdo->query('SELECT * FROM skill_categories ORDER BY name')->fetchAll();
$page_title = 'Admin Categories';
require_once __DIR__ . '/../includes/header.php';

$page_data = [
    'title' => 'Manage Categories',
    'subtitle' => 'Add or remove skill categories available for listings.',
    'csrfToken' => csrf_token(),
    'categories' => array_map(static function ($category) {
        return [
            'id' => (int)$category['category_id'],
            'icon' => $category['icon'],
            'name' => $category['name'],
            'color' => $category['color'],
        ];
    }, $categories),
];

echo render_react_page('admin_categories', $page_data);
require_once __DIR__ . '/../includes/footer.php';