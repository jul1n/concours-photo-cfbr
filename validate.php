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

        // --- NEW PDF CLASS (Matched with jury_view_pdf.php) ---
        require('fpdf/fpdf.php');

        class PDF extends FPDF
        {
            function Header()
            {
                // Logo
                $logoPath = 'https://www.barrages-cfbr.eu/IMG/logo/siteon0.png';
                $this->Image($logoPath, 10, 6, 30);

                $this->SetFont('Arial', 'B', 14);
                $this->Cell(80);
                $this->Cell(80, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Règlement & Signature - Concours Photo 2026'), 0, 1, 'C');

                $this->SetFont('Arial', '', 10);
                $this->Cell(80);
                $this->Cell(80, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Comité Français des Barrages et Réservoirs'), 0, 1, 'C');
                $this->Ln(15);
            }

            function Footer()
            {
                $this->SetY(-15);
                $this->SetFont('Arial', 'I', 8);
                $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
            }

            function SectionTitle($label)
            {
                $this->SetFont('Arial', 'B', 12);
                $this->SetFillColor(230, 230, 230);
                $this->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', "  $label"), 0, 1, 'L', true);
                $this->Ln(4);
            }

            function SectionBody($txt)
            {
                $this->SetFont('Arial', '', 9); // Slightly smaller for long text
                $this->MultiCell(0, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $txt));
                $this->Ln(5);
            }

            function InfoPair($label, $value)
            {
                $this->SetFont('Arial', 'B', 10);
                $this->Cell(50, 6, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $label), 0, 0);
                $this->SetFont('Arial', '', 10);
                $this->Cell(0, 6, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $value), 0, 1);
            }
        }

        // Create PDF
        $pdf = new PDF();
        $pdf->AliasNbPages();
        $pdf->AddPage();

        // 1. Participant Info
        $pdf->SectionTitle("1. Informations du Participant");
        $pdf->InfoPair("Nom :", $participant['lastname']);
        $pdf->InfoPair("Prénom :", $participant['firstname']);
        $pdf->InfoPair("Email :", $participant['email']);
        $pdf->InfoPair("Société :", $participant['company'] ?: 'N/A');
        $pdf->InfoPair("Date soumission :", date('d/m/Y H:i:s', strtotime($participant['created_at'])));
        $pdf->InfoPair("ID Participation :", '#' . $participant['id']);
        $pdf->Ln(5);

        // 2. Declaration
        $pdf->SectionTitle("2. Déclaration sur l'honneur");
        $declaration = "Je soussigné(e) " . $participant['firstname'] . " " . $participant['lastname'] . ", reconnais avoir pris connaissance du règlement du concours photo \"Barrages : Entre nature et architecture\".\n";
        $declaration .= "Je certifie que les informations fournies sont exactes et que je suis l'auteur des photographies soumises.";
        $pdf->SectionBody($declaration);

        // --- FULL REGULATION TEXT ---
        $pdf->AddPage();
        $pdf->SectionTitle("3. Règlement Complet");

        $reglementPath = __DIR__ . '/assets/reglement_2026.txt';
        if (file_exists($reglementPath)) {
            $reglement = file_get_contents($reglementPath);
        } else {
            $reglement = "Erreur : Le fichier du règlement est introuvable.";
        }

        $pdf->SectionBody($reglement);


        // 3. Annexes
        $pdf->AddPage();
        $pdf->SectionTitle("4. Annexe A : Cession de Droits d'Auteur");

        $annexAText = "Entre les soussignés : Le Cédant (" . $participant['firstname'] . " " . $participant['lastname'] . ") et le Cessionnaire (Le CFBR).\n\n";
        $annexAText .= "Article 1 : Objet de la cession\n";
        $annexAText .= "Le présent contrat a pour objet la cession des droits d'exploitation des photographies listées ci-dessous, dans le cadre du Concours Photo Grand Public 2026 organisé par le CFBR.\n\n";
        $annexAText .= "Article 2 : Droits cédés\n";
        $annexAText .= "L'Auteur cède au CFBR, pour les photographies présélectionnées et/ou primées, les droits patrimoniaux de reproduction, représentation et adaptation.\n\n";
        $annexAText .= "Article 3 : Étendue de la cession\n";
        $annexAText .= "Cette cession est consentie à titre non exclusif, à titre gratuit, pour le monde entier et pour la durée légale de protection des droits d'auteur, pour une exploitation dans un but non commercial.\n\n";

        $annexAText .= "LISTE DES ŒUVRES CÉDÉES :\n";
        foreach ($photos as $i => $p) {
            $num = $i + 1;
            $title = $p['title'] ?: $p['filename'];
            $annexAText .= "- Photo $num : $title (Cat: " . $p['category'] . ")\n";
        }

        $pdf->SectionBody($annexAText);

        $annexAStatus = ($participant['agree_annex_a']) ? "[X] LU ET APPROUVÉ (Signature numérique)" : "[ ] NON VALIDÉ";
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $annexAStatus), 0, 1);
        $pdf->Ln(5);


        // Annex B
        $pdf->SectionTitle("5. Annexe B : Droit à l'image");
        $annexBText = "Titre : Autorisation d'Utilisation de l'Image d'une Personne\n";
        $annexBText .= "(Applicable uniquement si des personnes sont identifiables sur vos photos)\n\n";
        $annexBText .= "En soumettant des photos comportant des personnes identifiables, vous garantissez avoir recueilli leur consentement écrit pour autoriser le CFBR à utiliser leur image.\n\n";

        // Add identifiable persons if any
        if (!empty($participant['identifiable_persons'])) {
            $annexBText .= "PERSONNES IDENTIFIABLES DÉCLARÉES :\n" . $participant['identifiable_persons'] . "\n";
        } else {
            $annexBText .= "PERSONNES IDENTIFIABLES DÉCLARÉES : Néant (ou non renseigné)\n";
        }

        $pdf->SectionBody($annexBText);

        $annexBStatus = ($participant['agree_annex_b']) ? "[X] CERTIFIÉ SUR L'HONNEUR" : "[ ] NON COCHÉ";
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $annexBStatus), 0, 1);
        $pdf->Ln(10);


        // 4. Photos Submitted Recap
        $pdf->AddPage();
        $pdf->SectionTitle("6. Récapitulatif des Fichiers");
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(10, 7, 'ID', 1);
        $pdf->Cell(80, 7, 'Titre / Nom Fichier', 1);
        $pdf->Cell(40, 7, utf8_decode('Catégorie'), 1);
        $pdf->Cell(60, 7, 'Lieu', 1);
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 8);
        foreach ($photos as $p) {
            if (empty($p['title']))
                $p['title'] = $p['filename'];

            // Clean strings for PDF
            $title = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', substr($p['title'], 0, 45));
            $cat = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', substr($p['category'], 0, 20));
            $loc = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', substr($p['location'], 0, 30));

            $pdf->Cell(10, 6, $p['id'], 1);
            $pdf->Cell(80, 6, $title, 1);
            $pdf->Cell(40, 6, $cat, 1);
            $pdf->Cell(60, 6, $loc, 1);
            $pdf->Ln();
        }
        $pdf->Ln(10);

        // 5. Signature
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, "SIGNATURE ELECTRONIQUE", 0, 1, 'R');
        $pdf->SetFont('Courier', '', 10);
        $pdf->Cell(0, 5, "Token: " . $participant['validation_token'], 0, 1, 'R');
        $pdf->Cell(0, 5, "IP: " . $participant['ip'], 0, 1, 'R');
        $pdf->Cell(0, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', "Signé le: " . date('d/m/Y H:i:s', strtotime($participant['created_at']))), 0, 1, 'R');

        $pdfOutput = $pdf->Output('S'); // String output

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
        $message .= "Bonjour " . $participant['name'] . ",\n\nVotre inscription est validée. Veuillez trouver ci-joint votre preuve de signature du règlement.\n\nCordialement,\nLe CFBR\r\n";

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