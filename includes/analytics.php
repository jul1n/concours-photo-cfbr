<?php
// analytics.php - Lightweight Visitor Tracking
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../core/db.php';

try {
    // 1. Prepare Data
    $pageUrl = $_SERVER['REQUEST_URI'];
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'none';

    // 2. Anonymized Visitor Hash (IP + UA + Salt) 
    // This allows unique visitor counting without storing raw PII permanently in every row
    $visitorHash = hash('sha256', $ip . $ua . 'cfbr_salt_2026');

    // 3. Prevent double tracking on same page refresh within 1 minute (basic debounce)
    $lastTrackKey = 'last_track_' . md5($pageUrl);
    if (!isset($_SESSION[$lastTrackKey]) || (time() - $_SESSION[$lastTrackKey] > 60)) {
        $stmt = $pdo->prepare("INSERT INTO analytics (page_url, visitor_hash, user_agent, created_at) VALUES (?, ?, ?, CURRENT_TIMESTAMP)");
        $stmt->execute([$pageUrl, $visitorHash, $ua]);
        $_SESSION[$lastTrackKey] = time();
    }

} catch (Exception $e) {
    // Fail silently to not break the page for the user
}
