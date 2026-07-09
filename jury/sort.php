<?php
// jury_classement.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['jury_logged_in']) || $_SESSION['jury_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../core/db.php';

try {
    // SYNTHESIS QUERY
    // Shows all photos with their details and scores
    $sql = "
        SELECT p.id, p.filename_original, p.filename_4k, p.filename_thumb, p.width, p.height, p.title, p.category, p.description, p.location,
               part.id as participant_id, part.firstname, part.lastname,
               v.nb_votes,
               avg_aesthetic,
               avg_theme,
               (COALESCE(avg_aesthetic, 0) + COALESCE(avg_theme, 0)) as total_score
        FROM photos p
        LEFT JOIN participants part ON p.participant_id = part.id
        LEFT JOIN (
            SELECT photo_id, 
                   AVG(score_aesthetic) as avg_aesthetic, 
                   AVG(score_theme) as avg_theme,
                   COUNT(photo_id) as nb_votes
            FROM jury_votes_analytics 
            GROUP BY photo_id
        ) v ON p.id = v.photo_id
        WHERE (COALESCE(avg_aesthetic, 0) + COALESCE(avg_theme, 0)) > 0
        ORDER BY total_score DESC
    ";
    $stmt = $pdo->query($sql);
    $rankings = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Erreur DB");
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Synthèse - Classement Général</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Open+Sans:wght@400;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <?php include __DIR__ . '/../includes/pwa_loader.php'; ?>
</head>

<body class="bg-gray-100 font-sans pb-20">

    <?php
    $activeTab = 'sort';
    $headerTitle = "Synthèse des Votes";
    include __DIR__ . '/header.php';
    ?>

    <div class="container mx-auto p-4 max-w-5xl">

        <!-- Export Toolbar -->
        <div class="mb-8 flex flex-wrap gap-4 items-center justify-between bg-white p-4 rounded shadow">
            <div class="flex items-center gap-2">
                <i class="fas fa-file-archive text-[#0A2240] text-xl"></i>
                <span class="font-bold text-[#0A2240]">Exports ZIP :</span>
            </div>
            <div class="flex flex-wrap gap-2">
                <?php foreach ([3, 10, 25, 50, 100] as $lim): ?>
                    <a href="../admin/export_zip.php?limit=<?= $lim ?>" target="_blank"
                        class="bg-blue-50 text-blue-800 border border-blue-200 px-3 py-1 rounded text-sm font-bold hover:bg-blue-100 transition">
                        Top <?= $lim ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="w-px h-8 bg-gray-300 mx-2 hidden md:block"></div>

            <div class="flex items-center gap-2">
                <a href="../admin/export_pdf.php" target="_blank"
                    class="bg-red-600 text-white px-4 py-2 rounded font-bold hover:bg-red-700 shadow flex items-center">
                    <i class="fas fa-file-pdf mr-2"></i> Rapport Complet (PDF)
                </a>
            </div>
        </div>

        <h2 class="text-2xl font-bold text-[#0A2240] text-center mb-4"><i class="fas fa-chart-bar mr-2 text-slate-500"></i>Synthèse des votes (Moyenne des Notes)</h2>

        <!-- Notice Étape Intermédiaire -->
        <div class="max-w-4xl mx-auto mb-8 bg-blue-50 border-l-4 border-blue-500 text-blue-800 p-4 rounded-r-lg shadow-sm flex items-start gap-3">
            <i class="fas fa-info-circle text-lg mt-0.5"></i>
            <div>
                <p class="font-bold text-sm">Classement Intermédiaire (Tour 2)</p>
                <p class="text-xs text-blue-700 mt-0.5">Il s'agit d'une étape intermédiaire basée sur la moyenne des notes attribuées par les jurés. Le classement final et définitif sera proclamé lors des délibérations à l'étape suivante.</p>
            </div>
        </div>

        <div class="bg-white shadow rounded overflow-hidden">
            <table class="min-w-full leading-normal">
                <thead>
                    <tr
                        class="bg-gray-50 border-b border-gray-200 text-xs font-bold text-gray-500 uppercase tracking-wider text-left">
                        <th class="px-5 py-3">Rang</th>
                        <th class="px-5 py-3">Photo</th>
                        <th class="px-5 py-3">Titre / Candidat</th>
                        <th class="px-5 py-3 text-center">Bts / Nb</th>
                        <th class="px-5 py-3 text-center">Esth.</th>
                        <th class="px-5 py-3 text-center">Thème</th>
                        <th class="px-5 py-3 text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rankings as $idx => $r):
                        $thumbSrc = '../photos/thumbs/' . $r['filename_thumb'];
                        ?>
                        <tr class="border-b border-gray-200 hover:bg-gray-50 transition">
                            <td class="px-5 py-4 text-sm font-bold text-gray-400">
                                #
                                <?= $idx + 1 ?>
                            </td>
                            <td class="px-5 py-4">
                                <img src="<?= $thumbSrc ?>" loading="lazy" class="w-16 h-16 object-cover rounded border">
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-bold text-[#0A2240]">
                                    <?= htmlspecialchars($r['title'] ?: 'Sans Titre') ?>
                                </div>
                                <div class="text-xs text-gray-500">
                                    Candidat n°<?= $r['participant_id'] ?>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-center text-xs">
                                <div class="font-bold">
                                    <?= $r['nb_votes'] ?>
                                </div>
                                <span class="text-gray-400">vote(s)</span>
                            </td>
                            <td class="px-5 py-4 text-center text-sm">
                                <?= number_format($r['avg_aesthetic'], 2) ?>
                            </td>
                            <td class="px-5 py-4 text-center text-sm">
                                <?= number_format($r['avg_theme'], 2) ?>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <span class="text-lg font-bold text-[#FF9900]">
                                    <?= number_format($r['total_score'], 2) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
</body>

</html>