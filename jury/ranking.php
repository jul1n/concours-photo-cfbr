<?php
// jury_tour2.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['jury_logged_in']) || $_SESSION['jury_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../core/db.php';

// --- Logic ---

$juryId = $_SERVER['REMOTE_ADDR'];
if (isset($_SESSION['jury_email']))
    $juryId = $_SESSION['jury_email'];

try {
    // LOGIQUE SHORT-LIST (TOP 10 based on scores)
    // If the jury has already voted in Tour 2, we load their custom order first.
    $sql = "
        SELECT p.*, 
               (COALESCE(SUM(v.score_aesthetic), 0) + COALESCE(SUM(v.score_theme), 0)) as total_score,
               COUNT(v.photo_id) as vote_count,
               v2.rank as jury_rank
        FROM photos p 
        LEFT JOIN jury_votes_analytics v ON p.id = v.photo_id
        LEFT JOIN votes_tour2 v2 ON p.id = v2.photo_id AND v2.jury_ip = ?
        GROUP BY p.id 
        HAVING total_score > 0
        ORDER BY CASE WHEN v2.rank IS NOT NULL THEN 0 ELSE 1 END ASC, v2.rank ASC, total_score DESC 
        LIMIT 10
    ";
    $stm = $pdo->prepare($sql);
    $stm->execute([$juryId]);
    $shortlist = $stm->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Erreur DB: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Jury - Tour 2 (Classement Final)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Open+Sans:wght@400;600&display=swap"
        rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <?php include __DIR__ . '/../includes/pwa_loader.php'; ?>
    <style>
        .sortable-ghost {
            opacity: 0.4;
            background-color: #F8F8F8;
        }

        .sortable-drag {
            cursor: grabbing;
        }
    </style>
</head>

<body class="bg-gray-100 font-sans pb-20">

    <?php
    $activeTab = 'ranking';
    $headerTitle = "Espace Jury - Classement Final";
    include __DIR__ . '/header.php';
    ?>

    <main class="container mx-auto p-4 max-w-4xl">
        <div class="bg-blue-50 border-l-4 border-[#0A2240] p-4 mb-6 shadow-sm">
            <p class="text-sm text-[#0A2240] font-bold"><i class="fas fa-info-circle mr-1"></i> Instructions :</p>
            <p class="text-sm">Voici les 10 photos les mieux notées. Classez-les par ordre de préférence (de haut en
                bas) pour l'attribution finale des prix.</p>
        </div>

        <form id="rankingForm" action="vote_r.php" method="POST">
            <ul id="rankingList" class="space-y-4">
                <?php foreach ($shortlist as $index => $photo):
                    $thumbSrc = '../photos/thumbs/' . $photo['filename_thumb'];
                    $largeSrc = !empty($photo['filename_4k']) ? '../photos/display_4k/' . $photo['filename_4k'] : '../photos/originals/' . $photo['filename_original'];
                    ?>
                    <li class="bg-white p-4 rounded shadow flex items-center space-x-4 cursor-grab active:cursor-grabbing border border-gray-100 transition hover:shadow-md"
                        data-id="<?= $photo['id'] ?>">
                        <div class="flex flex-col items-center">
                            <span class="text-[10px] font-bold text-gray-400 mb-1 uppercase tracking-wider">Rang</span>
                            <input type="number" min="1" max="10" value="<?= $index + 1 ?>" 
                                class="w-14 text-center border border-gray-300 rounded font-bold py-1.5 text-base rank-input focus:ring-2 focus:ring-[#FF9900] focus:border-[#FF9900] outline-none" 
                                onchange="moveRowByInput(this)">
                        </div>

                        <!-- Thumbnail with Click to Expand -->
                        <div class="relative group cursor-pointer"
                            onclick="openModal('<?= $largeSrc ?>', '<?= htmlspecialchars($photo['title']) ?>')">
                            <img src="<?= $thumbSrc ?>" class="h-24 w-24 object-cover rounded border border-gray-200">
                            <div
                                class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition flex items-center justify-center rounded">
                                <i class="fas fa-search-plus text-white opacity-0 group-hover:opacity-100"></i>
                            </div>
                        </div>

                        <div class="flex-grow">
                            <h3 class="font-bold text-[#0A2240] text-lg">
                                <?= !empty($photo['title']) ? htmlspecialchars($photo['title']) : 'SANS TITRE' ?>
                            </h3>
                            <p class="text-xs text-gray-500 mb-1">
                                <i class="fas fa-star text-yellow-400 mr-1"></i>Score Notation :
                                <strong><?= $photo['total_score'] ?></strong> pts
                            </p>
                            <?php if (!empty($photo['description'])): ?>
                                <p class="text-xs text-gray-400 italic line-clamp-1">
                                    <?= htmlspecialchars($photo['description']) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        <i class="fas fa-grip-lines text-gray-300 text-xl"></i>
                    </li>
                <?php endforeach; ?>
            </ul>

            <input type="hidden" name="ranking_order" id="rankingOrder">

            <button type="submit"
                class="fixed bottom-6 right-6 bg-[#FF9900] text-[#0A2240] px-8 py-4 rounded-full font-bold text-xl shadow-2xl hover:bg-[#0A2240] hover:text-white transition transform hover:scale-105 z-40 border-4 border-white">
                <i class="fas fa-trophy mr-2"></i> Valider mon Classement
            </button>
        </form>
    </main>

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
        // Copy Paste Modal Logic
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
        document.addEventListener('keydown', function (event) { if (event.key === "Escape") closeModal(); });

        // Sortable drag-and-drop
        const el = document.getElementById('rankingList');
        const sortable = Sortable.create(el, {
            animation: 150,
            onEnd: function (evt) { updateRanks(); }
        });

        function updateRanks() {
            const items = document.querySelectorAll('#rankingList li');
            const order = [];
            items.forEach((item, index) => {
                const rankInput = item.querySelector('.rank-input');
                if (rankInput) {
                    rankInput.value = index + 1;
                }
                order.push(item.getAttribute('data-id'));
            });
            document.getElementById('rankingOrder').value = JSON.stringify(order);
        }
        updateRanks();

        function moveRowByInput(input) {
            const list = document.getElementById('rankingList');
            const items = Array.from(list.querySelectorAll('li'));
            const currentItem = input.closest('li');
            const currentIndex = items.indexOf(currentItem);
            
            let newIndex = parseInt(input.value) - 1;
            if (isNaN(newIndex) || newIndex < 0) newIndex = 0;
            if (newIndex >= items.length) newIndex = items.length - 1;
            
            if (newIndex !== currentIndex) {
                if (newIndex > currentIndex) {
                    list.insertBefore(currentItem, list.children[newIndex + 1] || null);
                } else {
                    list.insertBefore(currentItem, list.children[newIndex]);
                }
            }
            updateRanks();
        }

        document.getElementById('rankingForm').addEventListener('submit', function (e) {
            updateRanks();
            if (!confirm("Confirmez-vous ce classement définitif ?")) {
                e.preventDefault();
            }
        });
    </script>
</body>

</html>