<?php
// jury_view_pdf.php
session_start();
if (!isset($_SESSION['jury_logged_in']) || $_SESSION['jury_logged_in'] !== true) {
    die("Accès refusé");
}

require_once 'db_connect.php';
require('fpdf/fpdf.php');

if (!isset($_GET['id'])) {
    die("ID manquant");
}

$id = (int) $_GET['id'];

// Fetch participant
$stmt = $pdo->prepare("SELECT * FROM participants WHERE id = ?");
$stmt->execute([$id]);
$participant = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$participant) {
    die("Participant introuvable");
}

// Fetch photos for this participant
$stmtPhotos = $pdo->prepare("SELECT * FROM photos WHERE participant_id = ?");
$stmtPhotos->execute([$id]);
$photos = $stmtPhotos->fetchAll(PDO::FETCH_ASSOC);

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
$pdf->Ln(5);

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


// --- ANNEXE A FILLED ---
$pdf->AddPage();
$pdf->SectionTitle("4. Annexe A : Formulaire de Cession de Droits d'Auteur");

$annexAText = "Ce document unique, valable pour l'ensemble des photographies soumises (jusqu'à 5), est à remplir et signer par chaque participant ou représentant d'organisme.\n\n";
$annexAText .= "Titre : Cession de Droits d'Auteur à Titre Gratuit et Non Exclusif\n\n";

$annexAText .= "Entre les soussignés :\n";
$annexAText .= "Le Cédant :\n";
$annexAText .= "Nom et Prénom du Photographe (ou Nom de l'Organisme) : " . mb_strtoupper($participant['lastname']) . " " . $participant['firstname'] . "\n";
$annexAText .= "Adresse postale : " . $participant['address'] . "\n";
$annexAText .= "Adresse e-mail : " . $participant['email'] . "\n";
$annexAText .= "Ci-après dénommé « l'Auteur »,\n\n";

$annexAText .= "Et le Cessionnaire :\n";
$annexAText .= "Le Comité Français des Barrages et Réservoirs (CFBR)\n";
$annexAText .= "Adresse : Savoie Technolac, 4 allée du Lac de Tignes, 73290 La Motte-Servolex. Ci-après dénommé « le CFBR »,\n\n";

$annexAText .= "Article 1 : Objet de la cession\n";
$annexAText .= "Le présent contrat a pour objet la cession des droits d'exploitation de la ou des photographie(s) suivante(s), dans le cadre du Concours Photo Grand Public 2026 organisé par le CFBR :\n\n";

// Fill Photos
$pCount = 1;
foreach ($photos as $p) {
    $title = $p['title'] ?: $p['filename'];
    $annexAText .= "Nom du fichier $pCount : " . $p['filename_original'] . "\n";
    $annexAText .= "Titre : " . $title . "\n\n";
    $pCount++;
}
// Fill empty slots up to 5
for ($j = $pCount; $j <= 5; $j++) {
    $annexAText .= "Nom du fichier $j : (Vide)\n";
    $annexAText .= "Titre : (Vide)\n\n";
}

$annexAText .= "Article 2 : Droits cédés\n";
$annexAText .= "L'Auteur cède au CFBR, pour les photographies présélectionnées et/ou primées, les droits patrimoniaux suivants :\n";
$annexAText .= "- Le droit de reproduction (fixer, dupliquer, copier sur tous supports).\n";
$annexAText .= "- Le droit de représentation (exposition, diffusion web, réseaux sociaux).\n";
$annexAText .= "- Le droit d'adaptation (recadrage, colorimétrie pour l'impression).\n\n";

$annexAText .= "Article 3 : Étendue de la cession\n";
$annexAText .= "Cette cession est consentie à titre non exclusif, à titre gratuit, pour le monde entier et pour la durée légale de protection des droits d'auteur, pour une exploitation sur tous les supports de communication du CFBR dans un but non commercial.\n\n";

$dateSign = date('d/m/Y', strtotime($participant['created_at']));
$annexAText .= "Fait à : (Signature Électronique) Le : $dateSign\n";

$signatureStatus = ($participant['agree_annex_a']) ? "LU ET APPROUVE (Signé Numériquement)" : "NON SIGNE";
$annexAText .= "Signature de l'Auteur : " . $signatureStatus . "\n";

$pdf->SectionBody($annexAText);


// --- ANNEXE B (Template / Notice) ---
$pdf->AddPage();
$pdf->SectionTitle("5. Annexe B : Autorisation de Droit à l'Image");

$annexBText = "Ce document est à remplir et signer uniquement si une ou plusieurs personnes sont clairement identifiables sur une photographie.\n";
$annexBText .= "Le participant certifie avoir obtenu les autorisations nécessaires pour les personnes identifiables déclarées ci-dessous.\n\n";

$annexBText .= "PERSONNES IDENTIFIABLES DÉCLARÉES PAR LE PARTICIPANT :\n";
if (!empty($participant['identifiable_persons'])) {
    $annexBText .= $participant['identifiable_persons'] . "\n\n";
} else {
    $annexBText .= "Néant (ou non renseigné)\n\n";
}

$annexBText .= "----------------------------------------------------------------\n";
$annexBText .= "MODÈLE D'AUTORISATION (REFERENCE) :\n\n";
$annexBText .= "Titre : Autorisation d'Utilisation de l'Image d'une Personne\n\n";
$annexBText .= "Je soussigné(e),\n";
$annexBText .= "Nom et Prénom du modèle : ..............................................................................................................\n";
$annexBText .= "Adresse postale : ................................................................................................................................\n";
$annexBText .= "Date de naissance : .... / .... / ..........\n\n";
$annexBText .= "Autorise par la présente, à titre gracieux, M./Mme " . mb_strtoupper($participant['lastname']) . " " . $participant['firstname'] . " (nom du photographe) ainsi que le Comité Français des Barrages et Réservoirs (CFBR) à utiliser, reproduire et diffuser mon image, représentée sur [LES PHOTOGRAPHIES SOUMISES], prise dans le cadre du Concours Photo CFBR 2026.\n\n";
$annexBText .= "Cette autorisation est valable pour une utilisation à des fins non commerciales de communication et de promotion liées au concours et aux activités du CFBR, sur tous supports, pour le monde entier et la durée légale de protection des droits d'auteur.\n\n";

$annexBText .= "Signature du participant garantissant l'obtention des droits :\n";
$annexBStatus = ($participant['agree_annex_b']) ? "CERTIFIÉ SUR L'HONNEUR (Signé Numériquement)" : "NON CERTIFIÉ";
$annexBText .= $annexBStatus;

$pdf->SectionBody($annexBText);


// 5. Signature Footer
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, "SIGNATURE ELECTRONIQUE", 0, 1, 'R');
$pdf->SetFont('Courier', '', 10);
$pdf->Cell(0, 5, "Token: " . $participant['validation_token'], 0, 1, 'R');
$pdf->Cell(0, 5, "IP: " . $participant['ip'], 0, 1, 'R');
$pdf->Cell(0, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', "Signé le: " . date('d/m/Y H:i:s', strtotime($participant['created_at']))), 0, 1, 'R');

$pdf->Output('I', 'Reglement_' . $participant['lastname'] . '_' . $participant['id'] . '.pdf');

?>