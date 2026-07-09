<?php
// dossier.php - Candidate Portal
require_once __DIR__ . '/core/db.php';
require_once __DIR__ . '/core/auth.php';
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
$pdfPath = get_participant_pdf_path($p);
$pdfExists = file_exists($pdfPath);

// Deduplicate candidate name
$displayName = $p['firstname'] . ' ' . $p['lastname'];
$avatarInitials = strtoupper(substr($p['firstname'], 0, 1) . substr($p['lastname'], 0, 1));
if ($p['candidacy_type'] === 'corporate' || !empty($p['company'])) {
    if ($p['firstname'] === 'Corporate') {
        $displayName = $p['lastname'];
        $avatarInitials = strtoupper(substr($p['lastname'], 0, 2));
    } elseif (stripos($p['lastname'], $p['firstname']) === 0) {
        $displayName = $p['lastname'];
        $avatarInitials = strtoupper(substr($p['lastname'], 0, 2));
    }
} else {
    if (stripos($p['lastname'], $p['firstname']) === 0) {
        $displayName = $p['lastname'];
        $avatarInitials = strtoupper(substr($p['lastname'], 0, 2));
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Dossier -
        <?= htmlspecialchars($displayName) ?>
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
                        <h2 class="font-bold text-xl">
                            <?= htmlspecialchars($displayName) ?>
                        </h2>
                        <p class="text-xs text-white/60">
                            <?= htmlspecialchars($p['email']) ?>
                        </p>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">Catégorie</span>
                            <span class="font-bold text-[#0A2240]">
                                <?= ($p['candidacy_type'] === 'corporate') ? "Entreprise / Association" : (($p['candidacy_type'] === 'individual') ? "Individuelle" : htmlspecialchars($p['candidacy_type'])) ?>
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
                        
                        <?php
                        // Determine current step index and status label
                        $currentStep = 1; // 1: Soumis, 2: Règlement Signé, 3: Validé par le Jury, 4: Notation en cours
                        $statusLabel = "Dossier Soumis";
                        $statusClass = "bg-blue-100 text-blue-800";
                        
                        if ($p['is_verified']) {
                            $currentStep = 2;
                            $statusLabel = "Règlement Signé";
                            $statusClass = "bg-amber-100 text-amber-800";
                            
                            if ($p['validation_status'] === 'approved') {
                                $currentStep = 3;
                                $statusLabel = "Validé par le Jury";
                                $statusClass = "bg-green-100 text-green-800";
                            } elseif ($p['validation_status'] === 'pre_rejected' || $p['validation_status'] === 'rejected') {
                                $currentStep = 2; // Keep at signed but flag error
                                $statusLabel = "Non retenu";
                                $statusClass = "bg-red-100 text-red-800";
                            }
                        }
                        ?>
                        
                        <div class="flex items-center justify-between text-sm pb-2 border-b border-gray-100">
                            <span class="text-gray-500">Statut actuel</span>
                            <span class="px-2.5 py-0.5 rounded-full <?= $statusClass ?> text-[10px] font-bold uppercase">
                                <?= $statusLabel ?>
                            </span>
                        </div>

                        <!-- Stepper / Timeline -->
                        <div class="pt-2">
                            <span class="text-xs text-gray-400 uppercase tracking-wider font-bold block mb-3">Suivi de la candidature</span>
                            
                            <!-- Timeline Steps -->
                            <div class="space-y-4 text-xs">
                                <!-- Step 1: Soumission -->
                                <div class="flex items-start gap-3">
                                    <div class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0 <?= ($currentStep >= 1) ? 'bg-[#0A2240] text-white' : 'bg-gray-200 text-gray-500' ?>">
                                        <i class="fas <?= ($currentStep > 1) ? 'fa-check text-[9px]' : 'fa-upload text-[7px]' ?>"></i>
                                    </div>
                                    <div class="flex-grow">
                                        <p class="font-bold text-[#0A2240]">Dossier Soumis</p>
                                        <p class="text-[10px] text-gray-400">Photos téléchargées avec succès.</p>
                                    </div>
                                </div>

                                <!-- Step 2: Signature -->
                                <div class="flex items-start gap-3">
                                    <div class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0 <?= ($currentStep >= 2) ? 'bg-[#0A2240] text-white' : 'bg-gray-200 text-gray-500' ?>">
                                        <i class="fas <?= ($currentStep > 2) ? 'fa-check text-[9px]' : 'fa-signature text-[7px]' ?>"></i>
                                    </div>
                                    <div class="flex-grow">
                                        <p class="font-bold <?= ($currentStep >= 2) ? 'text-[#0A2240]' : 'text-gray-400' ?>">Règlement Signé</p>
                                        <p class="text-[10px] text-gray-400">Signature électronique validée.</p>
                                    </div>
                                </div>

                                <!-- Step 3: Modération / Validation -->
                                <div class="flex items-start gap-3">
                                    <div class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0 <?= ($currentStep >= 3) ? 'bg-[#0A2240] text-white' : (($p['validation_status'] === 'rejected' || $p['validation_status'] === 'pre_rejected') ? 'bg-red-500 text-white' : 'bg-gray-200 text-gray-500') ?>">
                                        <i class="fas <?= ($currentStep >= 3) ? 'fa-check text-[9px]' : (($p['validation_status'] === 'rejected' || $p['validation_status'] === 'pre_rejected') ? 'fa-times text-[9px]' : 'fa-shield-alt text-[7px]') ?>"></i>
                                    </div>
                                    <div class="flex-grow">
                                        <p class="font-bold <?= ($currentStep >= 3) ? 'text-[#0A2240]' : (($p['validation_status'] === 'rejected' || $p['validation_status'] === 'pre_rejected') ? 'text-red-600' : 'text-gray-400') ?>">
                                            <?= ($p['validation_status'] === 'rejected' || $p['validation_status'] === 'pre_rejected') ? 'Candidature Non Retenue' : 'Validé par le Jury' ?>
                                        </p>
                                        <p class="text-[10px] text-gray-400">Vérification de conformité technique.</p>
                                    </div>
                                </div>

                                <!-- Step 4: Évaluation / Notation -->
                                <div class="flex items-start gap-3">
                                    <div class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0 <?= ($currentStep >= 3 && $p['validation_status'] === 'approved') ? 'bg-emerald-500 text-white animate-pulse' : 'bg-gray-200 text-gray-500' ?>">
                                        <i class="fas fa-star text-[7px]"></i>
                                    </div>
                                    <div class="flex-grow">
                                        <p class="font-bold <?= ($currentStep >= 3 && $p['validation_status'] === 'approved') ? 'text-emerald-600' : 'text-gray-400' ?>">Notation en cours</p>
                                        <p class="text-[10px] text-gray-400">Les jurés évaluent vos clichés.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action : PDF -->
                <div class="bg-[#FF9900]/10 border-2 border-dashed border-[#FF9900]/20 rounded-2xl p-6 text-center">
                    <i class="fas fa-file-pdf text-4xl text-[#FF9900] mb-3"></i>
                    <h3 class="font-bold text-slate-800 mb-4">Votre récépissé officiel</h3>
                    <?php if ($pdfExists): ?>
                        <a href="download_pdf.php?token=<?= urlencode($token) ?>"
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