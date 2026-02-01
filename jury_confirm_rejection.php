<?php
// jury_confirm_rejection.php
session_start();
// Basic Auth Check
// if (!isset($_SESSION['jury_logged_in'])) { header("Location: jury_login.php"); exit; }

require_once 'db_connect.php';

try {
    // Current Jury ID (Mocked for now as we don't have full multi-user sessions yet)
    $currentJuryId = 1;

    // Retrieve candidates marked as 'pre_rejected'
    // Ideally we should filter WHERE jury_vote_1_by != currentJuryId to ensure 2 different people context.
    // For this demo, we show all.
    $stmt = $pdo->query("SELECT p.*, COUNT(ph.id) as photo_count, GROUP_CONCAT(ph.filename_thumb) as thumbs 
                         FROM participants p 
                         LEFT JOIN photos ph ON p.id = ph.participant_id 
                         WHERE p.validation_status = 'pre_rejected' 
                         GROUP BY p.id 
                         ORDER BY p.id ASC");
    $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Initialiser les tableaux pour éviter les erreurs si vide
    if (!$candidates)
        $candidates = [];

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

        header("Location: jury_confirm_rejection.php");
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
    <title>Jury - Confirmation Rejet</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Open+Sans:wght@400;600&display=swap"
        rel="stylesheet">
</head>

<body class="bg-gray-100 font-sans">

    <header class="bg-red-800 text-white p-4 shadow-md mb-8">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold font-title"><i class="fas fa-gavel mr-2"></i>Contre-Expertise (Rejets)</h1>
            <div class="space-x-4">
                <a href="jury_tour1.php" class="text-sm font-bold hover:text-orange-300 transition-colors">
                    <i class="fas fa-arrow-left mr-1"></i> Retour Validation
                </a>
                <a href="index.php" class="text-sm font-bold hover:text-orange-300 transition-colors">
                    <i class="fas fa-home mr-1"></i> Accueil
                </a>
            </div>
        </div>
    </header>

    <div class="container mx-auto px-4 py-8">

        <?php if (empty($candidates)): ?>
            <div class="bg-white p-8 rounded shadow text-center text-gray-500">
                <i class="fas fa-check-circle text-4xl mb-4 text-green-500"></i>
                <p>Aucun dossier en attente de confirmation de rejet.</p>
            </div>
        <?php else: ?>

            <div class="mb-4 text-sm text-gray-600 bg-yellow-50 p-3 rounded border border-yellow-200">
                <i class="fas fa-info-circle mr-2"></i> Ces dossiers ont été pré-rejetés. Une seconde validation est
                nécessaire pour les exclure définitivement ("Confirmer le Rejet") ou les repêcher ("Repêcher").
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($candidates as $candidate): ?>
                    <div class="bg-white rounded-lg shadow-xl overflow-hidden border-2 border-red-100">
                        <!-- Header Dossier -->
                        <div class="bg-red-50 text-red-900 p-4 flex justify-between items-center">
                            <div>
                                <h3 class="font-bold text-lg">
                                    <?= htmlspecialchars($candidate['name']) ?>
                                </h3>
                                <div class="text-xs text-red-700">
                                    <?= htmlspecialchars($candidate['category']) ?> •
                                    <?= $candidate['photo_count'] ?> Photos
                                </div>
                            </div>
                            <div class="text-xs bg-white text-red-900 px-2 py-1 rounded border border-red-200">
                                Pré-Rejet
                            </div>
                        </div>

                        <!-- Thumbs Strip -->
                        <div
                            class="p-4 grid grid-cols-3 gap-2 grayscale opacity-80 hover:grayscale-0 hover:opacity-100 transition">
                            <?php
                            $thumbs = explode(',', $candidate['thumbs']);
                            foreach ($thumbs as $thumb):
                                if (empty($thumb))
                                    continue;
                                ?>
                                <img src="photos/thumbs/<?= $thumb ?>" alt="Thumb"
                                    class="w-full h-24 object-cover rounded border border-gray-200">
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