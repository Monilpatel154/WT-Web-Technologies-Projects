<?php
// auth/register.php
session_start();
if (isset($_SESSION['user_id'])) { header('Location: /index.php'); exit; }
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (!defined('BASE_URL')) {
    define('BASE_URL', '');
}

$errors = [];
$data   = ['name' => '', 'email' => '', 'college_id' => ''];

// Fetch colleges for dropdown
$colleges = $pdo->query("SELECT college_id, name, city FROM colleges ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $name      = trim($_POST['name'] ?? '');
    $email     = strtolower(trim($_POST['email'] ?? ''));
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm_password'] ?? '';
    $college_id = (int)($_POST['college_id'] ?? 0);

    if (strlen($name) < 2)  $errors[] = 'Name must be at least 2 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';
    if ($college_id < 1) $errors[] = 'Please select your college.';

    if (empty($errors)) {
        // Check email uniqueness
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'An account with this email already exists.';
        }
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        // Get college details including coordinates
        $col  = $pdo->prepare("SELECT name, latitude, longitude FROM colleges WHERE college_id = ?");
        $col->execute([$college_id]);
        $college_data = $col->fetch();
        
        $college_name = $college_data['name'] ?? '';
        $lat = $college_data['latitude'] ?? null;
        $lon = $college_data['longitude'] ?? null;

        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, college, college_id, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $hash, $college_name, $college_id, $lat, $lon]);
        $user_id = $pdo->lastInsertId();

        $_SESSION['user_id']     = $user_id;
        $_SESSION['user_name']   = $name;
        $_SESSION['user_email']  = $email;
        $_SESSION['user_role']   = 'user';
        $_SESSION['user_college']= $college_name;
        $_SESSION['user_avatar'] = null;
        $_SESSION['user_lat']    = $lat;
        $_SESSION['user_lon']    = $lon;

        flash('success', 'Welcome to SkillSwap, ' . $name . '! Set up your profile to get started.');
        header('Location: /auth/setup_profile.php');
        exit;
    }

    $data = compact('name', 'email', 'college_id');
}

$page_data = [
    'csrfToken' => csrf_token(),
    'errors' => $errors,
    'form' => [
        'name' => $data['name'] ?? '',
        'email' => $data['email'] ?? '',
        'college_id' => $data['college_id'] ?? '',
    ],
    'colleges' => array_map(static function ($college) {
        return [
            'id' => (int)$college['college_id'],
            'name' => $college['name'],
            'city' => $college['city'] ?? '',
        ];
    }, $colleges),
];
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join SkillSwap — Register</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/theme-clean.css">
    <link rel="stylesheet" href="/assets/css/premium.css?v=<?= time() ?>">
    <script src="/assets/js/theme.js" defer></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body style="margin:0;padding:0;">
<?php echo render_react_page('register', $page_data); ?>
</body>
</html>
