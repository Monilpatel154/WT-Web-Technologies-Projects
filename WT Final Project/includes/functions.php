<?php
// includes/functions.php - Common utility functions

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

function verify_csrf(): void {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        http_response_code(403);
        die('CSRF validation failed.');
    }
}

function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

function flash(string $key, string $message): void {
    $_SESSION['flash'][$key] = $message;
}

function get_flash(string $key): ?string {
    if (isset($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
    return null;
}

function render_flash(): string {
    $html = '';
    if (!empty($_SESSION['flash'])) {
        foreach ($_SESSION['flash'] as $type => $msg) {
            $class = ($type === 'success') ? 'alert-success' : (($type === 'error') ? 'alert-error' : 'alert-info');
            $html .= '<div class="alert ' . $class . '">' . e($msg) . '<button class="alert-close" onclick="this.parentElement.remove()">×</button></div>';
        }
        unset($_SESSION['flash']);
    }
    return $html;
}

function time_ago(string $datetime): string {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->y > 0) return $diff->y . 'y ago';
    if ($diff->m > 0) return $diff->m . 'mo ago';
    if ($diff->d > 0) return $diff->d . 'd ago';
    if ($diff->h > 0) return $diff->h . 'h ago';
    if ($diff->i > 0) return $diff->i . 'm ago';
    return 'Just now';
}

function stars(float $rating): string {
    $html = '<span class="stars">';
    for ($i = 1; $i <= 5; $i++) {
        $html .= ($i <= round($rating)) ? '★' : '☆';
    }
    $html .= '</span>';
    return $html;
}

function avatar_url(?string $avatar, string $name): string {
    if ($avatar && file_exists(__DIR__ . '/../uploads/avatars/' . $avatar)) {
        return BASE_URL . '/uploads/avatars/' . $avatar;
    }
    $initials = strtoupper(substr($name, 0, 1));
    return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=6C63FF&color=fff&size=128';
}

function mode_badge(string $mode): string {
    $map = [
        'online'    => ['', 'badge-online', 'Online'],
        'in-person' => ['', 'badge-inperson', 'In-Person'],
        'both'      => ['', 'badge-both', 'Hybrid'],
    ];
    [$icon, $class, $label] = $map[$mode] ?? ['', '', $mode];
    $prefix = $icon !== '' ? $icon . ' ' : '';
    return "<span class=\"badge {$class}\">{$prefix}{$label}</span>";
}

function render_react_page(string $pageType, array $data = [], string $rootId = 'app-root'): string {
    $GLOBALS['disable_legacy_js'] = true;
    $payload = json_encode([
        'type' => $pageType,
        'data' => $data,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    return '<div id="' . htmlspecialchars($rootId, ENT_QUOTES, 'UTF-8') . '"></div>'
        . '<script>window.__SKILLSWAP_PAGE__ = ' . $payload . ';</script>'
        . '<script src="https://unpkg.com/react@18/umd/react.development.js"></script>'
        . '<script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js"></script>'
        . '<script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>'
        . '<script type="text/babel" data-presets="react" src="' . BASE_URL . '/assets/js/app-react.jsx"></script>';
}
