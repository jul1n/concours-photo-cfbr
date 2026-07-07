<?php
// jury_verify.php
require_once __DIR__ . '/../core/auth.php'; // fournit app_config() + démarre la session
require __DIR__ . '/../core/db.php';


function showErrorAndDie($title, $message)
{
    ?>
    <!DOCTYPE html>
    <html lang="fr">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Erreur - Concours Photo CFBR</title>
        <?php include __DIR__ . '/../includes/pwa_loader.php'; ?>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Open+Sans:wght@400;600&display=swap"
            rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
        <style>
            body {
                font-family: 'Open Sans', sans-serif;
                background-color: #F8F8F8;
            }

            h1 {
                font-family: 'Montserrat', sans-serif;
            }
        </style>
    </head>

    <body class="bg-gray-100 h-screen flex items-center justify-center">
        <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-md text-center border-t-4 border-red-500">
            <div class="mb-4 text-red-500">
                <i class="fas fa-exclamation-circle text-5xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-[#0A2240] mb-2"><?= htmlspecialchars($title) ?></h1>
            <p class="text-gray-600 mb-6"><?= htmlspecialchars($message) ?></p>

            <a href="login.php"
                class="inline-block bg-[#0A2240] text-white px-6 py-3 rounded-full font-bold hover:bg-[#FF9900] hover:text-[#0A2240] transition-colors shadow-md">
                <i class="fas fa-redo mr-2"></i> Demander un nouveau lien
            </a>

            <div class="mt-4">
                <a href="../index.php" class="text-sm text-gray-400 hover:text-gray-600 underline">Retour à l'accueil</a>
            </div>
        </div>
    </body>

    </html>
    <?php
    exit;
}

$token = isset($_GET['token']) ? (string)$_GET['token'] : '';

if (!$token) {
    showErrorAndDie("Lien Invalide", "Le jeton de connexion est manquant.");
}

// Check token in DB
$stmt = $pdo->prepare("SELECT t.id, t.jury_id, t.used_at, t.created_at, j.name, j.email 
                       FROM jury_tokens t 
                       JOIN jury_members j ON t.jury_id = j.id 
                       WHERE t.token = ?");
$stmt->execute([$token]);
$tokenData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tokenData) {
    showErrorAndDie("Lien Introuvable", "Ce lien de connexion n'existe pas ou est incorrect.");
}

// Usage unique
if ($tokenData['used_at']) {
    showErrorAndDie("Lien Expiré", "Ce lien a déjà été utilisé pour se connecter. Pour des raisons de sécurité, veuillez demander un nouveau lien.");
}

// Expiration temporelle
$ttl = app_config()['jury_token_ttl'] ?? 3600;
if (!empty($tokenData['created_at'])) {
    $age = time() - strtotime($tokenData['created_at'] . ' UTC');
    if ($age > $ttl) {
        showErrorAndDie("Lien Expiré", "Ce lien a expiré. Veuillez demander un nouveau lien de connexion.");
    }
}

// Marque comme utilisé
$now = date('Y-m-d H:i:s');
$updateStmt = $pdo->prepare("UPDATE jury_tokens SET used_at = ? WHERE id = ?");
$updateStmt->execute([$now, $tokenData['id']]);

// Login User
$_SESSION['jury_logged_in'] = true;
$_SESSION['jury_id'] = $tokenData['jury_id'];
$_SESSION['jury_name'] = $tokenData['name'];
$_SESSION['jury_email'] = $tokenData['email'];

// Redirect to jury space
header("Location: home.php");
exit;

