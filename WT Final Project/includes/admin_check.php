<?php
// includes/admin_check.php - Redirect if not admin
require_once __DIR__ . '/auth_check.php';
if ($_SESSION['user_role'] !== 'admin') {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}
