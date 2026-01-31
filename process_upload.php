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
    $signature = isset($_POST['signature']) ? 1 : 0;
    $instagram = isset($_POST['instagram']) ? 1 : 0;
    $token = bin2hex(random_bytes(16));
    $ip = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $timestamp = date('Y-m-d H:i:s');

    // Log signature
    $signatureLog = "SIGNED at $timestamp | IP: $ip | UA: $userAgent";

    if (!$signature) {
        die("Erreur : La signature est obligatoire.");
    }

    // Insert Participant
    $stmt = $pdo->prepare("INSERT INTO participants (firstname, lastname, email, ip, signature_log, instagram_auth, validation_token, is_verified) VALUES (?, ?, ?, ?, ?, ?, ?, 0)");
    $stmt->execute([$firstname, $lastname, $email, $ip, $signatureLog, $instagram, $token]);
    $participantId = $pdo->lastInsertId();

    $files = $_FILES['photos'];
    $titles = $_POST['titles'] ?? [];
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
                $stmtPhoto = $pdo->prepare("INSERT INTO photos (participant_id, filename_original, filename_4k, filename_thumb, width, height, is_upscale_suspect, title, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmtPhoto->execute([$participantId, $fileOriginal, $file4k, $fileThumb, $width, $height, $isUpscaleSuspect, $title, $description]);

                $uploadedCount++;
            }
        }
    }

    echo "<h1>Candidature en attente de validation !</h1>";
    echo "<p>Merci $firstname $lastname. $uploadedCount photo(s) ont été enregistrées.</p>";
    echo "<div style='background:#e0f2fe; padding:20px; margin:20px 0; border-left:5px solid #0284c7;'>";
    echo "<h3>✉️ Vérifiez vos emails</h3>";
    echo "<p>Un email vous a été envoyé pour valider votre signature électronique.</p>";
    echo "<p><strong>Pour les besoins du test (simulé) :</strong></p>";
    echo "<p>Votre token est : <strong>$token</strong></p>";
    echo "<p><a href='validate.php?token=$token' style='background:#FF9900; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Simuler le clic sur le lien de validation</a></p>";
    echo "</div>";
    echo '<a href="index.php">Retour à l\'accueil</a>';
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