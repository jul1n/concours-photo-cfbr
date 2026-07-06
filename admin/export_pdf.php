<?php
// admin_export_pdf.php
session_start();
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../fpdf/fpdf.php';

// Security Check
$isUnlocked = (isset($_SESSION['admin_unlocked']) && $_SESSION['admin_unlocked'] === true) || (isset($_SESSION['maintenance_authed']) && $_SESSION['maintenance_authed'] === true);
if (!$isUnlocked) {
    die("Accès refusé. Veuillez déverrouiller depuis la page de résultats.");
}

// UTF-8 to ISO-8859-1 conversion helper for FPDF
function toIso($str) {
    return mb_convert_encoding($str ?? '', 'ISO-8859-1', 'UTF-8');
}

// Custom PDF Class
class PDF extends FPDF
{
    function Header()
    {
        // Logo on the left
        $logoPath = __DIR__ . '/../assets/logo_cfbr_100_ans.png';
        if (file_exists($logoPath)) {
            $this->Image($logoPath, 10, 8, 38);
        }
        
        // Identité Concours aligned to the right
        $this->SetY(10);
        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor(10, 34, 64); // Brand Blue #0A2240
        $this->Cell(0, 8, toIso('CONCOURS PHOTO CFBR 2026'), 0, 1, 'R');
        
        $this->SetFont('Arial', 'B', 11);
        $this->SetTextColor(255, 153, 0); // Orange Brand #FF9900
        $this->Cell(0, 5, toIso('RAPPORT DÉTAILLÉ DU JURY'), 0, 1, 'R');
        
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 4, toIso('Généré le ' . date('d/m/Y H:i')), 0, 1, 'R');
        
        // Orange divider line
        $this->SetDrawColor(255, 153, 0); // Orange Brand #FF9900
        $this->SetLineWidth(0.8);
        $this->Line(10, 31, 200, 31);
        
        // Set cursor Y below the header
        $this->SetY(36);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

// Fetch Data
try {
    // 1. Rankings (with Tour 1 scores calculated)
    $sqlRanking = "
        SELECT p.*, 
               SUM(v.points) as total_points, 
               (SELECT COALESCE(SUM(score_aesthetic) + SUM(score_theme), 0) FROM jury_votes_analytics WHERE photo_id = p.id) as score_tour1,
               pa.firstname, pa.lastname
        FROM photos p
        JOIN votes_tour2 v ON p.id = v.photo_id
        JOIN participants pa ON p.participant_id = pa.id
        GROUP BY p.id
        ORDER BY total_points DESC
    ";
    $rankings = $pdo->query($sqlRanking)->fetchAll(PDO::FETCH_ASSOC);

    // 2. Detailed Votes Tour 1 (Aesthetics/Theme)
    $sqlTour1 = "SELECT * FROM jury_votes_analytics ORDER BY photo_id";
    $votesTour1 = $pdo->query($sqlTour1)->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC); // Group by photo_id

    // 3. Detailed Votes Tour 2 (Ranking)
    $sqlTour2 = "SELECT * FROM votes_tour2 ORDER BY photo_id";
    $votesTour2 = $pdo->query($sqlTour2)->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC); // Group by photo_id

} catch (Exception $e) {
    die("Erreur DB");
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

// SECTION 1: CLASSEMENT GÉNÉRAL
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(10, 34, 64); // Brand Blue
$pdf->SetTextColor(255);
$pdf->Cell(0, 8, toIso("  1. Classement Final"), 0, 1, 'L', true);
$pdf->SetTextColor(0);
$pdf->Ln(3);

$pdf->SetFillColor(255, 153, 0); // Orange Brand
$pdf->SetTextColor(255);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(15, 7, 'Rang', 1, 0, 'C', true);
$pdf->Cell(60, 7, 'Candidat', 1, 0, 'L', true);
$pdf->Cell(60, 7, toIso('Titre Photo'), 1, 0, 'L', true);
$pdf->Cell(25, 7, toIso('Note Tour 1'), 1, 0, 'C', true);
$pdf->Cell(25, 7, toIso('Points Tour 2'), 1, 1, 'C', true);
$pdf->SetTextColor(0);
$pdf->SetFont('Arial', '', 10);

foreach ($rankings as $idx => $row) {
    $pdf->Cell(15, 7, '#' . ($idx + 1), 1, 0, 'C');
    $pdf->Cell(60, 7, toIso($row['firstname'] . ' ' . $row['lastname']), 1, 0);
    $title = $row['title'] ?: 'Sans Titre';
    if (strlen($title) > 30)
        $title = substr($title, 0, 27) . '...';
    $pdf->Cell(60, 7, toIso($title), 1, 0);
    $pdf->Cell(25, 7, number_format($row['score_tour1'], 1) . ' pts', 1, 0, 'C');
    $pdf->Cell(25, 7, $row['total_points'] . ' pts', 1, 1, 'C');
}

$pdf->AddPage();

// SECTION 2: DÉTAIL PAR PHOTO
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(10, 34, 64); // Brand Blue
$pdf->SetTextColor(255);
$pdf->Cell(0, 8, toIso("  2. Audit Détaillé des Votes"), 0, 1, 'L', true);
$pdf->SetTextColor(0);
$pdf->Ln(5);

foreach ($rankings as $idx => $row) {
    $pid = $row['id'];

    // Header Photo
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->SetFillColor(240, 244, 248); // Light gray-blue background
    $pdf->Cell(0, 8, toIso(' #' . ($idx + 1) . ' - ' . ($row['title'] ?: 'Sans Titre') . ' (ID: ' . $pid . ')'), 1, 1, 'L', true);

    // Info Candidat
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 6, toIso('Candidat: ' . $row['firstname'] . ' ' . $row['lastname']), 'LR', 1);

    // Votes Tour 1 (Si existent)
    if (isset($votesTour1[$pid])) {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 6, toIso('  > Tour 1 (Notation) :'), 'LR', 1);
        $pdf->SetFont('Arial', '', 9);
        foreach ($votesTour1[$pid] as $v) {
            $jury = $v['jury_identifier'];
            $aes = $v['score_aesthetic'];
            $theme = $v['score_theme'];
            $pdf->Cell(0, 5, toIso("    - Jury [$jury] : Esth. $aes | Thème $theme"), 'LR', 1);
        }
    } else {
        $pdf->Cell(0, 6, toIso('  > Pas de notes Tour 1'), 'LR', 1);
    }

    // Votes Tour 2 (Si existent)
    if (isset($votesTour2[$pid])) {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 6, toIso('  > Tour 2 (Classement) :'), 'LR', 1);
        $pdf->SetFont('Arial', '', 9);
        foreach ($votesTour2[$pid] as $v) {
            $jury = $v['jury_ip'];
            $points = $v['points'];
            $rank = $v['rank'];
            $pdf->Cell(0, 5, toIso("    - Jury [$jury] : Classé #$rank ($points pts)"), 'LR', 1);
        }
    }

    $pdf->Cell(0, 1, '', 'T', 1); // Separator
    $pdf->Ln(5);
}

$pdf->Output('I', 'Rapport_Audit_Concours.pdf'); // Changed output mode from 'D' (Download) to 'I' (Inline inline display) for cleaner experience
?>