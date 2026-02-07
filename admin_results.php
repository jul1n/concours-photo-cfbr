<?php
// admin_results.php
session_start();
require_once 'db_connect.php';

// --- Security Logic ---
$password = "concours2026"; // Hardcoded password
$isUnlocked = isset($_SESSION['admin_unlocked']) && $_SESSION['admin_unlocked'] === true;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unlock_password'])) {
    if ($_POST['unlock_password'] === $password) {
        $_SESSION['admin_unlocked'] = true;
        $isUnlocked = true;
    } else {
        $error = "Mot de passe incorrect.";
    }
}

// --- Fetch Results ---
try {
    $sql = "
        SELECT p.*, SUM(v.points) as total_points, pa.name as author_name, pa.firstname, pa.lastname, pa.email as author_email, pa.company
        FROM photos p
        JOIN votes_tour2 v ON p.id = v.photo_id
        JOIN participants pa ON p.participant_id = pa.id
        GROUP BY p.id
        ORDER BY total_points DESC
    ";
    $stmt = $pdo->query($sql);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Erreur DB");
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Résultats Officiels</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Open+Sans:wght@400;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="bg-gray-100 font-sans pb-20">

    <!-- Standard Admin/Jury Header -->
    <header class="bg-[#0A2240] text-white p-4 shadow-md mb-8 sticky top-0 z-50">
        <div class="container mx-auto flex justify-between items-center">
            <div>
                <h1 class="text-xl font-bold font-title">Résultats Officiels</h1>
                <div class="space-x-4 text-xs mt-1">
                    <a href="jury_qualification.php" class="text-gray-400 hover:text-white transition">1.
                        Qualification</a>
                    <a href="jury_tour1.php" class="text-gray-400 hover:text-white transition">2. Notation</a>
                    <a href="jury_tour2.php" class="text-gray-400 hover:text-white transition">3. Classement</a>
                    <a href="jury_classement.php" class="text-gray-400 hover:text-white transition">4. Synthèse</a>
                    <span class="text-[#FF9900] font-bold">5. Résultats</span>
                </div>
            </div>
            <a href="index.php" class="text-sm font-bold hover:text-[#FF9900] transition-colors">
                <i class="fas fa-home mr-1"></i> Retour Accueil
            </a>
        </div>
    </header>

    <main class="container mx-auto p-4 max-w-6xl">

        <!-- Locked State -->
        <?php if (!$isUnlocked): ?>
            <div class="max-w-md mx-auto bg-white p-8 rounded shadow-lg text-center mt-10">
                <div class="mb-4 text-[#0A2240] text-5xl"><i class="fas fa-lock"></i></div>
                <h2 class="text-2xl font-bold mb-2">Accès Sécurisé</h2>
                <p class="text-gray-500 mb-6">Veuillez entrer le mot de passe pour voir les noms des candidats et accéder
                    aux exports.</p>

                <?php if (isset($error)): ?>
                    <div class="bg-red-100 text-red-700 p-2 rounded mb-4 text-sm"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST" class="space-y-4">
                    <input type="password" name="unlock_password" placeholder="Mot de passe..."
                        class="w-full border border-gray-300 rounded p-3 text-center text-lg focus:ring-2 focus:ring-[#0A2240] outline-none">
                    <button type="submit"
                        class="w-full bg-[#FF9900] text-[#0A2240] font-bold py-3 rounded hover:bg-[#e68a00] transition">
                        Déverrouiller
                    </button>
                </form>
            </div>

            <!-- Public/Locked Table (Anonymous) -->
            <div class="mt-12 bg-white p-8 rounded shadow opacity-50 pointer-events-none filter blur-[2px] select-none">
                <h3 class="text-xl font-bold mb-4">Aperçu (Verrouillé)</h3>
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50">
                            <th>Rang</th>
                            <th>Points</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($results, 0, 5) as $i => $r): ?>
                            <tr>
                                <td class="p-2">#<?= $i + 1 ?></td>
                                <td class="p-2"><?= $r['total_points'] ?> pts</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php else: ?>

            <!-- Unlocked Content -->

            <!-- Export Toolbar -->
            <div class="mb-8 flex flex-wrap gap-4 items-center justify-between bg-white p-4 rounded shadow">
                <div class="flex items-center gap-2">
                    <i class="fas fa-file-archive text-[#0A2240] text-xl"></i>
                    <span class="font-bold text-[#0A2240]">Exports ZIP :</span>
                </div>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ([3, 10, 25, 50, 100] as $lim): ?>
                        <a href="admin_export_zip.php?limit=<?= $lim ?>" target="_blank"
                            class="bg-blue-50 text-blue-800 border border-blue-200 px-3 py-1 rounded text-sm font-bold hover:bg-blue-100 transition">
                            Top <?= $lim ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <div class="w-px h-8 bg-gray-300 mx-2 hidden md:block"></div>

                <div class="flex items-center gap-2">
                    <a href="admin_export_pdf.php" target="_blank"
                        class="bg-red-600 text-white px-4 py-2 rounded font-bold hover:bg-red-700 shadow flex items-center">
                        <i class="fas fa-file-pdf mr-2"></i> Rapport Complet (PDF)
                    </a>
                </div>
            </div>

            <!-- Podium -->
            <?php if (count($results) > 0): ?>
                <div class="flex flex-col md:flex-row justify-center items-end gap-6 mb-12">
                    <!-- 2eme -->
                    <?php if (isset($results[1])): ?>
                        <div class="text-center order-2 md:order-1">
                            <div class="relative inline-block">
                                <img src="photos/thumbs/<?= $results[1]['filename_thumb'] ?>"
                                    class="h-32 w-auto rounded border-4 border-gray-300 shadow-md">
                                <div
                                    class="absolute -top-3 -right-3 bg-gray-300 text-white w-8 h-8 rounded-full flex items-center justify-center font-bold shadow">
                                    2</div>
                            </div>
                            <div class="mt-2 font-bold">
                                <?= htmlspecialchars($results[1]['firstname'] . ' ' . $results[1]['lastname']) ?>
                            </div>
                            <div class="text-sm text-gray-500"><?= $results[1]['total_points'] ?> pts</div>
                        </div>
                    <?php endif; ?>

                    <!-- 1er -->
                    <div class="text-center order-1 md:order-2 pb-4">
                        <div class="relative inline-block">
                            <img src="photos/thumbs/<?= $results[0]['filename_thumb'] ?>"
                                class="h-48 w-auto rounded border-4 border-[#FF9900] shadow-xl">
                            <div
                                class="absolute -top-4 -right-4 bg-[#FF9900] text-white w-10 h-10 rounded-full flex items-center justify-center font-bold text-xl shadow">
                                1</div>
                        </div>
                        <div class="mt-2 text-xl font-bold text-[#0A2240]">
                            <?= htmlspecialchars($results[0]['firstname'] . ' ' . $results[0]['lastname']) ?>
                        </div>
                        <div class="text-[#FF9900] font-bold"><?= $results[0]['total_points'] ?> pts</div>
                    </div>

                    <!-- 3eme -->
                    <?php if (isset($results[2])): ?>
                        <div class="text-center order-3 md:order-3">
                            <div class="relative inline-block">
                                <img src="photos/thumbs/<?= $results[2]['filename_thumb'] ?>"
                                    class="h-32 w-auto rounded border-4 border-yellow-700 shadow-md">
                                <div
                                    class="absolute -top-3 -right-3 bg-yellow-700 text-white w-8 h-8 rounded-full flex items-center justify-center font-bold shadow">
                                    3</div>
                            </div>
                            <div class="mt-2 font-bold">
                                <?= htmlspecialchars($results[2]['firstname'] . ' ' . $results[2]['lastname']) ?>
                            </div>
                            <div class="text-sm text-gray-500"><?= $results[2]['total_points'] ?> pts</div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Full List -->
                <div class="bg-white rounded shadow overflow-hidden">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="p-4">Rang</th>
                                <th class="p-4">Photo</th>
                                <th class="p-4">Candidat</th>
                                <th class="p-4">Entreprise</th>
                                <th class="p-4">Points</th>
                                <th class="p-4">Original</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <?php foreach ($results as $i => $row): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="p-4 font-bold text-gray-500">#<?= $i + 1 ?></td>
                                    <td class="p-4">
                                        <a href="photos/display_4k/<?= $row['filename_4k'] ?: $row['filename_original'] ?>"
                                            target="_blank">
                                            <img src="photos/thumbs/<?= $row['filename_thumb'] ?>"
                                                class="w-16 h-16 object-cover rounded border">
                                        </a>
                                    </td>
                                    <td class="p-4">
                                        <div class="font-bold"><?= htmlspecialchars($row['firstname'] . ' ' . $row['lastname']) ?>
                                        </div>
                                        <div class="text-xs text-gray-500"><?= htmlspecialchars($row['author_email']) ?></div>
                                    </td>
                                    <td class="p-4 text-sm"><?= htmlspecialchars($row['company']) ?></td>
                                    <td class="p-4 font-bold text-[#0A2240]"><?= $row['total_points'] ?></td>
                                    <td class="p-4">
                                        <a href="photos/originals/<?= $row['filename_original'] ?>" download
                                            class="text-green-600 hover:text-green-800">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php endif; ?> <!-- End If Locked -->

        <?php endif; ?>

    </main>
</body>

</html>