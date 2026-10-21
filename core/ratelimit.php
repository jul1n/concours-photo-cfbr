<?php
/**
 * ratelimit.php — Limitation de fréquence (fenêtres fixes) stockée dans la base SQLite.
 *
 * Usage :
 *   require_once __DIR__ . '/ratelimit.php';
 *   if (rate_limit_exceeded($pdo, [
 *       ['upload_ip', client_ip(), 10, 3600],     // [action, sujet, max, fenêtre en secondes]
 *       ['upload_mail', $email, 3, 86400],
 *   ])) { ... }
 *
 * Tous les compteurs de la liste sont incrémentés ; la fonction renvoie true si l'un
 * d'eux dépasse sa limite. En cas d'erreur de base, on laisse passer (et on journalise)
 * plutôt que de bloquer le site.
 */

require_once __DIR__ . '/auth.php';

/**
 * Adresse IP du client. N'utilise un en-tête de proxy (ex. 'HTTP_X_FORWARDED_FOR',
 * 'HTTP_CF_CONNECTING_IP') que si 'trusted_proxy_header' est renseigné dans config.php :
 * ces en-têtes sont falsifiables dès que le serveur est joignable sans passer par le proxy.
 */
function client_ip(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $header = app_config()['trusted_proxy_header'] ?? '';
    if ($header !== '' && !empty($_SERVER[$header])) {
        $candidate = trim(explode(',', $_SERVER[$header])[0]);
        if (filter_var($candidate, FILTER_VALIDATE_IP)) {
            $ip = $candidate;
        }
    }
    return $ip;
}

function rate_limit_exceeded(PDO $pdo, array $rules): bool
{
    try {
        $pdo->exec('CREATE TABLE IF NOT EXISTS rate_limits (
            rkey TEXT NOT NULL,
            bucket INTEGER NOT NULL,
            n INTEGER NOT NULL DEFAULT 0,
            expires INTEGER NOT NULL,
            PRIMARY KEY (rkey, bucket)
        )');

        $now = time();
        $exceeded = false;
        foreach ($rules as [$action, $subject, $max, $window]) {
            // Sujet haché : pas d'e-mail ni d'IP en clair dans cette table.
            $key = $action . ':' . hash('sha256', strtolower((string) $subject));
            $bucket = intdiv($now, $window);

            $pdo->prepare('INSERT OR IGNORE INTO rate_limits (rkey, bucket, n, expires) VALUES (?, ?, 0, ?)')
                ->execute([$key, $bucket, ($bucket + 1) * $window]);
            $pdo->prepare('UPDATE rate_limits SET n = n + 1 WHERE rkey = ? AND bucket = ?')
                ->execute([$key, $bucket]);
            $stmt = $pdo->prepare('SELECT n FROM rate_limits WHERE rkey = ? AND bucket = ?');
            $stmt->execute([$key, $bucket]);
            if ((int) $stmt->fetchColumn() > $max) {
                $exceeded = true;
            }
        }

        // Purge occasionnelle des fenêtres terminées.
        if (random_int(1, 50) === 1) {
            $pdo->prepare('DELETE FROM rate_limits WHERE expires < ?')->execute([$now]);
        }
        return $exceeded;
    } catch (Throwable $e) {
        error_log('[ratelimit] ' . $e->getMessage());
        return false;
    }
}

/** Réponse standard quand la limite est atteinte (HTTP 429 + Retry-After). */
function rate_limit_block(string $message = 'Trop de tentatives. Merci de réessayer plus tard.', int $retryAfter = 900): void
{
    http_response_code(429);
    header('Retry-After: ' . $retryAfter);
    header('Content-Type: text/plain; charset=utf-8');
    exit($message);
}
