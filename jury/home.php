<?php
// jury_tour1.php
require_once __DIR__ . '/../includes/analytics.php';
session_start();
if (!isset($_SESSION['jury_logged_in']) || $_SESSION['jury_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../core/db.php';

// --- Logic ---

$juryId = $_SERVER['REMOTE_ADDR']; // Or Session User ID if exists
if (isset($_SESSION['jury_email'])) {
    $juryId = $_SESSION['jury_email'];
}

// 1. Fetch Approved Participants & Photos
try {
    // Only fetch folks approved in Qualification
    $sql = "SELECT p.id, p.filename_original, p.filename_4k, p.filename_thumb, p.width, p.height, p.title, p.description, p.category, p.location, p.is_promo,
                   part.firstname, part.lastname, part.company, part.email, part.instagram, part.linkedin
            FROM photos p
            JOIN participants part ON p.participant_id = part.id
            WHERE part.validation_status = 'approved'
            ORDER BY part.id ASC";
    $stmt = $pdo->query($sql);
    $photos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Fetch Existing Votes for this Jury
    $sqlVotes = "SELECT * FROM jury_votes_analytics WHERE jury_identifier = ?";
    $stmtV = $pdo->prepare($sqlVotes);
    $stmtV->execute([$juryId]);
    $myVotes = $stmtV->fetchAll(PDO::FETCH_ASSOC);

    // Map votes by photo_id
    $votesMap = [];
    foreach ($myVotes as $v) {
        $votesMap[$v['photo_id']] = $v;
    }

} catch (Exception $e) {
    die("DB Error: " . $e->getMessage());
}

// Handle AJAX Vote Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_vote'])) {
    header('Content-Type: application/json');
    $photoId = intval($_POST['photo_id']);
    $aesthetic = floatval($_POST['aesthetic']);
    $theme = floatval($_POST['theme']);

    if ($aesthetic < 1 || $aesthetic > 10 || $theme < 1 || $theme > 10) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid values']);
        exit;
    }

    try {
        $sql = "INSERT INTO jury_votes_analytics (photo_id, jury_identifier, score_aesthetic, score_theme, updated_at) 
                VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)
                ON CONFLICT(photo_id, jury_identifier) 
                DO UPDATE SET score_aesthetic = excluded.score_aesthetic, 
                              score_theme = excluded.score_theme,
                              updated_at = CURRENT_TIMESTAMP";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$photoId, $juryId, $aesthetic, $theme]);
        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Jury - Tour 1 (Notation)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Open+Sans:wght@400;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <?php include __DIR__ . '/../includes/pwa_loader.php'; ?>
    <style>
        .photo-card:hover .overlay-info {
            opacity: 1;
        }

        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
    </style>
</head>

<body class="bg-gray-50 font-sans pb-20">

    <!-- Header -->
    <header class="bg-[#0A2240] text-white p-4 shadow-md sticky top-0 z-50">
        <div class="container mx-auto flex justify-between items-center">
            <div>
                <h1 class="text-xl font-bold font-title">Espace Jury - Notation (Tour 1)</h1>
                <div class="space-x-4 text-xs mt-1">
                    <a href="qualif.php" class="text-gray-400 hover:text-white transition">1.
                        Qualification</a>
                    <span class="text-[#FF9900] font-bold">2. Notation</span>
                    <a href="ranking.php" class="text-gray-400 hover:text-white transition">3. Classement</a>
                    <a href="sort.php" class="text-gray-400 hover:text-white transition">4. Synthèse</a>
                </div>
            </div>
            <div class="text-right text-xs">
                <div class="font-bold">Jury:
                    <?= htmlspecialchars($_SESSION['jury_email'] ?? $_SESSION['jury_name'] ?? $juryId) ?>
                </div>
                <div id="saveStatus" class="opacity-0 transition-opacity text-green-400">Enregistré</div>
            </div>
        </div>
    </header>

    <div class="container mx-auto px-4 py-8">

        <div class="mb-6 p-4 bg-white border-l-4 border-[#FF9900] shadow-sm rounded">
            <h2 class="font-bold text-[#0A2240] mb-1">Instructions de Notation</h2>
            <p class="text-sm text-gray-600">
                Notez chaque photo sur deux critères (de 1 à 10, décimales autorisées ex: 8.5) :<br>
                1. <strong>Esthétisme</strong> : Qualité technique, composition, lumière, émotion.<br>
                2. <strong>Thème</strong> : Respect du thème, intégration de l'ouvrage, pertinence.<br>
                <em>Vos notes sont enregistrées automatiquement dès que vous quittez le champ.</em>
            </p>
        </div>

        <?php if (empty($photos)): ?>
            <div class="text-center py-20 text-gray-500">
                <i class="fas fa-camera text-6xl mb-4 text-gray-300"></i>
                <p class="text-xl">Aucune photo qualifiée pour le moment.</p>
                <p>Allez dans l'étape "Qualification" pour valider des dossiers.</p>
            </div>
        <?php else: ?>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                <?php foreach ($photos as $p):
                    $pid = $p['id'];
                    $vote = $votesMap[$pid] ?? ['score_aesthetic' => '', 'score_theme' => ''];
                    $thumbSrc = '../photos/thumbs/' . $p['filename_thumb'];
                    $largeSrc = !empty($p['filename_4k']) ? '../photos/display_4k/' . $p['filename_4k'] : '../photos/originals/' . $p['filename_original'];
                    ?>
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden border border-gray-100 flex flex-col h-full">

                        <!-- Image Area -->
                        <div class="relative group cursor-pointer h-64 bg-gray-200"
                            onclick="openModal('<?= $largeSrc ?>', '<?= htmlspecialchars($p['title']) ?>')">
                            <img src="<?= $thumbSrc ?>" class="w-full h-full object-cover">
                            <div
                                class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition flex items-center justify-center">
                                <i
                                    class="fas fa-expand-alt text-white opacity-0 group-hover:opacity-100 transform scale-75 group-hover:scale-100 transition duration-300 text-3xl drop-shadow-lg"></i>
                            </div>
                            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-3">
                                <h3 class="text-white text-sm font-bold truncate">
                                    <?= htmlspecialchars($p['title'] ?: 'Sans Titre') ?>
                                </h3>
                            </div>
                        </div>

                        <!-- Scoring Area -->
                        <div class="p-4 bg-gray-50 flex-grow">
                            <div class="grid grid-cols-2 gap-4">
                                <!-- Esthétisme -->
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Esthétisme /10</label>
                                    <div class="relative">
                                        <input type="number" step="0.1" min="1" max="10"
                                            class="w-full border border-gray-300 rounded p-2 text-center font-bold text-[#0A2240] focus:ring-2 focus:ring-[#FF9900] focus:border-[#FF9900] outline-none transition"
                                            placeholder="-" value="<?= $vote['score_aesthetic'] ?>" id="input_a_<?= $pid ?>"
                                            onchange="saveVote(<?= $pid ?>)">
                                        <i
                                            class="fas fa-eye absolute right-3 top-3 text-gray-300 text-xs pointer-events-none"></i>
                                    </div>
                                </div>

                                <!-- Thème -->
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Thème /10</label>
                                    <div class="relative">
                                        <input type="number" step="0.1" min="1" max="10"
                                            class="w-full border border-gray-300 rounded p-2 text-center font-bold text-[#0A2240] focus:ring-2 focus:ring-[#FF9900] focus:border-[#FF9900] outline-none transition"
                                            placeholder="-" value="<?= $vote['score_theme'] ?>" id="input_t_<?= $pid ?>"
                                            onchange="saveVote(<?= $pid ?>)">
                                        <i
                                            class="fas fa-bullseye absolute right-3 top-3 text-gray-300 text-xs pointer-events-none"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Total Preview (JS calculated) -->
                            <div class="mt-3 text-center flex items-center justify-between">
                                <span class="text-xs text-gray-400 font-semibold italic">
                                    <?php if (!empty($p['instagram']) || !empty($p['linkedin'])): ?>
                                        Social:
                                        <?php if (!empty($p['linkedin'])): ?>
                                            <i class="fab fa-linkedin text-blue-600 ml-1"
                                                title="<?= htmlspecialchars($p['linkedin']) ?>"></i>
                                        <?php endif; ?>
                                        <?php if (!empty($p['instagram'])): ?>
                                            <i class="fab fa-instagram text-pink-500 ml-1"
                                                title="<?= htmlspecialchars($p['instagram']) ?>"></i>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </span>
                                <span class="text-xs text-gray-400 font-semibold">Total :
                                    <span id="total_<?= $pid ?>" class="text-[#0A2240]">
                                        <?= ($vote['score_aesthetic'] && $vote['score_theme']) ? ($vote['score_aesthetic'] + $vote['score_theme']) : '-' ?>
                                    </span> / 20
                                </span>
                            </div>

                            <!-- Promo Actions -->
                            <div class="mt-4 flex gap-2">
                                <?php
                                $jsArgs = [
                                    (int) $pid,
                                    (string) ($p['title'] ?? ''),
                                    (string) ($p['firstname'] . ' ' . $p['lastname']),
                                    (string) ($p['instagram'] ?? ''),
                                    (string) ($p['linkedin'] ?? '')
                                ];
                                $jsCall = "generatePromoText(" . implode(', ', array_map('json_encode', $jsArgs)) . ")";
                                ?>
                                <button onclick="<?= htmlspecialchars($jsCall) ?>"
                                    class="flex-grow bg-slate-100 hover:bg-[#FF9900]/10 text-slate-600 hover:text-[#FF9900] text-[10px] font-bold py-2 rounded-lg transition-all border border-slate-200 hover:border-[#FF9900]/30 flex items-center justify-center gap-2">
                                    <i class="fas fa-bullhorn"></i> Texto
                                </button>

                                <?php
                                // On récupère l'ID de la photo (ici il y a une seule photo par bloc Jury dans cette vue simplifiée)
                                // Note: La structure actuelle semble boucler sur les PARTICIPANTS et non les PHOTOS. 
                                // Je vais chercher l'ID de la première photo du participant.
                                $photoId = null;
                                try {
                                    $stmtPh = $pdo->prepare("SELECT id, is_promo FROM photos WHERE participant_id = ? LIMIT 1");
                                    $stmtPh->execute([$pid]);
                                    $phInfo = $stmtPh->fetch(PDO::FETCH_ASSOC);
                                    $photoId = $phInfo['id'];
                                    $isPromo = $phInfo['is_promo'];
                                } catch (Exception $e) {
                                }
                                ?>

                                <button id="promo_btn_<?= $photoId ?>" onclick="togglePromo(<?= $photoId ?>)"
                                    class="px-3 rounded-lg border transition-all flex items-center justify-center <?= $isPromo ? 'bg-amber-100 border-amber-300 text-amber-600' : 'bg-slate-50 border-slate-200 text-slate-400 hover:text-amber-500' ?>"
                                    title="Marquer pour l'export Social Media">
                                    <i class="<?= $isPromo ? 'fas' : 'far' ?> fa-star"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal Fullscreen -->
    <div id="imageModal"
        class="fixed inset-0 z-[100] hidden bg-black bg-opacity-95 flex items-center justify-center p-4">
        <button class="absolute top-4 right-4 text-white hover:text-red-500 text-4xl z-[110]" onclick="closeModal()">
            <i class="fas fa-times"></i>
        </button>
        <img id="modalImg" src="" class="max-w-full max-h-full rounded shadow-2xl object-contain">
        <div id="modalTitle"
            class="absolute bottom-6 left-0 right-0 text-center text-white text-lg font-bold drop-shadow-md"></div>
    </div>

    <script>
        function openModal(src, title) {
            document.getElementById('modalImg').src = src;
            document.getElementById('modalTitle').textContent = title;
            document.getElementById('imageModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            document.getElementById('imageModal').classList.add('hidden');
            document.getElementById('modalImg').src = '';
            document.body.style.overflow = '';
        }

        // Close on escape key
        document.addEventListener('keydown', function (event) {
            if (event.key === "Escape") {
                closeModal();
            }
        });

        function saveVote(photoId) {
            const aesInput = document.getElementById('input_a_' + photoId);
            const themeInput = document.getElementById('input_t_' + photoId);
            const totalSpan = document.getElementById('total_' + photoId);

            let aes = parseFloat(aesInput.value);
            let theme = parseFloat(themeInput.value);

            // Validation simple UI
            if (isNaN(aes) || aes < 1 || aes > 10) aesInput.classList.add('bg-red-50'); else aesInput.classList.remove('bg-red-50');
            if (isNaN(theme) || theme < 1 || theme > 10) themeInput.classList.add('bg-red-50'); else themeInput.classList.remove('bg-red-50');

            if (!isNaN(aes) && !isNaN(theme)) {
                totalSpan.textContent = (aes + theme).toFixed(1); // update UI
            } else {
                totalSpan.textContent = '-';
            }

            if (!isNaN(aes) && !isNaN(theme) && aes >= 1 && aes <= 10 && theme >= 1 && theme <= 10) {
                // Submit AJAX
                const formData = new FormData();
                formData.append('ajax_vote', '1');
                formData.append('photo_id', photoId);
                formData.append('aesthetic', aes);
                formData.append('theme', theme);

                fetch('home.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.status === 'success') {
                            showSavedStatus();
                        } else {
                            console.error('Error saving:', data.message);
                        }
                    })
                    .catch(err => console.error('Fetch error:', err));
            }
        }

        function showSavedStatus() {
            const el = document.getElementById('saveStatus');
            el.classList.remove('opacity-0');
            setTimeout(() => {
                el.classList.add('opacity-0');
            }, 2000);
        }

        function togglePromo(photoId) {
            const btn = document.getElementById(`promo_btn_${photoId}`);
            const icon = btn.querySelector('i');

            fetch('toggle_promo.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `photo_id=${photoId}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        if (data.is_promo) {
                            btn.classList.add('bg-amber-100', 'border-amber-300', 'text-amber-600');
                            btn.classList.remove('bg-slate-50', 'border-slate-200', 'text-slate-400');
                            icon.classList.replace('far', 'fas');
                        } else {
                            btn.classList.remove('bg-amber-100', 'border-amber-300', 'text-amber-600');
                            btn.classList.add('bg-slate-50', 'border-slate-200', 'text-slate-400');
                            icon.classList.replace('fas', 'far');
                        }
                    }
                });
        }

        function generatePromoText(pid, title, author, insta, linkedin) {
            let text = `Coup de cœur du Jury @CFBR ! 🌊\n\n📸 "${title}" par ${author}\n\nUn magnifique exemple de notre patrimoine hydraulique partagé pour le centenaire.\n\n`;

            if (insta) text += `Instagram: ${insta}\n`;
            if (linkedin) text += `LinkedIn: ${linkedin}\n`;

            text += `\n#Barrages2026 #CFBR100ans #PhotoContest #GénieCivil`;

            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(() => {
                    alert("Texte promotionnel copié dans le presse-papier !\n\nVous pouvez maintenant le coller sur LinkedIn ou Instagram.");
                });
            } else {
                console.log(text);
                alert("Texte généré (voir console si copie impossible) :\n\n" + text);
            }
}
    </script>
</body>

</html>