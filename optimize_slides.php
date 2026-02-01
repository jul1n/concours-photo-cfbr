<?php
// optimize_slides.php
// Script to scan data/slide/, resize images, and save them to photos/slides_optimized/
// Usage: Run this script once (or whenever new photos are added) via browser or CLI.

header('Content-Type: text/plain'); // Plain text output for easier reading

// Configuration
$sourceDir = __DIR__ . '/data/slide/';
$targetDir = __DIR__ . '/photos/slides_optimized/';
$maxDim = 1920; // Full HD
$quality = 85;

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

    $dst = imagecreatetruecolor($newWidth, $newHeight);

    // Preserve transparency for PNG/WebP (converted to black/white bg for JPEG usually, but here we save as JPEG)
    // Best practice for slideshow: fill background if transparent or just keep it black. 
    // Since we output JPEG, transparency becomes black.

    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    // Save as JPEG
    imagejpeg($dst, $dest, $quality);

    imagedestroy($src);
    imagedestroy($dst);

    return true;
}

// Recursive iterator to find files
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
            // Path: .../data/slide/Firstname Lastname/photo.jpg
            $relativePath = substr($file->getPath(), strlen($sourceDir));
            // relativePath might be "Firstname Lastname" or "Firstname Lastname/Subfolder"
            // We assume direct subfolder is the name.

            $parts = explode(DIRECTORY_SEPARATOR, $relativePath);

            // Clean up name (remove extra slashes if any)
            $participantName = trim($parts[0] ?? 'Unknown');
            if (empty($participantName))
                $participantName = 'Inconnu';

            // Sanitize filenames but PRESERVE ACCENTS
            // Target format: "Participant Name___OriginalName.jpg"
            // We use a separator "___" to easily split later in JS/PHP

            // Allow letters (including unicode), numbers, spaces, dashes, underscores
            $safeName = preg_replace('/[^\p{L}0-9\-\_ ]/u', '', $participantName);

            // For filename, we might be stricter, but let's allow accents too to be safe/nice
            $safeFilename = pathinfo($file->getFilename(), PATHINFO_FILENAME);
            $safeFilename = preg_replace('/[^\p{L}0-9\-\_]/u', '', $safeFilename);

            $targetFilename = $safeName . "___" . $safeFilename . ".jpg";
            $targetPath = $targetDir . $targetFilename;

            if (!file_exists($targetPath)) {
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
