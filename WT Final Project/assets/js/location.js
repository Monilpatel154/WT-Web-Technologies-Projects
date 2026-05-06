// SkillSwap - Location & GPS Detection

(function () {
    'use strict';

    // Only run for logged-in users
    const updateUrl = '/profile/update_location.php';

    function sendLocation(lat, lon) {
        fetch(updateUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ lat, lon })
        }).catch(() => {}); // silent fail
    }

    function requestLocation() {
        if (!navigator.geolocation) return;

        // Only ask once per session
        if (sessionStorage.getItem('loc_asked')) return;
        sessionStorage.setItem('loc_asked', '1');

        navigator.geolocation.getCurrentPosition(
            pos => sendLocation(pos.coords.latitude, pos.coords.longitude),
            () => {} // user denied — that's fine
        );
    }

    // Trigger location capture after 2 seconds
    if (document.body.dataset.loggedIn === 'true') {
        setTimeout(requestLocation, 2000);
    }

})();
