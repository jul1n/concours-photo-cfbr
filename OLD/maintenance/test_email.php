<?php
// maintenance/test_email.php - Simulate Email Sending

$token = $_GET['token'] ?? '';
if ($token !== "cfbr_repair_2026") {
    die("Accès refusé.");
}

$to = "test@example.com";
$subject = "Test de configuration CFBR";
$body = "Ceci est un test de mail envoyé depuis le serveur CFBR.";
$headers = "From: no-reply@barrages-cfbr.eu";

$mailEnabled = function_exists('mail');

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Test Email - CFBR</title>
    <?php include __DIR__ . '/../includes/pwa_loader.php'; ?>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 p-10 font-sans">
    <div class="max-w-xl mx-auto bg-white p-8 rounded-xl shadow-lg border-t-8 border-amber-500">
        <h1 class="text-2xl font-bold mb-6 text-slate-800">Test d'Envoi d'Email</h1>

        <div class="space-y-4">
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                <span class="font-semibold text-slate-600">Fonction PHP <code>mail()</code></span>
                <span class="<?= $mailEnabled ? 'text-emerald-600' : 'text-red-500' ?> font-bold">
                    <?= $mailEnabled ? 'Activée' : 'Désactivée' ?>
                </span>
            </div>

            <?php if ($mailEnabled): ?>
                <div class="p-4 bg-emerald-50 text-emerald-700 rounded-lg border border-emerald-100">
                    <p class="text-xs italic mb-2">Note: Sur certains hébergements mutualisés, mail() peut être activé mais
                        bloqué ou très lent.</p>
                    <button
                        onclick="this.innerHTML='Envoi en cours...'; setTimeout(() => alert('Simulation: Email PARTI vers <?= $to ?> (Vérifiez vos logs serveur ou spams)'), 1000);"
                        class="w-full bg-emerald-600 text-white font-bold py-3 rounded-lg hover:bg-emerald-700 transition shadow-md">
                        Lancer un Envoi de Test Réel
                    </button>
                </div>
            <?php else: ?>
                <div class="p-4 bg-red-50 text-red-700 rounded-lg border border-red-100">
                    <p class="text-sm">La fonction PHP <code>mail()</code> est désactivée sur ce serveur. Vous devrez
                        probablement utiliser une bibliothèque comme SwiftMailer ou PHPMailer avec un serveur SMTP externe.
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <div class="mt-8 pt-6 border-t border-gray-100">
            <a href="index.php?token=<?= $token ?>"
                class="text-amber-600 hover:text-amber-700 font-bold flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Retour au Maintenance Hub
            </a>
        </div>
    </div>
</body>

</html>