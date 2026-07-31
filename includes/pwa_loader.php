<?php
// includes/pwa_loader.php

// Simple and effective base path detection
$isSubdir = (strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false ||
    strpos($_SERVER['SCRIPT_NAME'], '/jury/') !== false ||
    strpos($_SERVER['SCRIPT_NAME'], '/core/') !== false ||
    strpos($_SERVER['SCRIPT_NAME'], '/maintenance/') !== false);
$base = $isSubdir ? '../' : '';
?>
<link rel="manifest" href="<?= $base ?>manifest.json">
<link rel="shortcut icon" href="https://www.barrages-cfbr.eu/favicon.ico" type="image/x-icon">
<link rel="icon" href="https://www.barrages-cfbr.eu/favicon.ico" type="image/x-icon">
<script>
    // 1. Register Service Worker
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('<?= $base ?>service-worker.js')
                .then(reg => console.log('SW Registered'))
                .catch(err => console.log('SW Fail:', err));
        });
    }

    // 2. Notifications Logic
    if ('Notification' in window) {
        if (Notification.permission !== 'granted' && Notification.permission !== 'denied') {
            Notification.requestPermission();
        }
    }

    // 3. Polling for New Content
    let lastCount = -1;

    function checkNotifications() {
        const apiUrl = '<?= $base ?>jury/api_notifications.php'; // Correct path to API
        fetch(apiUrl)
            .then(res => {
                if (!res.ok) throw new Error('API not reachable');
                return res.json();
            })
            .then(data => {
                const currentCount = parseInt(data.count_to_rate);
                if (lastCount === -1) {
                    lastCount = currentCount;
                    return;
                }
                if (currentCount > lastCount) {
                    sendNotification("Nouveau dossier à noter !", "Il vous reste " + currentCount + " photos à évaluer.");
                }
                lastCount = currentCount;
            })
            .catch(err => {
                // Silently fail if API is not there (e.g. from home page without jury context)
            });
    }

    function sendNotification(title, body) {
        if (Notification.permission === 'granted') {
            navigator.serviceWorker.ready.then(registration => {
                registration.showNotification(title, {
                    body: body,
                    icon: '<?= $base ?>assets/favicon.png',
                    vibrate: [200, 100, 200]
                });
            });
        }
    }

    // Check every 60 seconds
    setInterval(checkNotifications, 60000);
</script>