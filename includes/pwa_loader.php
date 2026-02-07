<?php
// includes/pwa_loader.php
?>
<link rel="manifest" href="manifest.json">
<link rel="icon" href="assets/favicon.png" type="image/x-icon">
<script>
    // 1. Register Service Worker
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('service-worker.js')
                .then(reg => console.log('SW Registered'))
                .catch(err => console.log('SW Fail:', err));
        });
    }

    // 2. Notifications Logic
    if ('Notification' in window) {
        // Request Permission immediately (or maybe on a button click better UX, but simple here)
        if (Notification.permission !== 'granted' && Notification.permission !== 'denied') {
            Notification.requestPermission();
        }
    }

    // 3. Polling for New Content
    let lastCount = -1;

    function checkNotifications() {
        fetch('api_notifications.php')
            .then(res => res.json())
            .then(data => {
                const currentCount = parseInt(data.count_to_rate);

                // If it's the first load, just set the baseline
                if (lastCount === -1) {
                    lastCount = currentCount;
                    return;
                }

                // If number of photos to rate INCREASED, it means a new photo was approved (or vote reset)
                // Use a simpler logic: if count_to_rate > lastCount, simplistic trigger
                // (More robust: track exact list of IDs)
                if (currentCount > lastCount) {
                    sendNotification("Nouveau dossier à noter !", "Il vous reste " + currentCount + " photos à évaluer.");
                }

                lastCount = currentCount;
            })
            .catch(err => console.error(err));
    }

    function sendNotification(title, body) {
        if (Notification.permission === 'granted') {
            // Mobile friendly check
            navigator.serviceWorker.ready.then(registration => {
                registration.showNotification(title, {
                    body: body,
                    icon: 'assets/favicon.png',
                    vibrate: [200, 100, 200]
                });
            });
        }
    }

    // Check every 60 seconds
    setInterval(checkNotifications, 60000);
</script>