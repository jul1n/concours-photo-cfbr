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
    $stmt = $pdo->prepare("SELECT * FROM participants WHERE validation_token = ?");
    $stmt->execute([$token]);
    $participant = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($participant) {
        // Valider
        $updateFor = $pdo->prepare("UPDATE participants SET is_verified = 1 WHERE id = ?");
        $updateFor->execute([$participant['id']]);

        // Fetch photos for this participant
        $stmtPhotos = $pdo->prepare("SELECT * FROM photos WHERE participant_id = ?");
        $stmtPhotos->execute([$participant['id']]);
        $photos = $stmtPhotos->fetchAll(PDO::FETCH_ASSOC);

        // --- NEW PDF CLASS USAGE (Refactored) ---
        require_once(__DIR__ . '/includes/PDFGenerator.php');

        // Ensure directory exists for persistent storage
        $pdfDir = __DIR__ . '/uploads/pdfs/';
        if (!is_dir($pdfDir)) {
            mkdir($pdfDir, 0755, true);
        }

        $pdfPath = $pdfDir . 'agreement_' . $participant['id'] . '.pdf';

        // 1. Generate and Save to Server (Persistent)
        PDFGenerator::generateForParticipant($participant, $photos, 'F', $pdfPath);

        // 2. Generate String for Email Attachment (Dynamic)
        $pdfOutput = PDFGenerator::generateForParticipant($participant, $photos, 'S');

        // --- EMAIL WITH ATTACHMENT START ---
        // Fetch full email if needed
        $stmtEmail = $pdo->prepare("SELECT email FROM participants WHERE id = ?");
        $stmtEmail->execute([$participant['id']]);
        $userEmail = $stmtEmail->fetchColumn();

        $to = $userEmail;
        $subject = "Confirmation Inscription et Signature Reglement - Concours CFBR";
        $from = "no-reply@barrages-cfbr.eu";
        $boundary = md5(time());

        // Headers
        $headers = "From: $from\r\n";
        $headers .= "Cc: concoursphoto2026@barrages-cfbr.eu\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

        // Body
        $message = "--$boundary\r\n";
        $message .= "Content-Type: text/plain; charset=\"utf-8\"\r\n";
        $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $message .= "Bonjour " . $participant['firstname'] . " " . $participant['lastname'] . ",\n\nVotre inscription est validée. Veuillez trouver ci-joint votre preuve de signature du règlement.\n\nCordialement,\nLe CFBR\r\n";

        // Attachment
        $message .= "--$boundary\r\n";
        $message .= "Content-Type: application/pdf; name=\"Reglement_Signe_" . $participant['id'] . ".pdf\"\r\n";
        $message .= "Content-Transfer-Encoding: base64\r\n";
        $message .= "Content-Disposition: attachment; filename=\"Reglement_Signe_" . $participant['id'] . ".pdf\"\r\n\r\n";
        $message .= chunk_split(base64_encode($pdfOutput)) . "\r\n";
        $message .= "--$boundary--";

        @mail($to, $subject, $message, $headers);
        // --- EMAIL END ---

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
                        <?= htmlspecialchars($participant['firstname'] . ' ' . $participant['lastname']) ?>
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