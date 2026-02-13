<?php
// Fix slideshow filenames
$slideDir = __DIR__ . '/photos/slides_optimized/';

// 1. Remove --- prefix from files
$files = glob($slideDir . '---*.jpg');
foreach ($files as $file) {
    $newName = str_replace('---', '', basename($file));
    $newPath = dirname($file) . '/' . $newName;
    if (rename($file, $newPath)) {
        echo "Renamed: " . basename($file) . " -> " . $newName . "\n";
    } else {
        echo "Failed to rename: " . basename($file) . "\n";
    }
}

// 2. Fix Anton MITEV file naming
$antonFile = $slideDir . 'Anton MITEV___20170928_102207.jpg';
$antonOldFile = $slideDir . 'Anton MITEV___20170928_102207.jpg';
if (!file_exists($antonFile) && file_exists($slideDir . 'Anton MITEV___20170928_102207.jpg')) {
    // File already has correct prefix, just needs to be verified
    echo "Anton MITEV file exists with correct naming\n";
}

// 3. Check for missing participants
$missingParticipants = ['Yann FOURNIE', 'Daniel Santin', 'Claudio CARVAJAL', 'Lucas BILLUART'];
echo "\n=== Missing Optimized Slides ===\n";
foreach ($missingParticipants as $name) {
    $pattern = $slideDir . $name . '___*.jpg';
    $found = glob($pattern);
    if (empty($found)) {
        echo "MISSING: " . $name . " - no optimized slides found\n";

        // Check if they have original photos
        $originalPattern = __DIR__ . '/photos/originals/' . '*' . str_replace(' ', '*', $name) . '*.jpg';
        $originals = glob($originalPattern, GLOB_BRACE);
        if (!empty($originals)) {
            echo "  -> Has " . count($originals) . " original photo(s) that need optimization\n";
        } else {
            echo "  -> No original photos found either!\n";
        }
    } else {
        echo "FOUND: " . $name . " - " . count($found) . " slide(s)\n";
    }
}

echo "\n=== Done ===\n";
