<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) session_start();

// Define BASE_URL if not already defined
if (!defined('BASE_URL')) {
    $scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
    $baseUrl = rtrim(str_replace('\\', '/', dirname(dirname($scriptPath))), '/');
    if ($baseUrl === '' || $baseUrl === '.' || $baseUrl === '/') {
        $baseUrl = '';
    }
    define('BASE_URL', $baseUrl);
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/notify_helper.php';

$unread_count = 0;
if (isset($_SESSION['user_id'])) {
    $unread_count = get_unread_count($pdo, $_SESSION['user_id']);
}

$page_title = $page_title ?? 'SkillSwap';
$is_admin = ($_SESSION['user_role'] ?? '') === 'admin';
$logged_in = isset($_SESSION['user_id']);
$theme = (($_COOKIE['skillswap_theme'] ?? 'dark') === 'light') ? 'light' : 'dark';
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= e($theme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SkillSwap helps students exchange practical skills, find matches, and manage swaps across campus.">
    <title><?= e($page_title) ?> — SkillSwap</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/theme-clean.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/premium.css?v=<?= time() ?>">
    <script src="<?= BASE_URL ?>/assets/js/theme.js" defer></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" href="<?= BASE_URL ?>/assets/img/logo.svg?v=3" type="image/svg+xml">
    <link rel="shortcut icon" href="<?= BASE_URL ?>/assets/img/logo.svg?v=3" type="image/svg+xml">
</head>
<body>

<a class="skip-link" href="#mainContent">Skip to main content</a>

<nav class="navbar">
    <div class="nav-container">
        <a href="<?= BASE_URL ?>/index.php" class="nav-brand">
            <img src="<?= BASE_URL ?>/assets/img/logo.svg" alt="SkillSwap logo" class="brand-logo">
            <span class="brand-text">Skill<span class="brand-accent">Swap</span></span>
        </a>

        <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
            <span></span><span></span><span></span>
        </button>

        <div class="nav-menu" id="navMenu">
            <a href="<?= BASE_URL ?>/index.php" class="nav-link">Home</a>
            <a href="<?= BASE_URL ?>/skills/explore.php" class="nav-link">Explore Skills</a>
            <a href="<?= BASE_URL ?>/match/smart_match.php" class="nav-link">Smart Match</a>
            <a href="<?= BASE_URL ?>/sessions/my_sessions.php" class="nav-link">Sessions</a>
            <?php if ($logged_in): ?>
                <a href="<?= BASE_URL ?>/swaps/my_requests.php" class="nav-link">My Swaps</a>
                <a href="<?= BASE_URL ?>/skills/add.php" class="nav-link">Add Skill</a>
                <a href="<?= BASE_URL ?>/wanted/add.php" class="nav-link">Add Want</a>
            <?php endif; ?>

            <?php if ($is_admin): ?>
                <a href="<?= BASE_URL ?>/admin/dashboard.php" class="nav-link nav-link-admin">Admin</a>
            <?php endif; ?>

            <div class="nav-actions">
                    <button type="button" class="theme-toggle" id="themeToggle" aria-label="Toggle dark and light mode" aria-pressed="false">
                        <span class="theme-toggle-icon" aria-hidden="true">◐</span>
                        <span class="theme-toggle-text">Dark</span>
                    </button>
                <?php if ($logged_in): ?>
                    <a href="<?= BASE_URL ?>/notifications/index.php" class="nav-link notif-btn" aria-label="Notifications">
                        🔔
                        <?php if ($unread_count > 0): ?>
                            <span class="notif-badge"><?= $unread_count ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="nav-user-menu">
                        <div class="nav-user-main">
                        <a href="<?= BASE_URL ?>/profile/view.php?id=<?= $_SESSION['user_id'] ?>" class="nav-user-btn">
                            <img src="<?= avatar_url($_SESSION['user_avatar'] ?? null, $_SESSION['user_name'] ?? 'U') ?>" 
                                 alt="Avatar" class="nav-avatar">
                            <span><?= e(explode(' ', $_SESSION['user_name'] ?? 'User')[0]) ?></span>
                        </a>
                        <button type="button" class="nav-user-toggle" id="userMenuBtn" aria-label="Open user menu">
                            <span class="dropdown-arrow">▾</span>
                        </button>
                        </div>
                        <div class="user-dropdown" id="userDropdown">
                            <a href="<?= BASE_URL ?>/profile/view.php?id=<?= $_SESSION['user_id'] ?>">My Profile</a>
                            <a href="<?= BASE_URL ?>/profile/edit.php">Settings</a>
                            <a href="<?= BASE_URL ?>/profile/edit.php">Edit Profile</a>
                            <a href="<?= BASE_URL ?>/notifications/index.php">Notifications</a>
                            <a href="<?= BASE_URL ?>/swaps/my_requests.php">My Swaps</a>
                            <a href="<?= BASE_URL ?>/skills/add.php">Add Skill</a>
                            <a href="<?= BASE_URL ?>/wanted/add.php">Add Want</a>
                            <?php if ($is_admin): ?>
                                <a href="<?= BASE_URL ?>/admin/dashboard.php">Admin Dashboard</a>
                            <?php endif; ?>
                            <hr>
                            <a href="<?= BASE_URL ?>/auth/logout.php" class="dropdown-logout">Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/auth/login.php" class="btn btn-ghost">Login</a>
                    <a href="<?= BASE_URL ?>/auth/register.php" class="btn btn-primary">Join Free</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<main class="main-content" id="mainContent">
<?= render_flash() ?>
