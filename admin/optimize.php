<?php
require_once __DIR__ . '/../core/auth.php';
require_admin();
// optimize_slides.php
// Script to scan data/slide/, resize images, and save them to photos/slides_optimized/
// Usage: Run this script once (or whenever new photos are added) via browser or CLI.

header('Content-Type: text/plain'); // Plain text output for easier reading

// Configuration
$sourceDir = __DIR__ . '/../_LOCAL_ONLY/original_photos/slide/';
$targetDir = __DIR__ . '/../photos/slides_optimized/';
$maxDim = 1920; // Full HD
$quality = 85;
$force = isset($_GET['force']) && $_GET['force'] == '1';

// Create target directory
if (!is_dir($targetDir)) {
    if (!mkdir($targetDir, 0755, true)) {
        die("Error: Could not create target directory: $targetDir");
    }
}

// Function to resize and save image
function processImage($source, $dest, $maxDim, $quality)
{
    list($width, $height, $type) = getimagesize($source);

    if (!$width)
        return false;

    // Calculate new dimensions (contain)
    $ratio = $width / $height;
    if ($width > $maxDim || $height > $maxDim) {
        if ($ratio > 1) {
            $newWidth = $maxDim;
            $newHeight = $maxDim / $ratio;
        } else {
            $newHeight = $maxDim;
            $newWidth = $maxDim * $ratio;
        }
    } else {
        $newWidth = $width;
        $newHeight = $height;
    }

    // Load source
    switch ($type) {
        case IMAGETYPE_JPEG:
            $src = imagecreatefromjpeg($source);
            break;
        case IMAGETYPE_PNG:
            $src = imagecreatefrompng($source);
            break;
        case IMAGETYPE_WEBP:
            $src = imagecreatefromwebp($source);
            break;
        default:
            return false; // Unsupported
    }

    if (!$src)
        return false;

    // Create dest
    $dst = imagecreatetruecolor($newWidth, $newHeight);

    // Resize
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    // Save as JPEG
    imagejpeg($dst, $dest, $quality);

    imagedestroy($src);
    imagedestroy($dst);

    return true;
}

// Function to sanitize strings for filenames (strips accents and special chars)
function sanitizeString($text)
{
    $map = array(
        'À' => 'A',
        'Á' => 'A',
        'Â' => 'A',
        'Ã' => 'A',
        'Ä' => 'A',
        'Å' => 'A',
        'Æ' => 'AE',
        'Ç' => 'C',
        'È' => 'E',
        'É' => 'E',
        'Ê' => 'E',
        'Ë' => 'E',
        'Ì' => 'I',
        'Í' => 'I',
        'Î' => 'I',
        'Ï' => 'I',
        'Ð' => 'D',
        'Ñ' => 'N',
        'Ò' => 'O',
        'Ó' => 'O',
        'Ô' => 'O',
        'Õ' => 'O',
        'Ö' => 'O',
        'Ø' => 'O',
        'Ù' => 'U',
        'Ú' => 'U',
        'Û' => 'U',
        'Ü' => 'U',
        'Ý' => 'Y',
        'Þ' => 'th',
        'ß' => 'ss',
        'à' => 'a',
        'á' => 'a',
        'â' => 'a',
        'ã' => 'a',
        'ä' => 'a',
        'å' => 'a',
        'æ' => 'ae',
        'ç' => 'c',
        'è' => 'e',
        'é' => 'e',
        'ê' => 'e',
        'ë' => 'e',
        'ì' => 'i',
        'í' => 'i',
        'î' => 'i',
        'ï' => 'i',
        'ð' => 'd',
        'ñ' => 'n',
        'ò' => 'o',
        'ó' => 'o',
        'ô' => 'o',
        'õ' => 'o',
        'ö' => 'o',
        'ø' => 'o',
        'ù' => 'u',
        'ú' => 'u',
        'û' => 'u',
        'ü' => 'u',
        'ý' => 'y',
        'þ' => 'th',
        'ÿ' => 'y'
    );
    $text = strtr($text, $map);
    // Remove anything that is not alphanumeric, dash, underscore or space
    $text = preg_replace('/[^A-Za-z0-9\-\_ ]/', '', $text);
    return trim($text);
}

// Recursive iterator to find files
if (!is_dir($sourceDir)) {
    die("Error: Source directory not found: $sourceDir\nPlease ensure you have photos in _LOCAL_ONLY/original_photos/slide/");
}
$dirIterator = new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS);
$iterator = new RecursiveIteratorIterator($dirIterator);

$count = 0;
$skipped = 0;
$errors = 0;

echo "Starting optimization...\n";
echo "Source: $sourceDir\n";
echo "Target: $targetDir\n\n";

foreach ($iterator as $file) {
    if ($file->isFile()) {
        $ext = strtolower($file->getExtension());
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($ext, $allowedExts)) {
            // Logic to extract participant name from folder
            $relativePath = substr($file->getPath(), strlen($sourceDir));
            $parts = explode(DIRECTORY_SEPARATOR, $relativePath);

            // Clean up name
            $participantName = trim($parts[0] ?? 'Unknown');
            if (empty($participantName))
                $participantName = 'Inconnu';

            // Sanitize name and filename (REMOVE ACCENTS)
            $safeName = sanitizeString($participantName);
            $safeFilename = sanitizeString(pathinfo($file->getFilename(), PATHINFO_FILENAME));

            $targetFilename = $safeName . "___" . $safeFilename . ".jpg";
            $targetPath = $targetDir . $targetFilename;

            if ($force || !file_exists($targetPath)) {
                echo "Processing: " . $file->getFilename() . " (User: $participantName)... ";
                if (processImage($file->getRealPath(), $targetPath, $maxDim, $quality)) {
                    echo "OK\n";
                    $count++;
                } else {
                    echo "ERROR (Image processing failed)\n";
                    $errors++;
                }
            } else {
                // Already exists
                // echo "Skipping (Exists): " . $file->getFilename() . "\n";
                $skipped++;
            }
        }
    }
}

echo "\nDone!\n";
echo "Processed: $count\n";
echo "Skipped (Already existed): $skipped\n";
echo "Errors: $errors\n";
