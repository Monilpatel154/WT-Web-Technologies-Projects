<?php
// wanted/add.php - Add a skill I want to learn
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

$page_title = 'Add Skill Want';
$categories = $pdo->query("SELECT * FROM skill_categories ORDER BY name")->fetchAll();

// My existing wants
$existing = $pdo->prepare("SELECT category_id FROM skill_wants WHERE user_id = ?");
$existing->execute([$_SESSION['user_id']]);
$my_wants = array_column($existing->fetchAll(), 'category_id');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $category_id = (int)($_POST['category_id'] ?? 0);
    $description = trim(substr($_POST['description'] ?? '', 0, 300));

    if ($category_id > 0) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO skill_wants (user_id, category_id, description) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $category_id, $description]);
        flash('success', 'Added to your "Want to Learn" list!');
        header('Location: /match/smart_match.php');
        exit;
    }
}

require_once __DIR__ . '/../includes/header.php';
?>
<?php
$want_rows = $pdo->prepare("SELECT sw.want_id, sc.name, sc.icon FROM skill_wants sw JOIN skill_categories sc ON sw.category_id = sc.category_id WHERE sw.user_id = ?");
$want_rows->execute([$_SESSION['user_id']]);

$page_data = [
    'csrfToken' => csrf_token(),
    'action' => '/wanted/add.php',
    'categories' => array_map(static function ($cat) {
        return [
            'id' => (int)$cat['category_id'],
            'name' => $cat['name'],
            'icon' => $cat['icon'],
        ];
    }, $categories),
    'existingCategoryIds' => array_map('intval', $my_wants),
    'wants' => array_map(static function ($want) {
        return [
            'id' => (int)$want['want_id'],
            'name' => $want['name'],
            'icon' => $want['icon'],
        ];
    }, $want_rows->fetchAll()),
];

echo render_react_page('wanted_add', $page_data);
require_once __DIR__ . '/../includes/footer.php';
