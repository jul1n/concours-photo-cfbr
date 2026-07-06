<?php
// jury_confirm_rejection.php
session_start();
// Basic Auth Check
// if (!isset($_SESSION['jury_logged_in'])) { header("Location: jury_login.php"); exit; }

require_once __DIR__ . '/../core/db.php';

try {
    // Current Jury ID (Mocked for now as we don't have full multi-user sessions yet)
    $currentJuryId = 1;

    // Retrieve candidates marked as 'pre_rejected'
    // Ideally we should filter WHERE jury_vote_1_by != currentJuryId to ensure 2 different people context.
    // For this demo, we show all.
    $stmt = $pdo->query("SELECT p.*, COUNT(ph.id) as photo_count 
                         FROM participants p 
                         LEFT JOIN photos ph ON p.id = ph.participant_id 
                         WHERE p.validation_status = 'pre_rejected' 
                         GROUP BY p.id 
                         ORDER BY p.id ASC");
    $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Initialiser les tableaux pour éviter les erreurs si vide
    if (!$candidates)
        $candidates = [];

    // Enrich with photo statuses
    foreach ($candidates as &$c) {
        $firstname = trim($c['firstname'] ?? '');
        $lastname = trim($c['lastname'] ?? $c['name']);
        if (stripos($lastname, $firstname) === 0) {
            $c['name'] = $lastname;
        } else {
            $c['name'] = trim($firstname . ' ' . $lastname);
        }

        $stmtPhotos = $pdo->prepare("SELECT id, filename_thumb, status FROM photos WHERE participant_id = ?");
        $stmtPhotos->execute([$c['id']]);
        $c['photos'] = $stmtPhotos->fetchAll(PDO::FETCH_ASSOC);
    }
    unset($c);

    // Handle Actions (POST)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $candidateId = $_POST['candidate_id'];
        $action = $_POST['action'];

        if ($action === 'confirm_reject') {
            // Confirm Rejection -> Status 'rejected'
            $stmt = $pdo->prepare("UPDATE participants SET validation_status = 'rejected', jury_vote_2_by = ? WHERE id = ?");
            $stmt->execute([$currentJuryId, $candidateId]);
        } elseif ($action === 'revoke') {
            // Revoke Rejection -> Status 'approved' (Back to pool) or 'pending'? 
            // Let's say we save it, so 'approved'.
            $stmt = $pdo->prepare("UPDATE participants SET validation_status = 'approved', jury_vote_2_by = ? WHERE id = ?");
            $stmt->execute([$currentJuryId, $candidateId]);
        }

        header("Location: confirm_rejection.php");
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
    <title>Jury - Dossiers Rejetés (Seconde Validation)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Open+Sans:wght@400;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <?php include __DIR__ . '/../includes/pwa_loader.php'; ?>
    <style>
        body {
            font-family: 'Open Sans', sans-serif;
        }
        h1, h3 {
            font-family: 'Montserrat', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-100 font-sans pb-20">

    <?php
    $activeTab = 'qualif';
    $headerTitle = "Dossiers en attente de Rejet";
    include __DIR__ . '/header.php';
    ?>

    <main class="container mx-auto p-4 max-w-5xl mt-6">
        <div class="mb-6">
            <a href="qualif.php" class="text-sm text-gray-500 hover:text-gray-800 font-semibold">
                <i class="fas fa-arrow-left mr-1"></i> Retour à la Qualification
            </a>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold text-[#0A2240] mb-4">Dossiers Rejetés / Suspendus</h2>
            <div class="mb-4 text-sm text-gray-600 bg-yellow-50 p-3 rounded border border-yellow-200">
                <i class="fas fa-info-circle mr-2"></i> Ces dossiers ont été envoyés ici car tout ou partie de leurs clichés ont été invalidés. 
                Une seconde validation permet d'exclure définitivement le dossier (Exclure) ou de le repêcher (Repêcher).
            </div>

            <?php if (empty($candidates)): ?>
                <div class="p-8 rounded text-center text-gray-500 bg-gray-50 border border-dashed border-gray-300">
                    <i class="fas fa-check-circle text-4xl mb-3 text-green-500"></i>
                    <p class="font-medium">Aucun dossier en attente de confirmation de rejet.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($candidates as $candidate): ?>
                    <div class="bg-white rounded-lg shadow-xl overflow-hidden border border-red-200 flex flex-col justify-between">
                        <!-- Header Dossier -->
                        <div class="bg-red-50 text-red-900 p-4 flex justify-between items-center">
                            <div>
                                <h3 class="font-bold text-base leading-tight">
                                    <?= htmlspecialchars($candidate['name']) ?>
                                </h3>
                                <div class="text-[10px] text-red-700 font-bold uppercase mt-1">
                                    <?= htmlspecialchars($candidate['candidacy_type'] === 'corporate' ? 'Entreprise / Association' : 'Individuelle') ?>
                                </div>
                            </div>
                            <div class="text-[9px] bg-red-600 text-white px-2 py-0.5 rounded font-bold uppercase">
                                À réviser
                            </div>
                        </div>

                        <!-- Thumbs Strip -->
                        <div class="p-4 grid grid-cols-5 gap-2 border-b border-gray-100">
                            <?php foreach ($candidate['photos'] as $p): ?>
                                <div class="relative group">
                                    <img src="../photos/thumbs/<?= $p['filename_thumb'] ?>" alt="Thumb"
                                        class="w-full h-12 object-cover rounded border <?= $p['status'] === 'rejected' ? 'border-red-400 opacity-60 grayscale' : 'border-emerald-400' ?>">
                                    <div class="absolute -top-1 -right-1 rounded-full w-4 h-4 flex items-center justify-center text-[8px] font-bold text-white shadow <?= $p['status'] === 'rejected' ? 'bg-red-500' : 'bg-emerald-500' ?>">
                                        <i class="fas <?= $p['status'] === 'rejected' ? 'fa-times' : 'fa-check' ?>"></i>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Actions -->
                        <div class="p-4 bg-gray-50 border-t border-gray-100 flex justify-between space-x-2">
                            <form method="POST" class="w-1/2">
                                <input type="hidden" name="candidate_id" value="<?= $candidate['id'] ?>">
                                <input type="hidden" name="action" value="confirm_reject">
                                <button type="submit"
                                    onclick="return confirm('ACTION IRRÉVERSIBLE : Ce candidat sera définitivement exclu. Confirmer ?')"
                                    class="w-full bg-red-600 text-white hover:bg-black px-4 py-2 rounded font-bold transition shadow-lg">
                                    <i class="fas fa-ban mr-1"></i> Exclure
                                </button>
                            </form>

                            <form method="POST" class="w-1/2">
                                <input type="hidden" name="candidate_id" value="<?= $candidate['id'] ?>">
                                <input type="hidden" name="action" value="revoke">
                                <button type="submit"
                                    class="w-full bg-green-500 text-white hover:bg-green-600 px-4 py-2 rounded font-bold transition shadow-lg">
                                    <i class="fas fa-life-ring mr-1"></i> Repêcher
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