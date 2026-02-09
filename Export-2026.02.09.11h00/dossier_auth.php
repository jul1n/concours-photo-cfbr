<?php
// dossier_auth.php
require_once __DIR__ . '/core/db.php';
require_once __DIR__ . '/includes/analytics.php';

session_start();

$message_sent = false;
$email_requested = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $email_requested = $email;

    // Check if participant exists
    $stmt = $pdo->prepare("SELECT firstname, validation_token FROM participants WHERE email = ? AND is_verified = 1 LIMIT 1");
    $stmt->execute([$email]);
    $participant = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($participant) {
        $token = $participant['validation_token'];
        $dossierLink = "http://" . $_SERVER['HTTP_HOST'] . "/dossier.php?token=$token";

        $subject = "Accès à votre dossier - Concours Photo CFBR";
        $message = "Bonjour " . $participant['firstname'] . ",\n\n";
        $message .= "Suite à votre demande, voici le lien sécurisé pour accéder à votre dossier de candidature :\n";
        $message .= $dossierLink . "\n\n";
        $message .= "Vous pourrez y retrouver vos photos, leurs descriptifs et télécharger votre reçu PDF.\n\n";
        $message .= "Cordialement,\nLe CFBR";

        $headers = "From: no-reply@barrages-cfbr.eu\r\n";
        $headers .= "Content-Type: text/plain; charset=utf-8";

        mail($email, $subject, $message, $headers);
    }

    $message_sent = true;
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérifiez vos emails - Concours Photo CFBR</title>
    <?php include __DIR__ . '/includes/pwa_loader.php'; ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Open+Sans:wght@400;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

<body class="bg-gray-100 h-screen flex items-center justify-center p-4">
    <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-md text-center border border-gray-100">
        <div class="mb-6">
            <div
                class="inline-flex items-center justify-center w-20 h-20 bg-green-50 text-green-500 rounded-full animate-pulse">
                <i class="fas fa-paper-plane text-3xl"></i>
            </div>
        </div>
        <h1 class="text-2xl font-bold text-[#0A2240] mb-4">Lien envoyé !</h1>
        <p class="text-gray-600 mb-8 leading-relaxed">Si l'adresse <strong>
                <?= htmlspecialchars($email_requested) ?>
            </strong> est associée à une candidature validée, vous recevrez un lien d'accès d'ici quelques instants.</p>

        <div class="space-y-4">
            <a href="index.php"
                class="block w-full bg-[#0A2240] text-white py-3 rounded-xl font-bold hover:bg-[#FF9900] transition-colors">Retour
                à l'accueil</a>
            <p class="text-[10px] text-gray-400 uppercase tracking-widest font-bold">Vérifiez vos spams si besoin</p>
        </div>

        <?php if ($participant && $_SERVER['HTTP_HOST'] === 'localhost:8000'): ?>
            <div class="mt-8 p-4 bg-amber-50 rounded-lg text-left text-[10px] border border-amber-200 break-all font-mono">
                <strong class="text-amber-800">DEBUG (Localhost) :</strong><br>
                <a href="<?= $dossierLink ?>" class="text-blue-600 underline">
                    <?= $dossierLink ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>