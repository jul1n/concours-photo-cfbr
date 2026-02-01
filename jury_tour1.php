<?php
// jury_tour1.php
session_start();
if (!isset($_SESSION['jury_logged_in']) || $_SESSION['jury_logged_in'] !== true) {
    header("Location: jury_login.php");
    exit;
}

require_once 'db_connect.php';

// Category Mapping
$categoryMap = [
    'cat1' => "Intégration Environnementale",
    'cat2' => "Hommes & Femmes de l'Art"
];

// Helper to analyze photo quality
function analyzePhotoQuality($p)
{
    $quality = ['badges' => [], 'warnings' => []];

    // 1. Resolution Check (Target A3 @ 300DPI approx 3500px short side, or 12MP+)
    // A3 is ~11.7 x 16.5 inches. 
    // 300 DPI -> 3510 x 4950 px (~17.4 MP) -> Perfect
    // 200 DPI -> 2340 x 3300 px (~7.7 MP) -> Acceptable

    $width = (int) $p['width'];
    $height = (int) $p['height'];
    $mp = ($width * $height) / 1000000;

    // Use the shortest side as a solid constraint for print width
    $shortSide = min($width, $height);

    if ($shortSide >= 3000 || $mp >= 16) {
        $quality['badges'][] = [
            'text' => 'A3 Haute Qualité',
            'color' => 'bg-green-100 text-green-800 border-green-200',
            'icon' => 'fas fa-print'
        ];
    } elseif ($shortSide >= 2300 || $mp >= 8) {
        $quality['badges'][] = [
            'text' => 'A3 Standard (OK)',
            'color' => 'bg-blue-50 text-blue-700 border-blue-200',
            'icon' => 'fas fa-check'
        ];
    } else {
        $quality['badges'][] = [
            'text' => 'Résolution Faible (Risque A3)',
            'color' => 'bg-red-50 text-red-700 border-red-200',
            'icon' => 'fas fa-exclamation-triangle'
        ];
    }

    // 2. Metadata / Upscale Detection
    $filePath = __DIR__ . '/photos/originals/' . $p['filename_original'];
    $upscaleKeywords = ['topaz', 'gigapixel', 'ai', 'upscale', 'enhance', 'waifu', 'remini'];
    $foundKeywords = [];

    // Check DB flag first
    if (!empty($p['is_upscale_suspect']) && $p['is_upscale_suspect'] == 1) {
        $foundKeywords[] = "Ratio Poids/Pixel Anormal";
    }

    // Check Exif if file exists
    if (file_exists($filePath) && function_exists('exif_read_data')) {
        // Suppress warnings for missing exif
        $exif = @exif_read_data($filePath, 0, true);

        if ($exif) {
            // Flatten relevant fields to string for search
            $searchString = "";
            if (isset($exif['IFD0']['Software']))
                $searchString .= " " . $exif['IFD0']['Software'];
            if (isset($exif['IFD0']['ImageDescription']))
                $searchString .= " " . $exif['IFD0']['ImageDescription'];
            if (isset($exif['COMPUTED']['UserComment']))
                $searchString .= " " . $exif['COMPUTED']['UserComment'];

            $searchString = strtolower($searchString);

            foreach ($upscaleKeywords as $kw) {
                if (strpos($searchString, $kw) !== false) {
                    $foundKeywords[] = ucfirst($kw);
                }
            }
        }
    }

    if (!empty($foundKeywords)) {
        $quality['warnings'][] = "Suspicion IA/Upscale: " . implode(', ', array_unique($foundKeywords));
    }

    return $quality;
}

