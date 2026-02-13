<?php
// dossier.php - Candidate Portal
require_once __DIR__ . '/core/db.php';
require_once __DIR__ . '/includes/analytics.php';

$token = $_GET['token'] ?? '';
if (empty($token)) {
    header("Location: dossier_login.php");
    exit;
}

// Fetch Participant
$stmt = $pdo->prepare("SELECT * FROM participants WHERE validation_token = ? LIMIT 1");
$stmt->execute([$token]);
$p = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$p) {
    die("Lien invalide ou expiré.");
}

$pid = $p['id'];

// Fetch Photos
$stmtPhotos = $pdo->prepare("SELECT * FROM photos WHERE participant_id = ? ORDER BY id ASC");
$stmtPhotos->execute([$pid]);
$photos = $stmtPhotos->fetchAll(PDO::FETCH_ASSOC);

// PDF Path (updated to match validate_email.php storage location)
$pdfPath = "uploads/pdfs/agreement_" . $pid . ".pdf";
$pdfExists = file_exists(__DIR__ . '/' . $pdfPath);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Dossier -
        <?= htmlspecialchars($p['firstname'] . ' ' . $p['lastname']) ?>
    </title>
    <?php include __DIR__ . '/includes/pwa_loader.php'; ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Open+Sans:wght@400;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --font-title: 'Montserrat', sans-serif;
            --font-body: 'Open Sans', sans-serif;
            --deep-blue: #0A2240;
            --accent-gold: #FF9900;
        }

        body {
            font-family: var(--font-body);
            background-color: #F3F4F6;
            color: #333;
        }

        h1,
        h2,
        h3 {
            font-family: var(--font-title);
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen">

    <nav class="bg-[#0A2240] text-white py-4 px-6 shadow-md fixed w-full z-50">
        <div class="max-w-6xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-3">
                <img src="assets/logo_cfbr_100_ans.png" alt="CFBR" class="h-10 bg-white rounded p-1">
                <div>
                    <h1 class="text-lg font-bold leading-none">Mon Dossier</h1>
                    <p class="text-[10px] text-white/50 uppercase tracking-widest mt-0.5">Espace Candidat</p>
                </div>
            </div>
            <a href="index.php" class="text-xs hover:text-[#FF9900] transition-colors"><i
                    class="fas fa-sign-out-alt"></i> Quitter</a>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto pt-24 pb-12 px-4">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Sidebar : Recap Infos -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-[#0A2240] to-blue-900 p-6 text-white text-center">
                        <div
                            class="w-16 h-16 bg-white/10 rounded-full flex items-center justify-center mx-auto mb-3 text-2xl">
                            <?= strtoupper(substr($p['firstname'], 0, 1) . substr($p['lastname'], 0, 1)) ?>
                        </div>
                        <h2 class="font-bold text-xl">
                            <?= htmlspecialchars($p['firstname'] . ' ' . $p['lastname']) ?>
                        </h2>
                        <p class="text-xs text-white/60">
                            <?= htmlspecialchars($p['email']) ?>
                        </p>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">Catégorie</span>
                            <span class="font-bold text-[#0A2240] capitalize">
                                <?= htmlspecialchars($p['candidacy_type']) ?>
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">ID Candidat</span>
                            <span class="font-mono text-[11px] bg-gray-100 px-2 py-0.5 rounded">#
                                <?= str_pad($pid, 4, '0', STR_PAD_LEFT) ?>
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">Photos déposées</span>
                            <span class="font-bold">
                                <?= count($photos) ?> / 5
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">Statut</span>
                            <span
                                class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-bold uppercase"><i
                                    class="fas fa-check-circle"></i> Validé</span>
                        </div>
                    </div>
                </div>

                <!-- Action : PDF -->
                <div class="bg-[#FF9900]/10 border-2 border-dashed border-[#FF9900]/20 rounded-2xl p-6 text-center">
                    <i class="fas fa-file-pdf text-4xl text-[#FF9900] mb-3"></i>
                    <h3 class="font-bold text-slate-800 mb-4">Votre récépissé officiel</h3>
                    <?php if ($pdfExists): ?>
                        <a href="<?= $pdfPath ?>" target="_blank"
                            class="block w-full bg-[#0A2240] text-white py-3 rounded-xl font-bold hover:shadow-lg transition-shadow flex items-center justify-center gap-2">
                            <i class="fas fa-download"></i> Télécharger le PDF
                        </a>
                    <?php else: ?>
                        <p class="text-xs text-amber-600 bg-amber-50 p-3 rounded-lg"><i
                                class="fas fa-exclamation-circle"></i> Le PDF est en cours de génération ou indisponible.
                            Veuillez contacter l'administrateur.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Main : Photo Gallery -->
            <div class="lg:col-span-2 space-y-8">
                <div class="flex items-center gap-3">
                    <div class="w-2 h-8 bg-[#FF9900] rounded-full"></div>
                    <h2 class="text-2xl font-bold text-[#0A2240]">Ma Galerie Photos</h2>
                </div>

                <div class="space-y-6">
                    <?php foreach ($photos as $i => $photo): ?>
                        <div
                            class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col md:flex-row">
                            <div class="w-full md:w-2/5 aspect-video md:aspect-auto bg-slate-100 relative group">
                                <img src="photos/display_4k/<?= htmlspecialchars($photo['filename_4k']) ?>"
                                    alt="<?= htmlspecialchars($photo['title']) ?>" class="w-full h-full object-cover">
                                <div
                                    class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-all cursor-zoom-in flex items-center justify-center opacity-0 group-hover:opacity-100">
                                    <a href="photos/originals/<?= htmlspecialchars($photo['filename_original']) ?>"
                                        target="_blank"
                                        class="bg-white/90 p-2 rounded-full text-[#0A2240] text-sm font-bold shadow-xl">
                                        <i class="fas fa-expand"></i> Voir l'original
                                    </a>
                                </div>
                            </div>
                            <div class="p-6 flex-grow space-y-4">
                                <div class="flex justify-between items-start">
                                    <span
                                        class="bg-[#0A2240] text-white text-[10px] px-2 py-0.5 rounded font-bold uppercase">Photo
                                        <?= $i + 1 ?>
                                    </span>
                                    <span class="text-[10px] text-gray-400 font-mono">
                                        <?= htmlspecialchars($photo['width']) ?>x
                                        <?= htmlspecialchars($photo['height']) ?> px
                                    </span>
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg text-[#0A2240]">
                                        <?= htmlspecialchars($photo['title'] ?: 'Sans titre') ?>
                                    </h3>
                                    <p class="text-xs text-gray-500 italic"><i class="fas fa-map-marker-alt"></i>
                                        <?= htmlspecialchars($photo['location'] ?: 'Non précisé') ?>
                                    </p>
                                </div>
                                <div class="bg-slate-50 p-3 rounded-lg border-l-2 border-slate-200">
                                    <p class="text-sm text-gray-600 leading-relaxed italic">
                                        <?= nl2br(htmlspecialchars($photo['description'] ?: 'Aucune description fournie.')) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div
                    class="bg-blue-50 p-6 rounded-2xl border border-blue-100 text-blue-800 text-sm leading-relaxed flex gap-4">
                    <i class="fas fa-info-circle text-xl mt-1 opacity-50"></i>
                    <p>Cet espace est en <strong>lecture seule</strong>. Conformément au règlement, aucune modification
                        de votre dossier n'est possible après validation finale pour garantir l'équité entre les
                        candidats.</p>
                </div>
            </div>

        </div>

    </main>

    <footer class="text-center py-12 text-gray-400 text-xs border-t border-gray-200 bg-white">
        <p>© 2026 Comité Français des Barrages et Réservoirs - Centenaire</p>
    </footer>

</body>

</html>