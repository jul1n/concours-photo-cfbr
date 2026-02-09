<?php
/**
 * maintenance/index.php - Unified Maintenance Dashboard
 * Centralizes DB Init, Jury Management, and System Diagnostics.
 */

// 1. Security check
session_start();
$requiredToken = "cfbr_repair_2026";

// Handle login via GET once, then use session
if (isset($_GET['token']) && $_GET['token'] === $requiredToken) {
    $_SESSION['maintenance_authed'] = true;
}

$isAuthorized = isset($_SESSION['maintenance_authed']) && $_SESSION['maintenance_authed'] === true;

if (!$isAuthorized) {
    header('HTTP/1.1 403 Forbidden');
    die("<h1>Accès refusé</h1><p>Veuillez utiliser le lien de maintenance officiel ou contacter l'administrateur.</p>");
}

// 2. CSRF Protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function checkCSRF()
{
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Erreur de sécurité : Jeton CSRF invalide.");
    }
}

// 2. Load DB Connection (Robust way for maintenance)
$dbPath = __DIR__ . '/../data/concours.db';
$dbStatus = "Non connecté";
$pdo = null;

try {
    // We replicate connection for maintenance to avoid global 'die' on failure
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbStatus = "Connecté";
} catch (Exception $e) {
    $dbStatus = "Erreur (Normal si base non initialisée) : " . $e->getMessage();
}

// 3. Define Jury Members (Static data for injection)
$jury_members = [
    ['email' => 'claudio.carvajal@inrae.fr', 'name' => 'Claudio Carvajal'],
    ['email' => 'frederic.laugier@edf.fr', 'name' => 'Frédéric Laugier'],
    ['email' => 'fabienne.mercier@smavd.org', 'name' => 'Fabienne Mercier'],
    ['email' => 'J.MEYNET@cnr.tm.fr', 'name' => 'Jérémy MEYNET'],
    ['email' => 'nathalie.rosin-corre@tractebel.engie.com', 'name' => 'Nathalie Rosin-Corre'],
    ['email' => 'stephanie.diss@arteliagroup.com', 'name' => 'Stéphanie DISS'],
    ['email' => 'desage@isl.fr', 'name' => 'Antoine DESAGE'],
    ['email' => 'florent.bacchus@developpement-durable.gouv.fr', 'name' => 'Florent Bacchus'],
    ['email' => 'julien.houssin@cfe-energies.com', 'name' => 'Julien HOUSSIN'],
    ['email' => 'jean-jacques.fry@wanadoo.fr', 'name' => 'Jean-Jacques Fry'],
    ['email' => 'thierry.theodore@sunr.com', 'name' => 'Thierry THEODORE'],
    ['email' => 'fabrice.emeriault@3sr-grenoble.fr', 'name' => 'Fabrice Emeriault'],
    ['email' => 'denis.aelbrecht@edf.fr', 'name' => 'Denis AELBRECHT'],
    ['email' => 'remy.tourment@inrae.fr', 'name' => 'Rémy TOURMENT'],
    ['email' => 'pierre.agresti@arteliagroup.com', 'name' => 'Pierre AGRESTI']
];

// 4. Action Handling
$messages = [];

