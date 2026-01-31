<?php
// jury_tour2.php
$dbPath = __DIR__ . '/data/concours.db';

try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // LOGIQUE SHORT-LIST AUTOMATIQUE
    // Sélectionner les photos ayant le plus de "oui" au tour 1
    // Disons top 10 pour l'exemple
    $sql = "
        SELECT p.*, COUNT(v.id) as score 
        FROM photos p 
        LEFT JOIN votes_tour1 v ON p.id = v.photo_id AND v.vote_value = 'oui'
        GROUP BY p.id 
        ORDER BY score DESC 
        LIMIT 10
    ";
    $stm = $pdo->query($sql);
    $shortlist = $stm->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Erreur DB");
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
    <!-- SortableJS pour le Drag & Drop -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
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

<body class="bg-gray-100 font-['Open_Sans'] pb-20">

    <header class="bg-[#0A2240] text-white p-4 sticky top-0 z-50">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold font-['Montserrat']">Espace Jury - Classement Final</h1>
            <div>
                <span class="text-xs uppercase bg-[#FF9900] text-[#0A2240] px-2 py-1 rounded font-bold">Tour 2</span>
            </div>
        </div>
    </header>

    <main class="container mx-auto p-4 max-w-4xl">
        <div class="bg-blue-50 border-l-4 border-[#0A2240] p-4 mb-6">
            <p class="text-sm text-[#0A2240] font-bold">Instructions :</p>
            <p class="text-sm">Classez les photos par ordre de préférence (de haut en bas) en les glissant-déposant. Le
                1er recevra 10 points, le 2ème 9 points, etc.</p>
        </div>

        <form id="rankingForm" action="vote_tour2.php" method="POST">
            <ul id="rankingList" class="space-y-4">
                <?php foreach ($shortlist as $index => $photo): ?>
                    <li class="bg-white p-4 rounded shadow flex items-center space-x-4 cursor-grab active:cursor-grabbing"
                        data-id="<?= $photo['id'] ?>">
                        <div class="font-bold text-2xl text-gray-300 w-8 text-center rank-index">
                            <?= $index + 1 ?>
                        </div>
                        <img src="photos/thumbs/<?= $photo['filename_thumb'] ?>"
                            class="h-20 w-20 object-cover rounded border">
                        <div class="flex-grow">
                            <h3 class="font-bold text-[#0A2240]">Photo #
                                <?= substr($photo['filename_original'], 0, 8) ?>
                            </h3>
                            <p class="text-xs text-gray-500">Score qualif :
                                <?= $photo['score'] ?> votes
                            </p>
                        </div>
                        <i class="fas fa-grip-lines text-gray-400"></i>
                    </li>
                <?php endforeach; ?>
            </ul>

            <!-- Input caché pour envoyer l'ordre -->
            <input type="hidden" name="ranking_order" id="rankingOrder">

            <button type="submit"
                class="fixed bottom-6 right-6 bg-[#FF9900] text-[#0A2240] px-8 py-4 rounded-full font-bold text-xl shadow-2xl hover:bg-[#0A2240] hover:text-white transition transform hover:scale-105">
                <i class="fas fa-save mr-2"></i> Valider mon Classement
            </button>
        </form>
    </main>

    <script>
        const el = document.getElementById('rankingList');
        const sortable = Sortable.create(el, {
            animation: 150,
            onEnd: function (evt) {
                updateRanks();
            }
        });

        function updateRanks() {
            const items = document.querySelectorAll('#rankingList li');
            const order = [];
            items.forEach((item, index) => {
                // Update numéro visuel
                item.querySelector('.rank-index').textContent = index + 1;
                // Capture ID
                order.push(item.getAttribute('data-id'));
            });
            document.getElementById('rankingOrder').value = JSON.stringify(order);
        }

        // Init initial order
        updateRanks();

        document.getElementById('rankingForm').addEventListener('submit', function (e) {
            updateRanks(); // Security
            if (!confirm("Confirmez-vous ce classement définitif ?")) {
                e.preventDefault();
            }
        });
    </script>
</body>

</html>