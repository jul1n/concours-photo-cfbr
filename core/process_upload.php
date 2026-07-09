<?php
// process_upload.php
// Augmenter les limites pour l'upload (à configurer aussi dans php.ini)
ini_set('upload_max_filesize', '100M');
ini_set('post_max_size', '100M');
ini_set('memory_limit', '512M');

require_once __DIR__ . '/auth.php'; // app_config()
require_once __DIR__ . '/db.php';

// Chemins
$uploadDirOriginal = __DIR__ . '/../photos/originals/';
$uploadDir4k = __DIR__ . '/../photos/display_4k/';
$uploadDirThumb = __DIR__ . '/../photos/thumbs/';

/**
 * Crée un dossier s'il n'existe pas et y dépose un .htaccess de protection.
 * $mode : 'deny' = accès direct interdit, 'nophp' = lecture ok mais pas d'exécution PHP.
 */
function ensureProtectedDir(string $dir, string $mode): void
{
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $ht = $dir . '.htaccess';
    if (!file_exists($ht)) {
        if ($mode === 'deny') {
            file_put_contents($ht, "Require all denied\n");
        } else { // nophp
            file_put_contents(
                $ht,
                "php_flag engine off\n"
                . "RemoveHandler .php .phtml .php3 .php4 .php5 .php7 .phps\n"
                . "RemoveType .php .phtml .php3 .php4 .php5 .php7 .phps\n"
                . "<FilesMatch \"\\.(?i:php|phtml|php3|php4|php5|php7|phps|pht)\$\">\n"
                . "    Require all denied\n"
                . "</FilesMatch>\n"
            );
        }
    }
}

