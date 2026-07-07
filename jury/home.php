<?php
// jury_tour1.php
require_once __DIR__ . '/../core/auth.php';
require_jury();
require_once __DIR__ . '/../includes/analytics.php';
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
            WHERE part.validation_status = 'approved' AND p.status = 'approved'
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
    csrf_check();
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
        error_log('[vote] ' . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Enregistrement impossible']);
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
    <?php
    $activeTab = 'home';
    $headerTitle = "Espace Jury - Notation (Tour 1)";
    include __DIR__ . '/header.php';
    ?>

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
                    $aesVal = $vote['score_aesthetic'];
                    $themeVal = $vote['score_theme'];
                    $aesClass = ($aesVal === '' || $aesVal === null) ? 'bg-amber-50 border-amber-300 placeholder-amber-400 focus:ring-[#FF9900]' : 'bg-emerald-50/30 border-emerald-500/30 text-emerald-900 focus:ring-emerald-500';
                    $themeClass = ($themeVal === '' || $themeVal === null) ? 'bg-amber-50 border-amber-300 placeholder-amber-400 focus:ring-[#FF9900]' : 'bg-emerald-50/30 border-emerald-500/30 text-emerald-900 focus:ring-emerald-500';
                    $isComplete = ($aesVal !== '' && $aesVal !== null && $themeVal !== '' && $themeVal !== null);
                    $btnClass = $isComplete ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-[#0A2240] hover:bg-[#1E3A5F]';
                    $btnText = $isComplete ? 'Vote validé' : 'Note incomplète';
                    $btnIcon = $isComplete ? 'fas fa-check-circle' : 'far fa-circle';
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
                                            class="w-full border rounded p-2 text-center font-bold outline-none transition <?= $aesClass ?>"
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
                                            class="w-full border rounded p-2 text-center font-bold outline-none transition <?= $themeClass ?>"
                                            placeholder="-" value="<?= $vote['score_theme'] ?>" id="input_t_<?= $pid ?>"
                                            onchange="saveVote(<?= $pid ?>)">
                                        <i
                                            class="fas fa-bullseye absolute right-3 top-3 text-gray-300 text-xs pointer-events-none"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions Row -->
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
                                <!-- Validate Note & Show Total -->
                                <button id="btn_status_<?= $pid ?>" onclick="saveVote(<?= $pid ?>)" 
                                        class="flex-grow text-white font-bold py-2 rounded-lg text-xs transition flex items-center justify-center gap-1.5 shadow-sm <?= $btnClass ?>">
                                    <i id="icon_status_<?= $pid ?>" class="<?= $btnIcon ?>"></i> <span id="text_status_<?= $pid ?>"><?= $btnText ?></span> (<span id="total_<?= $pid ?>"><?= ($vote['score_aesthetic'] && $vote['score_theme']) ? ($vote['score_aesthetic'] + $vote['score_theme']) : '-' ?></span>/20)
                                </button>

                                <!-- Share Social -->
                                <button onclick="<?= htmlspecialchars($jsCall) ?>"
                                    class="bg-slate-100 hover:bg-[#FF9900]/10 text-slate-600 hover:text-[#FF9900] px-3 py-2 rounded-lg transition-all border border-slate-200 hover:border-[#FF9900]/30 flex items-center justify-center gap-2"
                                    title="Générer le texte de partage LinkedIn & Réseaux Sociaux">
                                    <i class="fab fa-linkedin text-sky-700 text-sm"></i>
                                    <i class="fas fa-share-alt text-slate-500 text-sm"></i>
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
        window.CSRF_TOKEN = "<?= htmlspecialchars(csrf_token(), ENT_QUOTES) ?>";
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
            const btn = document.getElementById('btn_status_' + photoId);
            const icon = document.getElementById('icon_status_' + photoId);
            const text = document.getElementById('text_status_' + photoId);

            let aes = parseFloat(aesInput.value);
            let theme = parseFloat(themeInput.value);

            // Cap inputs to valid range [1, 10]
            if (!isNaN(aes)) {
                if (aes > 10) { aes = 10; aesInput.value = 10; }
                if (aes < 1) { aes = 1; aesInput.value = 1; }
            }
            if (!isNaN(theme)) {
                if (theme > 10) { theme = 10; themeInput.value = 10; }
                if (theme < 1) { theme = 1; themeInput.value = 1; }
            }

            // Validation simple UI & dynamic colors for inputs
            if (isNaN(aes) || aes < 1 || aes > 10) {
                aesInput.classList.remove('bg-emerald-50/30', 'border-emerald-500/30', 'text-emerald-900');
                aesInput.classList.add('bg-amber-50', 'border-amber-300');
            } else {
                aesInput.classList.remove('bg-amber-50', 'border-amber-300');
                aesInput.classList.add('bg-emerald-50/30', 'border-emerald-500/30', 'text-emerald-900');
            }

            if (isNaN(theme) || theme < 1 || theme > 10) {
                themeInput.classList.remove('bg-emerald-50/30', 'border-emerald-500/30', 'text-emerald-900');
                themeInput.classList.add('bg-amber-50', 'border-amber-300');
            } else {
                themeInput.classList.remove('bg-amber-50', 'border-amber-300');
                themeInput.classList.add('bg-emerald-50/30', 'border-emerald-500/30', 'text-emerald-900');
            }

            if (!isNaN(aes) && !isNaN(theme)) {
                totalSpan.textContent = (aes + theme).toFixed(1); // update UI
            } else {
                totalSpan.textContent = '-';
            }

            if (!isNaN(aes) && !isNaN(theme) && aes >= 1 && aes <= 10 && theme >= 1 && theme <= 10) {
                // Vote completed & valid -> Green indicator
                btn.classList.remove('bg-[#0A2240]', 'hover:bg-[#1E3A5F]');
                btn.classList.add('bg-emerald-600', 'hover:bg-emerald-700');
                icon.className = 'fas fa-check-circle';
                text.textContent = 'Vote validé';

                // Submit AJAX
                const formData = new FormData();
                formData.append('ajax_vote', '1');
                formData.append('csrf_token', window.CSRF_TOKEN);
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
            } else {
                // Incomplete or invalid -> Dark blue indicator
                btn.classList.remove('bg-emerald-600', 'hover:bg-emerald-700');
                btn.classList.add('bg-[#0A2240]', 'hover:bg-[#1E3A5F]');
                icon.className = 'far fa-circle';
                text.textContent = 'Note incomplète';
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
                body: `photo_id=${photoId}&csrf_token=${encodeURIComponent(window.CSRF_TOKEN)}`
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