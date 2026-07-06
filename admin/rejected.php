<?php
// admin/rejected.php
session_start();
require_once __DIR__ . '/../core/db.php';

// Security Check
$isUnlocked = isset($_SESSION['admin_unlocked']) && $_SESSION['admin_unlocked'] === true;
if (!$isUnlocked) {
    header("Location: results.php");
    exit;
}

// Handle restore
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['restore_candidate_id'])) {
    $candidateId = intval($_POST['restore_candidate_id']);
    // Put back to pending qualification
    $stmt = $pdo->prepare("UPDATE participants SET validation_status = 'pending' WHERE id = ?");
    $stmt->execute([$candidateId]);
    
    // Also reset their photos to pending
    $stmtP = $pdo->prepare("UPDATE photos SET status = 'pending' WHERE participant_id = ?");
    $stmtP->execute([$candidateId]);
    
    header("Location: rejected.php");
    exit;
}

// Retrieve rejected candidates
try {
    $stmt = $pdo->query("SELECT p.*, COUNT(ph.id) as photo_count 
                         FROM participants p 
                         LEFT JOIN photos ph ON p.id = ph.participant_id 
                         WHERE p.validation_status = 'rejected' 
                         GROUP BY p.id 
                         ORDER BY p.id ASC");
    $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!$candidates) {
        $candidates = [];
    }
    
    // Enrich with photo details
    foreach ($candidates as &$c) {
        $firstname = trim($c['firstname'] ?? '');
        $lastname = trim($c['lastname'] ?? $c['name']);
        if (stripos($lastname, $firstname) === 0) {
            $c['fullname'] = $lastname;
        } else {
            $c['fullname'] = trim($firstname . ' ' . $lastname);
        }

        $stmtPhotos = $pdo->prepare("SELECT id, filename_thumb, status FROM photos WHERE participant_id = ?");
        $stmtPhotos->execute([$c['id']]);
        $c['photos'] = $stmtPhotos->fetchAll(PDO::FETCH_ASSOC);
    }
    unset($c);

} catch (Exception $e) {
    die("Erreur DB: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administration - Candidatures Exclues</title>
    <?php include __DIR__ . '/../includes/pwa_loader.php'; ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: 'Open Sans', sans-serif; }
        h1, h2, h3 { font-family: 'Montserrat', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 pb-20">

    <header class="bg-[#0A2240] text-white py-5 px-6 shadow-lg sticky top-0 z-50 border-b border-[#1E3A5F]">
        <div class="w-full flex justify-between items-center">
            <h1 class="text-2xl font-bold tracking-tight">Espace Administrateur</h1>
            <a href="results.php" class="bg-white/10 hover:bg-white/20 border border-white/20 text-white font-semibold rounded-lg px-4 py-2 text-sm flex items-center gap-2 transition duration-200">
                <i class="fas fa-arrow-left"></i> Retour aux Résultats
            </a>
        </div>
    </header>

    <main class="container mx-auto p-4 max-w-5xl mt-8">
        <div class="bg-white p-6 rounded-lg shadow-md border border-red-100">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-red-700 flex items-center gap-2">
                        <i class="fas fa-trash-alt text-red-600"></i> Dossiers Exclus Définitivement
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Ces candidatures ont fait l'objet d'un double rejet par le jury et ne participent plus au concours.</p>
                </div>
                <span class="bg-red-100 text-red-800 text-xs font-bold px-3 py-1 rounded-full uppercase">
                    <?= count($candidates) ?> exclus
                </span>
            </div>

            <?php if (empty($candidates)): ?>
                <div class="p-12 rounded-lg text-center text-gray-500 bg-gray-50 border border-dashed border-gray-300">
                    <i class="fas fa-shield-alt text-5xl mb-4 text-emerald-500"></i>
                    <p class="font-medium text-lg text-gray-700">Aucun dossier exclu définitivement.</p>
                    <p class="text-sm text-gray-400 mt-1">Tous les candidats soumis sont soit en cours de modération, soit validés.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($candidates as $candidate): ?>
                        <div class="bg-white rounded-xl shadow border border-gray-200 overflow-hidden flex flex-col justify-between hover:shadow-md transition">
                            <div class="p-4 bg-red-50/50 border-b border-gray-100">
                                <h3 class="font-bold text-base text-gray-800 leading-tight uppercase">
                                    <?= htmlspecialchars($candidate['fullname']) ?>
                                </h3>
                                <div class="text-[10px] text-gray-400 font-bold uppercase mt-1">
                                    <?= htmlspecialchars($candidate['candidacy_type'] === 'corporate' ? 'Entreprise / Association' : 'Individuelle') ?> 
                                    <?= !empty($candidate['company']) ? ' • ' . htmlspecialchars($candidate['company']) : '' ?>
                                </div>
                                <div class="text-[10px] text-gray-500 mt-1 truncate">
                                    <i class="fas fa-envelope mr-1 text-gray-400"></i> <?= htmlspecialchars($candidate['email']) ?>
                                </div>
                            </div>

                            <!-- Thumbs Strip -->
                            <div class="p-4 grid grid-cols-5 gap-2 border-b border-gray-100">
                                <?php foreach ($candidate['photos'] as $p): ?>
                                    <div class="relative">
                                        <img src="../photos/thumbs/<?= $p['filename_thumb'] ?>" alt="Thumb"
                                            class="w-full h-10 object-cover rounded border border-red-300 opacity-60 grayscale">
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Actions -->
                            <div class="p-3 bg-gray-50 flex justify-end">
                                <form method="POST" onsubmit="return confirm('Voulez-vous ré-intégrer ce candidat dans la file de qualification ?')">
                                    <input type="hidden" name="restore_candidate_id" value="<?= $candidate['id'] ?>">
                                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs px-3 py-1.5 rounded transition shadow-sm flex items-center gap-1.5">
                                        <i class="fas fa-undo"></i> Ré-intégrer
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
