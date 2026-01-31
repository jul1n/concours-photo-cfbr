<?php
// jury_tour1.php
$dbPath = __DIR__ . '/data/concours.db';
try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer les photos (exclure celles déjà votées par cette IP idéalement, mais ici on affiche tout pour demo)
    // On veut afficher les photos qui ont le statut 'pending' ou validé
    $stmt = $pdo->query("SELECT * FROM photos ORDER BY id DESC");
    $photos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Erreur DB");
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
    <script>
        function vote(photoId, value, btn) {
            // Animation Feedback
            const card = document.getElementById('card-' + photoId);
            const overlay = document.getElementById('overlay-' + photoId);

            // Envoyer vote AJAX
            fetch('vote_tour1.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'photo_id=' + photoId + '&value=' + value
            })
                .then(response => response.text())
                .then(data => {
                    // UI Update
                    if (value === 'oui') {
                        overlay.innerHTML = '<i class="fas fa-check-circle text-6xl text-green-500"></i>';
                        overlay.classList.remove('hidden');
                        card.classList.add('border-green-500', 'border-4');
                    } else {
                        overlay.innerHTML = '<i class="fas fa-times-circle text-6xl text-red-500"></i>';
                        overlay.classList.remove('hidden');
                        card.classList.add('opacity-50');
                    }
                });
        }
    </script>
</head>

<body class="bg-gray-100 font-['Open_Sans']">

    <header class="bg-[#0A2240] text-white p-4 sticky top-0 z-50">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold font-['Montserrat']">Espace Jury - Qualification</h1>
            <div>
                <span class="text-xs uppercase bg-[#FF9900] text-[#0A2240] px-2 py-1 rounded font-bold">Tour 1</span>
            </div>
        </div>
    </header>

    <main class="container mx-auto p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($photos as $photo): ?>
                <div id="card-<?= $photo['id'] ?>" class="bg-white rounded-lg shadow-lg overflow-hidden relative group">

                    <!-- Overlay pour confirmation vote -->
                    <div id="overlay-<?= $photo['id'] ?>"
                        class="absolute inset-0 bg-white/80 z-20 hidden flex items-center justify-center"></div>

                    <!-- Image (4K version for display quality, or thumb if heavy) -->
                    <!-- On utilise la version 4K pour que le jury juge la qualité, mais affichée en petit -->
                    <div class="aspect-w-16 aspect-h-9 relative bg-gray-200">
                        <img src="photos/display_4k/<?= $photo['filename_4k'] ?>"
                            class="object-cover w-full h-64 cursor-pointer" onclick="window.open(this.src, '_blank')"
                            title="Cliquez pour agrandir">
                        <?php if ($photo['is_upscale_suspect']): ?>
                            <span
                                class="absolute top-2 right-2 bg-orange-500 text-white text-xs px-2 py-1 rounded font-bold shadow"
                                title="Ratio Poids/Pixels faible">
                                <i class="fas fa-exclamation-triangle"></i> Upscale ?
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="p-4 flex justify-between items-center bg-gray-50">
                        <div class="text-xs text-gray-500">
                            ID: #
                            <?= substr($photo['filename_original'], 0, 8) ?>...
                            <br>
                            <?= $photo['width'] ?> x
                            <?= $photo['height'] ?> px
                        </div>
                        <div class="flex space-x-2">
                            <button onclick="vote(<?= $photo['id'] ?>, 'non', this)"
                                class="bg-white border border-red-500 text-red-500 hover:bg-red-500 hover:text-white px-3 py-1 rounded-full transition">
                                <i class="fas fa-times"></i> Rejeter
                            </button>
                            <button onclick="vote(<?= $photo['id'] ?>, 'oui', this)"
                                class="bg-[#0A2240] text-white hover:bg-[#FF9900] hover:text-[#0A2240] px-4 py-1 rounded-full transition font-bold shadow">
                                <i class="fas fa-heart"></i> Retenir
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

</body>

</html>