if (isset($_POST['action'])) {
    if (!$pdo) {
        $messages[] = ['type' => 'danger', 'text' => "Impossible de réaliser l'action : pas de connexion à la base de données ($dbStatus)."];
    } else {
        checkCSRF();
        $action = $_POST['action'];

        // DB Initialization / Repair
        if ($action === 'init_db') {
            try {
                $tables = [
                    "participants" => "CREATE TABLE IF NOT EXISTS participants (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        name TEXT,
                        firstname TEXT,
                        lastname TEXT,
                        email TEXT,
                        address TEXT,
                        ip TEXT,
                        candidacy_type TEXT DEFAULT 'individual',
                        company TEXT,
                        signature_log TEXT,
                        instagram TEXT,
                        linkedin TEXT,
                        agree_annex_a INTEGER DEFAULT 0,
                        agree_annex_b INTEGER DEFAULT 0,
                        validation_token TEXT,
                        is_verified INTEGER DEFAULT 0,
                        validation_status TEXT DEFAULT 'pending',
                        jury_vote_1_by INTEGER,
                        jury_vote_2_by INTEGER,
                        identifiable_persons TEXT DEFAULT '',
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                    )",
                    "photos" => "CREATE TABLE IF NOT EXISTS photos (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        participant_id INTEGER,
                        filename_original TEXT,
                        filename_4k TEXT,
                        filename_thumb TEXT,
                        width INTEGER,
                        height INTEGER,
                        title TEXT,
                        description TEXT,
                        category TEXT,
                        location TEXT,
                        is_upscale_suspect INTEGER DEFAULT 0,
                        is_low_res INTEGER DEFAULT 0,
                        is_promo INTEGER DEFAULT 0,
                        status TEXT DEFAULT 'pending',
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (participant_id) REFERENCES participants(id)
                    )",
                    "votes_tour1" => "CREATE TABLE IF NOT EXISTS votes_tour1 (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        photo_id INTEGER,
                        jury_ip TEXT,
                        vote_value TEXT CHECK(vote_value IN ('oui', 'non')),
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (photo_id) REFERENCES photos(id)
                    )",
                    "votes_tour2" => "CREATE TABLE IF NOT EXISTS votes_tour2 (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        photo_id INTEGER,
                        jury_ip TEXT,
                        rank INTEGER,
                        points INTEGER,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (photo_id) REFERENCES photos(id)
                    )",
                    "jury_members" => "CREATE TABLE IF NOT EXISTS jury_members (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        name TEXT,
                        email TEXT
                    )",
                    "jury_tokens" => "CREATE TABLE IF NOT EXISTS jury_tokens (
                        id INTEGER PRIMARY KEY AUTOINCREMENT, 
                        jury_id INTEGER, 
                        token TEXT, 
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP, 
                        used_at DATETIME, 
                        FOREIGN KEY (jury_id) REFERENCES jury_members(id)
                    )",
                    "settings" => "CREATE TABLE IF NOT EXISTS settings (
                        key TEXT PRIMARY KEY,
                        value TEXT
                    )",
                    "analytics" => "CREATE TABLE IF NOT EXISTS analytics (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        page_url TEXT,
                        visitor_hash TEXT,
                        user_agent TEXT,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                    )"
                ];

                foreach ($tables as $sql) {
                    $pdo->exec($sql);
                }

                // Column updates for social media and other additions
                $updates = [
                    ["participants", "identifiable_persons", "ALTER TABLE participants ADD COLUMN identifiable_persons TEXT DEFAULT ''"],
                    ["participants", "validation_status", "ALTER TABLE participants ADD COLUMN validation_status TEXT DEFAULT 'pending'"],
                    ["participants", "jury_vote_1_by", "ALTER TABLE participants ADD COLUMN jury_vote_1_by INTEGER"],
                    ["participants", "jury_vote_2_by", "ALTER TABLE participants ADD COLUMN jury_vote_2_by INTEGER"],
                    ['participants', 'linkedin', "ALTER TABLE participants ADD COLUMN linkedin TEXT"],
                    ['photos', 'is_low_res', "ALTER TABLE photos ADD COLUMN is_low_res INTEGER DEFAULT 0"],
                    ['photos', 'is_promo', "ALTER TABLE photos ADD COLUMN is_promo INTEGER DEFAULT 0"]
                ];

                foreach ($updates as $upd) {
                    try {
                        $q = $pdo->query("PRAGMA table_info({$upd[0]})");
                        $cols = $q->fetchAll(PDO::FETCH_ASSOC);
                        $exists = false;
                        foreach ($cols as $c) {
                            if ($c['name'] === $upd[1]) {
                                $exists = true;
                                break;
                            }
                        }
                        if (!$exists) {
                            $pdo->exec($upd[2]);
                        }
                    } catch (Exception $e) {
                    }
                }

                $messages[] = ['type' => 'success', 'text' => 'Base de données initialisée/réparée avec succès.'];
            } catch (Exception $e) {
                $messages[] = ['type' => 'danger', 'text' => 'Erreur Initialisation: ' . $e->getMessage()];
            }
        }

        // Jury Seeding (Robust)
        if ($action === 'seed_jury') {
            try {
                // Check if table exists first
                $q = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='jury_members'");
                if (!$q->fetch()) {
                    throw new Exception("La table 'jury_members' n'existe pas. Veuillez cliquer sur 'Initialiser / Réparer' d'abord.");
                }

                // (Static list defined at the top)

                foreach ($jury_members as $member) {
                    $stmt = $pdo->prepare("SELECT id FROM jury_members WHERE email = ?");
                    $stmt->execute([$member['email']]);
                    if (!$stmt->fetch()) {
                        $ins = $pdo->prepare("INSERT INTO jury_members (email, name) VALUES (?, ?)");
                        $ins->execute([$member['email'], $member['name']]);
                    }
                }
                $messages[] = ['type' => 'success', 'text' => 'Membres du jury officiels injectés avec succès.'];
            } catch (Exception $e) {
                $messages[] = ['type' => 'danger', 'text' => 'Erreur Jury: ' . $e->getMessage()];
            }
        }

        // Add Manual Jury Member
        if ($action === 'add_jury_manual') {
            $name = trim($_POST['jury_name'] ?? '');
            $email = trim($_POST['jury_email'] ?? '');
            if (!$name || !$email) {
                $messages[] = ['type' => 'danger', 'text' => 'Veuillez remplir le nom et l\'email.'];
            } else {
                try {
                    $stmt = $pdo->prepare("SELECT id FROM jury_members WHERE email = ?");
                    $stmt->execute([$email]);
                    if ($stmt->fetch()) {
                        $messages[] = ['type' => 'orange', 'text' => "Cet email ($email) est déjà présent dans le jury."];
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO jury_members (name, email) VALUES (?, ?)");
                        $stmt->execute([$name, $email]);
                        $messages[] = ['type' => 'success', 'text' => "Membre ajouté : $name ($email)"];
                    }
                } catch (Exception $e) {
                    $messages[] = ['type' => 'danger', 'text' => 'Erreur: ' . $e->getMessage()];
                }
            }
        }

        // Delete Jury Member
        if ($action === 'delete_jury') {
            $jury_id = (int) ($_POST['jury_id'] ?? 0);
            if ($jury_id > 0) {
                try {
                    // First check for active tokens
                    $pdo->prepare("DELETE FROM jury_tokens WHERE jury_id = ?")->execute([$jury_id]);
                    $stmt = $pdo->prepare("DELETE FROM jury_members WHERE id = ?");
                    $stmt->execute([$jury_id]);
                    $messages[] = ['type' => 'success', 'text' => "Membre du jury supprimé."];
                } catch (Exception $e) {
                    $messages[] = ['type' => 'danger', 'text' => 'Erreur suppression: ' . $e->getMessage()];
                }
            }
        }

        // Prize Image Optimization
        if ($action === 'optimize_prizes') {
            $sourceDir = __DIR__ . '/../data/interne/';
            $localOnlyDir = __DIR__ . '/../_LOCAL_ONLY/original_photos/prizes/';

            if (!is_dir($localOnlyDir))
                mkdir($localOnlyDir, 0777, true);

            $files_to_process = [
                '01.PHOTO_MEMBRE_001.jpg' => 'prix-01-photo.jpg',
                '1er prix bon format.jpg' => 'prix-01-overlay.jpg',
                '02.PHOTO_MEMBRE_101.jpg' => 'prix-02-photo.jpg',
                '2ème prix bon format.jpg' => 'prix-02-overlay.jpg',
                '03.PHOTO_MEMBRE_087.jpg' => 'prix-03-photo.jpg',
                '3ème prix bon format.jpg' => 'prix-03-overlay.jpg',
            ];

            $done = 0;
            $skipped = 0;

            foreach ($files_to_process as $oldName => $newName) {
                $oldPath = $sourceDir . $oldName;
                if (file_exists($oldPath)) {
                    // 1. Move original to LOCAL_ONLY
                    rename($oldPath, $localOnlyDir . $oldName);

                    // 2. Generate optimized version (simple copy if GD not used here, but we SHOULD compress)
                    // For the sake of this tool and since we have no easy way to call Python from PHP here without exec
                    // We will use a message asking user to run the Python script OR try GD if available
                    $done++;
                } else {
                    $skipped++;
                }
            }

            if ($done > 0) {
                $messages[] = ['type' => 'success', 'text' => "$done photos déplacées vers _LOCAL_ONLY. Veuillez maintenant relancer le script de compression Python pour générer les versions légères avec les nouveaux noms."];
            } else {
                $messages[] = ['type' => 'orange', 'text' => "Aucune photo trouvée dans data/interne/. Peut-être ont-elles déjà été déplacées ?"];
            }
        }

        // File Integrity Verification
        if ($action === 'verify_integrity') {
            $report = [
                'dirs' => [],
                'files' => [],
                'photos' => []
            ];

            // 1. Critical Directories
            $dirsToCheck = [
                'data' => __DIR__ . '/../data',
                'photos/originals' => __DIR__ . '/../photos/originals',
                'photos/slides_optimized' => __DIR__ . '/../photos/slides_optimized',
                'core' => __DIR__ . '/../core',
                'jury' => __DIR__ . '/../jury',
                'admin' => __DIR__ . '/../admin'
            ];
            foreach ($dirsToCheck as $label => $path) {
                $report['dirs'][$label] = is_dir($path);
            }

            // 2. Critical Files
            $filesToCheck = [
                'index.php' => __DIR__ . '/../index.php',
                'core/db.php' => __DIR__ . '/../core/db.php',
                'data/concours.db' => __DIR__ . '/../data/concours.db'
            ];
            foreach ($filesToCheck as $label => $path) {
                $report['files'][$label] = file_exists($path);
            }

            // 3. Database Photos Asset Check
            try {
                $stmt = $pdo->query("SELECT p.id, p.filename_original, p.title, part.firstname, part.lastname 
                                    FROM photos p 
                                    JOIN participants part ON p.participant_id = part.id");
                $dbPhotos = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($dbPhotos as $photo) {
                    $origPath = __DIR__ . '/../photos/originals/' . $photo['filename_original'];
                    // Note: Checking only original for now as it's the primary asset
                    if (!file_exists($origPath)) {
                        $report['photos'][] = [
                            'id' => $photo['id'],
                            'title' => $photo['title'],
                            'author' => $photo['firstname'] . ' ' . $photo['lastname'],
                            'filename' => $photo['filename_original']
                        ];
                    }
                }
            } catch (Exception $e) {
                $messages[] = ['type' => 'danger', 'text' => "Erreur lors du scan DB : " . $e->getMessage()];
            }

            // 4. Actual Write Test
            $testFile = __DIR__ . '/../photos/originals/write_test_' . time() . '.txt';
            if (@file_put_contents($testFile, "Maintenance Write Test")) {
                $report['write_test'] = ['status' => true, 'path' => $testFile];
                @unlink($testFile);
            } else {
                $report['write_test'] = ['status' => false, 'error' => error_get_last()];
            }

            // Store report in session or pass simple flag
            $integrityReport = $report;
            $messages[] = ['type' => 'success', 'text' => 'Vérification de l\'intégrité terminée. Consultez les détails ci-dessous.'];
        }

        // Dummy Data Seeding (3 virtual entries)
        if ($action === 'seed_dummies') {
            try {
                $firstnames = ['Alex', 'Morgan', 'Taylor', 'Jordan', 'Casey', 'Robin'];
                $lastnames = ['Durand', 'Petit', 'Lefebvre', 'Moreau', 'Fournier', 'Roux'];
                $damImages = [
                    'https://images.unsplash.com/photo-1590732152848-1da50906f0f7?auto=format&fit=crop&q=80&w=1200',
                    'https://images.unsplash.com/photo-1541604193435-22287d32c2c2?auto=format&fit=crop&q=80&w=1200',
                    'https://images.unsplash.com/photo-1581093196277-9f6c2066562e?auto=format&fit=crop&q=80&w=1200',
                    'https://images.unsplash.com/photo-1603703478051-4091bc7d9f7a?auto=format&fit=crop&q=80&w=1200',
                    'https://images.unsplash.com/photo-1506466010722-395aa2bef877?auto=format&fit=crop&q=80&w=1200'
                ];

                for ($i = 0; $i < 3; $i++) {
                    $fname = $firstnames[array_rand($firstnames)];
                    $lname = $lastnames[array_rand($lastnames)];
                    $email = strtolower($fname . "." . $lname . "." . rand(100, 999) . "@test.com");

                    // 1. Insert Participant
                    $stmt = $pdo->prepare("INSERT INTO participants (firstname, lastname, name, email, candidacy_type, is_verified, validation_status) 
                                           VALUES (?, ?, ?, ?, 'individual', 1, 'verified')");
                    $stmt->execute([$fname, $lname, "$fname $lname", $email]);
                    $pid = $pdo->lastInsertId();

                    // 2. Insert 1 Dummy Photo
                    $imgUrl = $damImages[array_rand($damImages)];
                    $stmt = $pdo->prepare("INSERT INTO photos (participant_id, filename_original, title, description, category, status) 
                                           VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $pid,
                        $imgUrl, // Using URL as filename for dummy data (slideshow handles it)
                        "Barrage Virtuel " . ($i + 1),
                        "Ceci est une donnée de test générée automatiquement.",
                        'cat1',
                        'approved'
                    ]);
                }
                $messages[] = ['type' => 'success', 'text' => '3 nouveaux participants virtuels ajoutés avec succès !'];
            } catch (Exception $e) {
                $messages[] = ['type' => 'danger', 'text' => "Erreur Seeding Dummy: " . $e->getMessage()];
            }
        }

        // Purge Dummy Data
        if ($action === 'purge_dummies') {
            try {
                // Delete photos of test participants
                $pdo->exec("DELETE FROM photos WHERE participant_id IN (SELECT id FROM participants WHERE email LIKE '%@test.com')");
                // Delete test participants
                $pdo->exec("DELETE FROM participants WHERE email LIKE '%@test.com'");
                $messages[] = ['type' => 'success', 'text' => 'Données virtuelles (@test.com) supprimées.'];
            } catch (Exception $e) {
                $messages[] = ['type' => 'danger', 'text' => "Erreur Purge: " . $e->getMessage()];
            }
        }
        // Save SMTP Settings
        if ($action === 'save_smtp') {
            $smtp_data = [
                'smtp_host' => $_POST['smtp_host'] ?? '',
                'smtp_port' => $_POST['smtp_port'] ?? '',
                'smtp_user' => $_POST['smtp_user'] ?? '',
                'smtp_pass' => $_POST['smtp_pass'] ?? ''
            ];
            try {
                $stmt = $pdo->prepare("INSERT INTO settings (key, value) VALUES (?, ?) ON CONFLICT(key) DO UPDATE SET value = excluded.value");
                foreach ($smtp_data as $key => $val) {
                    $stmt->execute([$key, $val]);
                }
                $messages[] = ['type' => 'success', 'text' => 'Configuration SMTP sauvegardée.'];
            } catch (Exception $e) {
                $messages[] = ['type' => 'danger', 'text' => "Erreur SMTP: " . $e->getMessage()];
            }
        }

        // Test Email action
        if ($action === 'test_email') {
            $to = "julien.houssin@cfe-energies.com"; // Updated per user request
            $subject = "Test de configuration SMTP - CFBR";
            $body = "Ceci est un test d'envoi d'email depuis le Maintenance Hub.";
            $headers = "From: " . ($settings['smtp_user'] ?? 'no-reply@barrages-cfbr.eu');

            $mailLogs = [
                'Destinataire' => $to,
                'Sujet' => $subject,
                'Headers' => $headers,
                'Fonction mail()' => function_exists('mail') ? 'Installée' : 'Indisponible'
            ];

            if (function_exists('mail')) {
                // We attempt the mail send. In some environments it won't actually send but return true/false
                $result = @mail($to, $subject, $body, $headers);
                $mailLogs['Résultat Envoi'] = $result ? 'Succès (Vérifiez la boîte de réception)' : 'Échec (Erreur serveur mail)';
                $messages[] = ['type' => $result ? 'success' : 'danger', 'text' => $result ? 'Tentative d\'envoi effectuée avec succès.' : 'L\'envoi a échoué.'];
            } else {
                $messages[] = ['type' => 'danger', 'text' => 'La fonction mail() est désactivée sur ce serveur.'];
            }
            $_SESSION['mail_test_logs'] = $mailLogs;
        }

        // Test PDF action
        if ($action === 'test_pdf') {
            @ini_set('memory_limit', '512M'); // Prevents "Memory exhaustion" on heavy asset processing
            $pdfGenPath = __DIR__ . '/../includes/PDFGenerator.php';
            if (file_exists($pdfGenPath)) {
                require_once $pdfGenPath;

                $dummyParticipant = [
                    'id' => 'TEST-2026',
                    'firstname' => 'Théo',
                    'lastname' => 'COURANT',
                    'company' => 'Bureau d\'études BarrageX',
                    'address' => '123 Avenue des Ingénieurs, 75000 Paris',
                    'email' => 'theo.courant@exemple.fr',
                    'created_at' => date('Y-m-d H:i:s'),
                    'agree_annex_a' => 1,
                    'agree_annex_b' => 1,
                    'identifiable_persons' => "M. Jean Dupont (Accord obtenu)\nMme. Claire Vallet (Accord obtenu)",
                    'ip' => '127.0.0.1'
                ];

                $dummyPhotos = [
                    [
                        'category' => 'cat1',
                        'title' => 'PhotoBarrage extraordinaire',
                        'filename_original' => 'barrage_nature_high_res.jpg'
                    ],
                    [
                        'category' => 'cat2',
                        'title' => 'Ingénieur en action',
                        'filename_original' => 'travaux_barrage_2026.png'
                    ]
                ];

                // Generate and output to browser
                PDFGenerator::generateForParticipant($dummyParticipant, $dummyPhotos, 'I');
                exit;
            } else {
                $messages[] = ['type' => 'danger', 'text' => 'Librairie PDFGenerator non trouvée dans /includes/'];
            }
        }
    }
}

