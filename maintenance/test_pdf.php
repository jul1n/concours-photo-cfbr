<?php
require __DIR__ . '/../fpdf/fpdf.php';

// -----------------------------------------------------------------------------
// DUMMY DATA FOR PROTOTYPING
// -----------------------------------------------------------------------------
$participant = [
    'id' => 1234,
    'firstname' => 'Jean',
    'lastname' => 'DUPONT',
    'company' => 'Hydro Ouest Solutions', // Optional
    'address' => '10 Rue de la Turbine, 75011 Paris',
    'email' => 'jean.dupont@example.com',
    'created_at' => date('Y-m-d H:i:s'),
    'agree_annex_a' => 1,
    'agree_annex_b' => 1,
    'identifiable_persons' => 'Marie Curie (Photo 1), Pierre Curie (Photo 3)'
];

$photos = [
    ['filename' => 'barrage_vougeglans.jpg', 'title' => 'Lumière sur Vouglans', 'category' => 'Intégration Environnementale'],
    ['filename' => 'technicien_cordiste.jpg', 'title' => 'Inspection acrobatique', 'category' => 'Femmes & Hommes de l\'Art'],
    ['filename' => 'turbine_kaplan.jpg', 'title' => 'Cœur d\'acier', 'category' => 'Femmes & Hommes de l\'Art']
];

// -----------------------------------------------------------------------------
// PDF CLASS DEFINITION
// -----------------------------------------------------------------------------
class PDF extends FPDF
{
    function Header()
    {
        // Logo
        if (file_exists('assets/logo_cfbr_100_ans.png')) {
            $this->Image('assets/logo_cfbr_100_ans.png', 10, 6, 30);
        }
        // Police Arial gras 15
        $this->SetFont('Arial', 'B', 15);
        // Décalage à droite
        $this->Cell(40);
        // Titre
        $this->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Concours Photo CFBR 2026'), 0, 1, 'C');
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Accord de Participation & Cessions'), 0, 1, 'C');
        $this->Ln(20);
    }

    function Footer()
    {
        // Positionnement à 1,5 cm du bas
        $this->SetY(-15);
        // Police Arial italique 8
        $this->SetFont('Arial', 'I', 8);
        // Numéro de page
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
        $this->SetFont('Times', '', 10); // Serif font for reading regulations looks more "official"
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
}

// -----------------------------------------------------------------------------
// MAIN GENERATION LOGIC
// -----------------------------------------------------------------------------

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

// 1. HEADER INFO (MAIL MERGE STYLE)
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
$declaration .= "2. Reconnais avoir pris connaissance dans son intégralité du règlement 2026-2027 ci-après et l'accepter sans réserve.\n";
$declaration .= "3. Certifie l'exactitude des informations transmises.";
$pdf->SectionBody($declaration);


// 3. REGLEMENT COMPLET
$pdf->AddPage();
$pdf->SectionTitle("2. Règlement du Concours Photo ouvert au public 2026-2027 - CFBR");

$reglementPath = __DIR__ . '/../assets/reglement_2026.txt';
if (file_exists($reglementPath)) {
    $reglementText = file_get_contents($reglementPath);
} else {
    $reglementText = "ERREUR: Le fichier source du règlement (assets/reglement_2026.txt) est introuvable sur le serveur.";
}
$pdf->SectionBody($reglementText);


// 4. ANNEXE A
$pdf->AddPage();
$pdf->SectionTitle("3. Annexe A : Cession de Droits d'Auteur");

$annexAHeader = "Entre les soussignés :\n";
$annexAHeader .= "1. Le Cédant : " . $participant['firstname'] . " " . $participant['lastname'] . "\n";
$annexAHeader .= "2. Le Cessionnaire : Le Comité Français des Barrages et Réservoirs (CFBR)\n\n";
$annexAHeader .= "OBJET DE LA CESSION :\n";
$annexAHeader .= "L'Auteur autorise le CFBR à exercer les droits d'exploitation (reproduction, représentation, adaptation technique) des photographies listées ci-dessous, pour le monde entier et pour une durée de dix (10) ans à compter de sa signature, à titre gratuit et non exclusif, à des fins non commerciales de promotion des activités du CFBR.\n\n";

$pdf->SectionBody($annexAHeader);

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 8, "LISTE DES ŒUVRES SOUMISES PAR L'AUTEUR :", 0, 1, 'L');
$pdf->SetFont('Arial', '', 9);

// Table Header
$pdf->SetFillColor(240, 240, 240);
$pdf->Cell(15, 7, '#', 1, 0, 'C', true);
$pdf->Cell(60, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Titre de l\'œuvre'), 1, 0, 'L', true);
$pdf->Cell(60, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Catégorie'), 1, 0, 'L', true);
$pdf->Cell(55, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Fichier'), 1, 1, 'L', true);

// Table Rows
foreach ($photos as $i => $p) {
    $pdf->Cell(15, 7, $i + 1, 1, 0, 'C');
    $pdf->Cell(60, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', substr($p['title'], 0, 35)), 1, 0, 'L');
    $pdf->Cell(60, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', substr($p['category'], 0, 35)), 1, 0, 'L');
    $pdf->Cell(55, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', substr($p['filename'], 0, 30)), 1, 1, 'L');
}
$pdf->Ln(10);

$statusAnnexA = ($participant['agree_annex_a']) ? "[ X ] BON POUR ACCORD ET SIGNATURE" : "[ ] NON SIGNÉ";
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $statusAnnexA), 0, 1, 'R');


// 5. ANNEXE B
$pdf->Ln(5);
$pdf->SectionTitle("4. Annexe B : Droit à l'image (Personnes)");
$annexBText = "TITRE : Autorisation d'exploitation de l'image d'une personne.\n";
$annexBText .= "Si des personnes sont identifiables sur les photos, l'Auteur garantit avoir obtenu leur autorisation écrite explicite.\n\n";

if (!empty($participant['identifiable_persons'])) {
    $annexBText .= "PERSONNES IDENTIFIÉES DÉCLARÉES PAR L'AUTEUR :\n";
    $annexBText .= $participant['identifiable_persons'] . "\n";
} else {
    $annexBText .= "Aucune personne identifiable n'a été déclarée par l'auteur.\n";
}

$pdf->SectionBody($annexBText);
$pdf->Ln(5);
$statusAnnexB = ($participant['agree_annex_b']) ? "[ X ] JE CERTIFIE SUR L'HONNEUR" : "[ ] NON CERTIFIÉ";
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $statusAnnexB), 0, 1, 'R');


// 6. FINAL SIGNATURE FOOOTER
$pdf->Ln(15);
$pdf->SetDrawColor(200, 200, 200);
$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
$pdf->Ln(5);

$pdf->SetFont('Courier', '', 9);
$pdf->SetTextColor(100, 100, 100);
$signatureText = "Ce document a été généré automatiquement par la plateforme CFBR le " . date('d/m/Y à H:i:s') . ".\n";
$signatureText .= "Signature numérique validée par l'acceptation des CGU et le dépôt du dossier.\n";
$signatureText .= "IP au moment de la signature : 192.168.1.1 (Simulée)";
$pdf->MultiCell(0, 4, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $signatureText), 0, 'R');

// Output
$pdf->Output('I', 'Test_Modele_Reglement.pdf');
