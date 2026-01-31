<?php
// admin_results.php
$dbPath = __DIR__ . '/data/concours.db';

try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // CALCUL DES GAGNANTS (Somme des points du Tour 2)
    $sql = "
        SELECT p.*, SUM(v.points) as total_points, pa.name as author_name, pa.email as author_email
        FROM photos p
        JOIN votes_tour2 v ON p.id = v.photo_id
        JOIN participants pa ON p.participant_id = pa.id
        GROUP BY p.id
        ORDER BY total_points DESC
    ";

    $stmt = $pdo->query($sql);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Erreur DB");
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Résultats Finaux - Concours CFBR</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Open+Sans:wght@400;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="bg-gray-100 font-['Open_Sans']">

    <header class="bg-[#0A2240] text-white p-4">
        <div class="container mx-auto">
            <h1 class="text-2xl font-bold font-['Montserrat']">🏆 Résultats Officiels</h1>
        </div>
    </header>

    <main class="container mx-auto p-4 max-w-5xl">
        <h2 class="text-3xl font-bold text-[#0A2240] mb-8 text-center">Le Podium</h2>

        <?php if (count($results) > 0): ?>
            <div class="flex flex-col md:flex-row justify-center items-end gap-4 mb-16">
                <!-- 2ème -->
                <?php if (isset($results[1])): ?>
                    <div class="order-2 md:order-1 text-center">
                        <div class="relative">
                            <img src="photos/display_4k/<?= $results[1]['filename_4k'] ?>"
                                class="h-48 w-auto rounded-lg shadow-lg border-4 border-gray-300">
                            <div
                                class="absolute -top-4 -right-4 bg-gray-300 text-white w-10 h-10 rounded-full flex items-center justify-center font-bold text-xl shadow">
                                2</div>
                        </div>
                        <p class="mt-2 font-bold text-gray-600">
                            <?= htmlspecialchars($results[1]['author_name']) ?>
                        </p>
                        <p class="text-sm text-gray-500">
                            <?= $results[1]['total_points'] ?> pts
                        </p>
                    </div>
                <?php endif; ?>

                <!-- 1er -->
                <div class="order-1 md:order-2 text-center transform md:-translate-y-8">
                    <div class="relative">
                        <img src="photos/display_4k/<?= $results[0]['filename_4k'] ?>"
                            class="h-64 w-auto rounded-lg shadow-2xl border-4 border-[#FF9900]">
                        <div
                            class="absolute -top-6 -right-6 bg-[#FF9900] text-white w-14 h-14 rounded-full flex items-center justify-center font-bold text-3xl shadow">
                            1</div>
                    </div>
                    <p class="mt-4 font-bold text-[#0A2240] text-xl">
                        <?= htmlspecialchars($results[0]['author_name']) ?>
                    </p>
                    <p class="text-[#FF9900] font-bold">
                        <?= $results[0]['total_points'] ?> pts
                    </p>
                    <a href="photos/originals/<?= $results[0]['filename_original'] ?>" download
                        class="inline-block mt-2 text-xs bg-[#0A2240] text-white px-2 py-1 rounded">Télécharger Original</a>
                </div>

                <!-- 3ème -->
                <?php if (isset($results[2])): ?>
                    <div class="order-3 text-center">
                        <div class="relative">
                            <img src="photos/display_4k/<?= $results[2]['filename_4k'] ?>"
                                class="h-40 w-auto rounded-lg shadow-lg border-4 border-yellow-700">
                            <div
                                class="absolute -top-4 -right-4 bg-yellow-700 text-white w-10 h-10 rounded-full flex items-center justify-center font-bold text-xl shadow">
                                3</div>
                        </div>
                        <p class="mt-2 font-bold text-gray-600">
                            <?= htmlspecialchars($results[2]['author_name']) ?>
                        </p>
                        <p class="text-sm text-gray-500">
                            <?= $results[2]['total_points'] ?> pts
                        </p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="bg-white p-8 rounded shadow-lg overflow-x-auto">
                <h3 class="text-xl font-bold mb-4">Classement Complet</h3>
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="p-3 border">Rang</th>
                            <th class="p-3 border">Photo</th>
                            <th class="p-3 border">Auteur</th>
                            <th class="p-3 border">Email</th>
                            <th class="p-3 border">Points</th>
                            <th class="p-3 border">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $i => $row): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="p-3 border font-bold">
                                    <?= $i + 1 ?>
                                </td>
                                <td class="p-3 border"><a href="photos/display_4k/<?= $row['filename_4k'] ?>" target="_blank"
                                        class="text-blue-600 hover:underline">Voir</a></td>
                                <td class="p-3 border">
                                    <?= htmlspecialchars($row['author_name']) ?>
                                </td>
                                <td class="p-3 border text-sm text-gray-500">
                                    <?= htmlspecialchars($row['author_email']) ?>
                                </td>
                                <td class="p-3 border font-bold text-[#0A2240]">
                                    <?= $row['total_points'] ?>
                                </td>
                                <td class="p-3 border">
                                    <a href="photos/originals/<?= $row['filename_original'] ?>" download
                                        class="text-green-600 hover:underline"><i class="fas fa-download"></i> Original</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php else: ?>
            <div class="text-center py-20 bg-white rounded shadow">
                <p class="text-xl text-gray-500">Aucun résultat pour le moment. Le jury n'a pas encore voté.</p>
            </div>
        <?php endif; ?>

    </main>
</body>

</html>