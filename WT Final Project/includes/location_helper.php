<?php
// includes/location_helper.php - Haversine distance calculation

function haversine_distance(float $lat1, float $lon1, float $lat2, float $lon2): float {
    $earth_radius = 6371; // km
    $dlat = deg2rad($lat2 - $lat1);
    $dlon = deg2rad($lon2 - $lon1);
    $a = sin($dlat / 2) * sin($dlat / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dlon / 2) * sin($dlon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return round($earth_radius * $c, 1);
}

function midpoint(float $lat1, float $lon1, float $lat2, float $lon2): array {
    return [
        'lat' => ($lat1 + $lat2) / 2,
        'lng' => ($lon1 + $lon2) / 2
    ];
}

function maps_link(float $lat, float $lng): string {
    return "https://www.google.com/maps?q={$lat},{$lng}";
}

function distance_badge(?float $dist, ?string $user_college, ?string $other_college): string {
    if ($user_college && $other_college && $user_college === $other_college) {
        return '<span class="badge badge-same-college">Same College</span>';
    }
    if ($dist !== null) {
        if ($dist < 0.5) {
            return '<span class="badge badge-same-campus">Very Near</span>';
        }
        return '<span class="badge badge-nearby">' . $dist . ' km away</span>';
    }
    return '';
}
