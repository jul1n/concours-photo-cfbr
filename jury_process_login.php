<?php
// jury_process_login.php
require 'db_connect.php'; // Updated to avoid re-initialization outputs

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $ip = $_SERVER['REMOTE_ADDR'];
    $timestamp = date('Y-m-d H:i:s');

    // 1. Log the solicitation to CSV
    $logFile = __DIR__ . '/data/login_requests.csv';
    $logEntry = [$timestamp, $ip, $email];

    if (!file_exists($logFile)) {
        $fp = fopen($logFile, 'w');
        fputcsv($fp, ['Date', 'IP', 'Email', 'Status']);
        fclose($fp);
    }

    // Check if email is in jury_members
    $stmt = $pdo->prepare("SELECT id, name FROM jury_members WHERE email = ?");
    $stmt->execute([$email]);
    $jury = $stmt->fetch(PDO::FETCH_ASSOC);

    $status = $jury ? 'Valid' : 'Invalid';

    // Append to CSV
    $fp = fopen($logFile, 'a');
    fputcsv($fp, [$timestamp, $ip, $email, $status]);
    fclose($fp);

    if ($jury) {
        // 2. Generate Token
        $token = bin2hex(random_bytes(32));

        // 3. Save Token to DB
        $stmt = $pdo->prepare("INSERT INTO jury_tokens (jury_id, token) VALUES (?, ?)");
        $stmt->execute([$jury['id'], $token]);

        // 4. Send Email
        $loginLink = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/jury_verify.php?token=$token";

        $subject = "Connexion Espace Jury - Concours Photo CFBR";
        $message = "Bonjour " . $jury['name'] . ",\n\n";
        $message .= "Voici votre lien de connexion unique pour accéder à l'espace jury :\n";
        $message .= $loginLink . "\n\n";
        $message .= "Ce lien est valide pour une seule utilisation.\n\n";
        $message .= "Cordialement,\nLe CFBR";

        $headers = "From: no-reply@barrages-cfbr.eu\r\n";
        $headers .= "Content-Type: text/plain; charset=utf-8";

        // Attempt to send email
        // Note: checking mail return value isn't always reliable for delivery, but good for local check
        if (mail($email, $subject, $message, $headers)) {
            // Email sent
        } else {
            // Email failed (likely local env)
            // For development purposes, we might want to expose the link if email fails? 
            // The user asked for email. I will keep it silent for security in prod, 
            // but maybe log it to a separate debug file if needed.
        }

    }

    // Always show the same message to prevent email enumeration
    // (Though the user didn't explicitly ask for security against enumeration, it's best practice)
    // However, the user wants "send a link".

    // For the purpose of this task and immediate feedback for the user (Julien):
    // I will output a message.
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification - Concours Photo CFBR</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Open+Sans:wght@400;600&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #F8F8F8;
        }
    </style>
</head>

<body class="bg-gray-100 h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-md text-center">
        <div class="mb-6">
            <i class="fas fa-envelope-open-text text-4xl text-[#0A2240]"></i>
        </div>
        <h1 class="text-2xl font-bold text-[#0A2240] mb-4">Vérifiez vos emails</h1>
        <p class="text-gray-600 mb-6">Si votre adresse est autorisée, vous recevrez un lien de connexion dans quelques
            instants.</p>
        <a href="index.php" class="text-[#FF9900] font-bold hover:underline">Retour à l'accueil</a>

        <?php if ($jury && $_SERVER['HTTP_HOST'] === 'localhost:8000'): ?>
            <!-- DEBUG ONLY: Show link locally because creating mail server is hard -->
            <div class="mt-8 p-4 bg-yellow-100 text-left text-xs break-all">
                <strong>DEBUG (Localhost only):</strong><br>
                <a href="<?php echo $loginLink; ?>">
                    <?php echo $loginLink; ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>