// Fetch Jury Members for the list view
$all_jury = [];
try {
    $stmt = $pdo->query("SELECT * FROM jury_members ORDER BY name ASC");
    $all_jury = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
}

// Fetch current settings for UI pre-fill
$settings = [];
try {
    $stmt = $pdo->query("SELECT key, value FROM settings");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $settings[$row['key']] = $row['value'];
    }
} catch (Exception $e) {
}

// Fetch Analytics stats
$stats = [
    'total_views' => 0,
    'unique_visitors' => 0,
    'by_page' => []
];
try {
    // Total Views
    $stats['total_views'] = $pdo->query("SELECT COUNT(*) FROM analytics")->fetchColumn();

    // Unique Visitors
    $stats['unique_visitors'] = $pdo->query("SELECT COUNT(DISTINCT visitor_hash) FROM analytics")->fetchColumn();

    // By Page
    $stmt = $pdo->query("SELECT page_url, COUNT(*) as views, COUNT(DISTINCT visitor_hash) as uniques 
                         FROM analytics 
                         GROUP BY page_url 
                         ORDER BY views DESC");
    $stats['by_page'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
}

// 4. Diagnositcs logic
function checkDir($path)
{
    if (!is_dir($path))
        return ["status" => "Dossier manquant", "color" => "red"];
    if (!is_writable($path))
        return ["status" => "Lecture seule", "color" => "orange"];
    return ["status" => "OK (Écriture)", "color" => "emerald"];
}

$diag = [
    "PHP version" => [
        "status" => PHP_VERSION,
        "color" => version_compare(PHP_VERSION, '7.4', '>=') ? "emerald" : "red"
    ],
    "Extension PDO SQLite" => [
        "status" => extension_loaded('pdo_sqlite') ? "Activée" : "Manquante",
        "color" => extension_loaded('pdo_sqlite') ? "emerald" : "red"
    ],
    "Extension GD (Images)" => [
        "status" => extension_loaded('gd') ? "Activée" : "Manquante",
        "color" => extension_loaded('gd') ? "emerald" : "red"
    ],
    "Extension EXIF (Rotation)" => [
        "status" => extension_loaded('exif') ? "Activée" : "Manquante",
        "color" => extension_loaded('exif') ? "emerald" : "red"
    ],
    "Dossier /data" => checkDir(__DIR__ . '/../data'),
    "Dossier /photos/originals" => array_merge(checkDir(__DIR__ . '/../photos/originals'), ["status" => (is_dir(__DIR__ . '/../photos/originals') ? "OK (Écriture)" : "Facultatif (local-only)"), "color" => (is_dir(__DIR__ . '/../photos/originals') ? "emerald" : "sky")]),
    "Statut Connexion DB" => [
        "status" => ($pdo ? "Connecté" : "Déconnecté"),
        "color" => ($pdo ? "emerald" : "red")
    ],
];

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance - Concours Photo CFBR</title>
    <?php include __DIR__ . '/../includes/pwa_loader.php'; ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Inter:wght@400;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        h1,
        h2 {
            font-family: 'Montserrat', sans-serif;
        }

        /* Help Tooltips */
        .help-tip {
            position: relative;
            cursor: help;
        }

        .help-tip:hover::after {
            content: attr(data-tip);
            position: absolute;
            bottom: 125%;
            left: 50%;
            transform: translateX(-50%);
            background: #1e293b;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 11px;
            width: 200px;
            z-index: 50;
            line-height: 1.4;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }

        /* Modal README */
        #readmeModal:target {
            display: flex;
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen text-slate-800">

    <div class="max-w-4xl mx-auto px-4 py-12">
        <header class="flex items-center justify-between mb-10">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-slate-900 rounded-lg flex items-center justify-center text-white text-2xl">
                    <i class="fas fa-wrench"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 uppercase tracking-tight">Maintenance Hub</h1>
                    <p class="text-sm text-slate-500">Concours Photo CFBR - 2026</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <a href="#readmeModal"
                    class="text-sm font-semibold text-blue-600 hover:text-blue-800 transition flex items-center gap-2">
                    <i class="fas fa-book-open"></i> Voir Aide (README)
                </a>
                <a href="../index.php" class="text-sm font-semibold text-slate-600 hover:text-slate-900 transition">
                    <i class="fas fa-arrow-left mr-1"></i> Retour au site
                </a>
            </div>
        </header>

        <!-- Messages Flash -->
        <?php foreach ($messages as $msg): ?>
            <div
                class="mb-6 p-4 rounded-lg flex items-center gap-3 border-l-4 <?= $msg['type'] === 'success' ? 'bg-emerald-50 border-emerald-500 text-emerald-800' : 'bg-red-50 border-red-500 text-red-800' ?>">
                <i class="fas <?= $msg['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle' ?>"></i>
                <span><?= $msg['text'] ?></span>
            </div>
        <?php endforeach; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8items-start">

            <!-- Colonne GAUCHE : Gestion Email & Stats -->
            <section class="space-y-6">

                <!-- Diagnostic Système -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                        <h2 class="font-bold text-xs uppercase tracking-widest text-slate-500">Diagnostic Système</h2>
                    </div>
                    <div class="p-6">
                        <ul class="space-y-4">
                            <?php foreach ($diag as $title => $data): ?>
                                <li class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-slate-600"><?= $title ?></span>
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-<?= $data['color'] ?>-100 text-<?= $data['color'] ?>-800">
                                        <?= $data['status'] ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <!-- Statistiques de Visite -->
                <?php if ($stats['total_views'] > 0): ?>
                    <div
                        class="bg-gradient-to-br from-[#0A2240] to-slate-900 rounded-xl shadow-xl border border-white/5 p-6 text-white">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-3">
                                <i class="fas fa-chart-line text-[#FF9900] text-xl"></i>
                                <h2 class="font-bold text-lg">Statistiques de Visite</h2>
                            </div>
                            <span class="text-[10px] bg-white/10 px-2 py-1 rounded-full text-white/50">Temps réel</span>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div class="bg-white/5 rounded-lg p-4 border border-white/10">
                                <div class="text-[10px] uppercase text-white/40 font-bold mb-1">Vues Totales</div>
                                <div class="text-3xl font-bold text-[#FF9900]">
                                    <?= number_format($stats['total_views'], 0, ',', ' ') ?>
                                </div>
                            </div>
                            <div class="bg-white/5 rounded-lg p-4 border border-white/10">
                                <div class="text-[10px] uppercase text-white/40 font-bold mb-1">Visiteurs Uniques</div>
                                <div class="text-3xl font-bold text-white">
                                    <?= number_format($stats['unique_visitors'], 0, ',', ' ') ?>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <div class="text-[10px] uppercase text-white/40 font-bold mb-2">Poplarité par Page</div>
                            <?php foreach ($stats['by_page'] as $row):
                                $maxWidth = ($stats['total_views'] > 0) ? ($row['views'] / $stats['total_views']) * 100 : 0;
                                ?>
                                <div class="bg-white/5 rounded-lg p-3 border border-white/5">
                                    <div class="flex justify-between items-center text-xs mb-1">
                                        <span
                                            class="truncate pr-4 text-white/80 font-mono"><?= htmlspecialchars($row['page_url']) ?></span>
                                        <span class="font-bold"><?= $row['views'] ?> <span
                                                class="text-[9px] text-white/40 font-normal">vues</span></span>
                                    </div>
                                    <div class="w-full bg-white/5 rounded-full h-1 overflow-hidden">
                                        <div class="bg-[#FF9900] h-full" style="width: <?= $maxWidth ?>%"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- diagnostics & tests de santé -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-start gap-4">
                            <div
                                class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center shrink-0">
                                <i class="fas fa-stethoscope"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 mb-1">Diagnostics & Tests de Santé</h3>
                                <p class="text-xs text-slate-500 leading-relaxed">Vérifier l'intégrité des fichiers,
                                    l'envoi d'emails et le tunnel d'upload.</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-3">
                        <!-- Intégrité FTP -->
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <input type="hidden" name="action" value="verify_integrity">
                            <button type="submit"
                                class="w-full text-left px-4 py-3 bg-indigo-50 hover:bg-indigo-100 border border-indigo-100 rounded-xl text-xs font-bold flex items-center justify-between group transition-all">
                                <span class="flex items-center gap-3">
                                    <i class="fas fa-file-shield text-indigo-600 text-lg"></i>
                                    <span>Vérifier l'Intégrité (FTP/Structure)</span>
                                </span>
                                <i
                                    class="fas fa-chevron-right text-indigo-300 group-hover:translate-x-1 transition-transform"></i>
                            </button>
                        </form>

                        <div class="grid grid-cols-2 gap-3">
                            <!-- Email Test -->
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <input type="hidden" name="action" value="test_email">
                                <button type="submit"
                                    class="w-full text-left px-4 py-3 bg-amber-50 hover:bg-amber-100 border border-amber-100 rounded-xl text-[11px] font-bold flex items-center justify-between group transition-all">
                                    <span class="flex items-center gap-2">
                                        <i class="fas fa-envelope text-amber-500"></i>
                                        <span>Test Email</span>
                                    </span>
                                </button>
                            </form>

                            <!-- PDF Test -->
                            <form method="POST" target="_blank">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <input type="hidden" name="action" value="test_pdf">
                                <button type="submit"
                                    class="w-full text-left px-4 py-3 bg-red-50 hover:bg-red-100 border border-red-100 rounded-xl text-[11px] font-bold flex items-center justify-between group transition-all">
                                    <span class="flex items-center gap-2">
                                        <i class="fas fa-file-pdf text-red-500"></i>
                                        <span>Test PDF</span>
                                    </span>
                                </button>
                            </form>
                        </div>

                        <!-- Upload Test -->
                        <a href="../upload.php" target="_blank"
                            class="w-full text-left px-4 py-3 bg-blue-50 hover:bg-blue-100 border border-blue-100 rounded-xl text-xs font-bold flex items-center justify-between group transition-all">
                            <span class="flex items-center gap-3">
                                <i class="fas fa-upload text-blue-600 text-lg"></i>
                                <span>Tester le Tunnel d'Upload</span>
                            </span>
                            <i class="fas fa-external-link-alt text-blue-300"></i>
                        </a>
                    </div>

                    <?php if (isset($_SESSION['mail_test_logs'])): ?>
                        <div class="mt-4 p-4 bg-slate-50 rounded-lg border border-slate-200">
                            <h4 class="text-[10px] font-bold uppercase text-slate-400 mb-2">Logs Techniques Mail</h4>
                            <ul class="space-y-1">
                                <?php foreach ($_SESSION['mail_test_logs'] as $k => $v): ?>
                                    <li class="text-[10px] flex justify-between">
                                        <span class="font-semibold text-slate-500"><?= $k ?> :</span>
                                        <span class="text-slate-700 italic"><?= htmlspecialchars($v) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <button onclick="this.parentElement.remove()"
                                class="mt-2 text-[9px] text-blue-500 hover:underline">Effacer les logs</button>
                        </div>
                        <?php unset($_SESSION['mail_test_logs']); ?>
                    <?php endif; ?>
                </div>

                <!-- Configuration SMTP -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-start gap-4 mb-4">
                        <div
                            class="w-10 h-10 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center shrink-0">
                            <i class="fas fa-envelope-open-text"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800 mb-1">Configuration SMTP</h3>
                            <p class="text-xs text-slate-500 leading-relaxed">Paramètres de serveur pour les envois
                                d'emails automatiques.</p>
                        </div>
                    </div>
                    <form method="POST" class="space-y-3">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="action" value="save_smtp">
                        <div class="grid grid-cols-2 gap-2">
                            <input type="text" name="smtp_host" placeholder="Serveur SMTP"
                                value="<?= htmlspecialchars($settings['smtp_host'] ?? '') ?>"
                                class="text-xs border rounded p-2 w-full">
                            <input type="text" name="smtp_port" placeholder="Port (587/465)"
                                value="<?= htmlspecialchars($settings['smtp_port'] ?? '587') ?>"
                                class="text-xs border rounded p-2 w-full">
                        </div>
                        <input type="text" name="smtp_user" placeholder="Utilisateur / Email"
                            value="<?= htmlspecialchars($settings['smtp_user'] ?? '') ?>"
                            class="text-xs border rounded p-2 w-full">
                        <input type="password" name="smtp_pass" placeholder="Mot de passe"
                            value="<?= htmlspecialchars($settings['smtp_pass'] ?? '') ?>"
                            class="text-xs border rounded p-2 w-full">
                        <button type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 rounded-lg transition text-xs">
                            Mettre à jour SMTP
                        </button>
                    </form>
                    <p class="mt-3 text-[10px] text-slate-400 italic">
                        <strong>Usage :</strong> Utilisez les paramètres de votre hébergeur (ex: port 465 pour SSL) pour
                        permettre l'envoi d'emails.
                    </p>
                </div>

            </section>

            <!-- Colonne DROITE : Installation & Setup -->
            <section class="space-y-6">

                <!-- Initialisation DB -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-start gap-4 mb-4">
                        <div
                            class="w-10 h-10 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center shrink-0">
                            <i class="fas fa-database"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800 mb-1">Base de données</h3>
                            <p class="text-xs text-slate-500 leading-relaxed">Prépare les tables et schémas nécessaires.
                                <i class="fas fa-question-circle help-tip ml-1 text-slate-300"
                                    data-tip="Crée les tables SQLite et ajoute les colonnes manquantes sans effacer vos données existantes."></i>
                            </p>
                        </div>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="action" value="init_db">
                        <button type="submit"
                            class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-2.5 rounded-lg transition text-sm">
                            <i class="fas fa-magic mr-2"></i> Initialiser / Réparer
                        </button>
                    </form>
                    <p class="mt-3 text-[10px] text-slate-400 italic">
                        <strong>Usage :</strong> À faire une seule fois après l'installation ou pour corriger une erreur
                        de base de données.
                    </p>
                </div>

                <!-- Seeding Jury -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-start gap-4 mb-4">
                        <div
                            class="w-10 h-10 bg-orange-50 text-orange-600 rounded-full flex items-center justify-center shrink-0">
                            <i class="fas fa-users-cog"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800 mb-1">Équipe du Jury</h3>
                            <p class="text-xs text-slate-500 leading-relaxed">Gérez les membres du jury (Actuellement :
                                <strong><?= count($all_jury) ?></strong> en base).
                            </p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <form method="POST" class="flex-1">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <input type="hidden" name="action" value="seed_jury">
                            <button type="submit"
                                class="w-full bg-white border border-gray-300 hover:bg-gray-50 text-slate-700 font-bold py-2.5 rounded-lg transition text-sm">
                                <i class="fas fa-user-plus mr-2 text-orange-500"></i> Injecter le Jury
                                (<?= is_array($jury_members) ? count($jury_members) : 0 ?>)
                            </button>
                        </form>
                        <button onclick="document.getElementById('juryListModal').classList.remove('hidden')"
                            class="px-4 bg-orange-100 hover:bg-orange-200 text-orange-700 rounded-lg transition">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                    <p class="mt-3 text-[10px] text-slate-400 italic">
                        <strong>Note :</strong> Les identifiants seront envoyés par email automatiquement une fois le
                        SMTP activé.
                    </p>
                </div>

                <!-- Ajout Manuel Jury -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-start gap-4 mb-4">
                        <div
                            class="w-10 h-10 bg-purple-50 text-purple-600 rounded-full flex items-center justify-center shrink-0">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800 mb-1">Ajout Manuel</h3>
                            <p class="text-xs text-slate-500 leading-relaxed">Ajouter un membre du jury
                                individuellement.</p>
                        </div>
                    </div>
                    <form method="POST" class="space-y-3">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="action" value="add_jury_manual">
                        <input type="text" name="jury_name" placeholder="Nom Complet"
                            class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-purple-500 outline-none">
                        <input type="email" name="jury_email" placeholder="Email"
                            class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-purple-500 outline-none">
                        <button type="submit"
                            class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 rounded-lg transition text-sm">
                            Ajouter ce membre
                        </button>
                    </form>
                </div>

                <!-- Générateur de Hash -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-start gap-4 mb-4">
                        <div
                            class="w-10 h-10 bg-slate-50 text-slate-600 rounded-full flex items-center justify-center shrink-0">
                            <i class="fas fa-key"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800 mb-1">Générateur de Hash</h3>
                            <p class="text-xs text-slate-500 leading-relaxed">Générer un hash sécurisé pour vos mots de
                                passe hardcodés.</p>
                        </div>
                    </div>
                    <?php if (isset($_POST['action']) && $_POST['action'] === 'gen_hash' && !empty($_POST['pass_to_hash'])): ?>
                        <div
                            class="mb-3 p-2 bg-indigo-50 border border-indigo-100 rounded text-[10px] font-mono break-all text-indigo-700">
                            <?= password_hash($_POST['pass_to_hash'], PASSWORD_DEFAULT) ?>
                        </div>
                    <?php endif; ?>
                    <form method="POST" class="flex gap-2">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="action" value="gen_hash">
                        <input type="text" name="pass_to_hash" placeholder="Mot de passe à hacher..."
                            class="flex-grow text-xs border rounded p-2 focus:ring-2 focus:ring-slate-400 outline-none">
                        <button type="submit"
                            class="bg-slate-700 hover:bg-slate-800 text-white px-3 py-2 rounded text-xs font-bold transition">
                            Hacher
                        </button>
                    </form>
                    <p class="mt-3 text-[10px] text-slate-400 italic bg-slate-50 p-2 rounded border border-slate-100">
                        <strong>Usage :</strong> Entrez un texte, hachez-le, puis remplacez manuellement le hash dans
                        <code>maintenance/index.php</code> (ligne ~14) pour sécuriser l'accès.
                    </p>
                </div>
            </section>
        </div>

        <!-- Rapport d'Intégrité -->
        <?php if (isset($integrityReport)): ?>
            <section class="mt-8 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-slate-900 px-6 py-4 border-b border-slate-700 flex justify-between items-center text-white">
                    <h2 class="font-bold text-xs uppercase tracking-widest">Rapport d'Intégrité Détaillé</h2>
                    <span class="text-[10px] bg-indigo-500 px-2 py-0.5 rounded-full">Date: <?= date('d/m/Y H:i') ?></span>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <h4 class="text-sm font-bold text-slate-800 mb-4 border-b pb-2">Structure du Projet</h4>
                            <ul class="space-y-2">
                                <?php foreach ($integrityReport['dirs'] as $name => $exists): ?>
                                    <li class="flex justify-between items-center text-sm">
                                        <span class="text-slate-600">Dossier: <code
                                                class="bg-gray-100 px-1 rounded">/<?= $name ?></code></span>
                                        <i
                                            class="fas <?= $exists ? 'fa-check-circle text-emerald-500' : 'fa-times-circle text-red-500' ?>"></i>
                                    </li>
                                <?php endforeach; ?>
                                <?php foreach ($integrityReport['files'] as $name => $exists): ?>
                                    <li class="flex justify-between items-center text-sm">
                                        <span class="text-slate-600">Fichier: <code
                                                class="bg-gray-100 px-1 rounded"><?= $name ?></code></span>
                                        <i
                                            class="fas <?= $exists ? 'fa-check-circle text-emerald-500' : 'fa-times-circle text-red-500' ?>"></i>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-800 mb-4 border-b pb-2">Photos manquantes (Scan DB)</h4>
                            <?php if (empty($integrityReport['photos'])): ?>
                                <div class="p-4 bg-emerald-50 text-emerald-700 rounded-lg text-xs flex items-center gap-3">
                                    <i class="fas fa-check-circle"></i> Toutes les photos en base de données sont présentes sur
                                    le serveur !
                                </div>
                            <?php else: ?>
                                <div class="max-h-60 overflow-y-auto space-y-2">
                                    <?php foreach ($integrityReport['photos'] as $p): ?>
                                        <div class="p-3 bg-red-50 border border-red-100 rounded-lg text-xs">
                                            <div class="font-bold text-red-800"><?= htmlspecialchars($p['title']) ?></div>
                                            <div class="text-slate-500">Par: <?= htmlspecialchars($p['author']) ?></div>
                                            <div class="mt-1 font-mono text-red-600 text-[10px]">Fichier:
                                                <?= htmlspecialchars($p['filename']) ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <p class="mt-2 text-[10px] text-red-500 italic">Total: <?= count($integrityReport['photos']) ?>
                                    photo(s) introuvable(s) dans /photos/originals/</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>

    </div>

    <footer class="mt-12 text-center">
        <p class="text-xs text-slate-400 italic">
            <i class="fas fa-lock mr-1"></i> Accès sécurisé par token de maintenance.
        </p>
    </footer>
    </div>

    <!-- Diagnostic PWA Card -->
    <div class="max-w-4xl mx-auto px-4 mb-12">
        <div class="bg-slate-900 rounded-xl shadow-lg p-6 text-white border border-slate-700">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center border border-white/20">
                        <i class="fas fa-mobile-screen-button text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold">Santé PWA (Progressive Web App)</h2>
                        <p class="text-xs text-slate-400">Vérification de l'installation et des fonctionnalités offline.
                            <i class="fas fa-question-circle help-tip ml-1 text-slate-500"
                                data-tip="Vérifie si le site peut être installé comme une application mobile et s'il est prêt pour le mode hors-ligne."></i>
                        </p>
                    </div>
                </div>
                <button onclick="checkPWAStatus()"
                    class="bg-white text-slate-900 hover:bg-slate-100 px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-2">
                    <i class="fas fa-sync-alt" id="pwaSyncIcon"></i> Diagnostic PWA
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4" id="pwaResults">
                <div class="p-3 bg-white/5 rounded-lg border border-white/10 text-center">
                    <p class="text-[10px] font-bold text-slate-500 uppercase mb-1">Manifest</p>
                    <div class="flex flex-col items-center gap-1">
                        <i
                            class="fas <?= file_exists(__DIR__ . '/../manifest.json') ? 'fa-check-circle text-emerald-400' : 'fa-times-circle text-red-400' ?>"></i>
                        <span
                            class="text-[11px] font-semibold"><?= file_exists(__DIR__ . '/../manifest.json') ? 'Fichier OK' : 'Manquant' ?></span>
                    </div>
                </div>
                <div class="p-3 bg-white/5 rounded-lg border border-white/10 text-center">
                    <p class="text-[10px] font-bold text-slate-500 uppercase mb-1">S. Worker</p>
                    <div class="flex flex-col items-center gap-1">
                        <i
                            class="fas <?= file_exists(__DIR__ . '/../service-worker.js') ? 'fa-check-circle text-emerald-400' : 'fa-times-circle text-red-400' ?>"></i>
                        <span
                            class="text-[11px] font-semibold"><?= file_exists(__DIR__ . '/../service-worker.js') ? 'Fichier OK' : 'Manquant' ?></span>
                    </div>
                </div>
                <div class="p-3 bg-white/5 rounded-lg border border-white/10 text-center">
                    <p class="text-[10px] font-bold text-slate-500 uppercase mb-1">Navigateur</p>
                    <div class="flex flex-col items-center gap-1">
                        <i class="fas fa-question-circle text-slate-500" id="swRegIcon"></i>
                        <span class="text-[11px] font-semibold" id="swRegStatus">Attente...</span>
                    </div>
                </div>
                <div class="p-3 bg-white/5 rounded-lg border border-white/10 text-center">
                    <p class="text-[10px] font-bold text-slate-500 uppercase mb-1">Sécurité</p>
                    <div class="flex flex-col items-center gap-1">
                        <i
                            class="fas <?= (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'fa-shield-halved text-emerald-400' : 'fa-triangle-exclamation text-amber-400' ?>"></i>
                        <span
                            class="text-[11px] font-semibold"><?= (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'HTTPS OK' : 'Non Sécurisé' ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal README -->
    <div id="readmeModal" class="fixed inset-0 z-50 hidden bg-slate-900/80 backdrop-blur-sm p-4 overflow-y-auto">
        <div class="max-w-4xl mx-auto bg-white rounded-2xl shadow-2xl my-8">
            <div class="flex items-center justify-between p-6 border-b sticky top-0 bg-white rounded-t-2xl z-10">
                <h2 class="text-2xl font-bold text-slate-800 flex items-center gap-3">
                    <i class="fas fa-book text-blue-600"></i> Documentation du Projet
                </h2>
                <a href="#" class="text-slate-400 hover:text-slate-600 text-2xl"><i class="fas fa-times"></i></a>
            </div>
            <div class="p-8 prose prose-slate max-w-none">
                <?php
                $readmePath = __DIR__ . '/../README.md';
                if (file_exists($readmePath)) {
                    $content = file_get_contents($readmePath);
                    $content = htmlspecialchars($content);
                    // Simple MD formatting
                    $content = preg_replace('/^# (.*)$/m', '<h1 class="text-2xl font-bold text-blue-900 border-b-2 border-blue-100 pb-2 mb-4 mt-8 font-title uppercase tracking-tight">$1</h1>', $content);
                    $content = preg_replace('/^## (.*)$/m', '<h2 class="text-xl font-bold text-slate-800 mt-10 mb-4 border-l-4 border-blue-500 pl-4">$1</h2>', $content);
                    $content = preg_replace('/^### (.*)$/m', '<h3 class="text-lg font-bold text-slate-700 mt-8 mb-2 flex items-center gap-2"><i class="fas fa-chevron-right text-blue-400 text-xs"></i> $1</h3>', $content);
                    $content = preg_replace('/\*\*(.*?)\*\*/', '<strong class="text-blue-800 font-bold">$1</strong>', $content);
                    $content = preg_replace('/`(.*?)`/', '<code class="bg-slate-100 px-1.5 py-0.5 rounded text-indigo-700 font-mono text-[11px] border border-slate-200">$1</code>', $content);
                    $content = preg_replace('/^- (.*)$/m', '<li class="ml-6 list-none relative before:content-[\'→\'] before:absolute before:-left-5 before:text-blue-400 text-slate-600 my-2">$1</li>', $content);

                    echo '<div class="font-sans text-[13px] bg-white p-12 rounded-2xl border border-slate-100 shadow-inner max-h-[75vh] overflow-y-auto leading-relaxed text-slate-700">';
                    echo nl2br($content);
                    echo '</div>';
                } else {
                    echo '<p class="text-red-500 italic flex items-center gap-2"><i class="fas fa-exclamation-circle"></i> README.md non trouvé.</p>';
                }
                ?>
            </div>
            <div class="p-6 border-t text-right">
                <a href="#"
                    class="bg-slate-800 text-white px-8 py-3 rounded-lg font-bold hover:bg-slate-900 transition shadow-lg text-sm">Fermer</a>
            </div>
        </div>
    </div>

    <!-- Modal Liste Jury -->
    <div id="juryListModal" class="fixed inset-0 z-50 hidden bg-slate-900/80 backdrop-blur-sm p-4 overflow-y-auto">
        <div class="max-w-2xl mx-auto bg-white rounded-2xl shadow-2xl my-8">
            <div class="flex items-center justify-between p-6 border-b sticky top-0 bg-white rounded-t-2xl z-10">
                <h2 class="text-xl font-bold text-slate-800 flex items-center gap-3">
                    <i class="fas fa-users text-orange-500"></i> Membres du Jury Activés
                </h2>
                <button onclick="document.getElementById('juryListModal').classList.add('hidden')"
                    class="text-slate-400 hover:text-slate-600 text-2xl">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6">
                <?php if (empty($all_jury)): ?>
                    <div class="text-center py-10">
                        <i class="fas fa-users-slash text-slate-200 text-5xl mb-4"></i>
                        <p class="text-slate-400 italic">Aucun membre du jury en base de données.</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($all_jury as $member): ?>
                            <div
                                class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100 group">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="w-10 h-10 bg-orange-100 text-orange-600 rounded-full flex items-center justify-center font-bold text-sm">
                                        <?= strtoupper(substr($member['name'] ?? 'J', 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-800 text-sm">
                                            <?= htmlspecialchars($member['name'] ?? 'Inconnu') ?>
                                        </div>
                                        <div class="text-xs text-slate-500">
                                            <?= htmlspecialchars($member['email'] ?? 'pas d\'email') ?>
                                        </div>
                                    </div>
                                </div>
                                <form method="POST" onsubmit="return confirm('Supprimer ce membre du jury ?');">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <input type="hidden" name="action" value="delete_jury">
                                    <input type="hidden" name="jury_id" value="<?= $member['id'] ?>">
                                    <button type="submit"
                                        class="w-8 h-8 flex items-center justify-center text-slate-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="p-6 border-t text-right bg-gray-50 rounded-b-2xl">
                <button onclick="document.getElementById('juryListModal').classList.add('hidden')"
                    class="bg-slate-800 text-white px-6 py-2 rounded-lg font-bold hover:bg-slate-900 transition text-sm">Fermer</button>
            </div>
        </div>
    </div>

    <script>
        function checkPWAStatus() {
            const btn = document.querySelector('button[onclick="checkPWAStatus()"]');
            const icon = document.getElementById('pwaSyncIcon');
            const statusText = document.getElementById('swRegStatus');
            const statusIcon = document.getElementById('swRegIcon');

            if (icon) icon.classList.add('animate-spin');
            if (btn) btn.disabled = true;

            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.getRegistration().then(reg => {
                    setTimeout(() => {
                        if (icon) icon.classList.remove('animate-spin');
                        if (btn) btn.disabled = false;

                        if (reg) {
                            statusText.innerText = "Actif";
                            statusIcon.className = "fas fa-check-circle text-emerald-400";
                        } else {
                            statusText.innerText = "Non enregistré";
                            statusIcon.className = "fas fa-exclamation-triangle text-amber-400";
                        }
                    }, 800);
                });
            } else {
                statusText.innerText = "Non supporté";
                statusIcon.className = "fas fa-times-circle text-red-500";
                if (icon) icon.classList.remove('animate-spin');
                if (btn) btn.disabled = false;
            }
        }

        window.addEventListener('load', checkPWAStatus);
    </script>
</body>

</html>