<?php
// includes/auth_check.php - Redirect to login if not authenticated
if (session_status() === PHP_SESSION_NONE) session_start();

if (!defined('BASE_URL')) {
    define('BASE_URL', '');
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/auth/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}
