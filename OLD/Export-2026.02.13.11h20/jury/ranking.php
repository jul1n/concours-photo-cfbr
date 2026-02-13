<?php
// jury_tour2.php
session_start();
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
    // Score = Sum of Aesthetic + Theme
    // We average if multiple juries (future proof), but for now simplistic sum
    $sql = "
        SELECT p.*, 
               (COALESCE(SUM(v.score_aesthetic), 0) + COALESCE(SUM(v.score_theme), 0)) as total_score,
               COUNT(v.photo_id) as vote_count
        FROM photos p 
        LEFT JOIN jury_votes_analytics v ON p.id = v.photo_id
        GROUP BY p.id 
        HAVING total_score > 0
        ORDER BY total_score DESC 
        LIMIT 10
    ";
    $stm = $pdo->query($sql);
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

    <header class="bg-[#0A2240] text-white p-4 sticky top-0 z-50 shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <div>
                <h1 class="text-xl font-bold font-title">Espace Jury - Classement Final</h1>
                <div class="space-x-4 text-xs mt-1">
                    <a href="qualif.php" class="text-gray-400 hover:text-white transition">1.
                        Qualification</a>
                    <a href="home.php" class="text-gray-400 hover:text-white transition">2. Notation</a>
                    <span class="text-[#FF9900] font-bold">3. Classement</span>
                    <a href="sort.php" class="text-gray-400 hover:text-white transition">4. Synthèse</a>
                </div>
            </div>
            <div class="text-right text-xs">
                <span class="text-xs uppercase bg-[#FF9900] text-[#0A2240] px-2 py-1 rounded font-bold mr-2">Top
                    10</span>
                <span class="font-bold text-white">Jury:
                    <?= htmlspecialchars($_SESSION['jury_email'] ?? $_SESSION['jury_name'] ?? $juryId) ?></span>
            </div>
        </div>
    </header>

    <main class="container mx-auto p-4 max-w-4xl">
        <div class="bg-blue-50 border-l-4 border-[#0A2240] p-4 mb-6 shadow-sm">
            <p class="text-sm text-[#0A2240] font-bold"><i class="fas fa-info-circle mr-1"></i> Instructions :</p>
            <p class="text-sm">Voici les 10 photos les mieux notées. Classez-les par ordre de préférence (de haut en
                bas) pour l'attribution finale des prix.</p>
        </div>

        <form id="rankingForm" action="vote_tour2.php" method="POST">
            <ul id="rankingList" class="space-y-4">
                <?php foreach ($shortlist as $index => $photo):
                    $thumbSrc = '../photos/thumbs/' . $photo['filename_thumb'];
                    $largeSrc = !empty($photo['filename_4k']) ? '../photos/display_4k/' . $photo['filename_4k'] : '../photos/originals/' . $photo['filename_original'];
                    ?>
                    <li class="bg-white p-4 rounded shadow flex items-center space-x-4 cursor-grab active:cursor-grabbing border border-gray-100 transition hover:shadow-md"
                        data-id="<?= $photo['id'] ?>">
                        <div class="font-bold text-3xl text-gray-200 w-12 text-center rank-index font-title">
                            <?= $index + 1 ?>
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

        // Sortable
        const el = document.getElementById('rankingList');
        const sortable = Sortable.create(el, {
            animation: 150,
            onEnd: function (evt) { updateRanks(); }
        });

        function updateRanks() {
            const items = document.querySelectorAll('#rankingList li');
            const order = [];
            items.forEach((item, index) => {
                item.querySelector('.rank-index').textContent = index + 1;
                order.push(item.getAttribute('data-id'));
            });
            document.getElementById('rankingOrder').value = JSON.stringify(order);
        }
        updateRanks();

        document.getElementById('rankingForm').addEventListener('submit', function (e) {
            updateRanks();
            if (!confirm("Confirmez-vous ce classement définitif ?")) {
                e.preventDefault();
            }
        });
    </script>
</body>

</html>