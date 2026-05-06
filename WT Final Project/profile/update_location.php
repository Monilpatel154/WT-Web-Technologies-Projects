<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

header('Content-Type: application/json');

$input = file_get_contents('php://input');
$data = json_decode($input, true);
if (!$data) {
    $data = $_POST;
}

$lat = isset($data['lat']) ? (float)$data['lat'] : null;
$lon = isset($data['lon']) ? (float)$data['lon'] : null;

if (!$lat || !$lon) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid coordinates provided.']);
    exit;
}

// Add a small random offset for privacy
$lat += mt_rand(-20, 20) / 10000;
$lon += mt_rand(-20, 20) / 10000;

$stmt = $pdo->prepare('UPDATE users SET latitude = ?, longitude = ?, location_updated_at = NOW() WHERE user_id = ?');
$stmt->execute([$lat, $lon, $_SESSION['user_id']]);

echo json_encode(['status' => 'ok', 'lat' => $lat, 'lon' => $lon]);
