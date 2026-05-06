<?php
// auth/login.php
session_start();
if (isset($_SESSION['user_id'])) { header('Location: /index.php'); exit; }
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (!defined('BASE_URL')) {
    define('BASE_URL', '');
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email    = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] === 'suspended') {
                $error = 'Your account has been suspended. Contact support.';
            } else {
                $_SESSION['user_id']      = $user['user_id'];
                $_SESSION['user_name']    = $user['name'];
                $_SESSION['user_email']   = $user['email'];
                $_SESSION['user_role']    = $user['role'];
                $_SESSION['user_college'] = $user['college'];
                $_SESSION['user_avatar']  = $user['avatar'];
                $_SESSION['user_lat']     = $user['latitude'];
                $_SESSION['user_lon']     = $user['longitude'];

                $redirect = $_GET['redirect'] ?? '/index.php';
                // Validate redirect URL to prevent open redirect
                if (!str_starts_with($redirect, '/')) $redirect = '/index.php';
                header('Location: ' . $redirect);
                exit;
            }
        } else {
            $error = 'Incorrect email or password.';
        }
    } else {
        $error = 'Please fill in all fields.';
    }
}

$page_data = [
    'csrfToken' => csrf_token(),
    'error' => $error,
    'email' => $email,
    'redirect' => $_GET['redirect'] ?? '',
];
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — SkillSwap</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/theme-clean.css">
    <link rel="stylesheet" href="/assets/css/premium.css">
    <script src="/assets/js/theme.js" defer></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body style="margin:0;padding:0;">
<?php echo render_react_page('login', $page_data); ?>
</body>
</html>
