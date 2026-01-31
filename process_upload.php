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
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $signature = isset($_POST['signature']) ? 1 : 0;
    $ip = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $timestamp = date('Y-m-d H:i:s');

    // Log signature
    $signatureLog = "SIGNED at $timestamp | IP: $ip | UA: $userAgent";

    if (!$signature) {
        die("Erreur : La signature est obligatoire.");
    }

    // Insert Participant
    $stmt = $pdo->prepare("INSERT INTO participants (name, email, ip, signature_log) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $ip, $signatureLog]);
    $participantId = $pdo->lastInsertId();

    $files = $_FILES['photos'];
    $uploadedCount = 0;

    for ($i = 0; $i < count($files['name']); $i++) {
        if ($files['error'][$i] === UPLOAD_ERR_OK) {
            $tmpName = $files['tmp_name'][$i];
            $originalName = $files['name'][$i];
            $fileSize = $files['size'][$i];

            // Validation Image
            $imageInfo = getimagesize($tmpName);
            if ($imageInfo === false)
                continue; // Pas une image

            $width = $imageInfo[0];
            $height = $imageInfo[1];
            $mime = $imageInfo['mime'];

            // Détection Upscale (Ratio Poids/Pixels)
            // Un JPEG de haute qualité a généralement > 0.3-0.5 octets par pixel (très approx, dépend du contenu)
            // Si le ratio est très très bas pour une grande résolution, c'est suspect.
            // Mais plus fiable : regarder la "densité".
            // Ici règle simple demandé : Ratio Poids / Nombre de pixels.
            $pixelCount = $width * $height;
            $ratio = $fileSize / $pixelCount;

            // Seuil arbitraire : Si < 0.1 octet/pixel pour une image "Haute Def", c'est louche (très compressé ou upscale flou)
            $isUpscaleSuspect = ($ratio < 0.15 && $pixelCount > 10000000) ? 1 : 0;

            // Règle 3900px
            $maxDim = max($width, $height);
            // On accepte mais on peut flaguer.

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
                $stmtPhoto = $pdo->prepare("INSERT INTO photos (participant_id, filename_original, filename_4k, filename_thumb, width, height, is_upscale_suspect) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmtPhoto->execute([$participantId, $fileOriginal, $file4k, $fileThumb, $width, $height, $isUpscaleSuspect]);

                $uploadedCount++;
            }
        }
    }

    echo "<h1>Candidature reçue !</h1>";
    echo "<p>Merci $name. $uploadedCount photo(s) ont été traitées.</p>";
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