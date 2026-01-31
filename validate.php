<?php
// validate.php
$dbPath = __DIR__ . '/data/concours.db';

try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("Erreur DB: " . $e->getMessage());
}

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Vérifier le token
    $stmt = $pdo->prepare("SELECT id, name FROM participants WHERE validation_token = ?");
    $stmt->execute([$token]);
    $participant = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($participant) {
        // Valider
        $updateFor = $pdo->prepare("UPDATE participants SET is_verified = 1 WHERE id = ?");
        $updateFor->execute([$participant['id']]);

        ?>
        <!DOCTYPE html>
        <html lang="fr">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Validation Confirmée</title>
            <script src="https://cdn.tailwindcss.com"></script>
        </head>

        <body class="bg-gray-100 flex items-center justify-center h-screen">
            <div class="bg-white p-8 rounded-lg shadow-lg text-center max-w-md">
                <div class="text-green-500 text-6xl mb-4">
                    <i class="fas fa-check-circle"></i> ✓
                </div>
                <h1 class="text-2xl font-bold text-[#0A2240] mb-2">Inscription Validée !</h1>
                <p class="text-gray-600 mb-6">Merci <strong>
                        <?= htmlspecialchars($participant['name']) ?>
                    </strong>. Votre signature électronique est maintenant confirmée.</p>
                <a href="index.php"
                    class="bg-[#0A2240] text-white px-6 py-2 rounded-full font-semibold hover:bg-[#FF9900] transition-colors">Retour
                    à l'accueil</a>
            </div>
        </body>

        </html>
        <?php
    } else {
        echo "<h1 style='color:red;'>Token invalide ou expiré.</h1>";
    }
} else {
    echo "Aucun token fourni.";
}
?>