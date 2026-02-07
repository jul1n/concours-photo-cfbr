<?php
require_once 'db_connect.php';

$jury_members = [
    ['email' => 'claudio.carvajal@inrae.fr', 'name' => 'Claudio Carvajal'],
    ['email' => 'frederic.laugier@edf.fr', 'name' => 'Frédéric Laugier'],
    ['email' => 'fabienne.mercier@smavd.org', 'name' => 'Fabienne Mercier'],
    ['email' => 'J.MEYNET@cnr.tm.fr', 'name' => 'Jérémy MEYNET'],
    ['email' => 'nathalie.rosin-corre@tractebel.engie.com', 'name' => 'Nathalie Rosin-Corre'],
    ['email' => 'stephanie.diss@arteliagroup.com', 'name' => 'Stéphanie DISS'],
    ['email' => 'desage@isl.fr', 'name' => 'Antoine DESAGE'],
    ['email' => 'florent.bacchus@developpement-durable.gouv.fr', 'name' => 'Florent Bacchus'],
    ['email' => 'julien.houssin@edf.fr', 'name' => 'Julien HOUSSIN'],
    ['email' => 'jean-jacques.fry@wanadoo.fr', 'name' => 'Jean-Jacques Fry'],
    ['email' => 'thierry.theodore@sunr.com', 'name' => 'Thierry THEODORE'],
    ['email' => 'fabrice.emeriault@3sr-grenoble.fr', 'name' => 'Fabrice Emeriault']
];

echo "Process jury members...<br>";

foreach ($jury_members as $member) {
    try {
        // Check if exists
        $stmt = $pdo->prepare("SELECT id, name FROM jury_members WHERE email = ?");
        $stmt->execute([$member['email']]);
        $existing = $stmt->fetch();

        if ($existing) {
            // Update name if different
            if ($existing['name'] !== $member['name']) {
                $update = $pdo->prepare("UPDATE jury_members SET name = ? WHERE id = ?");
                $update->execute([$member['name'], $existing['id']]);
                echo "Updated name for: " . htmlspecialchars($member['email']) . " (was: " . htmlspecialchars($existing['name']) . " -> now: " . htmlspecialchars($member['name']) . ")<br>";
            } else {
                echo "Skipped (already exists and name match): " . htmlspecialchars($member['email']) . "<br>";
            }
        } else {
            $stmt = $pdo->prepare("INSERT INTO jury_members (email, name) VALUES (?, ?)");
            $stmt->execute([$member['email'], $member['name']]);
            echo "Added: " . htmlspecialchars($member['name']) . " (" . htmlspecialchars($member['email']) . ")<br>";
        }
    } catch (Exception $e) {
        echo "Error processing " . htmlspecialchars($member['email']) . ": " . $e->getMessage() . "<br>";
    }
}

echo "Done.";
?>