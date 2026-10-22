<?php
/**
 * paths.php — Emplacement des données privées (base SQLite, journal des connexions jury).
 *
 * Par défaut : data/ dans le site. Chez un hébergeur qui ignore .htaccess (Yulpa),
 * ces fichiers seraient téléchargeables : renseigner 'data_dir' dans core/config.php
 * pour les placer dans un dossier que le web ne peut pas atteindre, par exemple :
 *   'data_dir' => __DIR__ . '/../../../prive_cfbr',
 */

function data_dir_config(): ?string
{
    static $loaded = false, $dir = null;
    if (!$loaded) {
        $loaded = true;
        $file = __DIR__ . '/config.php';
        $config = is_file($file) ? (require $file) : [];
        if (!empty($config['data_dir'])) {
            $dir = rtrim($config['data_dir'], '/\\');
        }
    }
    return $dir;
}

function data_dir(): string
{
    return data_dir_config() ?? dirname(__DIR__) . '/data';
}

function db_file(): string
{
    return data_dir() . '/concours.db';
}

/** Journal CSV des demandes de connexion jury (déplacé avec la base si 'data_dir' est défini). */
function jury_log_file(): string
{
    return data_dir_config() !== null
        ? data_dir() . '/login_requests.csv'
        : dirname(__DIR__) . '/jury/data/login_requests.csv';
}