try {
    // 1. Fetch Candidates (Pending)
    // We select all fields to get firstname/lastname/company
    $stmt = $pdo->query("SELECT * FROM participants WHERE validation_status = 'pending' ORDER BY id ASC");
    $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$candidates)
        $candidates = [];

    // 2. Prepare Data & Duplicate Detection
    $emailCounts = [];
    $nameCounts = [];

    // Enrich candidates with their photos
    foreach ($candidates as &$c) {
        $c['fullname'] = trim($c['firstname'] . ' ' . $c['lastname']);
        if (empty($c['fullname']))
            $c['fullname'] = $c['name'] ?? 'Inconnu'; // Fallback

        // Count for duplicates
        $email = strtolower(trim($c['email']));
        $lastname = strtolower(trim($c['lastname']));

        if (!isset($emailCounts[$email]))
            $emailCounts[$email] = 0;
        $emailCounts[$email]++;

        if (!isset($nameCounts[$lastname]))
            $nameCounts[$lastname] = 0;
        $nameCounts[$lastname]++;

        // Fetch Photos for this candidate
        $stmtPhotos = $pdo->prepare("SELECT * FROM photos WHERE participant_id = ?");
        $stmtPhotos->execute([$c['id']]);
        $c['photos'] = $stmtPhotos->fetchAll(PDO::FETCH_ASSOC);

        // Determine main category (from first photo or mixed)
        $cats = [];
        foreach ($c['photos'] as $p) {
            if (!empty($p['category']))
                $cats[] = $p['category'];
        }
        $cats = array_unique($cats);
        $displayCats = [];
        foreach ($cats as $catCode) {
            $displayCats[] = $categoryMap[$catCode] ?? $catCode;
        }
        $c['category_label'] = !empty($displayCats) ? implode(', ', $displayCats) : 'Non défini';
    }
    unset($c); // Break reference

    // Handle Actions (POST)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $candidateId = $_POST['candidate_id'];
        $action = $_POST['action'];
        $juryId = 1;

        if ($action === 'approve') {
            $stmt = $pdo->prepare("UPDATE participants SET validation_status = 'approved', jury_vote_1_by = ? WHERE id = ?");
            $stmt->execute([$juryId, $candidateId]);
        } elseif ($action === 'reject') {
            $stmt = $pdo->prepare("UPDATE participants SET validation_status = 'pre_rejected', jury_vote_1_by = ? WHERE id = ?");
            $stmt->execute([$juryId, $candidateId]);
        }
        header("Location: jury_tour1.php");
        exit;
    }

} catch (Exception $e) {
    die("Erreur DB: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Jury - Tour 1 (Qualification)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Open+Sans:wght@400;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="bg-gray-100 font-sans">
    <!-- Header -->
    <header class="bg-[#0A2240] text-white p-4 shadow-md mb-8">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold font-title">Espace Jury - Qualification</h1>
            <a href="index.php" class="text-sm font-bold hover:text-[#FF9900] transition-colors">
                <i class="fas fa-home mr-1"></i> Retour Accueil
            </a>
        </div>
    </header>

    <div class="container mx-auto px-4 py-8">

        <?php if (empty($candidates)): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-md text-center max-w-2xl mx-auto"
                role="alert">
                <i class="fas fa-check-circle text-4xl mb-4 text-green-600"></i>
                <h2 class="text-xl font-bold mb-2">Terminé !</h2>
                <p class="mb-6">Aucun dossier en attente de validation.</p>
                <div>
                    <a href="jury_confirm_rejection.php"
                        class="bg-red-600 text-white px-6 py-3 rounded-full hover:bg-red-700 transition shadow-lg font-bold">
                        <i class="fas fa-exclamation-circle mr-2"></i>Voir les dossiers en attente de rejet (2ème avis)
                    </a>
                </div>
            </div>
        <?php else: ?>

            <div class="mb-6 text-sm text-gray-600 bg-blue-50 p-4 rounded border border-blue-200">
                <i class="fas fa-info-circle mr-2"></i> <strong>Mode Qualification :</strong> Validez ou rejetez le dossier
                complet (non-conformité, hors-sujet, etc.). Les dossiers validés passeront à l'étape suivante (Notation).
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($candidates as $candidate):
                    $email = strtolower(trim($candidate['email']));
                    $lastname = strtolower(trim($candidate['lastname']));
                    $isDuplicateEmail = ($emailCounts[$email] > 1);
                    $isDuplicateName = ($nameCounts[$lastname] > 1);
                    ?>
                    <div
                        class="bg-white rounded-lg shadow-xl overflow-hidden transform hover:scale-[1.01] transition duration-300 border border-gray-100 flex flex-col">
                        <!-- Header Dossier -->
                        <div class="bg-[#0A2240] text-white p-4 flex justify-between items-center relative">
                            <div>
                                <h3 class="font-bold text-lg leading-tight uppercase">
                                    <?= htmlspecialchars($candidate['fullname']) ?>
                                </h3>
                                <div class="text-xs text-orange-300 font-semibold mt-1">
                                    <i class="fas fa-tags mr-1"></i> <?= htmlspecialchars($candidate['category_label']) ?>
                                </div>
                                <div class="text-[10px] text-gray-400 mt-1">
                                    <i class="far fa-clock mr-1"></i> Soumis le
                                    <?= date('d/m/Y H:i', strtotime($candidate['created_at'])) ?>
                                </div>
                            </div>
                            <div class="flex flex-col items-end space-y-2">
                                <div class="text-xs bg-white text-[#0A2240] px-2 py-1 rounded font-bold">
                                    #<?= $candidate['id'] ?>
                                </div>
                                <a href="jury_view_pdf.php?id=<?= $candidate['id'] ?>" target="_blank"
                                    class="text-xs bg-red-600 text-white px-2 py-1 rounded hover:bg-red-500 transition shadow-sm flex items-center"
                                    title="Voir PDF Signé">
                                    <i class="fas fa-file-pdf mr-1"></i> PDF
                                </a>
                            </div>
                        </div>

                        <!-- ALERTS DOUBLONS -->
                        <?php if ($isDuplicateEmail || $isDuplicateName): ?>
                            <div class="bg-red-50 text-red-700 text-xs p-3 border-b border-red-200">
                                <?php if ($isDuplicateEmail): ?>
                                    <div class="font-bold mb-1"><i class="fas fa-exclamation-triangle mr-1"></i> Email dupliqué
                                        (<?= $emailCounts[$email] ?> dossiers)</div>
                                <?php endif; ?>
                                <?php if ($isDuplicateName): ?>
                                    <div class="font-bold"><i class="fas fa-user-friends mr-1"></i> Même nom de famille
                                        (<?= $nameCounts[$lastname] ?> dossiers)</div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Photos Grid -->
                        <div class="p-4 bg-gray-50 flex-grow">
                            <h4 class="text-xs font-bold text-gray-500 uppercase mb-2 border-b pb-1">
                                <?= count($candidate['photos']) ?> Photo(s) Soumise(s)
                            </h4>
                            <div class="space-y-4">
                                <?php foreach ($candidate['photos'] as $p):
                                    $link4k = !empty($p['filename_4k']) ? 'photos/display_4k/' . $p['filename_4k'] : '#';
                                    if ($link4k === '#')
                                        $link4k = 'photos/originals/' . $p['filename_original'];
                                    
                                    // Quality Check
                                    $q = analyzePhotoQuality($p);
                                    ?>
                                    <div class="bg-white p-3 rounded shadow-sm border border-gray-100">
                                        <div class="flex items-start space-x-3">
                                            <div class="flex-shrink-0 cursor-pointer group relative"
                                                onclick="window.open('<?= $link4k ?>', '_blank')">
                                                <img src="photos/thumbs/<?= $p['filename_thumb'] ?>"
                                                    class="w-24 h-24 object-cover rounded border border-gray-200 group-hover:opacity-80 transition">
                                                <div
                                                    class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 pointer-events-none">
                                                    <i class="fas fa-search-plus text-white drop-shadow-md"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow min-w-0">
                                                <div class="text-sm font-bold text-[#0A2240] truncate"
                                                    title="<?= htmlspecialchars($p['title']) ?>">
                                                    <?php if (empty($p['title']))
                                                        echo "Sans Titre";
                                                    else
                                                        echo htmlspecialchars($p['title']); ?>
                                                </div>
                                                
                                                <!-- Dimensions & Quality Badges -->
                                                <div class="flex flex-wrap gap-2 mt-1 mb-1">
                                                    <span class="text-[10px] bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded border border-gray-200">
                                                        <?= $p['width'] ?> x <?= $p['height'] ?> px
                                                    </span>
                                                    <?php foreach ($q['badges'] as $badge): ?>
                                                        <span class="text-[10px] px-1.5 py-0.5 rounded border font-semibold flex items-center <?= $badge['color'] ?>">
                                                            <i class="<?= $badge['icon'] ?> mr-1"></i> <?= $badge['text'] ?>
                                                        </span>
                                                    <?php endforeach; ?>
                                                </div>

                                                <?php if (!empty($q['warnings'])): ?>
                                                    <div class="mt-1">
                                                        <?php foreach ($q['warnings'] as $w): ?>
                                                            <div class="text-[10px] text-red-600 font-bold bg-red-50 px-2 py-1 rounded inline-block border border-red-100 animate-pulse">
                                                                <i class="fas fa-robot mr-1"></i> <?= $w ?>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php endif; ?>

                                                <?php if (!empty($p['description'])): ?>
                                                    <p class="text-xs text-gray-500 mt-1 line-clamp-2"
                                                        title="<?= htmlspecialchars($p['description']) ?>">
                                                        <?= htmlspecialchars($p['description']) ?>
                                                    </p>
                                                <?php endif; ?>
                                                <?php if (!empty($p['location'])): ?>
                                                    <div class="text-[10px] text-gray-400 mt-1"><i class="fas fa-map-marker-alt mr-1"></i>
                                                        <?= htmlspecialchars($p['location']) ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Info Supplémentaire -->
                        <div class="px-4 py-3 text-sm text-gray-700 bg-white border-t border-gray-100">
                            <div class="grid grid-cols-1 gap-2">
                                <div class="flex items-center" title="Email">
                                    <i class="fas fa-envelope w-5 text-gray-400 text-center"></i>
                                    <span class="truncate font-medium"><?= htmlspecialchars($candidate['email']) ?></span>
                                </div>
                                <?php if (!empty($candidate['company'])): ?>
                                    <div class="flex items-center text-blue-800 font-bold">
                                        <i class="fas fa-building w-5 text-center"></i>
                                        <span><?= htmlspecialchars($candidate['company']) ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Checkboxes -->
                            <div class="mt-3 pt-2 border-t border-gray-100 flex items-center justify-between text-xs">
                                <div class="flex items-center space-x-4">
                                    <div
                                        class="<?= $candidate['agree_annex_a'] ? 'text-green-600 font-bold' : 'text-red-500 op-50' ?>">
                                        <i class="fas <?= $candidate['agree_annex_a'] ? 'fa-check-square' : 'fa-square' ?>"></i>
                                        Annexe A
                                    </div>
                                    <div
                                        class="<?= $candidate['agree_annex_b'] ? 'text-green-600 font-bold' : 'text-gray-400' ?>">
                                        <i class="fas <?= $candidate['agree_annex_b'] ? 'fa-check-square' : 'fa-square' ?>"></i>
                                        Annexe B
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="p-4 bg-gray-50 border-t border-gray-200 flex justify-between space-x-3">
                            <form method="POST" class="w-1/2">
                                <input type="hidden" name="candidate_id" value="<?= $candidate['id'] ?>">
                                <input type="hidden" name="action" value="reject">
                                <button type="submit"
                                    onclick="return confirm('Attention : Ce dossier sera envoyé en ré-examen pour rejet. Confirmer ?')"
                                    class="w-full bg-white border border-red-300 text-red-600 hover:bg-red-50 hover:border-red-400 px-4 py-2 rounded shadow-sm font-semibold transition text-sm">
                                    <i class="fas fa-times mr-1"></i> Rejeter
                                </button>
                            </form>

                            <form method="POST" class="w-1/2">
                                <input type="hidden" name="candidate_id" value="<?= $candidate['id'] ?>">
                                <input type="hidden" name="action" value="approve">
                                <button type="submit"
                                    class="w-full bg-green-600 text-white hover:bg-green-700 px-4 py-2 rounded shadow-md font-bold transition text-sm">
                                    <i class="fas fa-check mr-1"></i> Valider
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>
    </div>
</body>

</html>