<?php
require_once __DIR__ . '/core/auth.php';
require_maintenance();
// Delete duplicate files with --- prefix
$slideDir = __DIR__ . '/photos/slides_optimized/';

$filesToDelete = [
    '---Jules GUILLOT___GuillotJules_01.jpg',
    '---Jules GUILLOT___GuillotJules_02.jpg',
    '---Jules GUILLOT___GuillotJules_03.jpg',
    '---Jules GUILLOT___GuillotJules_04.jpg',
    '---Jules GUILLOT___GuillotJules_05.jpg',
    '---Laurent PASCAL___RivesEtEaux_01.jpg',
    '---Laurent PASCAL___RivesEtEaux_02.jpg',
    '---Laurent PASCAL___RivesEtEaux_03.jpg',
];

echo "=== Deleting Duplicate Files with --- Prefix ===\n\n";

foreach ($filesToDelete as $file) {
    $fullPath = $slideDir . $file;
    if (file_exists($fullPath)) {
        if (unlink($fullPath)) {
            echo "✓ Deleted: $file\n";
        } else {
            echo "✗ Failed to delete: $file\n";
        }
    } else {
        echo "- Not found: $file\n";
    }
}

echo "\n=== Done ===\n";
echo "\nAll participants now have clean, working slideshow files!\n";
