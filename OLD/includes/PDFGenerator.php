<?php
require_once(__DIR__ . '/../fpdf/fpdf.php');

class PDFGenerator extends FPDF
{
    function Header()
    {
        // Logo
        $logoPath = __DIR__ . '/../assets/logo_cfbr_100_ans.png';
        if (file_exists($logoPath)) {
            $this->Image($logoPath, 10, 6, 30);
        }
        // Identité Concours
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(40);
        $this->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Concours Photo CFBR 2026'), 0, 1, 'C');
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Accord de Participation & Cessions'), 0, 1, 'C');
        $this->Ln(20);
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
        $this->SetFillColor(255, 153, 0); // Orange Brand
        $this->SetTextColor(255);
        $this->Cell(0, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', "  $label"), 0, 1, 'L', true);
        $this->SetTextColor(0);
        $this->Ln(4);
    }

    function SectionBody($txt)
    {
        $this->SetFont('Times', '', 10);
        $this->MultiCell(0, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $txt));
        $this->Ln();
    }

    function InfoPair($label, $value)
    {
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(50, 6, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $label), 0, 0);
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 6, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $value), 0, 1);
    }

    // MAIN GENERATION FUNCTION
    public static function generateForParticipant($participant, $photos, $outputMode = 'S', $outputPath = '')
    {
        $pdf = new PDFGenerator();
        $pdf->AliasNbPages();
        $pdf->AddPage();

        // 1. HEADER INFO
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Dossier de Participation #' . $participant['id']), 0, 1, 'L');
        $pdf->Ln(2);

        $pdf->InfoPair("Candidat :", $participant['firstname'] . ' ' . $participant['lastname']);
        $pdf->InfoPair("Organisme :", $participant['company'] ?: 'N/A');
        $pdf->InfoPair("Adresse :", $participant['address']);
        $pdf->InfoPair("Email :", $participant['email']);
        $pdf->InfoPair("Date de soumission :", $participant['created_at']);
        $pdf->Ln(5);
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Ln(5);

        // 2. DECLARATION
        $pdf->SectionTitle("1. Déclaration d'Acceptation");
        $declaration = "Je soussigné(e) " . strtoupper($participant['lastname']) . " " . $participant['firstname'] . ", agissant en mon nom personnel ou en qualité de représentant habilité de l'organisme mentionné ci-dessus :\n\n";
        $declaration .= "1. Confirme ma participation au concours photo \"Barrages : Entre nature et architecture\".\n";
        $declaration .= "2. Reconnais avoir pris connaissance dans son intégralité du règlement 2026 ci-après et l'accepter sans réserve.\n";
        $declaration .= "3. Certifie l'exactitude des informations transmises.";
        $pdf->SectionBody($declaration);

        // 3. REGLEMENT COMPLET
        $pdf->AddPage();
        $pdf->SectionTitle("2. Règlement du Concours 2026");
        $reglementPath = __DIR__ . '/../assets/reglement_2026.txt';
        $reglementText = file_exists($reglementPath) ? file_get_contents($reglementPath) : "Erreur : Règlement introuvable.";
        $pdf->SectionBody($reglementText);

        // 4. ANNEXE A
        $pdf->AddPage();
        $pdf->SectionTitle("3. Annexe A : Cession de Droits d'Auteur");

        $annexAHeader = "Entre les soussignés :\n";
        $annexAHeader .= "1. Le Cédant : " . $participant['firstname'] . " " . $participant['lastname'] . "\n";
        $annexAHeader .= "2. Le Cessionnaire : Le Comité Français des Barrages et Réservoirs (CFBR)\n\n";
        $annexAHeader .= "OBJET DE LA CESSION :\n";
        $annexAHeader .= "L'Auteur cède au CFBR les droits d'exploitation (reproduction, représentation, adaptation) des photographies listées ci-dessous, pour le monde entier et la durée légale des droits d'auteur, à titre gratuit et non exclusif, à des fins de promotion des activités du CFBR.\n\n";
        $pdf->SectionBody($annexAHeader);

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', "LISTE DES ŒUVRES SOUMISES PAR L'AUTEUR :"), 0, 1, 'L');
        $pdf->SetFont('Arial', '', 9);

        // Category Mapping
        $categoryMap = [
            'cat1' => "Intégration Environnementale",
            'cat2' => "Hommes & Femmes de l'Art"
        ];

        // Table Header
        $pdf->SetFillColor(240, 240, 240);
        $pdf->Cell(15, 7, '#', 1, 0, 'C', true);
        $pdf->Cell(60, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Titre de l\'œuvre'), 1, 0, 'L', true);
        $pdf->Cell(60, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Catégorie'), 1, 0, 'L', true);
        $pdf->Cell(55, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Fichier'), 1, 1, 'L', true);

        // Table Rows
        foreach ($photos as $i => $p) {
            $catLabel = $categoryMap[$p['category']] ?? $p['category']; // Fallback to code if unknown
            $pdf->Cell(15, 7, $i + 1, 1, 0, 'C');
            $pdf->Cell(60, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', substr($p['title'] ?: $p['filename_original'], 0, 35)), 1, 0, 'L');
            $pdf->Cell(60, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', substr($catLabel, 0, 35)), 1, 0, 'L');
            $pdf->Cell(55, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', substr($p['filename_original'], 0, 30)), 1, 1, 'L');
        }
        $pdf->Ln(10);

        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(80, 80, 80);
        $pdf->Cell(0, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', "(Les fichiers sont conservés sur les serveurs sécurisés du CFBR)"), 0, 1, 'R');
        $pdf->SetTextColor(0);
        $pdf->Ln(5);

        $statusAnnexA = ($participant['agree_annex_a']) ? "[ X ] BON POUR ACCORD ET SIGNATURE" : "[ ] NON SIGNÉ";
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $statusAnnexA), 0, 1, 'R');

        // 5. ANNEXE B output
        $pdf->Ln(5);
        $pdf->SectionTitle("4. Annexe B : Droit à l'image");
        $annexBText = "TITRE : Autorisation d'exploitation de l'image d'une personne.\n";
        $annexBText .= "Si des personnes sont identifiables sur les photos, l'Auteur garantit avoir obtenu leur autorisation écrite explicite.\n\n";

        if (!empty($participant['identifiable_persons'])) {
            $annexBText .= "PERSONNES IDENTIFIÉES DÉCLARÉES PAR L'AUTEUR :\n" . $participant['identifiable_persons'] . "\n";
        } else {
            $annexBText .= "Aucune personne identifiable n'a été déclarée par l'auteur.\n";
        }
        $pdf->SectionBody($annexBText);

        $statusAnnexB = ($participant['agree_annex_b']) ? "[ X ] JE CERTIFIE SUR L'HONNEUR" : "[ ] NON CERTIFIÉ";
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $statusAnnexB), 0, 1, 'R');

        // 6. FOOTER SIGNATURE
        $pdf->Ln(15);
        $pdf->SetDrawColor(200, 200, 200);
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Ln(5);

        $pdf->SetFont('Courier', '', 9);
        $pdf->SetTextColor(100, 100, 100);
        $signatureText = "Ce document a été généré automatiquement par la plateforme CFBR le " . date('d/m/Y à H:i:s') . ".\n";
        $signatureText .= "Signature numérique validée par l'acceptation des CGU et le dépôt du dossier.\n";
        $signatureText .= "IP au moment de la signature : " . ($participant['ip'] ?? 'N/A');

        $pdf->MultiCell(0, 4, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $signatureText), 0, 'R');

        return $pdf->Output($outputMode, $outputPath);
    }
}