// Originaux : jamais servis directement au public → accès interdit.
ensureProtectedDir($uploadDirOriginal, 'deny');
// 4K et thumbs : servis publiquement mais sans exécution de script.
ensureProtectedDir($uploadDir4k, 'nophp');
ensureProtectedDir($uploadDirThumb, 'nophp');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $firstname = htmlspecialchars($_POST['firstname']);
    $lastname = htmlspecialchars($_POST['lastname']);
    // Fallback if needed but we should now always have separate fields
    $fullname = $firstname . ' ' . $lastname;

    $email = htmlspecialchars($_POST['email']);
    $address = htmlspecialchars($_POST['address'] ?? ''); // Added capture
    $signature = isset($_POST['signature']) ? 1 : 0;

    // Social Media Handles
    $instagram = htmlspecialchars($_POST['instagram'] ?? '');
    $linkedin = htmlspecialchars($_POST['linkedin'] ?? '');

    // New Fields
    $category = htmlspecialchars($_POST['category'] ?? '');
    //$company = htmlspecialchars($_POST['company'] ?? ''); // Replaced below
    $candidacyType = htmlspecialchars($_POST['candidacy_type'] ?? 'individual');
    $company = ($candidacyType === 'corporate') ? htmlspecialchars($_POST['company'] ?? '') : '';
    $identifiablePersons = htmlspecialchars($_POST['identifiable_persons'] ?? '');
    $agreeAnnexA = isset($_POST['agree_annex_a']) ? 1 : 0;
    $agreeAnnexB = isset($_POST['agree_annex_b']) ? 1 : 0;

    $token = bin2hex(random_bytes(16));
    $ip = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $timestamp = date('Y-m-d H:i:s');

    // Log signature
    $signatureLog = "SIGNED at $timestamp | IP: $ip | UA: $userAgent";

    if (!$signature) {
        die("Erreur : La signature est obligatoire.");
    }
    if (!$agreeAnnexA || !$agreeAnnexB) {
        die("Erreur : Vous devez accepter les annexes A et B.");
    }
    if (empty($category)) {
        die("Erreur : Vous devez choisir une catégorie.");
    }
    if (empty($address)) {
        die("Erreur : L'adresse postale est obligatoire pour la cession de droits.");
    }
    if ($candidacyType === 'corporate' && empty($company)) {
        die("Erreur : La raison sociale est obligatoire pour une candidature d'entreprise / association.");
    }

    // Check Photos Count
    $files = $_FILES['photos'];
    $countFiles = 0;
    if (isset($files['name']) && is_array($files['name'])) {
        // Filter out empty uploads if any
        foreach ($files['name'] as $name) {
            if (!empty($name))
                $countFiles++;
        }
    }

    if ($countFiles < 1) {
        die("Erreur : Vous devez soumettre au moins une photo.");
    }

    // Insert Participant
    // Note: Ensuring columns exist is handled by init_db.php or update scripts
    $stmt = $pdo->prepare("INSERT INTO participants (firstname, lastname, email, address, ip, signature_log, instagram, linkedin, validation_token, is_verified, company, agree_annex_a, agree_annex_b, candidacy_type, identifiable_persons) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?, ?, ?, ?)");
    $stmt->execute([$firstname, $lastname, $email, $address, $ip, $signatureLog, $instagram, $linkedin, $token, $company, $agreeAnnexA, $agreeAnnexB, $candidacyType, $identifiablePersons]);
    $participantId = $pdo->lastInsertId();

    $files = $_FILES['photos'];
    $titles = $_POST['titles'] ?? [];
    $locations = $_POST['locations'] ?? [];
    $descriptions = $_POST['descriptions'] ?? [];

    $uploadedCount = 0;

    // Attention : $_FILES['photos']['name'] peut être un tableau si multiple
    $countFiles = count($files['name']);

    for ($i = 0; $i < $countFiles; $i++) {
        if ($files['error'][$i] === UPLOAD_ERR_OK) {
            $tmpName = $files['tmp_name'][$i];
            $originalName = $files['name'][$i];
            $fileSize = $files['size'][$i];
            $title = htmlspecialchars($titles[$i] ?? 'Sans titre');
            $location = htmlspecialchars($locations[$i] ?? '');
            $description = htmlspecialchars($descriptions[$i] ?? '');

            // Validation Taille (Backend)
            if ($fileSize > 25 * 1024 * 1024) {
                die("Erreur : Le fichier $originalName dépasse la limite de 25 Mo.");
            }

            // Validation Image + type réel (ne pas se fier à l'extension fournie)
            $imageInfo = getimagesize($tmpName);
            if ($imageInfo === false)
                continue; // Pas une image

            // Whitelist stricte des types d'image autorisés → extension canonique.
            $allowedTypes = [
                IMAGETYPE_JPEG => 'jpg',
                IMAGETYPE_PNG  => 'png',
                IMAGETYPE_GIF  => 'gif',
                IMAGETYPE_WEBP => 'webp',
            ];
            $detectedType = $imageInfo[2] ?? null;
            if (!isset($allowedTypes[$detectedType])) {
                continue; // Type d'image non autorisé (ou fichier polyglotte)
            }
            $safeExt = $allowedTypes[$detectedType];

            $width = $imageInfo[0];
            $height = $imageInfo[1];
            $longestSide = max($width, $height);

            // Détection Upscale (Ratio Poids/Pixels)
            $pixelCount = $width * $height;
            $ratio = ($pixelCount > 0) ? $fileSize / $pixelCount : 0;

            // Seuil arbitraire : Si < 0.15 octet/pixel pour une image "Haute Def" (>10MP), c'est louche
            $isUpscaleSuspect = ($ratio < 0.15 && $pixelCount > 10000000) ? 1 : 0;

            // Détection Basse Résolution (< 3000px)
            $isLowRes = ($longestSide < 3000) ? 1 : 0;

            // Génération nom anonyme (extension canonique validée, jamais celle fournie)
            $randomHash = bin2hex(random_bytes(8));
            $newBaseName = "photo_" . $randomHash;

            $fileOriginal = $newBaseName . "." . $safeExt;
            $file4k = $newBaseName . "_4k.jpg";
            $fileThumb = $newBaseName . "_thumb.jpg";

            // Move Original
            if (move_uploaded_file($tmpName, $uploadDirOriginal . $fileOriginal)) {

                // Traitement 4K + Thumb avec GD
                processImage($uploadDirOriginal . $fileOriginal, $uploadDir4k . $file4k, 3840, 85);
                processImage($uploadDirOriginal . $fileOriginal, $uploadDirThumb . $fileThumb, 400, 70);

                // DB Store
                $fileHash = md5_file($uploadDirOriginal . $fileOriginal);
                $stmt = $pdo->prepare("INSERT INTO photos (participant_id, filename_original, filename_4k, filename_thumb, width, height, title, location, description, category, is_upscale_suspect, is_low_res, file_hash) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$participantId, $fileOriginal, $file4k, $fileThumb, $width, $height, $title, $location, $description, $category, $isUpscaleSuspect, $isLowRes, $fileHash]);

                $uploadedCount++;
            }
        }
    }

    // Envoi de l'email de validation
    $link = rtrim(app_config()['base_url'], '/') . "/core/validate_email.php?token=" . urlencode($token);

    $subject = "Confirmez votre participation - Concours Photo CFBR";
    $message = "Bonjour $firstname $lastname,\n\n";
    $message .= "Merci pour votre dépôt de candidature ($uploadedCount photos) pour le concours \"Barrages : Entre nature et architecture\".\n\n";
    $message .= "Pour valider définitivement votre participation et confirmer votre signature électronique du règlement et des cessions de droits, veuillez cliquer sur le lien ci-dessous :\n\n";
    $message .= "$link\n\n";
    $message .= "Si vous n'êtes pas à l'origine de cette demande, merci d'ignorer cet email.\n\n";
    $message .= "Cordialement,\nle comité d'organisation du concours photo des 100 ans du Cfbr";

    // Récupération de l'expéditeur configuré (SMTP user)
    $mailFrom = app_config()['mail_from'];
    try {
        $stmt = $pdo->query("SELECT value FROM settings WHERE key = 'smtp_user'");
        $configuredFrom = $stmt->fetchColumn();
        if ($configuredFrom) {
            $mailFrom = $configuredFrom;
        }
    } catch (Exception $e) {
        // Fallback to default
    }

    $headers = "From: $mailFrom\r\n";
    $headers .= "Content-Type: text/plain; charset=utf-8";

    // Envoi réel (ne fonctionne que si SMTP configuré ou mail() actif)
    $mailSent = @mail($email, $subject, $message, $headers);

    if (!$mailSent) {
        // Log simple en cas d'échec pour le debug
        error_log("[" . date('Y-m-d H:i:s') . "] Mail failed to $email from $mailFrom. Result: false");
    }

    ?>
    <!DOCTYPE html>
    <html lang="fr">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Validation Requise - Concours Photo CFBR</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Open+Sans:wght@400;600&display=swap"
            rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <?php include __DIR__ . '/../includes/pwa_loader.php'; ?>
        <style>
            body {
                font-family: 'Open Sans', sans-serif;
                background-color: #F8F8F8;
            }

            h1,
            h2,
            h3 {
                font-family: 'Montserrat', sans-serif;
            }
        </style>
    </head>

    <body class="bg-[#F8F8F8] text-[#0A2240]">

        <header class="bg-[#0A2240] text-white p-4 shadow-md">
            <div class="container mx-auto flex justify-between items-center">
                <a href="../index.php" class="flex items-center space-x-2">
                    <img src="../assets/logo_cfbr_100_ans.png" alt="Logo" class="h-10 bg-white rounded p-1">
                    <span class="font-bold text-lg hidden md:block">Concours CFBR</span>
                </a>
                <a href="../index.php" class="hover:text-[#FF9900]">Retour Accueil</a>
            </div>
        </header>

        <main class="container mx-auto px-4 py-12 max-w-2xl">
            <div class="bg-white p-8 rounded-lg shadow-xl text-center">
                <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-envelope-open-text text-4xl text-[#0A2240]"></i>
                </div>

                <h1 class="text-3xl font-bold text-[#0A2240] mb-4">Candidature en attente de validation</h1>

                <p class="text-xl text-gray-700 mb-8">
                    Merci <strong><?= $firstname ?></strong> ! Vos <strong><?= $uploadedCount ?> photo(s)</strong> ont bien
                    été téléchargées.
                </p>

                <div class="bg-orange-50 border-l-4 border-[#FF9900] p-6 text-left mb-8 rounded-r-lg">
                    <h3 class="text-lg font-bold text-[#0A2240] mb-2"><i class="fas fa-info-circle mr-2"></i>Dernière étape
                        requise</h3>
                    <p class="text-gray-700 mb-6">
                        Un email vient de vous être envoyé. Il contient un <strong>lien de validation</strong> unique pour
                        confirmer votre signature.
                    </p>
                    <div class="pt-2">
                        <a href="../dossier.php?token=<?= $token ?>"
                            class="inline-block text-[#0A2240] font-bold underline hover:text-[#FF9900]">
                            <i class="fas fa-eye mr-1"></i> Prévisualiser mon dossier déposé
                        </a>
                    </div>
                    <p class="text-gray-700">
                        <i class="fas fa-check text-green-500 mr-1"></i> Ce clic vaut pour <strong>signature
                            électronique</strong> et confirme la conformité de votre dossier avec le règlement (droits
                        d'auteur, droit à l'image, etc.).
                    </p>
                </div>

                <div class="space-y-4">
                    <p class="text-sm text-gray-500 italic">Vous n'avez pas reçu l'email ? Vérifiez vos spams.</p>

                    <a href="../index.php"
                        class="inline-block text-[#0A2240] font-bold hover:text-[#FF9900] transition underline">
                        Retourner à l'accueil
                    </a>
                </div>

                <!-- Debug section removed -->

            </div>
        </main>

        <footer class="bg-[#0A2240] text-white py-8 mt-12">
            <div class="container mx-auto px-6 text-center">
                <p>&copy; 2026 Comité Français des Barrages et Réservoirs. Tous droits réservés.</p>
            </div>
        </footer>
    </body>

    </html>
    <?php
}

function processImage($source, $dest, $maxSize, $quality)
{
    list($width, $height, $type) = getimagesize($source);

    $ratio = $width / $height;
    if ($width > $maxSize || $height > $maxSize) {
        if ($ratio > 1) {
            $newWidth = $maxSize;
            $newHeight = $maxSize / $ratio;
        } else {
            $newHeight = $maxSize;
            $newWidth = $maxSize * $ratio;
        }
    } else {
        $newWidth = $width;
        $newHeight = $height;
    }

    $newWidth = (int)round($newWidth);
    $newHeight = (int)round($newHeight);

    $src = imagecreatefromstring(file_get_contents($source));
    $dst = imagecreatetruecolor($newWidth, $newHeight);

    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    imagejpeg($dst, $dest, $quality);

    imagedestroy($src);
    imagedestroy($dst);
}
?>