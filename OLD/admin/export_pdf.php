<?php
// admin_export_pdf.php
session_start();
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../fpdf/fpdf.php';

// Security Check
if (!isset($_SESSION['admin_unlocked']) || $_SESSION['admin_unlocked'] !== true) {
    die("Accès refusé. Veuillez déverrouiller depuis la page de résultats.");
}

// Custom PDF Class
class PDF extends FPDF
{
    function Header()
    {
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(0, 10, utf8_decode('Rapport du Jury - Concours Photo CFBR'), 0, 1, 'C');
        $this->SetFont('Arial', 'I', 10);
        $this->Cell(0, 10, 'Généré le ' . date('d/m/Y H:i'), 0, 1, 'C');
        $this->Ln(10);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

// Fetch Data
try {
    // 1. Rankings
    $sqlRanking = "
        SELECT p.*, SUM(v.points) as total_points, pa.firstname, pa.lastname
        FROM photos p
        JOIN votes_tour2 v ON p.id = v.photo_id
        JOIN participants pa ON p.participant_id = pa.id
        GROUP BY p.id
        ORDER BY total_points DESC
    ";
    $rankings = $pdo->query($sqlRanking)->fetchAll(PDO::FETCH_ASSOC);

    // 2. Detailed Votes Tour 1 (Aesthetics/Theme)
    // We want all votes per photo
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
$pdf->SetFont('Arial', '', 12);

// SECTION 1: CLASSEMENT GÉNÉRAL
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, utf8_decode('1. Classement Final'), 0, 1);
$pdf->SetFont('Arial', '', 10);

$pdf->SetFillColor(240, 240, 240);
$pdf->Cell(15, 7, 'Rang', 1, 0, 'C', true);
$pdf->Cell(80, 7, 'Candidat', 1, 0, 'L', true);
$pdf->Cell(70, 7, 'Titre Photo', 1, 0, 'L', true);
$pdf->Cell(20, 7, 'Points', 1, 1, 'C', true);

foreach ($rankings as $idx => $row) {
    $pdf->Cell(15, 7, '#' . ($idx + 1), 1, 0, 'C');
    $pdf->Cell(80, 7, utf8_decode($row['firstname'] . ' ' . $row['lastname']), 1, 0);
    $title = $row['title'] ?: 'Sans Titre';
    // Truncate title
    if (strlen($title) > 35)
        $title = substr($title, 0, 32) . '...';
    $pdf->Cell(70, 7, utf8_decode($title), 1, 0);
    $pdf->Cell(20, 7, $row['total_points'], 1, 1, 'C');
}

$pdf->AddPage();

// SECTION 2: DÉTAIL PAR PHOTO
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, utf8_decode('2. Audit Détaillé des Votes'), 0, 1);
$pdf->Ln(5);

foreach ($rankings as $idx => $row) {
    $pid = $row['id'];

    // Header Photo
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(230, 230, 250);
    $pdf->Cell(0, 10, utf8_decode('#' . ($idx + 1) . ' - ' . ($row['title'] ?: 'Sans Titre') . ' (ID: ' . $pid . ')'), 1, 1, 'L', true);

    // Info Candidat
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 6, utf8_decode('Candidat: ' . $row['firstname'] . ' ' . $row['lastname']), 'LR', 1);

    // Votes Tour 1 (Si existent)
    if (isset($votesTour1[$pid])) {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 6, utf8_decode('  > Tour 1 (Notation) :'), 'LR', 1);
        $pdf->SetFont('Arial', '', 9);
        foreach ($votesTour1[$pid] as $v) {
            $jury = $v['jury_identifier']; // IP or Email
            $aes = $v['score_aesthetic'];
            $theme = $v['score_theme'];
            $pdf->Cell(0, 5, utf8_decode("    - Jury [$jury] : Esth. $aes | Thème $theme"), 'LR', 1);
        }
    } else {
        $pdf->Cell(0, 6, utf8_decode('  > Pas de notes Tour 1'), 'LR', 1);
    }

    // Votes Tour 2 (Si existent)
    if (isset($votesTour2[$pid])) {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 6, utf8_decode('  > Tour 2 (Classement) :'), 'LR', 1);
        $pdf->SetFont('Arial', '', 9);
        foreach ($votesTour2[$pid] as $v) {
            $jury = $v['jury_ip'];
            $points = $v['points'];
            $rank = $v['rank'];
            $pdf->Cell(0, 5, utf8_decode("    - Jury [$jury] : Classé #$rank ($points pts)"), 'LR', 1);
        }
    }

    $pdf->Cell(0, 1, '', 'T', 1); // Separator
    $pdf->Ln(5);
}

$pdf->Output('D', 'Rapport_Audit_Concours.pdf');
?>