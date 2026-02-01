<?php
// process_upload.php
// Augmenter les limites pour l'upload (à configurer aussi dans php.ini)
ini_set('upload_max_filesize', '100M');
ini_set('post_max_size', '100M');
ini_set('memory_limit', '512M');

$dbPath = __DIR__ . '/data/concours.db';

// Chemins
$uploadDirOriginal = __DIR__ . '/photos/originals/';
$uploadDir4k = __DIR__ . '/photos/display_4k/';
$uploadDirThumb = __DIR__ . '/photos/thumbs/';

// Ensure directories exist
if (!is_dir($uploadDirOriginal))
    mkdir($uploadDirOriginal, 0755, true);
if (!is_dir($uploadDir4k))
    mkdir($uploadDir4k, 0755, true);
if (!is_dir($uploadDirThumb))
    mkdir($uploadDirThumb, 0755, true);

// Init DB
try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("Erreur DB: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = htmlspecialchars($_POST['firstname']);
    $lastname = htmlspecialchars($_POST['lastname']);
    // Fallback if needed but we should now always have separate fields
    $fullname = $firstname . ' ' . $lastname;

    $email = htmlspecialchars($_POST['email']);
    $address = htmlspecialchars($_POST['address'] ?? ''); // Added capture
    $signature = isset($_POST['signature']) ? 1 : 0;
    $instagram = isset($_POST['instagram']) ? 1 : 0;

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
        die("Erreur : Le nom de l'entreprise est obligatoire pour une candidature Corporate.");
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
    $stmt = $pdo->prepare("INSERT INTO participants (firstname, lastname, email, address, ip, signature_log, instagram_auth, validation_token, is_verified, company, agree_annex_a, agree_annex_b, candidacy_type, identifiable_persons) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?, ?, ?, ?)");
    $stmt->execute([$firstname, $lastname, $email, $address, $ip, $signatureLog, $instagram, $token, $company, $agreeAnnexA, $agreeAnnexB, $candidacyType, $identifiablePersons]);
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

            // Validation Image
            $imageInfo = getimagesize($tmpName);
            if ($imageInfo === false)
                continue; // Pas une image

            $width = $imageInfo[0];
            $height = $imageInfo[1];

            // Détection Upscale (Ratio Poids/Pixels)
            $pixelCount = $width * $height;
            $ratio = ($pixelCount > 0) ? $fileSize / $pixelCount : 0;

            // Seuil arbitraire : Si < 0.15 octet/pixel pour une image "Haute Def" (>10MP), c'est louche
            $isUpscaleSuspect = ($ratio < 0.15 && $pixelCount > 10000000) ? 1 : 0;

            // Génération nom anonyme
            $ext = pathinfo($originalName, PATHINFO_EXTENSION);
            $randomHash = bin2hex(random_bytes(8));
            $newBaseName = "photo_" . $participantId . "_" . $randomHash;

            $fileOriginal = $newBaseName . "." . $ext;
            $file4k = $newBaseName . "_4k.jpg";
            $fileThumb = $newBaseName . "_thumb.jpg";

            // Move Original
            if (move_uploaded_file($tmpName, $uploadDirOriginal . $fileOriginal)) {

                // Traitement 4K + Thumb avec GD
                processImage($uploadDirOriginal . $fileOriginal, $uploadDir4k . $file4k, 3840, 85);
                processImage($uploadDirOriginal . $fileOriginal, $uploadDirThumb . $fileThumb, 400, 70);

                // Insert Photo DB
                $stmtPhoto = $pdo->prepare("INSERT INTO photos (participant_id, filename_original, filename_4k, filename_thumb, width, height, is_upscale_suspect, title, description, category, location) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmtPhoto->execute([$participantId, $fileOriginal, $file4k, $fileThumb, $width, $height, $isUpscaleSuspect, $title, $description, $category, $location]);

                $uploadedCount++;
            }
        }
    }

    // Envoi de l'email de validation
    $link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/validate.php?token=$token";

    $subject = "Confirmez votre participation - Concours Photo CFBR";
    $message = "Bonjour $firstname $lastname,\n\n";
    $message .= "Merci pour votre dépôt de candidature ($uploadedCount photos) pour le concours \"Barrages : Entre nature et architecture\".\n\n";
    $message .= "Pour valider définitivement votre participation et confirmer votre signature électronique du règlement et des cessions de droits, veuillez cliquer sur le lien ci-dessous :\n\n";
    $message .= "$link\n\n";
    $message .= "Si vous n'êtes pas à l'origine de cette demande, merci d'ignorer cet email.\n\n";
    $message .= "Cordialement,\nLe Comité d'Organisation";

    $headers = "From: no-reply@barrages-cfbr.eu\r\n";
    $headers .= "Content-Type: text/plain; charset=utf-8";

    // Envoi réel (ne fonctionne que si SMTP configuré)
    @mail($email, $subject, $message, $headers);

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
                <a href="index.php" class="flex items-center space-x-2">
                    <img src="https://www.barrages-cfbr.eu/IMG/logo/siteon0.png?1572394244" alt="Logo"
                        class="h-10 bg-white rounded p-1">
                    <span class="font-bold text-lg hidden md:block">Concours CFBR</span>
                </a>
                <a href="index.php" class="hover:text-[#FF9900]">Retour Accueil</a>
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
                    <p class="text-gray-700 mb-2">
                        Un email vient de vous être envoyé. Il contient un <strong>lien de validation</strong> unique.
                    </p>
                    <p class="text-gray-700">
                        <i class="fas fa-check text-green-500 mr-1"></i> Ce clic vaut pour <strong>signature
                            électronique</strong> et confirme la conformité de votre dossier avec le règlement (droits
                        d'auteur, droit à l'image, etc.).
                    </p>
                </div>

                <div class="space-y-4">
                    <p class="text-sm text-gray-500 italic">Vous n'avez pas reçu l'email ? Vérifiez vos spams.</p>

                    <a href="index.php"
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

    $src = imagecreatefromstring(file_get_contents($source));
    $dst = imagecreatetruecolor($newWidth, $newHeight);

    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    imagejpeg($dst, $dest, $quality);

    imagedestroy($src);
    imagedestroy($dst);
}
?>