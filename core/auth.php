<?php
/**
 * auth.php — Contrôles d'accès et protection CSRF centralisés.
 *
 * Usage :
 *   require_once __DIR__ . '/../core/auth.php';
 *   require_jury();           // sur toute page/endpoint réservé au jury
 *   require_admin();          // sur les pages résultats/exports
 *   require_maintenance();    // sur le dashboard de maintenance
 *   csrf_field();             // dans un <form> pour insérer le token
 *   csrf_check();             // en tête du traitement POST
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Charge la configuration locale (core/config.php) avec repli sur des
 * valeurs par défaut sûres si le fichier est absent.
 */
function app_config(): array
{
    static $config = null;
    if ($config === null) {
        $path = __DIR__ . '/config.php';
        $config = is_file($path) ? require $path : [];

        // Detect current URL dynamically
        $detectedProtocol = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] === 1) || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) ? 'https' : 'http';
        $detectedHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        $subDir = '';
        if (isset($_SERVER['SCRIPT_NAME'])) {
            $dir = dirname($_SERVER['SCRIPT_NAME']);
            // Clean up folders from the script path to get the web root path
            $dir = str_replace(['/core', '/jury', '/maintenance', '/admin'], '', $dir);
            $dir = rtrim($dir, '/\\');
            $subDir = $dir;
        }
        $detectedUrl = $detectedProtocol . '://' . $detectedHost . $subDir;

        // If base_url is placeholder or has local/example values, and we have a valid request host, auto-detect it
        if (!isset($config['base_url']) 
            || $config['base_url'] === 'https://concours.barrages-cfbr.eu' 
            || (strpos($config['base_url'], 'localhost') !== false && strpos($detectedHost, 'localhost') === false)) {
            $config['base_url'] = $detectedUrl;
        }

        $config += [
            'base_url'                  => $detectedUrl,
            'mail_from'                 => 'no-reply@barrages-cfbr.eu',
            'admin_password_hash'       => '',
            'maintenance_password_hash' => '',
            'jury_token_ttl'            => 3600,
            'debug'                     => false,
        ];
    }
    return $config;
}

/* ------------------------------------------------------------------ */
/* Gardes d'accès                                                      */
/* ------------------------------------------------------------------ */

function is_jury(): bool
{
    return isset($_SESSION['jury_logged_in']) && $_SESSION['jury_logged_in'] === true;
}

function is_admin(): bool
{
    return (isset($_SESSION['admin_unlocked']) && $_SESSION['admin_unlocked'] === true)
        || (isset($_SESSION['maintenance_authed']) && $_SESSION['maintenance_authed'] === true);
}

function is_maintenance(): bool
{
    return isset($_SESSION['maintenance_authed']) && $_SESSION['maintenance_authed'] === true;
}

/**
 * Refuse l'accès si le jury n'est pas connecté.
 * $asApi = true → réponse JSON 403 ; sinon redirection vers login.
 */
function require_jury(bool $asApi = false): void
{
    if (is_jury()) {
        return;
    }
    if ($asApi) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'forbidden']);
    } else {
        header('Location: login.php');
    }
    exit;
}

function require_admin(): void
{
    if (!is_admin()) {
        http_response_code(403);
        exit('Accès refusé. Veuillez vous authentifier depuis la page des résultats.');
    }
}

function require_maintenance(): void
{
    if (!is_maintenance()) {
        http_response_code(403);
        exit('Accès refusé.');
    }
}

/* ------------------------------------------------------------------ */
/* Protection CSRF                                                     */
/* ------------------------------------------------------------------ */

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Champ caché à insérer dans les formulaires. */
function csrf_field(): void
{
    echo '<input type="hidden" name="csrf_token" value="'
        . htmlspecialchars(csrf_token(), ENT_QUOTES) . '">';
}

/**
 * Vérifie le token CSRF d'une requête POST. Interrompt si invalide.
 * Utilise hash_equals pour éviter les attaques temporelles.
 */
function csrf_check(): void
{
    $sent = $_POST['csrf_token'] ?? '';
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $sent)) {
        http_response_code(400);
        exit('Erreur de sécurité : jeton CSRF invalide ou expiré.');
    }
}

/**
 * Génère le chemin d'accès absolu du fichier PDF pour un participant,
 * incluant son nom de famille et prénom pour une identification plus aisée.
 * Gère le repli vers l'ancien nom de fichier s'il existe déjà.
 */
function get_participant_pdf_path(array $participant): string
{
    $pdfDir = __DIR__ . '/../uploads/pdfs/';
    
    $cleanFirstname = str_replace([' ', "'", '"', '’'], '_', mb_strtolower(trim($participant['firstname'] ?? ''), 'UTF-8'));
    $cleanLastname = str_replace([' ', "'", '"', '’'], '_', mb_strtolower(trim($participant['lastname'] ?? ''), 'UTF-8'));
    
    $unwanted_array = [
        'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
        'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
        'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
        'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
        'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ü'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y'
    ];
    $cleanFirstname = strtr($cleanFirstname, $unwanted_array);
    $cleanLastname = strtr($cleanLastname, $unwanted_array);
    $cleanFirstname = preg_replace('/[^a-z0-9_-]/', '', $cleanFirstname);
    $cleanLastname = preg_replace('/[^a-z0-9_-]/', '', $cleanLastname);
    
    $newName = $pdfDir . 'agreement_' . $participant['id'] . '_' . $cleanFirstname . '_' . $cleanLastname . '.pdf';
    $oldName = $pdfDir . 'agreement_' . $participant['id'] . '.pdf';
    
    if (file_exists($oldName) && !file_exists($newName)) {
        return $oldName;
    }
    return $newName;
}

