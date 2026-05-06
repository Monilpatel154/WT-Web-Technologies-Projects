<?php
// auth/setup_profile.php - First-login profile wizard
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: /auth/login.php'); exit; }
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (!defined('BASE_URL')) {
    define('BASE_URL', '');
}

$categories = $pdo->query("SELECT * FROM skill_categories ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $bio  = trim(substr($_POST['bio'] ?? '', 0, 500));
    $avail = json_encode(array_filter([
        'mon' => $_POST['avail_mon'] ?? '',
        'tue' => $_POST['avail_tue'] ?? '',
        'wed' => $_POST['avail_wed'] ?? '',
        'thu' => $_POST['avail_thu'] ?? '',
        'fri' => $_POST['avail_fri'] ?? '',
        'sat' => $_POST['avail_sat'] ?? '',
        'sun' => $_POST['avail_sun'] ?? '',
    ]));

    $stmt = $pdo->prepare("UPDATE users SET bio = ?, availability = ? WHERE user_id = ?");
    $stmt->execute([$bio, $avail, $_SESSION['user_id']]);

    flash('success', 'Profile set up! Now add your first skill to offer.');
    header('Location: /skills/add.php');
    exit;
}
$page_title = 'Set Up Profile';
$page_data = [
    'csrfToken' => csrf_token(),
    'firstName' => explode(' ', $_SESSION['user_name'])[0],
    'bio' => '',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Up Profile — SkillSwap</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/theme-clean.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
<?php echo render_react_page('setup_profile', $page_data); ?>
</body>
</html>
