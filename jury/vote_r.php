<?php
// vote_tour2.php
session_start();
$dbPath = __DIR__ . '/data/concours.db';

// Ensure PDO is available
try {
    if (!isset($pdo)) {
        $pdo = new PDO("sqlite:$dbPath");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
} catch (Exception $e) {
    die("Erreur de connexion DB");
}

$success = false;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rankingOrder = json_decode($_POST['ranking_order'], true);
    $ip = $_SERVER['REMOTE_ADDR'];
    // Use session email if available for consistency with other pages
    if (isset($_SESSION['jury_email']))
        $ip = $_SESSION['jury_email'];

    if (!$rankingOrder) {
        $error = "Erreur de données reçues.";
    } else {
        try {
            // Barème points
            $pointsMap = [10, 9, 8, 7, 6, 5, 4, 3, 2, 1];

            $pdo->beginTransaction();

            // DELETE inputs from this IP/User to allow re-vote
            $stmtDel = $pdo->prepare("DELETE FROM votes_tour2 WHERE jury_ip = ?");
            $stmtDel->execute([$ip]);

            foreach ($rankingOrder as $index => $photoId) {
                $rank = $index + 1;
                $points = isset($pointsMap[$index]) ? $pointsMap[$index] : 0;
                $stmt = $pdo->prepare("INSERT INTO votes_tour2 (photo_id, jury_ip, rank, points) VALUES (?, ?, ?, ?)");
                $stmt->execute([$photoId, $ip, $rank, $points]);
            }

            $pdo->commit();
            $success = true;

        } catch (Exception $e) {
            if ($pdo->inTransaction())
                $pdo->rollBack();
            $error = "Erreur DB : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Jury - Confirmation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Open+Sans:wght@400;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <?php include __DIR__ . '/includes/pwa_loader.php'; ?>
</head>

<body class="bg-gray-100 font-sans pb-20">

    <!-- Header -->
    <header class="bg-[#0A2240] text-white p-4 shadow-md mb-8 sticky top-0 z-50">
        <div class="container mx-auto flex justify-between items-center">
            <div>
                <h1 class="text-xl font-bold font-title">Espace Jury - Classement</h1>
                <div class="space-x-4 text-xs mt-1">
                    <a href="jury_qualification.php" class="text-gray-400 hover:text-white transition">1.
                        Qualification</a>
                    <a href="jury_tour1.php" class="text-gray-400 hover:text-white transition">2. Notation</a>
                    <span class="text-[#FF9900] font-bold">3. Classement</span>
                    <a href="jury_classement.php" class="text-gray-400 hover:text-white transition">4. Synthèse</a>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <div class="text-right text-xs">
                    <div class="font-bold">Jury:
                        <?= htmlspecialchars($_SESSION['jury_email'] ?? $_SESSION['jury_name'] ?? $ip) ?>
                    </div>
                </div>
                <a href="index.php" class="text-sm font-bold hover:text-[#FF9900] transition-colors">
                    <i class="fas fa-home mr-1"></i> Retour Accueil
                </a>
            </div>
        </div>
    </header>

    <div class="container mx-auto px-4 py-12 flex justify-center">
        <div class="bg-white rounded-lg shadow-xl p-10 max-w-lg text-center border-t-8 border-[#FF9900]">

            <?php if ($success): ?>
                <div class="mb-6">
                    <div
                        class="w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-4 text-4xl shadow-inner">
                        <i class="fas fa-check"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-[#0A2240] mb-2 font-title">Classement Enregistré !</h2>
                    <p class="text-gray-600 mb-8">Merci pour votre participation.<br>Vos points ont été attribués avec
                        succès.</p>

                    <div class="space-y-3">
                        <a href="jury_classement.php"
                            class="block w-full bg-[#0A2240] text-white font-bold py-3 px-6 rounded hover:bg-[#1a3a5f] transition shadow-md">
                            <i class="fas fa-chart-bar mr-2"></i> Voir la Synthèse des résultats
                        </a>
                        <a href="jury_tour2.php"
                            class="block w-full bg-gray-100 text-gray-600 font-bold py-3 px-6 rounded hover:bg-gray-200 transition">
                            <i class="fas fa-pencil-alt mr-2"></i> Modifier mon classement
                        </a>
                    </div>
                </div>
            <?php elseif ($error): ?>
                <div class="mb-6">
                    <div
                        class="w-20 h-20 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4 text-4xl">
                        <i class="fas fa-times"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-red-700 mb-2">Erreur</h2>
                    <p class="text-gray-600 mb-6"><?= htmlspecialchars($error) ?></p>
                    <a href="jury_tour2.php"
                        class="inline-block bg-[#0A2240] text-white font-bold py-2 px-6 rounded hover:bg-[#1a3a5f]">
                        Réessayer
                    </a>
                </div>
            <?php else: ?>
                <p class="text-gray-500">Aucune donnée soumise.</p>
                <a href="jury_tour2.php" class="text-[#FF9900] hover:underline font-bold mt-4 inline-block">Retour au
                    classement</a>
            <?php endif; ?>

        </div>
    </div>

</body>

</html>