<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['name'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $college_id = (int)($_POST['college_id'] ?? 0);
    $availability = [];
    foreach (['monday','tuesday','wednesday','thursday','friday','saturday','sunday'] as $day) {
        if (!empty($_POST['availability'][$day])) {
            $availability[] = $day;
        }
    }

    if ($name === '') {
        flash('error', 'Name cannot be empty.');
        redirect('/profile/edit.php');
    }

    $college_name = null;
    if ($college_id > 0) {
        $stmt = $pdo->prepare('SELECT name FROM colleges WHERE college_id = ?');
        $stmt->execute([$college_id]);
        $college_name = $stmt->fetchColumn();
        if (!$college_name) {
            $college_id = 0;
        }
    }

    $stmt = $pdo->prepare(
        'UPDATE users SET name = ?, bio = ?, college = ?, college_id = ?, availability = ? WHERE user_id = ?'
    );
    $stmt->execute([
        $name,
        $bio,
        $college_name ?: null,
        $college_id ?: null,
        json_encode($availability),
        $user_id,
    ]);

    $_SESSION['user_name'] = $name;
    flash('success', 'Profile updated successfully.');
    redirect('/profile/view.php?id=' . $user_id);
}

$user = $pdo->prepare('SELECT * FROM users WHERE user_id = ?');
$user->execute([$user_id]);
$user = $user->fetch();
if (!$user) {
    flash('error', 'User account not found.');
    redirect('/index.php');
}

$colleges = $pdo->query('SELECT college_id, name FROM colleges ORDER BY name')->fetchAll();
$availability = json_decode($user['availability'] ?? '[]', true);
if (!is_array($availability)) {
    $availability = [];
}

$page_title = 'Edit Profile';
require_once __DIR__ . '/../includes/header.php';

$page_data = [
    'csrfToken' => csrf_token(),
    'errors' => [],
    'user' => [
        'id' => (int)$user['user_id'],
        'name' => $user['name'],
        'bio' => $user['bio'] ?? '',
        'collegeId' => (int)($user['college_id'] ?? 0),
    ],
    'colleges' => array_map(static function ($college) {
        return [
            'id' => (int)$college['college_id'],
            'name' => $college['name'],
        ];
    }, $colleges),
    'availability' => array_values($availability),
];

echo render_react_page('profile_edit', $page_data);
require_once __DIR__ . '/../includes/footer.php';